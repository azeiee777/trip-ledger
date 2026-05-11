<?php

namespace Modules\Core\Services;

use Modules\Trip\Models\Trip;
use Modules\Expense\Models\ExpenseSplit;
use Modules\Settlement\Models\Settlement;
use Illuminate\Support\Facades\DB;

class SettlementEngine
{
    /**
     * Calculate each active member's net balance for a trip.
     *
     * Positive balance  → member underpaid  (they owe money)
     * Negative balance  → member overpaid   (others owe them)
     */
    public function calculateBalances(Trip $trip): array
    {
        $members = $trip->activeMembers()->with(['expensesPaid', 'splits'])->get();

        $paid = [];
        $owed = [];

        foreach ($members as $member) {
            $paid[$member->id] = 0;
            $owed[$member->id] = 0;
        }

        // Sum what each member paid (approved group expenses only)
        $groupExpenses = $trip->expenses()
            ->where('split_type', '!=', 'personal')
            ->where('approval_status', 'approved')
            ->withTrashed(false)
            ->get();

        foreach ($groupExpenses as $expense) {
            $id = $expense->paid_by_member_id;
            if (array_key_exists($id, $paid)) {
                $paid[$id] += (float) $expense->amount;
            }
        }

        // Sum each member's share from expense_splits
        $expenseIds = $groupExpenses->pluck('id');
        $splits = ExpenseSplit::whereIn('expense_id', $expenseIds)
            ->where('is_excluded', false)
            ->get();

        foreach ($splits as $split) {
            $id = $split->trip_member_id;
            if (array_key_exists($id, $owed)) {
                $owed[$id] += (float) $split->share_amount;
            }
        }

        $balances = [];
        foreach ($members as $member) {
            // positive = owes, negative = is owed
            $balances[$member->id] = round($owed[$member->id] - $paid[$member->id], 2);
        }

        return $balances;
    }

    /**
     * Debt simplification (greedy algorithm).
     * Returns minimum number of transactions to settle all debts.
     */
    public function simplifyDebts(array $balances): array
    {
        $creditors = []; // members who are owed (negative balance → others owe them)
        $debtors   = []; // members who owe (positive balance)

        foreach ($balances as $memberId => $balance) {
            if ($balance < -0.01) {
                $creditors[] = ['id' => $memberId, 'amount' => abs($balance)];
            } elseif ($balance > 0.01) {
                $debtors[] = ['id' => $memberId, 'amount' => $balance];
            }
        }

        // Sort descending to minimize transactions
        usort($creditors, fn ($a, $b) => $b['amount'] <=> $a['amount']);
        usort($debtors,   fn ($a, $b) => $b['amount'] <=> $a['amount']);

        $transactions = [];
        $i = 0;
        $j = 0;

        while ($i < count($debtors) && $j < count($creditors)) {
            $amount = min($debtors[$i]['amount'], $creditors[$j]['amount']);

            $transactions[] = [
                'payer_id'    => $debtors[$i]['id'],
                'receiver_id' => $creditors[$j]['id'],
                'amount'      => round($amount, 2),
            ];

            $debtors[$i]['amount']   -= $amount;
            $creditors[$j]['amount'] -= $amount;

            if ($debtors[$i]['amount'] < 0.01) {
                $i++;
            }
            if ($creditors[$j]['amount'] < 0.01) {
                $j++;
            }
        }

        return $transactions;
    }

    /**
     * Persist computed settlements to DB.
     * - Skips pairs already fully covered by paid settlements
     * - Updates partial settlements in place (preserves paid_amount history)
     * - Creates new pending for unsettled transactions
     * - Cleans up stale pending records no longer in the transaction list
     */
    public function generateSettlements(Trip $trip): void
    {
        DB::transaction(function () use ($trip) {
            $balances     = $this->calculateBalances($trip);
            $transactions = $this->simplifyDebts($balances);

            $handledKeys = [];

            foreach ($transactions as $txn) {
                $key           = $txn['payer_id'] . '-' . $txn['receiver_id'];
                $handledKeys[] = $key;
                $newAmount     = (float) $txn['amount'];

                $existing = Settlement::where('trip_id', $trip->id)
                    ->where('payer_member_id', $txn['payer_id'])
                    ->where('receiver_member_id', $txn['receiver_id'])
                    ->get();

                $totalPaid = (float) $existing->sum('paid_amount');
                $remaining = round($newAmount - $totalPaid, 2);

                // Remove stale pending records for this pair
                $existing->where('status', 'pending')->each->delete();

                if ($remaining <= 0.01) {
                    continue; // Already fully covered by prior payments
                }

                $partial = $existing->firstWhere('status', 'partial');

                if ($partial) {
                    // Keep partial in place, just refresh the total amount
                    $partial->update(['amount' => $newAmount]);
                } else {
                    Settlement::create([
                        'trip_id'            => $trip->id,
                        'payer_member_id'    => $txn['payer_id'],
                        'receiver_member_id' => $txn['receiver_id'],
                        'amount'             => $newAmount,
                        'paid_amount'        => 0,
                        'status'             => 'pending',
                    ]);
                }
            }

            // Remove pending settlements for pairs no longer in the transaction list
            Settlement::where('trip_id', $trip->id)
                ->where('status', 'pending')
                ->get()
                ->each(function ($s) use ($handledKeys) {
                    $key = $s->payer_member_id . '-' . $s->receiver_member_id;
                    if (! in_array($key, $handledKeys)) {
                        $s->delete();
                    }
                });
        });
    }
}

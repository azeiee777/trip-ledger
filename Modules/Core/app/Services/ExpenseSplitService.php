<?php

namespace Modules\Core\Services;

use Modules\Trip\Models\Trip;
use Modules\Trip\Models\TripMember;
use Modules\Trip\Models\CarGroup;
use Modules\Expense\Models\Expense;
use Modules\Expense\Models\ExpenseSplit;
use Illuminate\Support\Facades\DB;

class ExpenseSplitService
{
    /**
     * Create an expense with its splits inside a single transaction.
     * Handles equal, custom, percentage, per_car, and personal split types.
     */
    public function createWithSplits(Trip $trip, array $data, array $splits = [], ?CarGroup $carGroup = null): Expense
    {
        return DB::transaction(function () use ($trip, $data, $splits, $carGroup) {
            $expense = Expense::create($data);

            if ($expense->split_type === 'personal') {
                // Personal expenses have no group splits
                return $expense;
            }

            $this->createSplits($expense, $trip, $splits, $carGroup);
            $trip->refreshTotals();

            return $expense;
        });
    }

    /**
     * Update an expense and regenerate its splits.
     */
    public function updateWithSplits(Expense $expense, Trip $trip, array $data, array $splits = [], ?CarGroup $carGroup = null): Expense
    {
        return DB::transaction(function () use ($expense, $trip, $data, $splits, $carGroup) {
            $expense->update($data);
            ExpenseSplit::where('expense_id', $expense->id)->delete();

            if ($expense->split_type !== 'personal') {
                $this->createSplits($expense, $trip, $splits, $carGroup);
            }

            $trip->refreshTotals();
            return $expense->fresh();
        });
    }

    private function createSplits(Expense $expense, Trip $trip, array $customSplits, ?CarGroup $carGroup): void
    {
        match ($expense->split_type) {
            'equal'      => $this->equalSplit($expense, $trip, $customSplits),
            'custom'     => $this->customSplit($expense, $customSplits),
            'percentage' => $this->percentageSplit($expense, $customSplits),
            'per_car'    => $this->perCarSplit($expense, $carGroup),
            default      => null,
        };
    }

    private function equalSplit(Expense $expense, Trip $trip, array $selectedMemberIds = []): void
    {
        // If specific members were selected from the form, use only those.
        // Otherwise fall back to all active members.
        $query = $trip->activeMembers();
        if (! empty($selectedMemberIds)) {
            $query->whereIn('id', array_keys($selectedMemberIds));
        }
        $members = $query->get();
        $count   = $members->count();

        if ($count === 0) return;

        $share     = round((float) $expense->amount / $count, 2);
        $remainder = round((float) $expense->amount - ($share * $count), 2);

        foreach ($members as $i => $member) {
            ExpenseSplit::create([
                'expense_id'     => $expense->id,
                'trip_member_id' => $member->id,
                'share_amount'   => $i === 0 ? $share + $remainder : $share,
                'is_excluded'    => false,
            ]);
        }
    }

    private function customSplit(Expense $expense, array $splits): void
    {
        foreach ($splits as $memberId => $amount) {
            ExpenseSplit::create([
                'expense_id'     => $expense->id,
                'trip_member_id' => (int) $memberId,
                'share_amount'   => round((float) $amount, 2),
                'is_excluded'    => (float) $amount == 0,
            ]);
        }
    }

    private function percentageSplit(Expense $expense, array $splits): void
    {
        foreach ($splits as $memberId => $pct) {
            $amount = round((float) $expense->amount * ((float) $pct / 100), 2);
            ExpenseSplit::create([
                'expense_id'       => $expense->id,
                'trip_member_id'   => (int) $memberId,
                'share_amount'     => $amount,
                'share_percentage' => (float) $pct,
                'is_excluded'      => $pct == 0,
            ]);
        }
    }

    private function perCarSplit(Expense $expense, ?CarGroup $carGroup): void
    {
        if ($carGroup === null) {
            return;
        }

        $memberIds = $carGroup->member_ids ?? [];
        $count     = count($memberIds);

        if ($count === 0) return;

        $share = round((float) $expense->amount / $count, 2);

        foreach ($memberIds as $memberId) {
            ExpenseSplit::create([
                'expense_id'     => $expense->id,
                'trip_member_id' => (int) $memberId,
                'share_amount'   => $share,
                'is_excluded'    => false,
            ]);
        }
    }
}

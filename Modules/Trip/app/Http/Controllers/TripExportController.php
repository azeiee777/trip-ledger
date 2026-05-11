<?php

namespace Modules\Trip\Http\Controllers;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Modules\Trip\Models\Trip;

class TripExportController extends Controller
{
    public function pdf(Request $request, Trip $trip)
    {
        $this->authorize('view', $trip);

        $trip->load([
            'activeMembers.user',
            'expenses.category',
            'expenses.paidByMember',
            'expenses.splits.member',
            'expenses.carGroup',
            'settlements.payer',
            'settlements.receiver',
        ]);

        // Only approved group expenses
        $expenses = $trip->expenses
            ->filter(fn ($e) => $e->split_type !== 'personal' && $e->approval_status === 'approved')
            ->values();

        // Amount each member paid
        $memberPaid = [];
        foreach ($expenses as $exp) {
            $id = $exp->paid_by_member_id;
            $memberPaid[$id] = ($memberPaid[$id] ?? 0) + (float) $exp->amount;
        }

        // Share each member owes (from splits)
        $memberShares = $trip->activeMembers->mapWithKeys(fn ($m) => [$m->id => 0.0])->toArray();
        foreach ($expenses as $exp) {
            foreach ($exp->splits as $split) {
                if (! $split->is_excluded && array_key_exists($split->trip_member_id, $memberShares)) {
                    $memberShares[$split->trip_member_id] += (float) $split->share_amount;
                }
            }
        }

        $pdf = Pdf::loadView('trip::export-pdf', compact(
            'trip', 'expenses', 'memberPaid', 'memberShares'
        ));

        $pdf->setPaper('a4', 'portrait');

        $filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $trip->name) . '_Expense_Report.pdf';

        return $pdf->download($filename);
    }
}

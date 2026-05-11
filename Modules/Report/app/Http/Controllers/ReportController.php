<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Trip\Models\Trip;

class ReportController extends Controller
{
    public function show(Trip $trip)
    {
        $this->authorize('view', $trip);

        $trip->load([
            'activeMembers.user',
            'expenses.category',
            'expenses.paidByMember',
            'expenses.splits.member',
            'settlements.payer',
            'settlements.receiver',
        ]);

        $approvedExpenses = $trip->expenses
            ->filter(fn ($e) => $e->approval_status === 'approved' && $e->split_type !== 'personal');

        $totalByMember = $approvedExpenses
            ->groupBy('paid_by_member_id')
            ->map(fn ($exps) => $exps->sum('amount'));

        return view('report::show', compact('trip', 'approvedExpenses', 'totalByMember'));
    }
}

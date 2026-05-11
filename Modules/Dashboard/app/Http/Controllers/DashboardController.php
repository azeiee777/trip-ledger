<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Trip\Models\Trip;
use Modules\Trip\Models\TripMember;
use Modules\Expense\Models\Expense;
use Modules\Expense\Models\ExpenseSplit;
use Modules\Settlement\Models\Settlement;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user   = $request->user();
        $period = $request->get('period', 'all');

        [$fromDate, $toDate] = $this->resolveDateRange($period, $request);

        // --- Trip IDs this user is part of ---
        $createdTripIds = Trip::where('user_id', $user->id)->pluck('id');

        $joinedTripIds = Trip::whereHas('members', fn ($q) =>
            $q->where('user_id', $user->id)->where('invite_status', 'accepted')
        )->where('user_id', '!=', $user->id)->pluck('id');

        $myTripIds = $createdTripIds->merge($joinedTripIds)->unique()->values();

        // --- Member IDs for this user across all trips ---
        $myMemberIds = TripMember::where('user_id', $user->id)->pluck('id');

        // --- Expense base query ---
        $expBase = fn () => Expense::whereIn('trip_id', $myTripIds)
            ->where('split_type', '!=', 'personal')
            ->where('approval_status', 'approved')
            ->when($fromDate, fn ($q) => $q->where('expense_date', '>=', $fromDate))
            ->when($toDate,   fn ($q) => $q->where('expense_date', '<=', $toDate));

        // --- Period-filtered trip IDs (for My Trips card counts) ---
        $filteredMyTripIds = Trip::whereIn('id', $myTripIds)
            ->when($fromDate, fn ($q) => $q->where('start_date', '>=', $fromDate))
            ->when($toDate,   fn ($q) => $q->where('start_date', '<=', $toDate))
            ->pluck('id');

        $createdCount   = $createdTripIds->intersect($filteredMyTripIds)->count();
        $joinedCount    = $joinedTripIds->intersect($filteredMyTripIds)->count();
        $totalTrips     = $createdCount + $joinedCount;
        $activeCount    = Trip::whereIn('id', $filteredMyTripIds)->whereIn('status', ['upcoming', 'ongoing'])->count();
        $completedCount = Trip::whereIn('id', $filteredMyTripIds)->where('status', 'completed')->count();

        // --- Key stats ---
        $totalSpent = (float) $expBase()->sum('amount');

        $mySpend = (float) ExpenseSplit::whereHas('expense', fn ($q) =>
            $q->whereIn('trip_id', $myTripIds)
              ->where('split_type', '!=', 'personal')
              ->where('approval_status', 'approved')
              ->when($fromDate, fn ($q) => $q->where('expense_date', '>=', $fromDate))
              ->when($toDate,   fn ($q) => $q->where('expense_date', '<=', $toDate))
        )->whereIn('trip_member_id', $myMemberIds)->where('is_excluded', false)->sum('share_amount');

        // I Owe / Owed to Me — scoped to trips with expenses in the selected period
        $tripsWithExpensesInPeriod = $expBase()->distinct()->pluck('trip_id');
        $memberIdsInPeriod = TripMember::where('user_id', $user->id)
            ->whereIn('trip_id', $tripsWithExpensesInPeriod)
            ->pluck('id');

        $iOwe     = (float) Settlement::whereIn('payer_member_id', $memberIdsInPeriod)->whereIn('status', ['pending','partial'])->get()->sum('remaining_amount');
        $owedToMe = (float) Settlement::whereIn('receiver_member_id', $memberIdsInPeriod)->whereIn('status', ['pending','partial'])->get()->sum('remaining_amount');

        // --- Top 5 trips by spend (respects date filter) ---
        $spendByTrip = $expBase()
            ->selectRaw('trip_id, SUM(amount) as period_spend')
            ->groupBy('trip_id')
            ->pluck('period_spend', 'trip_id');

        $topTrips = Trip::whereIn('id', $spendByTrip->keys())
            ->with(['activeMembers'])
            ->get()
            ->map(function ($trip) use ($spendByTrip) {
                $trip->period_spend = (float) ($spendByTrip[$trip->id] ?? 0);
                return $trip;
            })
            ->sortByDesc('period_spend')
            ->take(5)
            ->values();

        $maxTripSpend = $topTrips->max('period_spend') ?: 1;

        // --- Top 5 travel partners ---
        $myTripMembershipIds = TripMember::where('user_id', $user->id)
            ->where('invite_status', 'accepted')
            ->pluck('trip_id');

        $topPartners = TripMember::whereIn('trip_id', $myTripMembershipIds)
            ->where('user_id', '!=', $user->id)
            ->where('invite_status', 'accepted')
            ->whereNotNull('user_id')
            ->with('user')
            ->get()
            ->groupBy('user_id')
            ->map(fn ($g) => [
                'user'   => $g->first()->user,
                'member' => $g->first(),
                'trips'  => $g->pluck('trip_id')->unique()->count(),
            ])
            ->sortByDesc('trips')
            ->take(5)
            ->values();

        // --- Monthly spend: last 6 months ---
        $monthlySpend = collect(range(5, 0))->map(function ($i) use ($myTripIds) {
            $m = now()->subMonths($i);
            return [
                'label' => $m->format('M'),
                'total' => (float) Expense::whereIn('trip_id', $myTripIds)
                    ->where('split_type', '!=', 'personal')
                    ->where('approval_status', 'approved')
                    ->whereBetween('expense_date', [$m->copy()->startOfMonth(), $m->copy()->endOfMonth()])
                    ->sum('amount'),
            ];
        });

        // --- Category breakdown ---
        $categoryBreakdown = $expBase()->with('category')->get()
            ->groupBy('category_id')
            ->map(fn ($g) => [
                'name'  => $g->first()->category?->name ?? 'Uncategorised',
                'color' => $g->first()->category?->color ?? '#9CA3AF',
                'total' => (float) $g->sum('amount'),
            ])
            ->sortByDesc('total')
            ->values()
            ->take(7);

        // --- Trip type breakdown ---
        $tripTypeBreakdown = Trip::whereIn('id', $myTripIds)
            ->selectRaw('trip_type, COUNT(*) as count')
            ->groupBy('trip_type')
            ->pluck('count', 'trip_type');

        // --- Active trips ---
        $activeTrips = Trip::whereIn('id', $myTripIds)
            ->whereIn('status', ['upcoming', 'ongoing'])
            ->with(['activeMembers'])
            ->latest('start_date')
            ->take(4)
            ->get();

        // --- Recent expenses feed (respects date filter) ---
        $recentExpenses = Expense::whereIn('trip_id', $myTripIds)
            ->when($fromDate, fn ($q) => $q->where('expense_date', '>=', $fromDate))
            ->when($toDate,   fn ($q) => $q->where('expense_date', '<=', $toDate))
            ->with(['trip', 'paidByMember', 'category'])
            ->latest('expense_date')
            ->take(5)
            ->get();

        // --- Pending approvals (admin only) ---
        $pendingApprovals = Expense::whereIn('trip_id', $createdTripIds)
            ->where('approval_status', 'pending_approval')
            ->with(['trip', 'paidByMember'])
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard::index', compact(
            'period', 'fromDate', 'toDate',
            'createdTripIds', 'joinedTripIds',
            'createdCount', 'joinedCount', 'totalTrips',
            'activeCount', 'completedCount',
            'totalSpent', 'mySpend', 'iOwe', 'owedToMe',
            'topTrips', 'maxTripSpend',
            'topPartners',
            'monthlySpend',
            'categoryBreakdown', 'tripTypeBreakdown',
            'activeTrips', 'recentExpenses',
            'pendingApprovals',
        ));
    }

    private function resolveDateRange(string $period, Request $request): array
    {
        return match ($period) {
            'month'   => [now()->startOfMonth(),    now()->endOfDay()],
            'quarter' => [now()->startOfQuarter(),  now()->endOfDay()],
            'year'    => [now()->startOfYear(),      now()->endOfDay()],
            'custom'  => [
                $request->from ? Carbon::parse($request->from)->startOfDay() : null,
                $request->to   ? Carbon::parse($request->to)->endOfDay()     : null,
            ],
            default   => [null, null],
        };
    }
}

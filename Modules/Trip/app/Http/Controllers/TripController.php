<?php

namespace Modules\Trip\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Modules\Trip\Models\Trip;
use Modules\Trip\Models\TripMember;
use Modules\Core\Models\Category;

class TripController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $tripIds = Trip::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhereHas('members', fn ($q2) => $q2->where('user_id', $user->id)->where('invite_status', 'accepted'));
        })->pluck('id');

        $trips = Trip::whereIn('id', $tripIds)
            ->with(['members', 'activeMembers'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->type, fn ($q, $t) => $q->where('trip_type', $t))
            ->latest()
            ->paginate(12);

        $tripCounts = Trip::whereIn('id', $tripIds)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status='ongoing'   THEN 1 ELSE 0 END) as ongoing,
                SUM(CASE WHEN status='upcoming'  THEN 1 ELSE 0 END) as upcoming,
                SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed
            ")
            ->first();

        return view('trip::index', compact('trips', 'tripCounts'));
    }

    public function create()
    {
        return view('trip::create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'destination' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'trip_type'   => 'required|in:road_trip,flight,local,international,pilgrimage,family',
            'status'      => 'required|in:upcoming,ongoing,completed,archived',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
        ]);

        $trip = Trip::create([...$validated, 'user_id' => $request->user()->id]);

        // Creator is always admin with accepted status
        TripMember::create([
            'trip_id'        => $trip->id,
            'user_id'        => $request->user()->id,
            'role'           => 'admin',
            'invite_status'  => 'accepted',
            'is_active'      => true,
            'joined_at'      => now(),
        ]);

        $trip->refreshTotals();

        return redirect()->route('trips.show', $trip)->with('success', 'Trip created successfully!');
    }

    public function show(Request $request, Trip $trip)
    {
        $this->authorize('view', $trip);

        $trip->load([
            'activeMembers.user',
            'expenses.category',
            'expenses.paidByMember',
            'expenses.splits',
            'carGroups',
            'settlements.payer',
            'settlements.receiver',
            'stops.expense',
        ]);

        $currentMember = $trip->members()
            ->where('user_id', $request->user()->id)
            ->where('invite_status', 'accepted')
            ->first();

        $isAdmin = $currentMember?->role === 'admin';

        // Personal expenses are only visible to their creator and the trip admin
        if (! $isAdmin) {
            $trip->setRelation('expenses', $trip->expenses->filter(
                fn ($e) => $e->split_type !== 'personal' || $e->created_by === $request->user()->id
            )->values());
        }

        $categories      = Category::all();
        $categorySpends  = $trip->groupExpenses()
            ->where('approval_status', 'approved')
            ->with('category')
            ->get()
            ->groupBy('category_id')
            ->map(fn ($grp) => [
                'name'   => $grp->first()->category?->name ?? 'Uncategorised',
                'color'  => $grp->first()->category?->color ?? '#9CA3AF',
                'total'  => $grp->sum('amount'),
            ])->values();

        // Actual share each member owes (from expense_splits, approved group expenses only)
        $memberShares = $trip->activeMembers->mapWithKeys(fn ($m) => [$m->id => 0.0])->toArray();
        foreach ($trip->expenses as $exp) {
            if ($exp->split_type === 'personal' || $exp->approval_status !== 'approved') {
                continue;
            }
            foreach ($exp->splits as $split) {
                if (! $split->is_excluded && array_key_exists($split->trip_member_id, $memberShares)) {
                    $memberShares[$split->trip_member_id] += (float) $split->share_amount;
                }
            }
        }

        return view('trip::show', compact('trip', 'categories', 'categorySpends', 'currentMember', 'isAdmin', 'memberShares'));
    }

    public function edit(Trip $trip)
    {
        $this->authorize('update', $trip);
        return view('trip::edit', compact('trip'));
    }

    public function update(Request $request, Trip $trip)
    {
        $this->authorize('update', $trip);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'destination' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'trip_type'   => 'required|in:road_trip,flight,local,international,pilgrimage,family',
            'status'      => 'required|in:upcoming,ongoing,completed,archived',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
        ]);

        $trip->update($validated);

        return redirect()->route('trips.show', $trip)->with('success', 'Trip updated!');
    }

    public function destroy(Trip $trip)
    {
        $this->authorize('delete', $trip);
        $trip->delete();

        return redirect()->route('trips.index')->with('success', 'Trip archived.');
    }

    public function joinViaInvite(Trip $trip, string $token)
    {
        if ($trip->invite_token !== $token) {
            abort(403, 'Invalid or expired invite link.');
        }

        // Check if invite was for a specific member and has expired
        $pendingMember = $trip->members()
            ->where('invite_status', 'pending')
            ->where(function ($q) {
                $q->whereNull('invite_token_expires_at')
                  ->orWhere('invite_token_expires_at', '>', now());
            })
            ->where(fn ($q) => $q->where('user_id', auth()->id())
                ->orWhere('invite_email', auth()->user()->email))
            ->first();

        if ($pendingMember) {
            $pendingMember->update([
                'user_id'             => auth()->id(),
                'invite_status'       => 'accepted',
                'invite_accepted_at'  => now(),
                'is_active'           => true,
                'joined_at'           => now(),
            ]);
            $trip->refreshTotals();
            return redirect()->route('trips.show', $trip)->with('success', 'You have joined the trip!');
        }

        // Fallback: add as new member if not already in trip
        $alreadyMember = $trip->members()->where('user_id', auth()->id())->exists();
        if (! $alreadyMember) {
            TripMember::create([
                'trip_id'       => $trip->id,
                'user_id'       => auth()->id(),
                'role'          => 'member',
                'invite_status' => 'accepted',
                'is_active'     => true,
                'joined_at'     => now(),
            ]);
            $trip->refreshTotals();
        }

        return redirect()->route('trips.show', $trip)->with('success', 'Joined trip successfully!');
    }

    public function guestShow(Request $request, Trip $trip)
    {
        $memberId = session("trip_guest_{$trip->id}") ?? $request->query('member');

        $member = $trip->members()
            ->where('id', $memberId)
            ->where('invite_status', 'accepted')
            ->first();

        abort_unless($member, 403, 'Access denied. Please verify your OTP first.');

        $trip->load([
            'activeMembers.user',
            'expenses.category',
            'expenses.paidByMember',
            'settlements.payer',
            'settlements.receiver',
        ]);

        // Guests see group expenses only (not personal expenses of others)
        $trip->setRelation('expenses', $trip->expenses->filter(
            fn ($e) => $e->split_type !== 'personal' && $e->approval_status === 'approved'
        )->values());

        return view('trip::guest-show', compact('trip', 'member'));
    }
}

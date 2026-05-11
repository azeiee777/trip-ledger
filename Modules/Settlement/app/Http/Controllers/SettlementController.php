<?php

namespace Modules\Settlement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Trip\Models\Trip;
use Modules\Settlement\Models\Settlement;
use Modules\Core\Services\SettlementEngine;

class SettlementController extends Controller
{
    public function __construct(private SettlementEngine $engine) {}

    public function index(Trip $trip)
    {
        $this->authorize('view', $trip);

        $trip->load(['activeMembers.user', 'settlements.payer.user', 'settlements.receiver.user']);

        $balances = $this->engine->calculateBalances($trip);

        $membersById = $trip->activeMembers->keyBy('id');

        return view('settlement::index', compact('trip', 'balances', 'membersById'));
    }

    public function calculate(Request $request, Trip $trip)
    {
        $this->authorize('update', $trip);

        $this->engine->generateSettlements($trip);

        return redirect()->route('trips.show', ['trip' => $trip, 'tab' => 'settlement'])
            ->with('success', 'Settlements recalculated.');
    }

    public function markPaid(Request $request, Settlement $settlement)
    {
        $this->authorize('view', $settlement->trip);

        // Only the payer themselves or a trip admin can record a payment
        $member = $settlement->trip->members()
            ->where('user_id', $request->user()->id)
            ->where('invite_status', 'accepted')
            ->first();

        abort_unless(
            $member && ($member->role === 'admin' || $member->id === $settlement->payer_member_id),
            403,
            'Only the payer or a trip admin can record this payment.'
        );

        $validated = $request->validate([
            'paid_amount'    => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:upi,cash,bank',
            'payment_note'   => 'nullable|string|max:255',
        ]);

        $newPaid = round((float) $settlement->paid_amount + (float) $validated['paid_amount'], 2);

        $status = 'partial';
        if ($newPaid >= (float) $settlement->amount) {
            $newPaid = (float) $settlement->amount;
            $status  = 'paid';
        }

        $settlement->update([
            'paid_amount'    => $newPaid,
            'status'         => $status,
            'payment_method' => $validated['payment_method'],
            'payment_note'   => $validated['payment_note'] ?? null,
            'settled_at'     => $status === 'paid' ? now() : $settlement->settled_at,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'status' => $status, 'settlement' => $settlement->fresh()]);
        }

        return redirect()->route('trips.show', ['trip' => $settlement->trip, 'tab' => 'settlement'])
            ->with('success', 'Payment recorded.');
    }
}

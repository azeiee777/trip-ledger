<?php

namespace Modules\Trip\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Trip\Models\Trip;

class TripOtpController extends Controller
{
    public function show(Trip $trip)
    {
        return view('trip::otp-verify', compact('trip'));
    }

    public function verify(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|digits:6',
        ]);

        // Check if ANY member with this email exists in the trip
        $emailMember = $trip->members()
            ->where('invite_email', $validated['email'])
            ->first();

        if (! $emailMember) {
            return back()
                ->withErrors(['email' => 'No invite found for this email address. Please ask the trip admin to add your email to the trip.'])
                ->withInput(['email' => $validated['email']]);
        }

        if ($emailMember->invite_status === 'accepted') {
            // Already verified — redirect them to login/trip
            if (auth()->check() && strtolower(auth()->user()->email) === strtolower($validated['email'])) {
                return redirect()->route('trips.show', $trip)->with('success', 'You already have access to this trip.');
            }
            return back()->withErrors(['otp' => 'This invite has already been used. Please log in with your account to view the trip.'])->withInput();
        }

        // Email found — now validate OTP
        $otpValid = $trip->members()
            ->where('invite_email', $validated['email'])
            ->where('invite_otp', $validated['otp'])
            ->where('invite_status', 'pending')
            ->where(function ($q) {
                $q->whereNull('invite_token_expires_at')
                  ->orWhere('invite_token_expires_at', '>', now());
            })
            ->exists();

        if (! $otpValid) {
            $expired = $emailMember->invite_token_expires_at?->isPast();
            $msg = $expired
                ? 'Your OTP has expired. Please ask the trip admin to resend the invite.'
                : 'Incorrect OTP. Please double-check the 6-digit code from your email.';

            return back()->withErrors(['otp' => $msg])->withInput(['email' => $validated['email']]);
        }

        $member = $emailMember;

        // Link to logged-in account if email matches; otherwise look up by email
        $authUser = auth()->user();
        if ($authUser && strtolower($authUser->email) === strtolower($validated['email'])) {
            $userId = $authUser->id;
        } else {
            $registeredUser = User::where('email', $validated['email'])->first();
            $userId = $registeredUser?->id ?? $member->user_id;
        }

        $member->update([
            'user_id'            => $userId,
            'invite_status'      => 'accepted',
            'invite_accepted_at' => now(),
            'invite_otp'         => null,
            'is_active'          => true,
            'joined_at'          => now(),
        ]);

        $trip->refreshTotals();

        session()->put("trip_guest_{$trip->id}", $member->id);

        if (auth()->check()) {
            return redirect()->route('trips.show', $trip)
                ->with('success', 'OTP verified! You have joined the trip.');
        }

        // Not logged in — check if they have a registered account
        $registeredUser = User::where('email', $validated['email'])->first();

        if ($registeredUser) {
            // Account exists but no password on record? Or just needs to log in.
            return redirect()->route('login')
                ->with('status', 'OTP verified! Sign in to your TripLedger account to access this trip.')
                ->with('login_prefill_email', $validated['email']);
        }

        // No account yet — take them to registration with email pre-filled
        return redirect()->route('register')
            ->with('status', 'OTP verified! Create your account to access this trip anytime — your email is already confirmed.')
            ->with('prefill_email', $validated['email']);
    }
}

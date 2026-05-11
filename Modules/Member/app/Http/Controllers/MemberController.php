<?php

namespace Modules\Member\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Modules\Trip\Models\Trip;
use Modules\Trip\Models\TripMember;
use Modules\Trip\Models\CarGroup;
use Modules\Core\Mail\TripInviteMail;

class MemberController extends Controller
{
    public function store(Request $request, Trip $trip)
    {
        $this->authorize('update', $trip);

        $validated = $request->validate([
            'user_id'      => 'nullable|exists:users,id',
            'guest_name'   => 'required_without:user_id|nullable|string|max:100',
            'guest_phone'  => 'nullable|string|max:20',
            'invite_email' => 'nullable|email|max:255',
            'upi_id'       => 'nullable|string|max:100',
        ]);

        // Prevent duplicate members
        if (! empty($validated['user_id'])) {
            if ($trip->members()->where('user_id', $validated['user_id'])->exists()) {
                return back()->with('error', 'This user is already a trip member.');
            }
        }

        // If invite_email matches a registered user, link them
        $linkedUserId = $validated['user_id'] ?? null;
        if (! $linkedUserId && ! empty($validated['invite_email'])) {
            if ($trip->members()->where('invite_email', $validated['invite_email'])->exists()) {
                return back()->with('error', 'A member with that email is already in this trip.');
            }
            $existingUser = User::where('email', $validated['invite_email'])->first();
            if ($existingUser) {
                if ($trip->members()->where('user_id', $existingUser->id)->exists()) {
                    return back()->with('error', 'A member with that email is already in this trip.');
                }
                $linkedUserId = $existingUser->id;
            }
        }

        $hasEmail = ! empty($validated['invite_email']);
        $isSelf   = $linkedUserId && $linkedUserId === $request->user()->id;

        // Guests with no email → active immediately (offline participants).
        // Members with email → pending until they verify OTP.
        // Adding yourself → active immediately.
        $inviteStatus = ($isSelf || (! $hasEmail && ! $linkedUserId)) ? 'accepted' : 'pending';

        // Generate OTP for email invites
        $otp = null;
        if ($hasEmail && $inviteStatus === 'pending') {
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        }

        $member = TripMember::create([
            'trip_id'                 => $trip->id,
            'user_id'                 => $linkedUserId,
            'guest_name'              => $validated['guest_name'] ?? null,
            'guest_phone'             => $validated['guest_phone'] ?? null,
            'invite_email'            => $validated['invite_email'] ?? null,
            'invite_otp'              => $otp,
            'upi_id'                  => $validated['upi_id'] ?? null,
            'role'                    => 'member',
            'invite_status'           => $inviteStatus,
            'invite_token_expires_at' => $hasEmail ? now()->addDays(7) : null,
            'is_active'               => $inviteStatus === 'accepted',
            'joined_at'               => $inviteStatus === 'accepted' ? now() : null,
        ]);

        $trip->refreshTotals();

        if ($otp) {
            $verifyUrl = route('trips.otp.show', $trip);
            try {
                Mail::to($validated['invite_email'])->send(new TripInviteMail($trip, $member, $verifyUrl, $otp));
                $member->update(['invite_sent_at' => now()]);
            } catch (\Exception $e) {
                logger()->error('Invite email failed', ['error' => $e->getMessage(), 'member_id' => $member->id]);
            }

            // Show OTP to admin so they can verify in person
            return back()->with('success_otp', [
                'message' => "Invite sent to {$validated['invite_email']}.",
                'name'    => $member->guest_name ?? $member->user?->name ?? $validated['invite_email'],
                'otp'     => $otp,
            ]);
        }

        return back()->with('success', 'Member added successfully.');
    }

    public function toggle(Request $request, Trip $trip, TripMember $member)
    {
        $this->authorize('update', $trip);
        abort_unless($member->trip_id === $trip->id, 403);
        abort_if($member->role === 'admin', 403, 'Cannot deactivate the trip admin.');

        $member->update(['is_active' => ! $member->is_active]);
        $trip->refreshTotals();

        return back()->with('success', 'Member status updated.');
    }

    public function destroy(Trip $trip, TripMember $member)
    {
        $this->authorize('update', $trip);
        abort_unless($member->trip_id === $trip->id, 403);
        abort_if($member->role === 'admin', 403, 'Cannot remove the trip admin.');

        if ($member->expensesPaid()->count() > 0 || $member->splits()->count() > 0) {
            return back()->with('error', 'Cannot remove a member who has expenses. Deactivate them instead.');
        }

        $member->delete();
        $trip->refreshTotals();

        return back()->with('success', 'Member removed.');
    }

    public function resendInvite(Request $request, Trip $trip, TripMember $member)
    {
        $this->authorize('update', $trip);
        abort_unless($member->trip_id === $trip->id, 403);
        abort_unless($member->invite_status === 'pending', 422, 'Member has already accepted.');

        $emailTo = $member->invite_email ?? $member->user?->email;
        abort_unless($emailTo, 422, 'No email address for this member.');

        // Regenerate OTP and reset expiry
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $member->update([
            'invite_otp'              => $otp,
            'invite_token_expires_at' => now()->addDays(7),
            'invite_sent_at'          => now(),
        ]);

        $verifyUrl = route('trips.otp.show', $trip);
        Mail::to($emailTo)->send(new TripInviteMail($trip, $member, $verifyUrl, $otp));

        return back()->with('success_otp', [
            'message' => "Invite resent to {$emailTo}.",
            'name'    => $member->display_name,
            'otp'     => $otp,
        ]);
    }

    public function updateEmail(Request $request, Trip $trip, TripMember $member)
    {
        $this->authorize('update', $trip);
        abort_unless($member->trip_id === $trip->id, 403);

        $validated = $request->validate([
            'invite_email' => 'required|email|max:255',
        ]);

        // Prevent using an email already on another member of this trip
        $conflict = $trip->members()
            ->where('invite_email', $validated['invite_email'])
            ->where('id', '!=', $member->id)
            ->exists();

        if ($conflict) {
            return back()->with('error', 'Another member in this trip already uses that email.');
        }

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $member->update([
            'invite_email'            => $validated['invite_email'],
            'invite_otp'              => $otp,
            'invite_status'           => 'pending',
            'invite_token_expires_at' => now()->addDays(7),
            'invite_sent_at'          => now(),
            'is_active'               => false,
            'joined_at'               => null,
        ]);

        $trip->refreshTotals();

        $verifyUrl = route('trips.otp.show', $trip);
        try {
            Mail::to($validated['invite_email'])->send(new TripInviteMail($trip, $member, $verifyUrl, $otp));
        } catch (\Exception $e) {
            logger()->error('Email update invite failed', ['error' => $e->getMessage(), 'member_id' => $member->id]);
        }

        return back()->with('success_otp', [
            'message' => "Invite sent to {$validated['invite_email']}.",
            'name'    => $member->display_name,
            'otp'     => $otp,
        ]);
    }

    public function storeCarGroup(Request $request, Trip $trip)
    {
        $this->authorize('update', $trip);

        $validated = $request->validate([
            'name'           => 'required|string|max:100',
            'member_ids'     => 'required|array|min:1',
            'member_ids.*'   => 'exists:trip_members,id',
        ]);

        $validIds = $trip->members()->pluck('id')->toArray();
        foreach ($validated['member_ids'] as $id) {
            abort_unless(in_array((int) $id, $validIds), 422, 'Invalid member in car group.');
        }

        $carGroup = CarGroup::create([
            'trip_id'    => $trip->id,
            'name'       => $validated['name'],
            'member_ids' => $validated['member_ids'],
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'car_group' => $carGroup]);
        }

        return back()->with('success', "Car group \"{$carGroup->name}\" created.");
    }

    public function destroyCarGroup(Trip $trip, CarGroup $carGroup)
    {
        $this->authorize('update', $trip);
        abort_unless($carGroup->trip_id === $trip->id, 403);

        $carGroup->delete();
        return back()->with('success', 'Car group removed.');
    }
}

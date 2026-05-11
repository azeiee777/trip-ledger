<?php

namespace Modules\Trip\Policies;

use App\Models\User;
use Modules\Trip\Models\Trip;

class TripPolicy
{
    public function view(User $user, Trip $trip): bool
    {
        return $trip->user_id === $user->id
            || $trip->members()
                ->where('user_id', $user->id)
                ->where('invite_status', 'accepted')
                ->exists();
    }

    public function update(User $user, Trip $trip): bool
    {
        return $trip->user_id === $user->id;
    }

    public function delete(User $user, Trip $trip): bool
    {
        return $trip->user_id === $user->id;
    }

    public function addExpense(User $user, Trip $trip): bool
    {
        $member = $trip->members()
            ->where('user_id', $user->id)
            ->where('invite_status', 'accepted')
            ->where('is_active', true)
            ->first();

        if (! $member) {
            return false;
        }

        // Admins can always add expenses (including post-trip accounting on completed trips)
        if ($member->role === 'admin') {
            return true;
        }

        // Regular members are blocked from adding expenses to closed trips
        return ! in_array($trip->status, ['completed', 'archived']);
    }
}

<?php

namespace Modules\Trip\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TripMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id', 'user_id', 'guest_name', 'guest_phone', 'invite_email',
        'invite_otp', 'upi_id', 'is_active', 'role', 'invite_status',
        'invite_sent_at', 'invite_accepted_at', 'invite_token_expires_at', 'joined_at',
    ];

    protected $casts = [
        'is_active'               => 'boolean',
        'joined_at'               => 'datetime',
        'invite_sent_at'          => 'datetime',
        'invite_accepted_at'      => 'datetime',
        'invite_token_expires_at' => 'datetime',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasAccepted(): bool
    {
        return $this->invite_status === 'accepted';
    }

    public function inviteExpired(): bool
    {
        return $this->invite_token_expires_at !== null
            && $this->invite_token_expires_at->isPast();
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expensesPaid(): HasMany
    {
        return $this->hasMany(\Modules\Expense\Models\Expense::class, 'paid_by_member_id');
    }

    public function splits(): HasMany
    {
        return $this->hasMany(\Modules\Expense\Models\ExpenseSplit::class);
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(\Modules\Settlement\Models\Settlement::class, 'payer_member_id');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->user?->name ?? $this->guest_name ?? 'Unknown';
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->user?->avatar) {
            return asset('storage/' . $this->user->avatar);
        }
        $name = urlencode($this->display_name);
        return "https://ui-avatars.com/api/?name={$name}&background=6C63FF&color=fff&size=80";
    }
}

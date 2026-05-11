<?php

namespace Modules\Trip\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Trip extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'destination', 'description',
        'trip_type', 'status', 'start_date', 'end_date',
        'cover_image', 'total_spend', 'member_count', 'invite_token',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'end_date'    => 'date',
        'total_spend' => 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Trip $trip) {
            if (empty($trip->invite_token)) {
                $trip->invite_token = Str::random(32);
            }
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(TripMember::class);
    }

    public function activeMembers(): HasMany
    {
        return $this->hasMany(TripMember::class)->where('is_active', true);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(\Modules\Expense\Models\Expense::class);
    }

    public function groupExpenses(): HasMany
    {
        return $this->hasMany(\Modules\Expense\Models\Expense::class)
            ->where('split_type', '!=', 'personal');
    }

    public function carGroups(): HasMany
    {
        return $this->hasMany(CarGroup::class);
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(\Modules\Settlement\Models\Settlement::class);
    }

    public function stops(): HasMany
    {
        return $this->hasMany(TripStop::class)->orderBy('visit_date')->orderBy('sort_order')->orderBy('visit_time');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(\Modules\Core\Models\TripAuditLog::class);
    }

    public function refreshTotals(): void
    {
        $total = $this->groupExpenses()->where('approval_status', 'approved')->sum('amount');
        $count = $this->activeMembers()->count();
        $this->update(['total_spend' => $total, 'member_count' => $count]);
    }
}

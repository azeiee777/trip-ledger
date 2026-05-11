<?php

namespace Modules\Trip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarGroup extends Model
{
    use HasFactory;

    protected $fillable = ['trip_id', 'name', 'member_ids'];

    protected $casts = ['member_ids' => 'array'];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function members()
    {
        return TripMember::whereIn('id', $this->member_ids ?? [])->get();
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(\Modules\Expense\Models\Expense::class);
    }
}

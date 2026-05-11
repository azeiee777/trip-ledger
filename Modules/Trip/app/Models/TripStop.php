<?php

namespace Modules\Trip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripStop extends Model
{
    protected $fillable = [
        'trip_id', 'expense_id', 'name', 'place_type',
        'visit_date', 'visit_time', 'address', 'notes',
        'estimated_cost', 'sort_order',
    ];

    protected $casts = [
        'visit_date'     => 'date',
        'estimated_cost' => 'decimal:2',
    ];

    // Emoji + color config per place type
    public static array $typeConfig = [
        'hotel'      => ['emoji' => '🏨', 'label' => 'Hotel / Stay',   'color' => 'indigo'],
        'attraction' => ['emoji' => '🏔️', 'label' => 'Attraction',     'color' => 'green'],
        'restaurant' => ['emoji' => '🍽️', 'label' => 'Food / Dining',  'color' => 'orange'],
        'activity'   => ['emoji' => '🎯', 'label' => 'Activity',        'color' => 'purple'],
        'transit'    => ['emoji' => '🚌', 'label' => 'Transit',         'color' => 'blue'],
        'other'      => ['emoji' => '📍', 'label' => 'Other',           'color' => 'gray'],
    ];

    public function getTypeEmojiAttribute(): string
    {
        return self::$typeConfig[$this->place_type]['emoji'] ?? '📍';
    }

    public function getTypeLabelAttribute(): string
    {
        return self::$typeConfig[$this->place_type]['label'] ?? 'Other';
    }

    public function getTypeColorAttribute(): string
    {
        return self::$typeConfig[$this->place_type]['color'] ?? 'gray';
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(\Modules\Expense\Models\Expense::class);
    }
}

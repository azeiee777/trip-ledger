<?php

namespace Modules\Settlement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Settlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id', 'payer_member_id', 'receiver_member_id',
        'amount', 'status', 'paid_amount', 'payment_method',
        'payment_note', 'settled_at',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'settled_at'  => 'datetime',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(\Modules\Trip\Models\Trip::class);
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(\Modules\Trip\Models\TripMember::class, 'payer_member_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(\Modules\Trip\Models\TripMember::class, 'receiver_member_id');
    }

    public function getRemainingAmountAttribute(): float
    {
        return round((float) $this->amount - (float) $this->paid_amount, 2);
    }

    public function generateUpiLink(): string
    {
        $upi = $this->receiver->upi_id ?? $this->receiver->user?->upi_id ?? '';
        if (empty($upi)) return '#';
        return sprintf(
            'upi://pay?pa=%s&am=%.2f&tn=%s&cu=INR',
            urlencode($upi),
            $this->remaining_amount,
            urlencode('TripLedger Settlement')
        );
    }
}

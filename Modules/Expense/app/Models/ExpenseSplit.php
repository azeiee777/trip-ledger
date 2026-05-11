<?php

namespace Modules\Expense\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseSplit extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_id', 'trip_member_id', 'share_amount', 'share_percentage', 'is_excluded',
    ];

    protected $casts = [
        'share_amount'      => 'decimal:2',
        'share_percentage'  => 'decimal:2',
        'is_excluded'       => 'boolean',
    ];

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(\Modules\Trip\Models\TripMember::class, 'trip_member_id');
    }
}

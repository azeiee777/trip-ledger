<?php

namespace Modules\Expense\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id', 'trip_member_id', 'category_id',
        'title', 'amount', 'expense_date', 'note',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(\Modules\Trip\Models\Trip::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(\Modules\Trip\Models\TripMember::class, 'trip_member_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(\Modules\Core\Models\Category::class);
    }
}

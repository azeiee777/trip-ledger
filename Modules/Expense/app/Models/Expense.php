<?php

namespace Modules\Expense\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'trip_id', 'paid_by_member_id', 'category_id', 'car_group_id',
        'title', 'amount', 'split_type', 'note', 'receipt_image',
        'expense_date', 'created_by',
        'approval_status', 'approved_by', 'approved_at', 'rejection_reason',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'expense_date' => 'date',
        'approved_at'  => 'datetime',
    ];

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function isPendingApproval(): bool
    {
        return $this->approval_status === 'pending_approval';
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(\Modules\Trip\Models\Trip::class);
    }

    public function paidByMember(): BelongsTo
    {
        return $this->belongsTo(\Modules\Trip\Models\TripMember::class, 'paid_by_member_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(\Modules\Core\Models\Category::class);
    }

    public function carGroup(): BelongsTo
    {
        return $this->belongsTo(\Modules\Trip\Models\CarGroup::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function splits(): HasMany
    {
        return $this->hasMany(ExpenseSplit::class);
    }

    public function isPersonal(): bool
    {
        return $this->split_type === 'personal';
    }
}

<?php

namespace Modules\Core\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripAuditLog extends Model
{
    public $timestamps = false;

    protected $table = 'trip_audit_log';

    protected $fillable = ['trip_id', 'user_id', 'action', 'old_value', 'new_value'];

    protected $casts = [
        'old_value'  => 'array',
        'new_value'  => 'array',
        'created_at' => 'datetime',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(\Modules\Trip\Models\Trip::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

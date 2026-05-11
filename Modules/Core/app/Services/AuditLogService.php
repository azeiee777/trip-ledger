<?php

namespace Modules\Core\Services;

use Modules\Core\Models\TripAuditLog;

class AuditLogService
{
    public function log(int $tripId, int $userId, string $action, ?array $oldValue = null, ?array $newValue = null): void
    {
        TripAuditLog::create([
            'trip_id'   => $tripId,
            'user_id'   => $userId,
            'action'    => $action,
            'old_value' => $oldValue,
            'new_value' => $newValue,
        ]);
    }
}

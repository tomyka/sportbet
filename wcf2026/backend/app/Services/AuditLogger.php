<?php

namespace App\Services;

use App\Models\AuditEvent;

class AuditLogger
{
    /**
     * @param array<string, mixed>|null $changes
     */
    public static function record(
        string $action,
        ?int $actorUserId = null,
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?array $changes = null,
        ?string $ip = null,
        ?string $userAgent = null,
    ): AuditEvent {
        return AuditEvent::create([
            'actor_user_id' => $actorUserId,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'changes' => $changes,
            'ip' => $ip,
            'user_agent' => $userAgent,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Modules\Operations\Application;

use CodeIgniter\Database\BaseConnection;

final class SlaClockService
{
    public function __construct(private readonly ?BaseConnection $db = null) {}

    public function ensureStarted(int $workItemId, ?int $assignedUserId = null, ?string $startedAt = null): void
    {
        $db = $this->db ?? db_connect();
        if ($db->table('work_item_sla')->where('work_item_id', $workItemId)->countAllResults()) return;

        $policy = $db->table('sla_policies')->where('is_active', 1)->orderBy('is_default', 'DESC')->get()->getRowArray();
        if (! $policy) return;

        $start = $startedAt ?: date('Y-m-d H:i:s');
        $db->table('work_item_sla')->insert([
            'work_item_id' => $workItemId,
            'policy_id' => $policy['id'],
            'assigned_user_id' => $assignedUserId,
            'started_at' => $start,
            'first_response_due_at' => date('Y-m-d H:i:s', strtotime($start . ' +' . (int) $policy['first_response_minutes'] . ' minutes')),
            'resolution_due_at' => date('Y-m-d H:i:s', strtotime($start . ' +' . (int) $policy['resolution_minutes'] . ' minutes')),
            'status' => 'RUNNING',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->record($workItemId, $assignedUserId, 'RECEIVED', $start);
    }

    public function assigned(int $workItemId, int $userId): void
    {
        $this->ensureStarted($workItemId, $userId);
        ($this->db ?? db_connect())->table('work_item_sla')->where('work_item_id', $workItemId)->update([
            'assigned_user_id' => $userId,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->record($workItemId, $userId, 'ASSIGNED');
    }

    public function firstDraft(int $workItemId, ?int $userId): void
    {
        $this->recordOnce($workItemId, $userId, 'FIRST_DRAFT');
    }

    public function firstResponse(int $workItemId, ?int $userId): void
    {
        $db = $this->db ?? db_connect();
        $now = date('Y-m-d H:i:s');
        $db->table('work_item_sla')->where('work_item_id', $workItemId)->where('first_response_at', null)->update([
            'first_response_at' => $now, 'updated_at' => $now,
        ]);
        $this->recordOnce($workItemId, $userId, 'FIRST_RESPONSE', $now);
    }

    public function resolved(int $workItemId, ?int $userId): void
    {
        $db = $this->db ?? db_connect();
        $now = date('Y-m-d H:i:s');
        $db->table('work_item_sla')->where('work_item_id', $workItemId)->update([
            'resolved_at' => $now, 'status' => 'COMPLETED', 'updated_at' => $now,
        ]);
        $this->recordOnce($workItemId, $userId, 'RESOLVED', $now);
    }

    public function record(int $workItemId, ?int $userId, string $eventType, ?string $occurredAt = null, array $metadata = []): void
    {
        ($this->db ?? db_connect())->table('work_item_time_events')->insert([
            'work_item_id' => $workItemId,
            'user_id' => $userId,
            'event_type' => strtoupper($eventType),
            'occurred_at' => $occurredAt ?: date('Y-m-d H:i:s'),
            'metadata' => $metadata === [] ? null : json_encode($metadata, JSON_UNESCAPED_UNICODE),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function recordOnce(int $workItemId, ?int $userId, string $eventType, ?string $occurredAt = null): void
    {
        $db = $this->db ?? db_connect();
        if ($db->table('work_item_time_events')->where(['work_item_id' => $workItemId, 'event_type' => $eventType])->countAllResults()) return;
        $this->record($workItemId, $userId, $eventType, $occurredAt);
    }
}

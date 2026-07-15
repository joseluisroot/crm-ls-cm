<?php

declare(strict_types=1);

namespace Modules\Operations\Application;

use CodeIgniter\Database\BaseConnection;

final class WorkItemAssignmentQueryService
{
    public function __construct(private readonly ?BaseConnection $db = null)
    {
    }

    /** @return array{assignments: array<int, array<string, mixed>>, metrics: array<string, mixed>} */
    public function get(int $workItemId): array
    {
        if ($workItemId <= 0) {
            return $this->emptyResult();
        }

        $db = $this->connection();
        $item = $db->table('work_items wi')
            ->select('wi.id, wi.assigned_user_id, wi.opened_at, wi.created_at, st.code AS status_code, au.name AS current_user_name')
            ->join('work_item_statuses st', 'st.id = wi.status_id')
            ->join('admin_users au', 'au.id = wi.assigned_user_id', 'left')
            ->where('wi.id', $workItemId)
            ->get()
            ->getRowArray();

        if (! $item) {
            return $this->emptyResult();
        }

        $events = $db->table('work_item_time_events e')
            ->select('e.id, e.user_id, e.occurred_at, u.name AS user_name')
            ->join('admin_users u', 'u.id = e.user_id', 'left')
            ->where('e.work_item_id', $workItemId)
            ->where('e.event_type', 'ASSIGNED')
            ->orderBy('e.occurred_at', 'ASC')
            ->orderBy('e.id', 'ASC')
            ->get()
            ->getResultArray();

        $isClosed = in_array(strtoupper((string) ($item['status_code'] ?? '')), ['RESOLVED', 'CLOSED', 'CANCELLED'], true);
        $resolvedAt = null;

        if ($isClosed) {
            $resolved = $db->table('work_item_time_events')
                ->select('occurred_at')
                ->where('work_item_id', $workItemId)
                ->where('event_type', 'RESOLVED')
                ->orderBy('occurred_at', 'DESC')
                ->orderBy('id', 'DESC')
                ->get()
                ->getRowArray();
            $resolvedAt = $resolved['occurred_at'] ?? null;
        }

        $assignments = [];
        foreach ($events as $index => $event) {
            $startedAt = (string) ($event['occurred_at'] ?? '');
            $next = $events[$index + 1]['occurred_at'] ?? null;
            $endedAt = $next ?: ($resolvedAt ?: null);
            $isCurrent = $index === array_key_last($events) && ! $isClosed;
            $endTimestamp = $endedAt ? strtotime((string) $endedAt) : time();
            $startTimestamp = $startedAt !== '' ? strtotime($startedAt) : false;

            $assignments[] = [
                'user_id' => (int) ($event['user_id'] ?? 0),
                'user_name' => $event['user_name'] ?: 'Usuario no disponible',
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
                'duration_seconds' => $startTimestamp === false || $endTimestamp === false ? null : max(0, $endTimestamp - $startTimestamp),
                'is_current' => $isCurrent,
            ];
        }

        $totalSeconds = array_sum(array_map(static fn (array $row): int => (int) ($row['duration_seconds'] ?? 0), $assignments));

        return [
            'assignments' => $assignments,
            'metrics' => [
                'current_user_id' => (int) ($item['assigned_user_id'] ?? 0) ?: null,
                'current_user_name' => $item['current_user_name'] ?? null,
                'reassignments' => max(0, count($assignments) - 1),
                'total_assignment_seconds' => $totalSeconds,
                'is_closed' => $isClosed,
                'has_history' => $assignments !== [],
            ],
        ];
    }

    /** @return array{assignments: array, metrics: array} */
    private function emptyResult(): array
    {
        return [
            'assignments' => [],
            'metrics' => [
                'current_user_id' => null,
                'current_user_name' => null,
                'reassignments' => 0,
                'total_assignment_seconds' => 0,
                'is_closed' => false,
                'has_history' => false,
            ],
        ];
    }

    private function connection(): BaseConnection
    {
        return $this->db ?? db_connect();
    }
}

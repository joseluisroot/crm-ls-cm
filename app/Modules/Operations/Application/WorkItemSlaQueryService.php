<?php

declare(strict_types=1);

namespace Modules\Operations\Application;

use CodeIgniter\Database\BaseConnection;

final class WorkItemSlaQueryService
{
    public function __construct(private readonly ?BaseConnection $db = null)
    {
    }

    /** @return array<string, mixed>|null */
    public function get(int $workItemId): ?array
    {
        if ($workItemId <= 0) {
            return null;
        }

        $row = $this->connection()->table('work_item_sla s')
            ->select('s.*, p.code AS policy_code, p.name AS policy_name, p.first_response_minutes, p.resolution_minutes, p.warning_percent')
            ->join('sla_policies p', 'p.id = s.policy_id', 'left')
            ->where('s.work_item_id', $workItemId)
            ->get()
            ->getRowArray();

        if (! $row) {
            return null;
        }

        $now = time();
        $startedAt = $this->timestamp($row['started_at'] ?? null) ?? $now;
        $firstResponseDueAt = $this->timestamp($row['first_response_due_at'] ?? null);
        $resolutionDueAt = $this->timestamp($row['resolution_due_at'] ?? null);
        $firstResponseAt = $this->timestamp($row['first_response_at'] ?? null);
        $resolvedAt = $this->timestamp($row['resolved_at'] ?? null);
        $warningPercent = max(1, min(100, (int) ($row['warning_percent'] ?? 80)));

        $firstResponse = $this->metric(
            startedAt: $startedAt,
            dueAt: $firstResponseDueAt,
            completedAt: $firstResponseAt,
            now: $now,
            warningPercent: $warningPercent,
        );

        $resolution = $this->metric(
            startedAt: $startedAt,
            dueAt: $resolutionDueAt,
            completedAt: $resolvedAt,
            now: $now,
            warningPercent: $warningPercent,
        );

        return [
            'work_item_id' => $workItemId,
            'policy' => [
                'code' => $row['policy_code'] ?? null,
                'name' => $row['policy_name'] ?? 'Política SLA',
                'first_response_minutes' => (int) ($row['first_response_minutes'] ?? 0),
                'resolution_minutes' => (int) ($row['resolution_minutes'] ?? 0),
                'warning_percent' => $warningPercent,
            ],
            'status' => strtoupper((string) ($row['status'] ?? 'RUNNING')),
            'started_at' => $row['started_at'] ?? null,
            'first_response_due_at' => $row['first_response_due_at'] ?? null,
            'resolution_due_at' => $row['resolution_due_at'] ?? null,
            'first_response_at' => $row['first_response_at'] ?? null,
            'resolved_at' => $row['resolved_at'] ?? null,
            'first_response' => $firstResponse,
            'resolution' => $resolution,
        ];
    }

    /** @return array<string, mixed> */
    private function metric(int $startedAt, ?int $dueAt, ?int $completedAt, int $now, int $warningPercent): array
    {
        if ($dueAt === null || $dueAt <= $startedAt) {
            return [
                'state' => 'UNAVAILABLE',
                'label' => 'Sin objetivo configurado',
                'percentage' => 0,
                'elapsed_seconds' => 0,
                'remaining_seconds' => null,
                'is_live' => false,
                'due_at_unix' => null,
                'started_at_unix' => $startedAt,
                'completed_at_unix' => $completedAt,
            ];
        }

        $end = $completedAt ?? $now;
        $targetSeconds = max(1, $dueAt - $startedAt);
        $elapsedSeconds = max(0, $end - $startedAt);
        $percentage = (int) round(($elapsedSeconds / $targetSeconds) * 100);
        $remainingSeconds = $dueAt - $end;

        if ($completedAt !== null) {
            $state = $completedAt <= $dueAt ? 'COMPLETED' : 'BREACHED';
            $label = $state === 'COMPLETED' ? 'Cumplido' : 'Cumplido fuera de tiempo';
        } elseif ($remainingSeconds < 0) {
            $state = 'BREACHED';
            $label = 'Vencido';
        } elseif ($percentage >= $warningPercent) {
            $state = 'WARNING';
            $label = 'Próximo a vencer';
        } else {
            $state = 'ON_TIME';
            $label = 'Dentro del tiempo';
        }

        return [
            'state' => $state,
            'label' => $label,
            'percentage' => max(0, min(100, $percentage)),
            'raw_percentage' => $percentage,
            'elapsed_seconds' => $elapsedSeconds,
            'remaining_seconds' => $remainingSeconds,
            'is_live' => $completedAt === null,
            'due_at_unix' => $dueAt,
            'started_at_unix' => $startedAt,
            'completed_at_unix' => $completedAt,
        ];
    }

    private function timestamp(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $timestamp = strtotime((string) $value);
        return $timestamp === false ? null : $timestamp;
    }

    private function connection(): BaseConnection
    {
        return $this->db ?? db_connect();
    }
}

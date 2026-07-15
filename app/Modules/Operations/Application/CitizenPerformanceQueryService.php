<?php

declare(strict_types=1);

namespace Modules\Operations\Application;

use CodeIgniter\Database\BaseConnection;

final class CitizenPerformanceQueryService
{
    public function __construct(private readonly ?BaseConnection $db = null) {}

    /** @param int[]|null $scopeUserIds */
    public function dashboard(?array $scopeUserIds): array
    {
        $db = $this->db ?? db_connect();
        $now = date('Y-m-d H:i:s');

        $usersBuilder = $db->table('admin_users')
            ->select('id, name, email, status')
            ->where('status', 'active')
            ->orderBy('name', 'ASC');
        if ($scopeUserIds !== null) {
            $scopeUserIds = array_values(array_unique(array_filter(array_map('intval', $scopeUserIds))));
            if ($scopeUserIds === []) {
                return $this->emptyDashboard();
            }
            $usersBuilder->whereIn('id', $scopeUserIds);
        }
        $users = $usersBuilder->get()->getResultArray();
        $userIds = array_map('intval', array_column($users, 'id'));
        if ($userIds === []) return $this->emptyDashboard();

        $workRows = $db->table('work_items wi')
            ->select('wi.assigned_user_id, st.code status_code, COUNT(*) total')
            ->join('work_item_statuses st', 'st.id = wi.status_id')
            ->whereIn('wi.assigned_user_id', $userIds)
            ->groupBy(['wi.assigned_user_id', 'st.code'])
            ->get()->getResultArray();

        $slaRows = $db->table('work_item_sla')
            ->select('assigned_user_id, started_at, first_response_due_at, first_response_at, resolution_due_at, resolved_at, status')
            ->whereIn('assigned_user_id', $userIds)
            ->get()->getResultArray();

        $byUser = [];
        foreach ($users as $user) {
            $id = (int) $user['id'];
            $byUser[$id] = [
                'id' => $id,
                'name' => (string) ($user['name'] ?? $user['email'] ?? ('Usuario #' . $id)),
                'email' => (string) ($user['email'] ?? ''),
                'pending' => 0,
                'active' => 0,
                'waiting' => 0,
                'completed' => 0,
                'sla_due_soon' => 0,
                'sla_breached' => 0,
                'responded' => 0,
                'responded_on_time' => 0,
                'response_minutes_total' => 0,
                'response_minutes_count' => 0,
            ];
        }

        foreach ($workRows as $row) {
            $id = (int) $row['assigned_user_id'];
            if (! isset($byUser[$id])) continue;
            $code = strtoupper((string) $row['status_code']);
            $total = (int) $row['total'];
            if ($code === 'NEW') $byUser[$id]['pending'] += $total;
            elseif (in_array($code, ['ASSIGNED','IN_PROGRESS','INVESTIGATING','DRAFT','PENDING_APPROVAL'], true)) $byUser[$id]['active'] += $total;
            elseif (in_array($code, ['WAITING_CITIZEN','WAITING_INTERNAL','WAITING_THIRD_PARTY'], true)) $byUser[$id]['waiting'] += $total;
            elseif (in_array($code, ['RESPONDED','RESOLVED','CLOSED'], true)) $byUser[$id]['completed'] += $total;
        }

        foreach ($slaRows as $sla) {
            $id = (int) ($sla['assigned_user_id'] ?? 0);
            if (! isset($byUser[$id])) continue;
            $due = $sla['first_response_due_at'] ?? null;
            $respondedAt = $sla['first_response_at'] ?? null;
            if ($respondedAt) {
                $byUser[$id]['responded']++;
                if ($due && strtotime((string) $respondedAt) <= strtotime((string) $due)) $byUser[$id]['responded_on_time']++;
                if (! empty($sla['started_at'])) {
                    $minutes = max(0, (int) floor((strtotime((string) $respondedAt) - strtotime((string) $sla['started_at'])) / 60));
                    $byUser[$id]['response_minutes_total'] += $minutes;
                    $byUser[$id]['response_minutes_count']++;
                }
                continue;
            }
            if (! $due) continue;
            $dueTimestamp = strtotime((string) $due);
            if ($dueTimestamp < strtotime($now)) {
                $byUser[$id]['sla_breached']++;
            } else {
                $startTimestamp = strtotime((string) ($sla['started_at'] ?? $now));
                $window = max(1, $dueTimestamp - $startTimestamp);
                $consumed = strtotime($now) - $startTimestamp;
                if (($consumed / $window) >= 0.8) $byUser[$id]['sla_due_soon']++;
            }
        }

        $operators = [];
        foreach ($byUser as $row) {
            $row['open'] = $row['pending'] + $row['active'] + $row['waiting'];
            $row['average_first_response_minutes'] = $row['response_minutes_count'] > 0
                ? (int) round($row['response_minutes_total'] / $row['response_minutes_count'])
                : null;
            $row['sla_compliance'] = $row['responded'] > 0
                ? round(($row['responded_on_time'] / $row['responded']) * 100, 1)
                : null;
            unset($row['response_minutes_total'], $row['response_minutes_count']);
            $operators[] = $row;
        }

        usort($operators, static fn (array $a, array $b): int => [$b['sla_breached'], $b['open']] <=> [$a['sla_breached'], $a['open']]);

        $summary = [
            'operators' => count($operators),
            'open' => array_sum(array_column($operators, 'open')),
            'due_soon' => array_sum(array_column($operators, 'sla_due_soon')),
            'breached' => array_sum(array_column($operators, 'sla_breached')),
            'responded' => array_sum(array_column($operators, 'responded')),
            'responded_on_time' => array_sum(array_column($operators, 'responded_on_time')),
        ];
        $summary['sla_compliance'] = $summary['responded'] > 0
            ? round(($summary['responded_on_time'] / $summary['responded']) * 100, 1)
            : null;

        return compact('summary', 'operators');
    }

    private function emptyDashboard(): array
    {
        return ['summary' => ['operators' => 0, 'open' => 0, 'due_soon' => 0, 'breached' => 0, 'responded' => 0, 'responded_on_time' => 0, 'sla_compliance' => null], 'operators' => []];
    }
}

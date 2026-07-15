<?php

declare(strict_types=1);

namespace Modules\Operations\Application;

use CodeIgniter\Database\BaseConnection;

final class MyWorkQueryService
{
    public function __construct(private readonly ?BaseConnection $db = null) {}

    public function dashboard(int $userId): array
    {
        $db = $this->db ?? db_connect();
        $today = date('Y-m-d');

        $statusCounts = [];
        $rows = $db->table('work_items wi')
            ->select('st.code, COUNT(*) total')
            ->join('work_item_statuses st', 'st.id = wi.status_id')
            ->where('wi.assigned_user_id', $userId)
            ->groupBy('st.code')->get()->getResultArray();
        foreach ($rows as $row) $statusCounts[(string) $row['code']] = (int) $row['total'];

        $groups = [
            'pending' => $this->sum($statusCounts, ['NEW']),
            'active' => $this->sum($statusCounts, ['ASSIGNED','IN_PROGRESS','INVESTIGATING','DRAFT','PENDING_APPROVAL']),
            'waiting' => $this->sum($statusCounts, ['WAITING_CITIZEN','WAITING_INTERNAL','WAITING_THIRD_PARTY']),
            'completed' => $this->sum($statusCounts, ['RESPONDED','RESOLVED','CLOSED']),
        ];

        $completedToday = (int) $db->table('work_items wi')
            ->join('work_item_statuses st', 'st.id = wi.status_id')
            ->where('wi.assigned_user_id', $userId)
            ->whereIn('st.code', ['RESPONDED','RESOLVED','CLOSED'])
            ->where('DATE(COALESCE(wi.closed_at, wi.updated_at)) =', $today, false)
            ->countAllResults();

        $drafts = $db->tableExists('response_drafts')
            ? (int) $db->table('response_drafts')->where('user_id', $userId)->where('status', 'DRAFT')->countAllResults()
            : 0;

        $openCases = 0;
        if ($db->tableExists('cases') && $db->tableExists('case_statuses')) {
            $terminalStatusIds = array_map('intval', array_column(
                $db->table('case_statuses')
                    ->select('id')
                    ->whereIn('name', ['Cerrado', 'Resuelto', 'Cancelado', 'Closed', 'Resolved', 'Cancelled'])
                    ->get()
                    ->getResultArray(),
                'id'
            ));

            $caseBuilder = $db->table('cases')
                ->where('assigned_to', $userId);

            if ($terminalStatusIds !== []) {
                $caseBuilder->whereNotIn('status_id', $terminalStatusIds);
            }

            $openCases = (int) $caseBuilder->countAllResults();
        }

        $priority = $db->table('work_items wi')
            ->select('wi.id, wi.title, wi.summary, wi.opened_at, pr.name priority_name, pr.code priority, st.name status_name, ch.code channel')
            ->join('work_item_priorities pr', 'pr.id = wi.priority_id')
            ->join('work_item_statuses st', 'st.id = wi.status_id')
            ->join('work_item_channels ch', 'ch.id = wi.channel_id')
            ->where('wi.assigned_user_id', $userId)
            ->whereNotIn('st.code', ['RESPONDED','RESOLVED','CLOSED','CANCELLED','DUPLICATE','NOT_APPLICABLE'])
            ->orderBy('pr.sort_order', 'DESC')->orderBy('wi.opened_at', 'ASC')->limit(8)
            ->get()->getResultArray();

        return compact('groups', 'completedToday', 'drafts', 'openCases', 'priority');
    }

    private function sum(array $counts, array $codes): int
    {
        return array_sum(array_map(static fn (string $code): int => $counts[$code] ?? 0, $codes));
    }
}

<?php

namespace Modules\Operations\Application;

use CodeIgniter\Database\BaseConnection;

final class OperationsQueueQueryService
{
    public function __construct(private readonly ?BaseConnection $db = null)
    {
    }

    public function summary(): array
    {
        $db = $this->connection();
        $total = (int) $db->table('work_items')->countAllResults();

        $rows = $db->table('work_items wi')
            ->select('st.code, COUNT(*) AS total')
            ->join('work_item_statuses st', 'st.id = wi.status_id')
            ->groupBy('st.code')
            ->get()
            ->getResultArray();

        $byStatus = [];
        foreach ($rows as $row) {
            $byStatus[$row['code']] = (int) $row['total'];
        }

        return [
            'total' => $total,
            'new' => $byStatus['NEW'] ?? 0,
            'assigned' => $byStatus['ASSIGNED'] ?? 0,
            'in_progress' => $byStatus['IN_PROGRESS'] ?? 0,
            'resolved' => ($byStatus['RESOLVED'] ?? 0) + ($byStatus['CLOSED'] ?? 0),
        ];
    }

    public function items(?string $status = null, ?string $priority = null, int $limit = 100): array
    {
        $builder = $this->connection()->table('work_items wi')
            ->select('wi.*, st.code AS status, st.name AS status_name, pr.code AS priority, pr.name AS priority_name, ch.code AS channel, ot.code AS origin_type, sc.author_name, sc.message AS comment_message, sc.commented_at, sp.external_post_id, sp.message AS post_message, sp.permalink_url')
            ->join('work_item_statuses st', 'st.id = wi.status_id')
            ->join('work_item_priorities pr', 'pr.id = wi.priority_id')
            ->join('work_item_channels ch', 'ch.id = wi.channel_id')
            ->join('work_item_origin_types ot', 'ot.id = wi.origin_type_id')
            ->join('social_comments sc', "ot.code = 'FACEBOOK_COMMENT' AND sc.external_comment_id = wi.origin_id", 'left')
            ->join('social_posts sp', 'sp.id = sc.social_post_id', 'left')
            ->orderBy('pr.sort_order', 'DESC')
            ->orderBy('wi.opened_at', 'ASC')
            ->limit(max(1, min($limit, 200)));

        if ($status) {
            $builder->where('st.code', strtoupper($status));
        }
        if ($priority) {
            $builder->where('pr.code', strtoupper($priority));
        }

        return $builder->get()->getResultArray();
    }

    public function statuses(): array
    {
        return $this->connection()->table('work_item_statuses')
            ->where('is_active', 1)->orderBy('sort_order')->get()->getResultArray();
    }

    public function priorities(): array
    {
        return $this->connection()->table('work_item_priorities')
            ->where('is_active', 1)->orderBy('sort_order')->get()->getResultArray();
    }

    private function connection(): BaseConnection
    {
        return $this->db ?? db_connect();
    }
}

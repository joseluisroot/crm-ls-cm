<?php

namespace Modules\Operations\Application;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;

final class OperationsQueueQueryService
{
    public function __construct(private readonly ?BaseConnection $db = null)
    {
    }

    /** @param int[]|null $assignedUserIds */
    public function summary(?array $assignedUserIds = null): array
    {
        $catalog = new OperationalQueueCatalog();
        $counts = array_fill_keys(array_keys($catalog->all()), 0);

        $builder = $this->connection()->table('work_items wi')
            ->select('st.code, COUNT(*) AS total')
            ->join('work_item_statuses st', 'st.id = wi.status_id')
            ->groupBy('st.code');
        $this->applyScope($builder, $assignedUserIds);

        foreach ($builder->get()->getResultArray() as $row) {
            $code = strtoupper((string) $row['code']);
            foreach ($catalog->all() as $group => $definition) {
                if (in_array($code, $definition['codes'], true)) {
                    $counts[$group] += (int) $row['total'];
                    break;
                }
            }
        }

        return [
            'total' => array_sum($counts),
            'pending' => $counts['PENDING'],
            'active' => $counts['ACTIVE'],
            'waiting' => $counts['WAITING'],
            'completed' => $counts['COMPLETED'],
            'cancelled' => $counts['CANCELLED'],
        ];
    }

    /** @param int[]|null $assignedUserIds */
    public function items(?string $group = null, ?string $status = null, ?string $priority = null, int $limit = 100, ?array $assignedUserIds = null): array
    {
        $builder = $this->connection()->table('work_items wi')
            ->select('wi.*, st.code AS status, st.name AS status_name, pr.code AS priority, pr.name AS priority_name, ch.code AS channel, ot.code AS origin_type, sc.author_name, sc.message AS comment_message, sc.commented_at, sp.external_post_id, sp.message AS post_message, sp.permalink_url, au.name AS assigned_user_name')
            ->join('work_item_statuses st', 'st.id = wi.status_id')
            ->join('work_item_priorities pr', 'pr.id = wi.priority_id')
            ->join('work_item_channels ch', 'ch.id = wi.channel_id')
            ->join('work_item_origin_types ot', 'ot.id = wi.origin_type_id')
            ->join('social_comments sc', "ot.code = 'FACEBOOK_COMMENT' AND sc.external_comment_id = wi.origin_id", 'left')
            ->join('social_posts sp', 'sp.id = sc.social_post_id', 'left')
            ->join('admin_users au', 'au.id = wi.assigned_user_id', 'left')
            ->orderBy('pr.sort_order', 'DESC')
            ->orderBy('wi.opened_at', 'ASC')
            ->limit(max(1, min($limit, 200)));

        $codes = (new OperationalQueueCatalog())->codesFor($group);
        if ($codes !== []) {
            $builder->whereIn('st.code', $codes);
        }
        if ($status) {
            $builder->where('st.code', strtoupper($status));
        }
        if ($priority) {
            $builder->where('pr.code', strtoupper($priority));
        }
        $this->applyScope($builder, $assignedUserIds);

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

    /** @param int[]|null $assignedUserIds */
    private function applyScope(BaseBuilder $builder, ?array $assignedUserIds): void
    {
        if ($assignedUserIds === null) {
            return;
        }

        $assignedUserIds = array_values(array_unique(array_filter(array_map('intval', $assignedUserIds))));
        if ($assignedUserIds === []) {
            $builder->where('1 = 0', null, false);
            return;
        }

        $builder->whereIn('wi.assigned_user_id', $assignedUserIds);
    }

    private function connection(): BaseConnection
    {
        return $this->db ?? db_connect();
    }
}

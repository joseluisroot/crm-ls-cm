<?php

namespace Modules\Operations\Application;

use CodeIgniter\Database\BaseConnection;

final class OperationsDetailQueryService
{
    public function __construct(private readonly ?BaseConnection $db = null)
    {
    }

    public function find(int $id): ?array
    {
        $item = $this->connection()->table('work_items wi')
            ->select('wi.*, ot.code AS origin_type, ch.code AS channel, st.code AS status, st.name AS status_name, pr.code AS priority, pr.name AS priority_name, au.name AS assigned_user_name, c.public_code AS case_public_code')
            ->join('work_item_origin_types ot', 'ot.id = wi.origin_type_id')
            ->join('work_item_channels ch', 'ch.id = wi.channel_id')
            ->join('work_item_statuses st', 'st.id = wi.status_id')
            ->join('work_item_priorities pr', 'pr.id = wi.priority_id')
            ->join('admin_users au', 'au.id = wi.assigned_user_id', 'left')
            ->join('cases c', 'c.id = wi.case_id', 'left')
            ->where('wi.id', $id)->get()->getRowArray();

        if (! $item) return null;
        $metadata = json_decode((string) ($item['metadata_json'] ?? '{}'), true);
        $item['metadata'] = is_array($metadata) ? $metadata : [];
        $originType = strtoupper((string) ($item['origin_type'] ?? ''));

        if ($originType === 'FACEBOOK_COMMENT') {
            $item['source'] = $this->connection()->table('social_comments sc')
                ->select('sc.*, sp.message AS post_message, sp.external_post_id, sp.permalink_url')
                ->join('social_posts sp', 'sp.id = sc.social_post_id')
                ->where('sc.external_comment_id', $item['origin_id'])->get()->getRowArray();
        } elseif (in_array($originType, ['MESSENGER', 'MESSENGER_MESSAGE'], true)) {
            $item['source'] = $this->connection()->table('messages m')
                ->select('m.*, ci.name AS author_name, ci.facebook_id, co.id AS conversation_id')
                ->join('conversations co', 'co.id = m.conversation_id')
                ->join('citizens ci', 'ci.id = co.citizen_id')
                ->groupStart()->where('m.external_message_id', $item['origin_id'])->orWhere('m.id', is_numeric((string) $item['origin_id']) ? (int) $item['origin_id'] : 0)->groupEnd()
                ->get()->getRowArray();
            if ($item['source']) $item['source']['message'] = $item['source']['body'] ?? null;
        } else {
            $item['source'] = null;
        }

        return $item;
    }

    public function timeline(int $id): array
    {
        return $this->connection()->table('system_events')->where('entity_type', 'work_item')->where('entity_id', $id)->orderBy('id', 'DESC')->get()->getResultArray();
    }

    public function users(): array
    {
        return $this->connection()->table('admin_users')->select('id, name, email')->where('status', 'active')->orderBy('name', 'ASC')->get()->getResultArray();
    }

    public function statuses(): array { return $this->catalog('work_item_statuses'); }
    public function priorities(): array { return $this->catalog('work_item_priorities'); }

    private function catalog(string $table): array
    {
        return $this->connection()->table($table)->where('is_active', 1)->orderBy('sort_order', 'ASC')->get()->getResultArray();
    }

    private function connection(): BaseConnection
    {
        return $this->db ?? db_connect();
    }
}

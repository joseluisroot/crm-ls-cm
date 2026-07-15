<?php

declare(strict_types=1);

namespace Modules\Cases\Application;

use CodeIgniter\Database\BaseConnection;

final class CaseWidgetQueryService
{
    public function __construct(private readonly ?BaseConnection $db = null)
    {
    }

    /** @return array<string, mixed>|null */
    public function find(int $caseId): ?array
    {
        if ($caseId <= 0) {
            return null;
        }

        $db = $this->db ?? db_connect();
        $case = $db->table('cases c')
            ->select('c.id, c.public_code, c.title, c.description, c.priority, c.sentiment, c.assigned_to, c.assigned_user_id, c.created_at, c.updated_at, c.closed_at, cs.name AS status_name, cat.name AS category_name, COALESCE(au_new.name, au_legacy.name) AS assigned_user_name', false)
            ->join('case_statuses cs', 'cs.id = c.status_id')
            ->join('categories cat', 'cat.id = c.category_id', 'left')
            ->join('admin_users au_new', 'au_new.id = c.assigned_user_id', 'left')
            ->join('admin_users au_legacy', 'au_legacy.id = c.assigned_to', 'left')
            ->where('c.id', $caseId)
            ->get()
            ->getRowArray();

        if (! $case) {
            return null;
        }

        $openedAt = (string) ($case['created_at'] ?? '');
        $closedAt = (string) ($case['closed_at'] ?? '');
        $start = $openedAt !== '' ? strtotime($openedAt) : false;
        $end = $closedAt !== '' ? strtotime($closedAt) : time();

        $case['open_seconds'] = $start === false || $end === false ? null : max(0, $end - $start);
        $case['is_closed'] = $closedAt !== '';
        $case['detail_url'] = site_url('admin/cases/' . $caseId);

        return $case;
    }
}

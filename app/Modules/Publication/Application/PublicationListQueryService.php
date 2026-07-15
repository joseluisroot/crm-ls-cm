<?php

declare(strict_types=1);

namespace Modules\Publication\Application;

use CodeIgniter\Database\BaseConnection;

final class PublicationListQueryService
{
    public function __construct(private readonly ?BaseConnection $db = null)
    {
    }

    /** @return array{items:array,total:int,page:int,perPage:int,pages:int} */
    public function paginate(string $search = '', int $page = 1, int $perPage = 25): array
    {
        $page = max(1, $page);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;
        $search = trim($search);
        $db = $this->connection();

        $count = $db->table('social_posts');
        $this->applySearch($count, $search);
        $total = (int) $count->countAllResults();
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $pages);

        $builder = $db->table('social_posts p')->select('p.*');
        $this->applySearch($builder, $search, 'p.');
        $items = $builder
            ->orderBy('p.published_at', 'DESC')
            ->orderBy('p.id', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();

        $ids = array_values(array_filter(array_map('intval', array_column($items, 'id'))));
        $commentCounts = [];
        $reactionCounts = [];

        if ($ids !== []) {
            foreach ($db->table('social_comments')
                         ->select('social_post_id, COUNT(*) AS total')
                         ->whereIn('social_post_id', $ids)
                         ->groupBy('social_post_id')
                         ->get()->getResultArray() as $row) {
                $commentCounts[(int) $row['social_post_id']] = (int) $row['total'];
            }

            foreach ($db->table('social_reactions')
                         ->select('social_post_id, COUNT(*) AS total')
                         ->whereIn('social_post_id', $ids)
                         ->where('is_active', 1)
                         ->groupBy('social_post_id')
                         ->get()->getResultArray() as $row) {
                $reactionCounts[(int) $row['social_post_id']] = (int) $row['total'];
            }
        }

        foreach ($items as &$item) {
            $postId = (int) $item['id'];
            $item['comments_count'] = $commentCounts[$postId] ?? 0;
            $item['reactions_count'] = $reactionCounts[$postId] ?? 0;
        }
        unset($item);

        return compact('items', 'total', 'page', 'perPage', 'pages');
    }

    private function applySearch($builder, string $search, string $prefix = ''): void
    {
        if ($search === '') {
            return;
        }

        $builder->groupStart()
            ->like($prefix . 'message', $search)
            ->orLike($prefix . 'external_post_id', $search)
            ->groupEnd();
    }

    private function connection(): BaseConnection
    {
        return $this->db ?? db_connect();
    }
}

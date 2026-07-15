<?php

declare(strict_types=1);

namespace Modules\Publication\Application;

use CodeIgniter\Database\BaseConnection;

final class PublicationListQueryService
{
    public function __construct(private readonly ?BaseConnection $db = null)
    {
    }

    public function paginate(string $search = '', int $page = 1, int $perPage = 25): array
    {
        $page = max(1, $page);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;
        $search = trim($search);

        $count = $this->connection()->table('social_posts');
        if ($search !== '') {
            $count->groupStart()->like('message', $search)->orLike('external_post_id', $search)->groupEnd();
        }
        $total = (int) $count->countAllResults();
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $pages);

        $builder = $this->connection()->table('social_posts p')->select('p.*');
        if ($search !== '') {
            $builder->groupStart()->like('p.message', $search)->orLike('p.external_post_id', $search)->groupEnd();
        }

        $items = $builder
            ->orderBy('p.published_at', 'DESC')
            ->orderBy('p.id', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();

        foreach ($items as &$item) {
            $postId = (int) $item['id'];
            $item['comments_count'] = $this->connection()->table('social_comments')->where('social_post_id', $postId)->countAllResults();
            $item['reactions_count'] = $this->connection()->table('social_reactions')->where('social_post_id', $postId)->where('is_active', 1)->countAllResults();
        }
        unset($item);

        return compact('items', 'total', 'page', 'perPage', 'pages');
    }

    private function connection(): BaseConnection
    {
        return $this->db ?? db_connect();
    }
}

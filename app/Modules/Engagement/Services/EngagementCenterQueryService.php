<?php

namespace Modules\Engagement\Services;

use CodeIgniter\Database\BaseConnection;

class EngagementCenterQueryService
{
    public function __construct(private readonly ?BaseConnection $db = null)
    {
    }

    public function summary(): array
    {
        $db = $this->connection();

        return [
            'posts' => (int) $db->table('social_posts')->countAllResults(),
            'comments' => (int) $db->table('social_comments')->countAllResults(),
            'pending_comments' => (int) $db->table('social_comments')
                ->where('requires_response', 1)
                ->whereNotIn('status', ['removed', 'responded', 'closed'])
                ->countAllResults(),
            'active_reactions' => (int) $db->table('social_reactions')->where('is_active', 1)->countAllResults(),
        ];
    }

    /** @return array{items:array,total:int,page:int,perPage:int,pages:int} */
    public function paginateComments(?string $status, string $search, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;
        $search = trim($search);

        $count = $this->commentsBuilder($status, $search, false);
        $total = (int) $count->countAllResults();
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $pages);

        $items = $this->commentsBuilder($status, $search, true)
            ->orderBy('c.commented_at', 'DESC')
            ->orderBy('c.id', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();

        return compact('items', 'total', 'page', 'perPage', 'pages');
    }

    public function comments(?string $status = null, int $limit = 50): array
    {
        return $this->commentsBuilder($status, '', true)
            ->orderBy('c.commented_at', 'DESC')
            ->orderBy('c.id', 'DESC')
            ->limit(max(1, min($limit, 200)))
            ->get()
            ->getResultArray();
    }

    public function reactions(int $limit = 50): array
    {
        return $this->connection()->table('social_reactions r')
            ->select('r.*, p.message AS post_message, p.external_post_id')
            ->join('social_posts p', 'p.id = r.social_post_id', 'left')
            ->where('r.is_active', 1)
            ->orderBy('r.reacted_at', 'DESC')
            ->orderBy('r.id', 'DESC')
            ->limit(max(1, min($limit, 200)))
            ->get()
            ->getResultArray();
    }

    /** @return array{items:array,total:int,page:int,perPage:int,pages:int} */
    public function paginateParticipants(string $search, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;
        $search = mb_strtolower(trim($search));
        $people = $this->allParticipants();

        if ($search !== '') {
            $people = array_values(array_filter($people, static function (array $person) use ($search): bool {
                return str_contains(mb_strtolower((string) $person['name']), $search)
                    || str_contains(mb_strtolower((string) $person['external_id']), $search);
            }));
        }

        $total = count($people);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $pages);
        $items = array_slice($people, ($page - 1) * $perPage, $perPage);

        return compact('items', 'total', 'page', 'perPage', 'pages');
    }

    public function participants(int $limit = 25): array
    {
        return array_slice($this->allParticipants(), 0, max(1, min($limit, 100)));
    }

    public function reactionBreakdown(): array
    {
        $rows = $this->connection()->table('social_reactions')
            ->select('reaction_type, COUNT(*) AS total')
            ->where('is_active', 1)
            ->groupBy('reaction_type')
            ->orderBy('total', 'DESC')
            ->get()
            ->getResultArray();

        return array_map(static fn (array $row): array => [
            'reaction_type' => $row['reaction_type'],
            'total' => (int) $row['total'],
        ], $rows);
    }

    private function commentsBuilder(?string $status, string $search, bool $select)
    {
        $builder = $this->connection()->table('social_comments c')
            ->join('social_posts p', 'p.id = c.social_post_id', 'left');

        if ($select) {
            $builder->select('c.*, p.message AS post_message, p.external_post_id, p.permalink_url');
        }

        if ($status !== null && $status !== '') {
            $builder->where('c.status', $status);
        }

        $search = trim($search);
        if ($search !== '') {
            $builder->groupStart()
                ->like('c.author_name', $search)
                ->orLike('c.message', $search)
                ->orLike('p.message', $search)
                ->orLike('p.external_post_id', $search)
                ->groupEnd();
        }

        return $builder;
    }

    private function allParticipants(): array
    {
        $db = $this->connection();
        $comments = $db->table('social_comments')
            ->select('author_external_id AS external_id, MAX(author_name) AS name, COUNT(*) AS comments_count, MAX(commented_at) AS last_comment_at')
            ->where('author_external_id IS NOT NULL', null, false)
            ->groupBy('author_external_id')->get()->getResultArray();
        $reactions = $db->table('social_reactions')
            ->select('actor_external_id AS external_id, MAX(actor_name) AS name, COUNT(*) AS reactions_count, MAX(reacted_at) AS last_reaction_at')
            ->where('is_active', 1)
            ->where('actor_external_id IS NOT NULL', null, false)
            ->groupBy('actor_external_id')->get()->getResultArray();

        $people = [];
        foreach ($comments as $row) {
            $id = (string) $row['external_id'];
            $people[$id] = [
                'external_id' => $id,
                'name' => $row['name'] ?: 'Usuario de Facebook',
                'comments_count' => (int) $row['comments_count'],
                'reactions_count' => 0,
                'last_interaction_at' => $row['last_comment_at'],
            ];
        }

        foreach ($reactions as $row) {
            $id = (string) $row['external_id'];
            if (! isset($people[$id])) {
                $people[$id] = [
                    'external_id' => $id,
                    'name' => $row['name'] ?: 'Usuario de Facebook',
                    'comments_count' => 0,
                    'reactions_count' => 0,
                    'last_interaction_at' => null,
                ];
            }
            $people[$id]['reactions_count'] = (int) $row['reactions_count'];
            $people[$id]['last_interaction_at'] = $this->latestDate($people[$id]['last_interaction_at'], $row['last_reaction_at']);
        }

        foreach ($people as &$person) {
            $person['total_interactions'] = $person['comments_count'] + $person['reactions_count'];
        }
        unset($person);

        usort($people, static fn (array $a, array $b): int =>
            $b['total_interactions'] <=> $a['total_interactions']
            ?: strcmp((string) $a['name'], (string) $b['name'])
        );

        return array_values($people);
    }

    private function latestDate(?string $first, ?string $second): ?string
    {
        if (! $first) return $second;
        if (! $second) return $first;
        return strtotime($first) >= strtotime($second) ? $first : $second;
    }

    private function connection(): BaseConnection
    {
        return $this->db ?? db_connect();
    }
}

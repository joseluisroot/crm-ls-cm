<?php

declare(strict_types=1);

namespace Modules\Publication\Application;

use CodeIgniter\Database\BaseConnection;

final class PublicationProfileQueryService
{
    public function __construct(private readonly ?BaseConnection $db = null)
    {
    }

    public function publications(int $limit = 100): array
    {
        $limit = max(1, min($limit, 200));

        return $this->connection()->table('social_posts p')
            ->select('p.*')
            ->select('(SELECT COUNT(*) FROM social_comments c WHERE c.social_post_id = p.id) AS comments_count', false)
            ->select('(SELECT COUNT(*) FROM social_reactions r WHERE r.social_post_id = p.id AND r.is_active = 1) AS reactions_count', false)
            ->orderBy('p.id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    public function profile(int $publicationId): ?array
    {
        if ($publicationId <= 0) {
            return null;
        }

        $db = $this->connection();
        $publication = $db->table('social_posts')
            ->where('id', $publicationId)
            ->get()
            ->getRowArray();

        if (! $publication) {
            return null;
        }

        $comments = $db->table('social_comments c')
            ->select('c.*, wi.id AS work_item_id, wi.case_id, st.code AS work_item_status, pr.code AS work_item_priority')
            ->join('work_items wi', 'wi.origin_id = c.external_comment_id', 'left')
            ->join('work_item_origin_types ot', "ot.id = wi.origin_type_id AND ot.code = 'FACEBOOK_COMMENT'", 'left')
            ->join('work_item_statuses st', 'st.id = wi.status_id', 'left')
            ->join('work_item_priorities pr', 'pr.id = wi.priority_id', 'left')
            ->where('c.social_post_id', $publicationId)
            ->orderBy('c.commented_at', 'DESC')
            ->orderBy('c.id', 'DESC')
            ->get()
            ->getResultArray();

        $reactions = $db->table('social_reactions')
            ->where('social_post_id', $publicationId)
            ->where('is_active', 1)
            ->orderBy('reacted_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        $participants = $this->participants($comments, $reactions);
        $workItemIds = [];
        $caseIds = [];
        $pendingComments = 0;

        foreach ($comments as $comment) {
            if (! empty($comment['work_item_id'])) {
                $workItemIds[(int) $comment['work_item_id']] = true;
            }
            if (! empty($comment['case_id'])) {
                $caseIds[(int) $comment['case_id']] = true;
            }
            if ((int) ($comment['requires_response'] ?? 0) === 1
                && ! in_array((string) ($comment['status'] ?? ''), ['removed', 'responded', 'closed'], true)) {
                $pendingComments++;
            }
        }

        $reactionBreakdown = [];
        foreach ($reactions as $reaction) {
            $type = strtoupper((string) ($reaction['reaction_type'] ?? 'UNKNOWN'));
            $reactionBreakdown[$type] = ($reactionBreakdown[$type] ?? 0) + 1;
        }
        arsort($reactionBreakdown);

        return [
            'publication' => $publication,
            'comments' => $comments,
            'reactions' => $reactions,
            'participants' => array_values($participants),
            'reaction_breakdown' => $reactionBreakdown,
            'metrics' => [
                'comments' => count($comments),
                'pending_comments' => $pendingComments,
                'reactions' => count($reactions),
                'participants' => count($participants),
                'work_items' => count($workItemIds),
                'cases' => count($caseIds),
            ],
        ];
    }

    private function participants(array $comments, array $reactions): array
    {
        $participants = [];

        foreach ($comments as $comment) {
            $externalId = trim((string) ($comment['author_external_id'] ?? ''));
            if ($externalId === '') {
                continue;
            }

            $participants[$externalId] ??= [
                'external_id' => $externalId,
                'name' => $comment['author_name'] ?: 'Usuario de Facebook',
                'comments_count' => 0,
                'reactions_count' => 0,
                'last_interaction_at' => null,
            ];
            $participants[$externalId]['comments_count']++;
            $participants[$externalId]['last_interaction_at'] = $this->latestDate(
                $participants[$externalId]['last_interaction_at'],
                $comment['commented_at'] ?? null,
            );
        }

        foreach ($reactions as $reaction) {
            $externalId = trim((string) ($reaction['actor_external_id'] ?? ''));
            if ($externalId === '') {
                continue;
            }

            $participants[$externalId] ??= [
                'external_id' => $externalId,
                'name' => $reaction['actor_name'] ?: 'Usuario de Facebook',
                'comments_count' => 0,
                'reactions_count' => 0,
                'last_interaction_at' => null,
            ];
            $participants[$externalId]['reactions_count']++;
            $participants[$externalId]['last_interaction_at'] = $this->latestDate(
                $participants[$externalId]['last_interaction_at'],
                $reaction['reacted_at'] ?? null,
            );
        }

        foreach ($participants as &$participant) {
            $participant['total_interactions'] = $participant['comments_count'] + $participant['reactions_count'];
        }
        unset($participant);

        uasort($participants, static fn (array $left, array $right): int =>
            $right['total_interactions'] <=> $left['total_interactions']
        );

        return $participants;
    }

    private function latestDate(?string $first, ?string $second): ?string
    {
        if (! $first) {
            return $second;
        }
        if (! $second) {
            return $first;
        }

        return strtotime($first) >= strtotime($second) ? $first : $second;
    }

    private function connection(): BaseConnection
    {
        return $this->db ?? db_connect();
    }
}

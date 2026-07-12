<?php

namespace Modules\Operations\Application;

use CodeIgniter\Database\BaseConnection;

final class FacebookCommentWorkItemAdapter
{
    public function __construct(
        private readonly CitizenOperationsService $operations,
        private readonly ?BaseConnection $db = null,
    ) {
    }

    public function importByExternalId(string $externalCommentId): ?array
    {
        $comment = $this->connection()->table('social_comments sc')
            ->select('sc.*, sp.external_post_id, sp.message AS post_message, sp.permalink_url')
            ->join('social_posts sp', 'sp.id = sc.social_post_id')
            ->where('sc.external_comment_id', $externalCommentId)
            ->get()
            ->getRowArray();

        if (! $comment || (int) $comment['requires_response'] !== 1 || $comment['status'] === 'removed') {
            return null;
        }

        return $this->operations->create(new CreateWorkItemData(
            originType: 'FACEBOOK_COMMENT',
            originId: (string) $comment['external_comment_id'],
            channel: 'FACEBOOK',
            title: 'Comentario de ' . ($comment['author_name'] ?: 'Usuario de Facebook'),
            summary: (string) ($comment['message'] ?: 'Comentario sin texto disponible.'),
            priority: strtoupper((string) ($comment['priority'] ?: 'NORMAL')),
            metadata: [
                'social_comment_id' => (int) $comment['id'],
                'social_post_id' => (int) $comment['social_post_id'],
                'external_post_id' => $comment['external_post_id'],
                'author_external_id' => $comment['author_external_id'],
                'author_name' => $comment['author_name'],
                'post_message' => $comment['post_message'],
                'permalink_url' => $comment['permalink_url'],
                'commented_at' => $comment['commented_at'],
            ],
        ));
    }

    public function importPending(int $limit = 200): int
    {
        $rows = $this->connection()->table('social_comments')
            ->select('external_comment_id')
            ->where('requires_response', 1)
            ->where('status !=', 'removed')
            ->orderBy('id', 'ASC')
            ->limit(max(1, min($limit, 1000)))
            ->get()
            ->getResultArray();

        $imported = 0;
        foreach ($rows as $row) {
            if ($this->importByExternalId((string) $row['external_comment_id']) !== null) {
                $imported++;
            }
        }

        return $imported;
    }

    private function connection(): BaseConnection
    {
        return $this->db ?? db_connect();
    }
}

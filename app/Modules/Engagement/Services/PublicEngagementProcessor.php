<?php

namespace Modules\Engagement\Services;

use CodeIgniter\Database\BaseConnection;
use Throwable;

class PublicEngagementProcessor
{
    public function __construct(
        private readonly ?BaseConnection $db = null,
    ) {
    }

    public function process(string $pageId, array $change): void
    {
        $field = (string) ($change['field'] ?? 'unknown');
        $value = is_array($change['value'] ?? null) ? $change['value'] : [];
        $eventType = $this->eventType($field, $value);
        $eventKey = $this->eventKey($pageId, $field, $eventType, $value);
        $db = $this->connection();

        $existing = $db->table('social_engagement_events')
            ->where('external_event_key', $eventKey)
            ->get()
            ->getRowArray();

        if ($existing) {
            return;
        }

        $eventId = $db->table('social_engagement_events')->insert([
            'platform' => 'facebook',
            'page_id' => $pageId,
            'field_name' => $field,
            'event_type' => $eventType,
            'external_event_key' => $eventKey,
            'raw_payload' => $this->json($change),
            'processed' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ], true);

        try {
            if ($field === 'feed') {
                $this->processFeedChange($pageId, $value);
            }

            $db->table('social_engagement_events')
                ->where('id', $eventId)
                ->update([
                    'processed' => 1,
                    'processed_at' => date('Y-m-d H:i:s'),
                ]);

            service('eventEngine')->emit(
                name: 'engagement.' . $eventType,
                module: 'engagement',
                payload: [
                    'page_id' => $pageId,
                    'field' => $field,
                    'value' => $value,
                ],
                entityType: 'social_engagement_event',
                entityId: (int) $eventId,
            );
        } catch (Throwable $error) {
            $db->table('social_engagement_events')
                ->where('id', $eventId)
                ->update([
                    'processing_error' => $error->getMessage(),
                    'processed_at' => date('Y-m-d H:i:s'),
                ]);

            throw $error;
        }
    }

    private function processFeedChange(string $pageId, array $value): void
    {
        $item = (string) ($value['item'] ?? '');

        if ($item === 'comment') {
            $this->upsertComment($pageId, $value);
            return;
        }

        if ($item === 'reaction') {
            $this->upsertReaction($pageId, $value);
            return;
        }

        if (in_array($item, ['status', 'post', 'photo', 'video', 'share'], true)) {
            $this->upsertPost($pageId, $value);
        }
    }

    private function upsertPost(string $pageId, array $value): int
    {
        $externalPostId = (string) ($value['post_id'] ?? $value['parent_id'] ?? $value['id'] ?? '');

        if ($externalPostId === '') {
            return 0;
        }

        $data = [
            'platform' => 'facebook',
            'page_id' => $pageId,
            'external_post_id' => $externalPostId,
            'message' => $value['message'] ?? null,
            'post_type' => $value['item'] ?? null,
            'published_at' => $this->dateFromTimestamp($value['created_time'] ?? null),
            'raw_payload' => $this->json($value),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $table = $this->connection()->table('social_posts');
        $existing = $table->where('external_post_id', $externalPostId)->get()->getRowArray();

        if ($existing) {
            $table->where('id', $existing['id'])->update($data);
            return (int) $existing['id'];
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        return (int) $table->insert($data, true);
    }

    private function upsertComment(string $pageId, array $value): void
    {
        $postId = $this->upsertPost($pageId, $value);
        $externalCommentId = (string) ($value['comment_id'] ?? $value['id'] ?? '');

        if ($postId <= 0 || $externalCommentId === '') {
            return;
        }

        $verb = (string) ($value['verb'] ?? 'add');
        $from = is_array($value['from'] ?? null) ? $value['from'] : [];
        $data = [
            'social_post_id' => $postId,
            'external_comment_id' => $externalCommentId,
            'external_parent_id' => $value['parent_id'] ?? null,
            'author_external_id' => $from['id'] ?? null,
            'author_name' => $from['name'] ?? null,
            'message' => $value['message'] ?? null,
            'status' => $verb === 'remove' ? 'removed' : 'new',
            'requires_response' => $verb === 'remove' ? 0 : 1,
            'commented_at' => $this->dateFromTimestamp($value['created_time'] ?? null),
            'raw_payload' => $this->json($value),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $table = $this->connection()->table('social_comments');
        $existing = $table->where('external_comment_id', $externalCommentId)->get()->getRowArray();

        if ($existing) {
            $table->where('id', $existing['id'])->update($data);
            return;
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $table->insert($data);
    }

    private function upsertReaction(string $pageId, array $value): void
    {
        $postId = $this->upsertPost($pageId, $value);
        $objectId = (string) ($value['post_id'] ?? $value['parent_id'] ?? $value['id'] ?? '');
        $from = is_array($value['from'] ?? null) ? $value['from'] : [];
        $actorId = (string) ($from['id'] ?? '');

        if ($objectId === '' || $actorId === '') {
            return;
        }

        $verb = (string) ($value['verb'] ?? 'add');
        $data = [
            'social_post_id' => $postId > 0 ? $postId : null,
            'external_object_id' => $objectId,
            'actor_external_id' => $actorId,
            'actor_name' => $from['name'] ?? null,
            'reaction_type' => strtoupper((string) ($value['reaction_type'] ?? 'LIKE')),
            'is_active' => $verb === 'remove' ? 0 : 1,
            'reacted_at' => $this->dateFromTimestamp($value['created_time'] ?? null),
            'raw_payload' => $this->json($value),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $table = $this->connection()->table('social_reactions');
        $existing = $table
            ->where('external_object_id', $objectId)
            ->where('actor_external_id', $actorId)
            ->get()
            ->getRowArray();

        if ($existing) {
            $table->where('id', $existing['id'])->update($data);
            return;
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $table->insert($data);
    }

    private function eventType(string $field, array $value): string
    {
        $item = strtolower((string) ($value['item'] ?? $field));
        $verb = strtolower((string) ($value['verb'] ?? 'received'));

        return match ($item) {
            'comment' => 'comment.' . $verb,
            'reaction' => 'reaction.' . $verb,
            'status', 'post', 'photo', 'video', 'share' => 'post.' . $verb,
            default => 'change.received',
        };
    }

    private function eventKey(string $pageId, string $field, string $type, array $value): string
    {
        return hash('sha256', implode('|', [
            $pageId,
            $field,
            $type,
            (string) ($value['post_id'] ?? ''),
            (string) ($value['comment_id'] ?? $value['id'] ?? ''),
            (string) (($value['from']['id'] ?? '') ?: ''),
            (string) ($value['created_time'] ?? ''),
        ]));
    }

    private function dateFromTimestamp(mixed $timestamp): ?string
    {
        return is_numeric($timestamp) ? date('Y-m-d H:i:s', (int) $timestamp) : null;
    }

    private function json(array $value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }

    private function connection(): BaseConnection
    {
        return $this->db ?? db_connect();
    }
}

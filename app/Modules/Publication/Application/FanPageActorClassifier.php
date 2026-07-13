<?php

declare(strict_types=1);

namespace Modules\Publication\Application;

final class FanPageActorClassifier
{
    public function enrich(array $profile): array
    {
        $pageId = trim((string) ($profile['publication']['page_id'] ?? ''));

        $profile['comments'] = array_map(
            fn (array $comment): array => $this->classify($comment, 'author_external_id', $pageId),
            $profile['comments'] ?? [],
        );

        $profile['participants'] = array_map(
            fn (array $participant): array => $this->classify($participant, 'external_id', $pageId),
            $profile['participants'] ?? [],
        );

        $profile['actor_metrics'] = [
            'institutional_comments' => count(array_filter(
                $profile['comments'],
                static fn (array $comment): bool => ! empty($comment['is_page_actor']),
            )),
            'institutional_participants' => count(array_filter(
                $profile['participants'],
                static fn (array $participant): bool => ! empty($participant['is_page_actor']),
            )),
        ];

        return $profile;
    }

    public function isPageActor(?string $externalId, ?string $pageId): bool
    {
        $externalId = trim((string) $externalId);
        $pageId = trim((string) $pageId);

        return $externalId !== '' && $pageId !== '' && hash_equals($pageId, $externalId);
    }

    private function classify(array $record, string $externalIdKey, string $pageId): array
    {
        $isPageActor = $this->isPageActor(
            isset($record[$externalIdKey]) ? (string) $record[$externalIdKey] : null,
            $pageId,
        );

        return [
            ...$record,
            'is_page_actor' => $isPageActor,
            'actor_scope' => $isPageActor ? 'INSTITUTION' : 'EXTERNAL',
            'actor_evidence' => $isPageActor ? 'external_id_matches_page_id' : null,
        ];
    }
}

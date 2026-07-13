<?php

declare(strict_types=1);

namespace Modules\Publication\Application;

use Modules\Citizen\Application\CitizenResolverService;
use Modules\Citizen\Application\IdentityRequest;
use Modules\Citizen\Domain\ValueObjects\ActorType;
use Modules\Citizen\Domain\ValueObjects\IdentityChannel;
use Modules\Citizen\Domain\ValueObjects\IdentityConfidence;
use Throwable;

final class ResolvePublicationParticipantsService
{
    public function __construct(private readonly CitizenResolverService $resolver)
    {
    }

    public function resolve(int $publicationId, array $participants): array
    {
        $candidates = self::candidates($participants);
        $result = [
            'publication_id' => $publicationId,
            'requested' => count($candidates),
            'resolved' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($candidates as $candidate) {
            try {
                $this->resolver->resolve(new IdentityRequest(
                    channel: new IdentityChannel(IdentityChannel::FACEBOOK),
                    externalId: $candidate['external_id'],
                    displayName: $candidate['name'],
                    actorType: new ActorType(ActorType::CITIZEN),
                    confidence: new IdentityConfidence(IdentityConfidence::EXACT),
                    metadata: [
                        'source' => 'publication_participant_resolution',
                        'publication_id' => $publicationId,
                        'comments_count' => $candidate['comments_count'],
                        'reactions_count' => $candidate['reactions_count'],
                    ],
                ));

                $result['resolved']++;
            } catch (Throwable $exception) {
                $result['failed']++;
                $result['errors'][] = [
                    'external_id' => $candidate['external_id'],
                    'message' => $exception->getMessage(),
                ];
            }
        }

        return $result;
    }

    public static function candidates(array $participants): array
    {
        $candidates = [];

        foreach ($participants as $participant) {
            if (
                ! empty($participant['citizen_id'])
                || ! empty($participant['identity_resolved'])
                || ! empty($participant['is_page_actor'])
                || (($participant['actor_scope'] ?? null) === 'INSTITUTION')
            ) {
                continue;
            }

            $externalId = trim((string) ($participant['external_id'] ?? ''));
            if ($externalId === '') {
                continue;
            }

            $candidates[$externalId] = [
                'external_id' => $externalId,
                'name' => trim((string) ($participant['name'] ?? '')) ?: 'Usuario de Facebook',
                'comments_count' => (int) ($participant['comments_count'] ?? 0),
                'reactions_count' => (int) ($participant['reactions_count'] ?? 0),
            ];
        }

        return array_values($candidates);
    }
}

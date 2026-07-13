<?php

namespace Modules\Citizen\Application;

use Modules\Citizen\Domain\Entities\SocialIdentity;
use Modules\Citizen\Domain\Repositories\SocialIdentityRepositoryInterface;
use Modules\Citizen\Infrastructure\Publishers\CitizenIdentityEventPublisher;
use Modules\Citizens\Models\CitizenModel;
use RuntimeException;

final class CitizenResolverService
{
    public function __construct(
        private readonly SocialIdentityRepositoryInterface $identities,
        private readonly CitizenIdentityEventPublisher $publisher,
        private readonly ?CitizenModel $citizens = null,
    ) {
    }

    public function resolve(IdentityRequest $request): array
    {
        $channel = $request->channel->value();
        $existing = $this->identities->findByChannelAndExternalId($channel, $request->externalId);

        if ($existing) {
            $citizen = $this->citizenModel()->find((int) $existing['citizen_id']);
            if (! $citizen) {
                throw new RuntimeException('Identity points to a missing citizen.');
            }

            $this->publisher->resolved((int) $citizen['id'], [
                'citizen_id' => (int) $citizen['id'],
                'identity_id' => (int) $existing['id'],
                'channel' => $channel,
                'external_id' => $request->externalId,
                'resolution' => 'existing',
            ]);

            return $citizen;
        }

        $citizenId = (int) $this->citizenModel()->insert([
            'facebook_id' => in_array($channel, ['FACEBOOK', 'MESSENGER'], true)
                ? $request->externalId
                : null,
            'name' => $request->displayName ?: 'Ciudadano ' . $request->externalId,
            'status' => 'active',
        ], true);

        if ($citizenId <= 0) {
            throw new RuntimeException('Unable to create citizen during identity resolution.');
        }

        $identity = new SocialIdentity(
            uuid: $this->uuidV4(),
            citizenId: $citizenId,
            channel: $request->channel,
            externalId: $request->externalId,
            displayName: $request->displayName,
            actorType: $request->actorType,
            confidence: $request->confidence,
            metadata: $request->metadata,
        );

        $identityId = $this->identities->create($identity);

        $this->publisher->created($citizenId, [
            'citizen_id' => $citizenId,
            'identity_id' => $identityId,
            'channel' => $channel,
            'external_id' => $request->externalId,
            'actor_type' => $request->actorType->value(),
            'confidence' => $request->confidence->value(),
        ]);

        return $this->citizenModel()->find($citizenId) ?? ['id' => $citizenId];
    }

    private function citizenModel(): CitizenModel
    {
        return $this->citizens ?? new CitizenModel();
    }

    private function uuidV4(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12),
        );
    }
}

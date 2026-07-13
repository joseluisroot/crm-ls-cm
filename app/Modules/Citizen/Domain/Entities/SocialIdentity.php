<?php

namespace Modules\Citizen\Domain\Entities;

use Modules\Citizen\Domain\ValueObjects\ActorType;
use Modules\Citizen\Domain\ValueObjects\IdentityChannel;
use Modules\Citizen\Domain\ValueObjects\IdentityConfidence;

final class SocialIdentity
{
    public function __construct(
        private readonly string $uuid,
        private readonly int $citizenId,
        private readonly IdentityChannel $channel,
        private readonly string $externalId,
        private readonly ?string $displayName,
        private readonly ActorType $actorType,
        private readonly IdentityConfidence $confidence,
        private readonly array $metadata = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'citizen_id' => $this->citizenId,
            'channel' => $this->channel->value(),
            'external_id' => $this->externalId,
            'display_name' => $this->displayName,
            'actor_type' => $this->actorType->value(),
            'confidence' => $this->confidence->value(),
            'metadata' => $this->metadata,
            'is_active' => 1,
        ];
    }
}

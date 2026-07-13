<?php

namespace Modules\Citizen\Application;

use Modules\Citizen\Domain\ValueObjects\ActorType;
use Modules\Citizen\Domain\ValueObjects\IdentityChannel;
use Modules\Citizen\Domain\ValueObjects\IdentityConfidence;

final class IdentityRequest
{
    public function __construct(
        public readonly IdentityChannel $channel,
        public readonly string $externalId,
        public readonly ?string $displayName = null,
        public readonly ActorType $actorType = new ActorType(ActorType::CITIZEN),
        public readonly IdentityConfidence $confidence = new IdentityConfidence(IdentityConfidence::EXACT),
        public readonly array $metadata = [],
    ) {
    }
}

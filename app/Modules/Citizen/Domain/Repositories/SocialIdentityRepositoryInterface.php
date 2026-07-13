<?php

namespace Modules\Citizen\Domain\Repositories;

use Modules\Citizen\Domain\Entities\SocialIdentity;

interface SocialIdentityRepositoryInterface
{
    public function findByChannelAndExternalId(string $channel, string $externalId): ?array;

    public function create(SocialIdentity $identity): int;
}

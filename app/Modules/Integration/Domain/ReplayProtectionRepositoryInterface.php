<?php

declare(strict_types=1);

namespace Modules\Integration\Domain;

interface ReplayProtectionRepositoryInterface
{
    public function findOriginalByExternalEventId(string $source, string $externalEventId): ?array;
}

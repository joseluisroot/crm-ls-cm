<?php

declare(strict_types=1);

namespace Modules\Integration\Application;

use Modules\Integration\Domain\ReplayProtectionRepositoryInterface;

final class IntegrationReplayProtectionService
{
    public function __construct(private readonly ReplayProtectionRepositoryInterface $repository)
    {
    }

    public function findDuplicate(string $source, ?string $externalEventId): ?array
    {
        $source = strtoupper(trim($source));
        $externalEventId = trim((string) $externalEventId);

        if ($source === '' || $externalEventId === '') {
            return null;
        }

        return $this->repository->findOriginalByExternalEventId($source, $externalEventId);
    }
}

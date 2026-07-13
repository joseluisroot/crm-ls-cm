<?php

declare(strict_types=1);

namespace Modules\Citizen\Application\Contracts;

use Modules\Citizen\Application\DTO\CitizenTimelineItemDTO;

interface CitizenTimelineRepositoryInterface
{
    /**
     * @return CitizenTimelineItemDTO[]
     */
    public function timeline(int $citizenId): array;

    public function metrics(int $citizenId): array;
}

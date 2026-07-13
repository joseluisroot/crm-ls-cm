<?php

declare(strict_types=1);

namespace Modules\Citizen\Application\Queries;

use InvalidArgumentException;
use Modules\Citizen\Application\Contracts\CitizenTimelineRepositoryInterface;
use Modules\Citizen\Application\DTO\CitizenTimelineDTO;

final class CitizenTimelineQueryService
{
    public function __construct(
        private readonly CitizenTimelineRepositoryInterface $repository,
    ) {
    }

    public function timeline(int $citizenId): CitizenTimelineDTO
    {
        if ($citizenId <= 0) {
            throw new InvalidArgumentException('Citizen ID must be greater than zero.');
        }

        return new CitizenTimelineDTO(
            citizenId: $citizenId,
            metrics: $this->repository->metrics($citizenId),
            items: $this->repository->timeline($citizenId),
        );
    }
}

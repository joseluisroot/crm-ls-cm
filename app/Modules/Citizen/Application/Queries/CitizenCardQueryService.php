<?php

declare(strict_types=1);

namespace Modules\Citizen\Application\Queries;

use InvalidArgumentException;
use Modules\Citizen\Application\Contracts\CitizenCardRepositoryInterface;
use Modules\Citizen\Application\DTO\CitizenCardDTO;

final class CitizenCardQueryService
{
    public function __construct(
        private readonly CitizenCardRepositoryInterface $repository,
    ) {
    }

    public function get(int $citizenId): ?CitizenCardDTO
    {
        if ($citizenId <= 0) {
            throw new InvalidArgumentException('Citizen ID must be greater than zero.');
        }

        return $this->repository->find($citizenId);
    }
}

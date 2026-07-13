<?php

declare(strict_types=1);

namespace Modules\Citizen\Application\Contracts;

use Modules\Citizen\Application\DTO\CitizenCardDTO;

interface CitizenCardRepositoryInterface
{
    public function find(int $citizenId): ?CitizenCardDTO;
}

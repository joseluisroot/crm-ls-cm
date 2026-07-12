<?php

namespace Modules\Operations\Domain\Repositories;

use Modules\Operations\Domain\Entities\WorkItem;

interface WorkItemRepositoryInterface
{
    public function create(WorkItem $workItem): int;

    public function findByOrigin(string $originType, string $originId): ?array;

    public function find(int $id): ?array;

    public function updateState(int $id, array $changes): void;
}

<?php

declare(strict_types=1);

namespace Modules\Citizen\Application\DTO;

final class CitizenTimelineDTO
{
    /**
     * @param CitizenTimelineItemDTO[] $items
     */
    public function __construct(
        public readonly int $citizenId,
        public readonly array $metrics,
        public readonly array $items,
    ) {
    }
}

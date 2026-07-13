<?php

declare(strict_types=1);

namespace Modules\Citizen\Application\DTO;

final class CitizenTimelineItemDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly string $title,
        public readonly ?string $description,
        public readonly string $occurredAt,
        public readonly array $metadata = [],
    ) {
    }
}

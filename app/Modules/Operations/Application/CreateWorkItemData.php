<?php

namespace Modules\Operations\Application;

final class CreateWorkItemData
{
    public function __construct(
        public readonly string $originType,
        public readonly string $originId,
        public readonly string $channel,
        public readonly string $title,
        public readonly ?string $summary = null,
        public readonly ?int $citizenId = null,
        public readonly string $priority = 'NORMAL',
        public readonly array $metadata = [],
    ) {
    }
}

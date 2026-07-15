<?php

declare(strict_types=1);

namespace Modules\Core\UI\Widgets;

final readonly class WidgetContext
{
    /** @param array<string, mixed> $attributes */
    public function __construct(
        public int $viewerUserId,
        public ?int $workItemId = null,
        public ?int $citizenId = null,
        public ?int $caseId = null,
        public array $attributes = [],
    ) {
    }

    public function attribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }
}

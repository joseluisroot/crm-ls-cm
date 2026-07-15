<?php

declare(strict_types=1);

namespace Modules\Core\UI\Widgets;

final readonly class WidgetResult
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $meta
     */
    public function __construct(
        public string $key,
        public string $title,
        public string $view,
        public array $data = [],
        public array $meta = [],
    ) {
        if ($this->key === '' || $this->view === '') {
            throw new \InvalidArgumentException('WidgetResult requires a key and view.');
        }
    }
}

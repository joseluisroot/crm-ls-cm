<?php

declare(strict_types=1);

namespace Modules\Core\UI\Widgets;

final class WidgetManager
{
    public function __construct(
        private readonly WidgetRegistry $registry,
        private readonly WidgetRenderer $renderer,
    ) {
    }

    /**
     * @param string[] $keys
     * @return array<string, string>
     */
    public function render(array $keys, WidgetContext $context): array
    {
        $results = [];

        foreach ($keys as $key) {
            $widget = $this->registry->get($key);
            if (! $widget->supports($context)) {
                continue;
            }

            $result = $widget->build($context);
            if ($result->key !== $widget->key()) {
                throw new \LogicException(sprintf(
                    'Widget "%s" returned result key "%s".',
                    $widget->key(),
                    $result->key,
                ));
            }

            $results[] = $result;
        }

        return $this->renderer->renderMany($results);
    }
}

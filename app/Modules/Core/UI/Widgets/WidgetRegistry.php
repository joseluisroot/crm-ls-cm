<?php

declare(strict_types=1);

namespace Modules\Core\UI\Widgets;

final class WidgetRegistry
{
    /** @var array<string, WidgetInterface> */
    private array $widgets = [];

    /** @param iterable<WidgetInterface> $widgets */
    public function __construct(iterable $widgets = [])
    {
        foreach ($widgets as $widget) {
            $this->register($widget);
        }
    }

    public function register(WidgetInterface $widget): self
    {
        $key = trim($widget->key());
        if ($key === '') {
            throw new \InvalidArgumentException('Widget key cannot be empty.');
        }

        if (isset($this->widgets[$key])) {
            throw new \LogicException(sprintf('Widget "%s" is already registered.', $key));
        }

        $this->widgets[$key] = $widget;

        return $this;
    }

    public function get(string $key): WidgetInterface
    {
        if (! isset($this->widgets[$key])) {
            throw new \OutOfBoundsException(sprintf('Widget "%s" is not registered.', $key));
        }

        return $this->widgets[$key];
    }

    /** @return array<string, WidgetInterface> */
    public function all(): array
    {
        return $this->widgets;
    }

    /** @return array<string, WidgetInterface> */
    public function supported(WidgetContext $context): array
    {
        return array_filter(
            $this->widgets,
            static fn (WidgetInterface $widget): bool => $widget->supports($context),
        );
    }
}

<?php

declare(strict_types=1);

namespace Modules\Core\UI\Widgets;

interface WidgetInterface
{
    public function key(): string;

    public function supports(WidgetContext $context): bool;

    public function build(WidgetContext $context): WidgetResult;
}

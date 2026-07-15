<?php

declare(strict_types=1);

namespace Modules\Citizen\Presentation\Widgets;

use Modules\Core\UI\Widgets\WidgetContext;
use Modules\Core\UI\Widgets\WidgetInterface;
use Modules\Core\UI\Widgets\WidgetResult;

final class CitizenWidget implements WidgetInterface
{
    public function key(): string
    {
        return 'citizen';
    }

    public function supports(WidgetContext $context): bool
    {
        return ($context->citizenId ?? 0) > 0;
    }

    public function build(WidgetContext $context): WidgetResult
    {
        if (! $this->supports($context)) {
            throw new \InvalidArgumentException('CitizenWidget requires a valid citizen ID.');
        }

        $citizen = service('citizenCard')->get((int) $context->citizenId);

        return new WidgetResult(
            key: $this->key(),
            title: 'Ciudadano',
            view: 'Modules\\Citizen\\Presentation\\Views\\widgets\\citizen',
            data: [
                'citizen' => $citizen,
                'workItemId' => $context->workItemId,
            ],
            meta: [
                'tone' => 'pink',
                'empty' => $citizen === null,
            ],
        );
    }
}

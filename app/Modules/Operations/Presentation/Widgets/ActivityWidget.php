<?php

declare(strict_types=1);

namespace Modules\Operations\Presentation\Widgets;

use Modules\Core\UI\Widgets\WidgetContext;
use Modules\Core\UI\Widgets\WidgetInterface;
use Modules\Core\UI\Widgets\WidgetResult;
use Modules\Operations\Application\WorkItemActivityQueryService;

final class ActivityWidget implements WidgetInterface
{
    public function __construct(private readonly ?WorkItemActivityQueryService $query = null)
    {
    }

    public function key(): string
    {
        return 'activity';
    }

    public function supports(WidgetContext $context): bool
    {
        return ($context->workItemId ?? 0) > 0;
    }

    public function build(WidgetContext $context): WidgetResult
    {
        return new WidgetResult(
            key: $this->key(),
            title: 'Actividad reciente',
            view: 'Modules\\Operations\\Presentation\\Views\\widgets\\activity',
            data: ($this->query ?? new WorkItemActivityQueryService())->get((int) $context->workItemId),
            meta: ['work_item_id' => $context->workItemId],
        );
    }
}

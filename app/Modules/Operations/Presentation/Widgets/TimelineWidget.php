<?php

declare(strict_types=1);

namespace Modules\Operations\Presentation\Widgets;

use Modules\Core\UI\Widgets\WidgetContext;
use Modules\Core\UI\Widgets\WidgetInterface;
use Modules\Core\UI\Widgets\WidgetResult;
use Modules\Operations\Application\WorkItemTimelineQueryService;

final class TimelineWidget implements WidgetInterface
{
    public function __construct(private readonly ?WorkItemTimelineQueryService $query = null)
    {
    }

    public function key(): string
    {
        return 'timeline';
    }

    public function supports(WidgetContext $context): bool
    {
        return ($context->workItemId ?? 0) > 0;
    }

    public function build(WidgetContext $context): WidgetResult
    {
        $timeline = ($this->query ?? new WorkItemTimelineQueryService())->get((int) $context->workItemId);

        return new WidgetResult(
            key: $this->key(),
            title: 'Timeline de atención',
            view: 'Modules\\Operations\\Presentation\\Views\\widgets\\timeline',
            data: $timeline,
            meta: ['work_item_id' => $context->workItemId],
        );
    }
}

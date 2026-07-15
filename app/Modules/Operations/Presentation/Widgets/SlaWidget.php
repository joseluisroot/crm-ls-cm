<?php

declare(strict_types=1);

namespace Modules\Operations\Presentation\Widgets;

use Modules\Core\UI\Widgets\WidgetContext;
use Modules\Core\UI\Widgets\WidgetInterface;
use Modules\Core\UI\Widgets\WidgetResult;
use Modules\Operations\Application\WorkItemSlaQueryService;

final class SlaWidget implements WidgetInterface
{
    public function __construct(private readonly ?WorkItemSlaQueryService $query = null)
    {
    }

    public function key(): string
    {
        return 'sla';
    }

    public function supports(WidgetContext $context): bool
    {
        return ($context->workItemId ?? 0) > 0;
    }

    public function build(WidgetContext $context): WidgetResult
    {
        $sla = ($this->query ?? new WorkItemSlaQueryService())->get((int) $context->workItemId);

        return new WidgetResult(
            key: $this->key(),
            title: 'SLA de atención',
            view: 'Modules\\Operations\\Presentation\\Views\\widgets\\sla',
            data: ['sla' => $sla],
            meta: ['work_item_id' => $context->workItemId],
        );
    }
}

<?php

declare(strict_types=1);

namespace Modules\Operations\Presentation\Widgets;

use Modules\Core\UI\Widgets\WidgetContext;
use Modules\Core\UI\Widgets\WidgetInterface;
use Modules\Core\UI\Widgets\WidgetResult;
use Modules\Operations\Application\WorkItemAssignmentQueryService;

final class AssignmentWidget implements WidgetInterface
{
    public function __construct(private readonly ?WorkItemAssignmentQueryService $query = null)
    {
    }

    public function key(): string
    {
        return 'assignment';
    }

    public function supports(WidgetContext $context): bool
    {
        return (int) ($context->workItemId ?? 0) > 0;
    }

    public function build(WidgetContext $context): WidgetResult
    {
        $history = ($this->query ?? new WorkItemAssignmentQueryService())->get((int) $context->workItemId);

        return new WidgetResult(
            key: $this->key(),
            title: 'Historial de asignaciones',
            view: 'Modules\\Operations\\Presentation\\Views\\widgets\\assignment',
            data: $history,
            meta: ['work_item_id' => $context->workItemId],
        );
    }
}

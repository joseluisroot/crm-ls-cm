<?php

declare(strict_types=1);

namespace Modules\Operations\Presentation\Widgets;

use Modules\Core\UI\Widgets\WidgetContext;
use Modules\Core\UI\Widgets\WidgetInterface;
use Modules\Core\UI\Widgets\WidgetResult;
use Modules\Operations\Application\WorkItemNotesService;

final class NotesWidget implements WidgetInterface
{
    public function key(): string
    {
        return 'operations.notes';
    }

    public function supports(WidgetContext $context): bool
    {
        return (int) ($context->workItemId ?? 0) > 0;
    }

    public function build(WidgetContext $context): WidgetResult
    {
        $workItemId = (int) $context->workItemId;

        return new WidgetResult(
            key: $this->key(),
            view: 'Modules\\Operations\\Presentation\\Views\\widgets\\notes',
            data: [
                'workItemId' => $workItemId,
                'notes' => (new WorkItemNotesService())->forWorkItem($workItemId),
                'canAddNote' => can('operations.update') || can('operations.reply'),
            ],
        );
    }
}

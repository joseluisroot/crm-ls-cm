<?php

declare(strict_types=1);

namespace Modules\Operations\Application\Workspace;

use Modules\Cases\Presentation\Widgets\CaseWidget;
use Modules\Citizen\Presentation\Widgets\CitizenWidget;
use Modules\Core\UI\Widgets\WidgetContext;
use Modules\Core\UI\Widgets\WidgetInterface;
use Modules\Core\UI\Widgets\WidgetRenderer;
use Modules\Operations\Presentation\Widgets\ActivityWidget;
use Modules\Operations\Presentation\Widgets\AssignmentWidget;
use Modules\Operations\Presentation\Widgets\SlaWidget;
use Modules\Operations\Presentation\Widgets\TimelineWidget;
use Throwable;

final class WorkspaceBuilder
{
    /** @var array<string, WidgetInterface> */
    private array $widgets;

    /**
     * @param iterable<WidgetInterface>|null $widgets
     */
    public function __construct(
        private readonly ?WidgetRenderer $renderer = null,
        ?iterable $widgets = null,
    ) {
        $this->widgets = [];

        foreach ($widgets ?? $this->defaultWidgets() as $widget) {
            $this->widgets[$widget->key()] = $widget;
        }
    }

    public function build(WidgetContext $context): WorkspaceViewModel
    {
        $renderer = $this->renderer ?? new WidgetRenderer();
        $rendered = [];
        $timings = [];

        foreach ($this->widgets as $key => $widget) {
            $startedAt = microtime(true);

            try {
                $rendered[$key] = $widget->supports($context)
                    ? $renderer->render($widget->build($context))
                    : null;
            } catch (Throwable $error) {
                $rendered[$key] = null;

                log_message('error', sprintf(
                    'Workspace widget "%s" failed for work item %s: %s',
                    $key,
                    (string) ($context->workItemId ?? 0),
                    $error->getMessage(),
                ));
            } finally {
                $timings[$key] = round((microtime(true) - $startedAt) * 1000, 2);
            }
        }

        return new WorkspaceViewModel($rendered, $timings);
    }

    /** @return WidgetInterface[] */
    private function defaultWidgets(): array
    {
        return [
            new CitizenWidget(),
            new TimelineWidget(),
            new SlaWidget(),
            new CaseWidget(),
            new AssignmentWidget(),
            new ActivityWidget(),
        ];
    }
}

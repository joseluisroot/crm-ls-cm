<?php

declare(strict_types=1);

namespace Modules\Operations\Application\Workspace;

final readonly class WorkspaceViewModel
{
    /**
     * @param array<string, ?string> $widgets
     * @param array<string, float> $timings
     */
    public function __construct(
        public array $widgets,
        public array $timings = [],
    ) {
    }

    public function widget(string $key): ?string
    {
        return $this->widgets[$key] ?? null;
    }

    /** @return array<string, ?string> */
    public function toViewData(): array
    {
        return [
            'citizenWidgetHtml' => $this->widget('citizen'),
            'timelineWidgetHtml' => $this->widget('timeline'),
            'slaWidgetHtml' => $this->widget('sla'),
            'caseWidgetHtml' => $this->widget('case'),
            'assignmentWidgetHtml' => $this->widget('assignment'),
            'activityWidgetHtml' => $this->widget('activity'),
            'workspaceWidgetTimings' => $this->timings,
        ];
    }
}

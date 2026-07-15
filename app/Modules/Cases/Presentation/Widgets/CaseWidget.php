<?php

declare(strict_types=1);

namespace Modules\Cases\Presentation\Widgets;

use Modules\Cases\Application\CaseWidgetQueryService;
use Modules\Core\UI\Widgets\WidgetContext;
use Modules\Core\UI\Widgets\WidgetInterface;
use Modules\Core\UI\Widgets\WidgetResult;

final class CaseWidget implements WidgetInterface
{
    public function __construct(private readonly ?CaseWidgetQueryService $query = null)
    {
    }

    public function key(): string
    {
        return 'case';
    }

    public function supports(WidgetContext $context): bool
    {
        return ($context->caseId ?? 0) > 0;
    }

    public function build(WidgetContext $context): WidgetResult
    {
        $case = ($this->query ?? new CaseWidgetQueryService())->find((int) $context->caseId);

        return new WidgetResult(
            key: $this->key(),
            title: 'Caso relacionado',
            view: 'Modules\\Cases\\Presentation\\Views\\widgets\\case',
            data: [
                'caseItem' => $case,
                'canUpdateCase' => function_exists('can') && can('cases.update'),
                'canAssignCase' => function_exists('can') && can('cases.assign'),
            ],
        );
    }
}

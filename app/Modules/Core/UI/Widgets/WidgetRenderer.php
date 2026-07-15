<?php

declare(strict_types=1);

namespace Modules\Core\UI\Widgets;

use CodeIgniter\View\RendererInterface;
use Config\Services;

final class WidgetRenderer
{
    public function __construct(private readonly ?RendererInterface $renderer = null)
    {
    }

    public function render(WidgetResult $result): string
    {
        $renderer = $this->renderer ?? Services::renderer();

        return $renderer
            ->setData([
                'widget' => $result,
                ...$result->data,
            ])
            ->render($result->view);
    }

    /** @param iterable<WidgetResult> $results */
    public function renderMany(iterable $results): array
    {
        $rendered = [];

        foreach ($results as $result) {
            $rendered[$result->key] = $this->render($result);
        }

        return $rendered;
    }
}

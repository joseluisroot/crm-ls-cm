<?php

namespace Modules\Analytics\Metrics;

interface MetricInterface
{
    public function key(): string;

    public function calculate(): mixed;
}
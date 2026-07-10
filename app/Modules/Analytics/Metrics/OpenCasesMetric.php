<?php

namespace Modules\Analytics\Metrics;

use Modules\Analytics\Repositories\AnalyticsRepository;

class OpenCasesMetric implements MetricInterface
{
    public function __construct(
        private readonly AnalyticsRepository $repository
    ) {
    }

    public function key(): string
    {
        return 'open_cases';
    }

    public function calculate(): int
    {
        return $this->repository->openCases();
    }
}
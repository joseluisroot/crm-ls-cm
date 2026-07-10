<?php


namespace Modules\Analytics\Metrics;

use Modules\Analytics\Repositories\AnalyticsRepository;

class ListeningEffectivenessMetric implements MetricInterface
{
    public function __construct(
        private readonly AnalyticsRepository $repository
    )
    {
    }

    public function key(): string
    {
        return 'listening_effectiveness';
    }

    public function calculate(): float
    {
        $inbound = $this->repository->totalInboundMessages();

        if ($inbound <= 0) {
            return 0.0;
        }

        $outbound = $this->repository->totalOutboundMessages();

        return round(
            min(100, ($outbound / $inbound) * 100),
            2
        );
    }
}
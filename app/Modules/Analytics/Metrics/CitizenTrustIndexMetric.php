<?php

namespace Modules\Analytics\Metrics;

use Modules\Analytics\Repositories\AnalyticsRepository;

class CitizenTrustIndexMetric implements MetricInterface
{
    public function __construct(
        private readonly AnalyticsRepository $repository
    ) {
    }

    public function key(): string
    {
        return 'citizen_trust_index';
    }

    public function calculate(): float
    {
        $citizens = $this->repository->totalCitizens();

        if ($citizens <= 0) {
            return 0.0;
        }

        $conversations = $this->repository->totalConversations();
        $recurring = $this->repository->recurringCitizens();
        $completedContext = $this->repository
            ->citizensWithCompletedContext();

        $inbound = $this->repository->totalInboundMessages();
        $outbound = $this->repository->totalOutboundMessages();

        $participationScore = min(
            100,
            ($conversations / $citizens) * 100
        );

        $recurrenceScore = min(
            100,
            ($recurring / $citizens) * 100
        );

        $contextCompletionScore = min(
            100,
            ($completedContext / $citizens) * 100
        );

        $responseScore = $inbound > 0
            ? min(100, ($outbound / $inbound) * 100)
            : 0;

        /*
         * Índice provisional del canal:
         * 30% participación
         * 25% recurrencia
         * 20% finalización de contexto
         * 25% respuesta efectiva
         */
        $score =
            ($participationScore * 0.30) +
            ($recurrenceScore * 0.25) +
            ($contextCompletionScore * 0.20) +
            ($responseScore * 0.25);

        return round(min(100, max(0, $score)), 2);
    }
}
<?php

namespace Modules\Analytics\Metrics;

use Modules\Analytics\Repositories\AnalyticsRepository;

class CIACHealthScoreMetric implements MetricInterface
{
    public function __construct(
        private readonly AnalyticsRepository $repository
    ) {
    }

    public function key(): string
    {
        return 'ciac_health_score';
    }

    public function calculate(): float
    {
        $totalCases = $this->repository->totalCases();

        if ($totalCases <= 0) {
            return 100.0;
        }

        $resolvedCases = $this->repository->resolvedCases();
        $assignedCases = $this->repository->assignedCases();
        $unassignedCases = $this->repository->unassignedCases();

        $resolutionScore = min(
            100,
            ($resolvedCases / $totalCases) * 100
        );

        $assignmentScore = min(
            100,
            ($assignedCases / $totalCases) * 100
        );

        $unassignedPenalty = min(
            100,
            ($unassignedCases / $totalCases) * 100
        );

        $listeningScore = (
        new ListeningEffectivenessMetric($this->repository)
        )->calculate();

        /*
         * Salud operativa inicial:
         * 35% resolución
         * 25% asignación
         * 30% efectividad de escucha
         * 10% penalización por casos sin asignar
         */
        $score =
            ($resolutionScore * 0.35) +
            ($assignmentScore * 0.25) +
            ($listeningScore * 0.30) +
            ((100 - $unassignedPenalty) * 0.10);

        return round(min(100, max(0, $score)), 2);
    }
}
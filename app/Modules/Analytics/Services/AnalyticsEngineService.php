<?php

namespace Modules\Analytics\Services;

use Modules\Analytics\DTO\AnalyticsDashboardDTO;
use Modules\Analytics\Metrics\CIACHealthScoreMetric;
use Modules\Analytics\Metrics\CitizenTrustIndexMetric;
use Modules\Analytics\Metrics\ListeningEffectivenessMetric;
use Modules\Analytics\Metrics\OpenCasesMetric;
use Modules\Analytics\Metrics\TotalCasesMetric;
use Modules\Analytics\Repositories\AnalyticsRepository;

class AnalyticsEngineService
{
    private AnalyticsRepository $repository;

    public function __construct(
        ?AnalyticsRepository $repository = null
    ) {
        $this->repository = $repository
            ?? new AnalyticsRepository();
    }

    public function buildExecutiveDashboard(): AnalyticsDashboardDTO
    {
        $totalCasesMetric = new TotalCasesMetric(
            $this->repository
        );

        $openCasesMetric = new OpenCasesMetric(
            $this->repository
        );

        $listeningMetric = new ListeningEffectivenessMetric(
            $this->repository
        );

        $trustMetric = new CitizenTrustIndexMetric(
            $this->repository
        );

        $healthMetric = new CIACHealthScoreMetric(
            $this->repository
        );

        return new AnalyticsDashboardDTO(
            citizens: [
                'total' => $this->repository->totalCitizens(),
                'new_today' => $this->repository
                    ->newCitizensToday(),
                'recurring' => $this->repository
                    ->recurringCitizens(),
            ],
            conversations: [
                'total' => $this->repository
                    ->totalConversations(),
                'open' => $this->repository
                    ->openConversations(),
                'today' => $this->repository
                    ->conversationsToday(),
            ],
            messages: [
                'today' => $this->repository
                    ->messagesToday(),
                'inbound' => $this->repository
                    ->totalInboundMessages(),
                'outbound' => $this->repository
                    ->totalOutboundMessages(),
            ],
            cases: [
                'total' => $totalCasesMetric->calculate(),
                'open' => $openCasesMetric->calculate(),
                'resolved' => $this->repository
                    ->resolvedCases(),
                'unassigned' => $this->repository
                    ->unassignedCases(),
                'assigned' => $this->repository
                    ->assignedCases(),
            ],
            distribution: [
                'by_status' => $this->repository
                    ->casesByStatus(),
                'by_category' => $this->repository
                    ->casesByCategory(),
                'by_municipality' => $this->repository
                    ->casesByMunicipality(),
                'by_responsible' => $this->repository
                    ->casesByResponsible(),
            ],
            trends: [
                'cases_last_14_days' => $this->repository
                    ->casesCreatedLastDays(14),
                'messages_last_14_days' => $this->repository
                    ->messagesCreatedLastDays(14),
            ],
            indices: [
                'listening_effectiveness' => $listeningMetric
                    ->calculate(),
                'citizen_trust_index' => $trustMetric
                    ->calculate(),
                'ciac_health_score' => $healthMetric
                    ->calculate(),
            ],
        );
    }
}
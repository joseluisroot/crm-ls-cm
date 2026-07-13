<?php

declare(strict_types=1);

namespace Modules\Publication\Application;

final class PublicationAnalyticsService
{
    public function analyze(array $profile): array
    {
        $comments = is_array($profile['comments'] ?? null) ? $profile['comments'] : [];
        $reactions = is_array($profile['reactions'] ?? null) ? $profile['reactions'] : [];
        $participants = is_array($profile['participants'] ?? null) ? $profile['participants'] : [];
        $metrics = is_array($profile['metrics'] ?? null) ? $profile['metrics'] : [];

        $commentCount = count($comments);
        $reactionCount = count($reactions);
        $interactionCount = $commentCount + $reactionCount;
        $workItemCount = (int) ($metrics['work_items'] ?? 0);
        $caseCount = (int) ($metrics['cases'] ?? 0);
        $pendingCount = (int) ($metrics['pending_comments'] ?? 0);

        $statusBreakdown = [];
        $priorityBreakdown = [];
        $respondedComments = 0;
        $activityByDate = [];

        foreach ($comments as $comment) {
            $status = strtoupper(trim((string) ($comment['status'] ?? 'RECEIVED'))) ?: 'RECEIVED';
            $statusBreakdown[$status] = ($statusBreakdown[$status] ?? 0) + 1;

            $priority = strtoupper(trim((string) ($comment['work_item_priority'] ?? '')));
            if ($priority !== '') {
                $priorityBreakdown[$priority] = ($priorityBreakdown[$priority] ?? 0) + 1;
            }

            if (in_array(strtolower((string) ($comment['status'] ?? '')), ['responded', 'closed'], true)) {
                $respondedComments++;
            }

            $this->incrementDate($activityByDate, $comment['commented_at'] ?? null, 'comments');
        }

        foreach ($reactions as $reaction) {
            $this->incrementDate($activityByDate, $reaction['reacted_at'] ?? null, 'reactions');
        }

        ksort($activityByDate);
        arsort($statusBreakdown);
        arsort($priorityBreakdown);

        $topParticipantShare = 0.0;
        if ($interactionCount > 0 && $participants !== []) {
            $topParticipantShare = round(
                ((int) ($participants[0]['total_interactions'] ?? 0) / $interactionCount) * 100,
                1,
            );
        }

        return [
            'kpis' => [
                'total_interactions' => $interactionCount,
                'response_rate' => $this->percentage($respondedComments, $commentCount),
                'pending_rate' => $this->percentage($pendingCount, $commentCount),
                'work_item_conversion_rate' => $this->percentage($workItemCount, $commentCount),
                'case_conversion_rate' => $this->percentage($caseCount, $workItemCount),
                'top_participant_share' => $topParticipantShare,
            ],
            'status_breakdown' => $statusBreakdown,
            'priority_breakdown' => $priorityBreakdown,
            'activity_by_date' => array_values($activityByDate),
        ];
    }

    private function incrementDate(array &$series, mixed $dateTime, string $field): void
    {
        if (! is_string($dateTime) || trim($dateTime) === '') {
            return;
        }

        $timestamp = strtotime($dateTime);
        if ($timestamp === false) {
            return;
        }

        $date = date('Y-m-d', $timestamp);
        $series[$date] ??= [
            'date' => $date,
            'comments' => 0,
            'reactions' => 0,
            'total' => 0,
        ];
        $series[$date][$field]++;
        $series[$date]['total']++;
    }

    private function percentage(int $value, int $total): float
    {
        if ($total <= 0) {
            return 0.0;
        }

        return round(($value / $total) * 100, 1);
    }
}

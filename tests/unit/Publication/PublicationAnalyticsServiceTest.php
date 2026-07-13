<?php

declare(strict_types=1);

namespace Tests\Unit\Publication;

use Modules\Publication\Application\PublicationAnalyticsService;
use PHPUnit\Framework\TestCase;

final class PublicationAnalyticsServiceTest extends TestCase
{
    public function testItCalculatesPublicationIndicators(): void
    {
        $analytics = (new PublicationAnalyticsService())->analyze([
            'comments' => [
                ['status' => 'responded', 'work_item_priority' => 'HIGH', 'commented_at' => '2026-07-10 09:00:00'],
                ['status' => 'received', 'work_item_priority' => 'MEDIUM', 'commented_at' => '2026-07-10 10:00:00'],
                ['status' => 'closed', 'work_item_priority' => null, 'commented_at' => '2026-07-11 10:00:00'],
            ],
            'reactions' => [
                ['reacted_at' => '2026-07-10 11:00:00'],
                ['reacted_at' => '2026-07-11 11:00:00'],
            ],
            'participants' => [
                ['total_interactions' => 3],
                ['total_interactions' => 2],
            ],
            'metrics' => [
                'pending_comments' => 1,
                'work_items' => 2,
                'cases' => 1,
            ],
        ]);

        self::assertSame(5, $analytics['kpis']['total_interactions']);
        self::assertSame(66.7, $analytics['kpis']['response_rate']);
        self::assertSame(33.3, $analytics['kpis']['pending_rate']);
        self::assertSame(66.7, $analytics['kpis']['work_item_conversion_rate']);
        self::assertSame(50.0, $analytics['kpis']['case_conversion_rate']);
        self::assertSame(60.0, $analytics['kpis']['top_participant_share']);
        self::assertSame(2, count($analytics['activity_by_date']));
        self::assertSame(1, $analytics['priority_breakdown']['HIGH']);
    }

    public function testItReturnsZeroRatesWithoutInteractions(): void
    {
        $analytics = (new PublicationAnalyticsService())->analyze([]);

        self::assertSame(0, $analytics['kpis']['total_interactions']);
        self::assertSame(0.0, $analytics['kpis']['response_rate']);
        self::assertSame([], $analytics['activity_by_date']);
    }
}

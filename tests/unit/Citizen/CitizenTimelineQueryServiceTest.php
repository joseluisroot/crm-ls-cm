<?php

declare(strict_types=1);

namespace Tests\Unit\Citizen;

use InvalidArgumentException;
use Modules\Citizen\Application\Contracts\CitizenTimelineRepositoryInterface;
use Modules\Citizen\Application\DTO\CitizenTimelineItemDTO;
use Modules\Citizen\Application\Queries\CitizenTimelineQueryService;
use PHPUnit\Framework\TestCase;

final class CitizenTimelineQueryServiceTest extends TestCase
{
    public function testItBuildsTimelineDtoFromRepository(): void
    {
        $repository = new class implements CitizenTimelineRepositoryInterface {
            public function timeline(int $citizenId): array
            {
                return [
                    new CitizenTimelineItemDTO(
                        id: 'work_item:10',
                        type: 'WORK_ITEM',
                        title: 'Work Item creado',
                        description: 'Comentario ciudadano',
                        occurredAt: '2026-07-13 08:00:00',
                    ),
                ];
            }

            public function metrics(int $citizenId): array
            {
                return [
                    'total_work_items' => 1,
                    'open_work_items' => 1,
                    'total_cases' => 0,
                    'total_identities' => 1,
                ];
            }
        };

        $timeline = (new CitizenTimelineQueryService($repository))->timeline(15);

        self::assertSame(15, $timeline->citizenId);
        self::assertSame(1, $timeline->metrics['total_work_items']);
        self::assertCount(1, $timeline->items);
        self::assertSame('WORK_ITEM', $timeline->items[0]->type);
    }

    public function testItRejectsInvalidCitizenId(): void
    {
        $repository = new class implements CitizenTimelineRepositoryInterface {
            public function timeline(int $citizenId): array
            {
                return [];
            }

            public function metrics(int $citizenId): array
            {
                return [];
            }
        };

        $this->expectException(InvalidArgumentException::class);

        (new CitizenTimelineQueryService($repository))->timeline(0);
    }
}

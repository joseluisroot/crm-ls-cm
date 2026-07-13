<?php

declare(strict_types=1);

namespace Tests\Unit\Citizen;

use InvalidArgumentException;
use Modules\Citizen\Application\Contracts\CitizenCardRepositoryInterface;
use Modules\Citizen\Application\DTO\CitizenCardDTO;
use Modules\Citizen\Application\Queries\CitizenCardQueryService;
use PHPUnit\Framework\TestCase;

final class CitizenCardQueryServiceTest extends TestCase
{
    public function testReturnsCitizenCard(): void
    {
        $card = new CitizenCardDTO(
            citizenId: 10,
            name: 'Ciudadano Demo',
            primaryChannel: 'FACEBOOK',
            totalWorkItems: 4,
            openWorkItems: 2,
            totalCases: 1,
            totalConversations: 3,
            totalIdentities: 1,
            lastActivity: '2026-07-13 12:00:00',
        );

        $repository = new class($card) implements CitizenCardRepositoryInterface {
            public function __construct(private readonly CitizenCardDTO $card)
            {
            }

            public function find(int $citizenId): ?CitizenCardDTO
            {
                return $citizenId === $this->card->citizenId ? $this->card : null;
            }
        };

        $result = (new CitizenCardQueryService($repository))->get(10);

        self::assertSame($card, $result);
        self::assertSame(2, $result?->openWorkItems);
    }

    public function testRejectsInvalidCitizenId(): void
    {
        $repository = new class implements CitizenCardRepositoryInterface {
            public function find(int $citizenId): ?CitizenCardDTO
            {
                return null;
            }
        };

        $this->expectException(InvalidArgumentException::class);
        (new CitizenCardQueryService($repository))->get(0);
    }
}

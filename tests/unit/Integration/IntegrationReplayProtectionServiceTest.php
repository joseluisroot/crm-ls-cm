<?php

declare(strict_types=1);

namespace Tests\Unit\Integration;

use Modules\Integration\Application\IntegrationReplayProtectionService;
use Modules\Integration\Domain\ReplayProtectionRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class IntegrationReplayProtectionServiceTest extends TestCase
{
    public function testFindsExistingOriginalEvent(): void
    {
        $repository = new InMemoryReplayProtectionRepository([
            'FACEBOOK:mid.123' => [
                'id' => 10,
                'correlation_id' => 'correlation-123',
                'status' => 'PROCESSED',
            ],
        ]);
        $service = new IntegrationReplayProtectionService($repository);

        $duplicate = $service->findDuplicate('facebook', ' mid.123 ');

        self::assertNotNull($duplicate);
        self::assertSame('correlation-123', $duplicate['correlation_id']);
        self::assertSame([['FACEBOOK', 'mid.123']], $repository->lookups);
    }

    public function testReturnsNullWhenEventWasNotPreviouslyCaptured(): void
    {
        $repository = new InMemoryReplayProtectionRepository([]);
        $service = new IntegrationReplayProtectionService($repository);

        self::assertNull($service->findDuplicate('FACEBOOK', 'mid.new'));
    }

    public function testSkipsLookupWhenExternalEventIdIsMissing(): void
    {
        $repository = new InMemoryReplayProtectionRepository([]);
        $service = new IntegrationReplayProtectionService($repository);

        self::assertNull($service->findDuplicate('FACEBOOK', null));
        self::assertNull($service->findDuplicate('FACEBOOK', '   '));
        self::assertSame([], $repository->lookups);
    }

    public function testSkipsLookupWhenSourceIsEmpty(): void
    {
        $repository = new InMemoryReplayProtectionRepository([]);
        $service = new IntegrationReplayProtectionService($repository);

        self::assertNull($service->findDuplicate(' ', 'mid.123'));
        self::assertSame([], $repository->lookups);
    }
}

final class InMemoryReplayProtectionRepository implements ReplayProtectionRepositoryInterface
{
    public array $lookups = [];

    public function __construct(private readonly array $events)
    {
    }

    public function findOriginalByExternalEventId(string $source, string $externalEventId): ?array
    {
        $this->lookups[] = [$source, $externalEventId];

        return $this->events[$source . ':' . $externalEventId] ?? null;
    }
}

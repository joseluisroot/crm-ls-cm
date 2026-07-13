<?php

declare(strict_types=1);

namespace Tests\Unit\Integration;

use Modules\Integration\Application\IntegrationEventQueryService;
use PHPUnit\Framework\TestCase;

final class IntegrationEventQueryServiceTest extends TestCase
{
    public function testDecodeReturnsAssociativeArrayForValidJson(): void
    {
        $decoded = IntegrationEventQueryService::decode('{"step":"received","metadata":{"id":15}}');

        self::assertSame('received', $decoded['step']);
        self::assertSame(15, $decoded['metadata']['id']);
    }

    public function testDecodeReturnsEmptyArrayForInvalidOrEmptyJson(): void
    {
        self::assertSame([], IntegrationEventQueryService::decode(null));
        self::assertSame([], IntegrationEventQueryService::decode(''));
        self::assertSame([], IntegrationEventQueryService::decode('{invalid'));
    }
}

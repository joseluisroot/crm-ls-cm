<?php

declare(strict_types=1);

namespace Tests\Unit\Integration;

use Modules\Integration\Domain\IntegrationEvent;
use PHPUnit\Framework\TestCase;

final class IntegrationEventReplayTest extends TestCase
{
    public function testReplayLineageIsStoredOnEntity(): void
    {
        $event = new IntegrationEvent(
            uuid: 'event-uuid',
            source: 'FACEBOOK',
            eventType: 'PAGE_CHANGE_WEBHOOK',
            eventVersion: 1,
            status: IntegrationEvent::STATUS_RECEIVED,
            correlationId: 'correlation-id',
            payloadJson: '{}',
            headersJson: null,
            externalEventId: null,
            endpoint: null,
            requestIp: null,
            signature: null,
            receivedAt: '2026-07-13 18:00:00',
            originalEventId: 15,
            replayAttempt: 2,
        );

        self::assertSame(15, $event->originalEventId);
        self::assertSame(2, $event->replayAttempt);
    }
}

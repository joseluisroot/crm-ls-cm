<?php

declare(strict_types=1);

namespace Tests\Unit\Integration;

use InvalidArgumentException;
use Modules\Integration\Domain\IntegrationEvent;
use PHPUnit\Framework\TestCase;

final class IntegrationEventTest extends TestCase
{
    public function testCreatesReceivedEnvelope(): void
    {
        $event = new IntegrationEvent(
            uuid: '11111111-1111-4111-8111-111111111111',
            source: 'FACEBOOK',
            eventType: 'PAGE_CHANGE_WEBHOOK',
            eventVersion: 1,
            status: IntegrationEvent::STATUS_RECEIVED,
            correlationId: '22222222-2222-4222-8222-222222222222',
            payloadJson: '{"object":"page"}',
            headersJson: '{}',
            externalEventId: 'comment-1',
            endpoint: '/webhooks/messenger',
            requestIp: '127.0.0.1',
            signature: 'sha256=test',
            receivedAt: '2026-07-13 17:00:00',
        );

        self::assertSame('FACEBOOK', $event->source);
        self::assertSame(IntegrationEvent::STATUS_RECEIVED, $event->status);
        self::assertContains(IntegrationEvent::STATUS_FAILED, IntegrationEvent::statuses());
    }

    public function testRejectsInvalidStatus(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new IntegrationEvent(
            uuid: '11111111-1111-4111-8111-111111111111',
            source: 'FACEBOOK',
            eventType: 'WEBHOOK',
            eventVersion: 1,
            status: 'INVALID',
            correlationId: '22222222-2222-4222-8222-222222222222',
            payloadJson: '{}',
            headersJson: null,
            externalEventId: null,
            endpoint: null,
            requestIp: null,
            signature: null,
            receivedAt: '2026-07-13 17:00:00',
        );
    }
}

<?php

declare(strict_types=1);

namespace Modules\Integration\Domain;

use InvalidArgumentException;

final class IntegrationEvent
{
    public const STATUS_RECEIVED = 'RECEIVED';
    public const STATUS_PROCESSING = 'PROCESSING';
    public const STATUS_PROCESSED = 'PROCESSED';
    public const STATUS_FAILED = 'FAILED';
    public const STATUS_REPLAYED = 'REPLAYED';

    public function __construct(
        public readonly string $uuid,
        public readonly string $source,
        public readonly string $eventType,
        public readonly int $eventVersion,
        public readonly string $status,
        public readonly string $correlationId,
        public readonly string $payloadJson,
        public readonly ?string $headersJson,
        public readonly ?string $externalEventId,
        public readonly ?string $endpoint,
        public readonly ?string $requestIp,
        public readonly ?string $signature,
        public readonly string $receivedAt,
        public readonly ?int $originalEventId = null,
        public readonly int $replayAttempt = 0,
    ) {
        if ($this->source === '' || $this->eventType === '') {
            throw new InvalidArgumentException('Integration event source and type are required.');
        }

        if ($this->eventVersion < 1) {
            throw new InvalidArgumentException('Integration event version must be greater than zero.');
        }

        if ($this->replayAttempt < 0) {
            throw new InvalidArgumentException('Replay attempt cannot be negative.');
        }

        if (! in_array($this->status, self::statuses(), true)) {
            throw new InvalidArgumentException('Invalid integration event status: ' . $this->status);
        }
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_RECEIVED,
            self::STATUS_PROCESSING,
            self::STATUS_PROCESSED,
            self::STATUS_FAILED,
            self::STATUS_REPLAYED,
        ];
    }
}

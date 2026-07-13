<?php

declare(strict_types=1);

namespace Modules\Integration\Application;

use Modules\Integration\Domain\IntegrationEventRepositoryInterface;
use RuntimeException;
use Throwable;

final class IntegrationEventReplayService
{
    public function __construct(
        private readonly IntegrationEventRepositoryInterface $repository,
        private readonly IntegrationEventCaptureService $capture,
        private readonly MetaIntegrationEventProcessor $metaProcessor,
    ) {
    }

    public function replay(int $eventId): array
    {
        $original = $this->repository->find($eventId);
        if (! $original) {
            throw new RuntimeException('Integration event not found.');
        }

        $rootId = (int) ($original['original_event_id'] ?: $original['id']);
        $root = $this->repository->find($rootId);
        if (! $root) {
            throw new RuntimeException('Original integration event not found.');
        }

        $payload = json_decode((string) $root['payload_json'], true, 512, JSON_THROW_ON_ERROR);
        $headers = $root['headers_json']
            ? json_decode((string) $root['headers_json'], true, 512, JSON_THROW_ON_ERROR)
            : [];
        $attempt = $this->repository->nextReplayAttempt($rootId);
        $startedAt = microtime(true);

        $replay = $this->capture->capture(
            source: (string) $root['source'],
            eventType: (string) $root['event_type'],
            payload: $payload,
            headers: $headers,
            externalEventId: $root['external_event_id'] ?: null,
            endpoint: 'REPLAY:' . ($root['endpoint'] ?: 'integration-event'),
            requestIp: null,
            signature: $root['signature'] ?: null,
            eventVersion: (int) $root['event_version'],
            originalEventId: $rootId,
            replayAttempt: $attempt,
        );

        $trace = [[
            'step' => 'replay_started',
            'original_event_id' => $rootId,
            'replay_attempt' => $attempt,
            'at' => date(DATE_ATOM),
        ]];

        try {
            if (strtoupper((string) $root['source']) !== 'FACEBOOK') {
                throw new RuntimeException('Replay processor is not available for source: ' . $root['source']);
            }

            $trace = array_merge($trace, $this->metaProcessor->process($payload));
            $trace[] = ['step' => 'replay_completed', 'at' => date(DATE_ATOM)];
            $this->capture->markProcessed((int) $replay['id'], $startedAt, $trace);

            return [...$replay, 'status' => 'PROCESSED'];
        } catch (Throwable $error) {
            $trace[] = ['step' => 'replay_failed', 'message' => $error->getMessage(), 'at' => date(DATE_ATOM)];
            $this->capture->markFailed((int) $replay['id'], $startedAt, $error->getMessage(), $trace);

            return [...$replay, 'status' => 'FAILED', 'error' => $error->getMessage()];
        }
    }
}

<?php

declare(strict_types=1);

namespace Modules\Integration\Controllers;

use App\Controllers\BaseController;
use RuntimeException;

final class IntegrationReplayController extends BaseController
{
    public function replay(int $eventId)
    {
        try {
            $result = service('integrationEventReplay')->replay($eventId);
        } catch (RuntimeException $error) {
            return redirect()->back()->with('integration_replay_error', $error->getMessage());
        }

        $message = sprintf(
            'Replay #%d creado para el evento original #%d con estado %s.',
            $result['replay_attempt'],
            $result['original_event_id'],
            $result['status'],
        );

        return redirect()->back()->with(
            $result['status'] === 'PROCESSED' ? 'integration_replay_success' : 'integration_replay_error',
            $message,
        );
    }
}

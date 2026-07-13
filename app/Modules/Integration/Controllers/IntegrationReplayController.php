<?php

declare(strict_types=1);

namespace Modules\Integration\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;
use RuntimeException;

final class IntegrationReplayController extends BaseController
{
    public function index()
    {
        $filters = [
            'status' => (string) $this->request->getGet('status'),
            'source' => (string) $this->request->getGet('source'),
            'event_type' => (string) $this->request->getGet('event_type'),
            'correlation_id' => (string) $this->request->getGet('correlation_id'),
            'external_event_id' => (string) $this->request->getGet('external_event_id'),
        ];
        $limit = (int) ($this->request->getGet('limit') ?: 100);

        return view('Modules\Integration\Views\index', [
            'title' => 'Replay Center',
            'events' => service('integrationEventQuery')->search($filters, $limit),
            'metrics' => service('integrationEventQuery')->metrics(),
            'filters' => $filters,
            'limit' => max(1, min($limit, 250)),
        ]);
    }

    public function show(int $eventId)
    {
        $detail = service('integrationEventQuery')->detail($eventId);

        if (! $detail) {
            throw PageNotFoundException::forPageNotFound('Evento de integración no encontrado.');
        }

        return view('Modules\Integration\Views\show', [
            'title' => 'Integration Event #' . $eventId,
            ...$detail,
        ]);
    }

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

        return redirect()->to(site_url('admin/integration/events/' . $result['event_id']))->with(
            $result['status'] === 'PROCESSED' ? 'integration_replay_success' : 'integration_replay_error',
            $message,
        );
    }
}

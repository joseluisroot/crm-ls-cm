<?php

declare(strict_types=1);

namespace Modules\Publication\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;

final class PublicationsController extends BaseController
{
    public function index()
    {
        $limit = (int) ($this->request->getGet('limit') ?: 100);

        return view('Modules\Publication\Views\index', [
            'title' => 'Publication Center',
            'publications' => service('publicationProfile')->publications($limit),
            'limit' => max(1, min($limit, 200)),
        ]);
    }

    public function show(int $id)
    {
        $profile = service('publicationProfile')->profile($id);

        if (! $profile) {
            throw PageNotFoundException::forPageNotFound('Publicación no encontrada.');
        }

        $profile = service('fanPageActorClassifier')->enrich($profile);
        $profile = service('publicationCitizenIdentity')->enrich($profile);

        return view('Modules\Publication\Views\show_threads', [
            'title' => 'Publicación #' . $id,
            ...$profile,
            'analytics' => service('publicationAnalytics')->analyze($profile),
            'commentThreads' => service('commentThreads')->build($profile['comments'] ?? []),
        ]);
    }

    public function resolveParticipants(int $id)
    {
        $profile = service('publicationProfile')->profile($id);

        if (! $profile) {
            throw PageNotFoundException::forPageNotFound('Publicación no encontrada.');
        }

        $profile = service('fanPageActorClassifier')->enrich($profile);
        $profile = service('publicationCitizenIdentity')->enrich($profile);
        $result = service('resolvePublicationParticipants')->resolve(
            $id,
            $profile['participants'] ?? [],
        );

        if ($result['requested'] === 0) {
            return redirect()->to(site_url('admin/publications/' . $id))
                ->with('participant_resolution_info', 'No existen participantes ciudadanos pendientes con identificador externo válido.');
        }

        $message = sprintf(
            'Resolución completada: %d de %d participantes procesados correctamente.',
            $result['resolved'],
            $result['requested'],
        );

        if ($result['failed'] > 0) {
            $message .= sprintf(' %d participante(s) no pudieron procesarse.', $result['failed']);
        }

        return redirect()->to(site_url('admin/publications/' . $id))
            ->with($result['failed'] > 0 ? 'participant_resolution_warning' : 'participant_resolution_success', $message);
    }
}

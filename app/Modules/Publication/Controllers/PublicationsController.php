<?php

declare(strict_types=1);

namespace Modules\Publication\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;
use Modules\Publication\Application\PublicationListQueryService;

final class PublicationsController extends BaseController
{
    public function index()
    {
        $search = trim((string) $this->request->getGet('q'));
        $page = max(1, (int) ($this->request->getGet('page') ?: 1));
        $perPage = (int) ($this->request->getGet('per_page') ?: 25);
        $result = (new PublicationListQueryService())->paginate($search, $page, $perPage);

        return view('Modules\Publication\Views\index', [
            'title' => 'Publication Center',
            'publications' => $result['items'],
            'pagination' => $result,
            'search' => $search,
            'perPage' => $result['perPage'],
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

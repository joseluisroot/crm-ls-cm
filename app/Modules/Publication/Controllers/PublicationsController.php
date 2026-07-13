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

        return view('Modules\Publication\Views\show', [
            'title' => 'Publicación #' . $id,
            ...$profile,
            'analytics' => service('publicationAnalytics')->analyze($profile),
        ]);
    }
}

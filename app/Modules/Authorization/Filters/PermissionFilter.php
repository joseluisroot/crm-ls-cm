<?php

declare(strict_types=1);

namespace Modules\Authorization\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use Modules\Authorization\Application\AuthorizationService;

final class PermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $permission = trim((string) ($arguments[0] ?? ''));
        $userId = (int) session()->get('admin_user_id');

        if ($permission !== '' && (new AuthorizationService())->can($userId, $permission)) {
            return null;
        }

        log_message('warning', sprintf(
            'Acceso denegado. Usuario: %d. Permiso: %s. Método: %s. URI: %s',
            $userId,
            $permission !== '' ? $permission : '[missing]',
            $request->getMethod(),
            (string) $request->getUri()
        ));

        if ($request->isAJAX() || str_contains(strtolower($request->getHeaderLine('Accept')), 'application/json')) {
            return Services::response()
                ->setStatusCode(403)
                ->setJSON([
                    'success' => false,
                    'message' => 'No tienes permisos para realizar esta acción.',
                    'permission' => $permission,
                ]);
        }

        if (strtoupper($request->getMethod()) !== 'GET') {
            return redirect()
                ->back()
                ->with('error', 'No tienes permisos para realizar esta acción.');
        }

        return Services::response()
            ->setStatusCode(403)
            ->setBody(view('Modules\Authorization\Views\forbidden', [
                'title' => 'Acceso denegado',
                'permission' => $permission,
            ]));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}

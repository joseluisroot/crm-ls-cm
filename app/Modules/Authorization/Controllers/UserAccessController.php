<?php

declare(strict_types=1);

namespace Modules\Authorization\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;
use Modules\Authorization\Application\AuthorizationService;

final class UserAccessController extends BaseController
{
    public function index()
    {
        $db = db_connect();

        $users = $db->table('admin_users u')
            ->select('u.id, u.name, u.email, u.status, u.last_login_at, u.created_at')
            ->orderBy('u.name', 'ASC')
            ->get()
            ->getResultArray();

        $roleRows = $db->table('user_roles ur')
            ->select('ur.user_id, r.code, r.name')
            ->join('roles r', 'r.id = ur.role_id')
            ->where('r.is_active', 1)
            ->orderBy('r.name', 'ASC')
            ->get()
            ->getResultArray();

        $rolesByUser = [];
        foreach ($roleRows as $row) {
            $rolesByUser[(int) $row['user_id']][] = $row;
        }

        return view('Modules\Authorization\Views\users\index', [
            'title' => 'Usuarios y accesos',
            'users' => $users,
            'rolesByUser' => $rolesByUser,
        ]);
    }

    public function show(int $id)
    {
        $db = db_connect();
        $user = $db->table('admin_users')
            ->select('id, name, email, status, last_login_at, created_at, updated_at')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if ($user === null) {
            throw PageNotFoundException::forPageNotFound('Usuario no encontrado.');
        }

        $roles = $db->table('roles')
            ->select('id, code, name, description')
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();

        $assignedRoleIds = array_map('intval', array_column(
            $db->table('user_roles')->select('role_id')->where('user_id', $id)->get()->getResultArray(),
            'role_id'
        ));

        $authorization = new AuthorizationService();

        return view('Modules\Authorization\Views\users\show', [
            'title' => 'Administrar acceso',
            'user' => $user,
            'roles' => $roles,
            'assignedRoleIds' => $assignedRoleIds,
            'effectivePermissions' => $authorization->permissionsForUser($id, true),
        ]);
    }

    public function updateStatus(int $id)
    {
        $status = strtolower(trim((string) $this->request->getPost('status')));
        if (! in_array($status, ['active', 'inactive'], true)) {
            return redirect()->back()->with('error', 'Estado de usuario inválido.');
        }

        $currentUserId = (int) session()->get('admin_user_id');
        if ($id === $currentUserId && $status !== 'active') {
            return redirect()->back()->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        $db = db_connect();
        if (! $this->userExists($id)) {
            throw PageNotFoundException::forPageNotFound('Usuario no encontrado.');
        }

        $db->table('admin_users')->where('id', $id)->update([
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(site_url('admin/access/users/' . $id))
            ->with('success', $status === 'active' ? 'Usuario activado correctamente.' : 'Usuario desactivado correctamente.');
    }

    public function syncRoles(int $id)
    {
        if (! $this->userExists($id)) {
            throw PageNotFoundException::forPageNotFound('Usuario no encontrado.');
        }

        $requestedRoleIds = array_values(array_unique(array_filter(
            array_map('intval', (array) $this->request->getPost('role_ids')),
            static fn (int $roleId): bool => $roleId > 0
        )));

        $db = db_connect();
        $validRoleIds = $requestedRoleIds === [] ? [] : array_map('intval', array_column(
            $db->table('roles')
                ->select('id')
                ->whereIn('id', $requestedRoleIds)
                ->where('is_active', 1)
                ->get()
                ->getResultArray(),
            'id'
        ));

        if (count($validRoleIds) !== count($requestedRoleIds)) {
            return redirect()->back()->with('error', 'Uno o más roles seleccionados no son válidos.');
        }

        $currentUserId = (int) session()->get('admin_user_id');
        if ($id === $currentUserId && ! $this->containsAdministratorRole($validRoleIds)) {
            return redirect()->back()->with('error', 'No puedes retirar tu propio rol de administrador.');
        }

        $db->transStart();
        $db->table('user_roles')->where('user_id', $id)->delete();

        foreach ($validRoleIds as $roleId) {
            $db->table('user_roles')->insert([
                'user_id' => $id,
                'role_id' => $roleId,
                'assigned_by' => $currentUserId > 0 ? $currentUserId : null,
                'assigned_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->with('error', 'No fue posible actualizar los roles.');
        }

        if ($id === $currentUserId) {
            (new AuthorizationService())->warmSession($id);
        }

        return redirect()->to(site_url('admin/access/users/' . $id))
            ->with('success', 'Roles actualizados correctamente.');
    }

    private function userExists(int $id): bool
    {
        return db_connect()->table('admin_users')->where('id', $id)->countAllResults() > 0;
    }

    /** @param int[] $roleIds */
    private function containsAdministratorRole(array $roleIds): bool
    {
        if ($roleIds === []) {
            return false;
        }

        return db_connect()->table('roles')
            ->whereIn('id', $roleIds)
            ->where('code', 'ADMINISTRATOR')
            ->countAllResults() > 0;
    }
}

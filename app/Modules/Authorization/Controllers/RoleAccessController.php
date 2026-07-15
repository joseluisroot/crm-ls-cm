<?php

declare(strict_types=1);

namespace Modules\Authorization\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;
use Modules\Authorization\Application\AuthorizationService;

final class RoleAccessController extends BaseController
{
    public function index()
    {
        $roles = db_connect()->table('roles r')
            ->select('r.id, r.code, r.name, r.description, r.is_system, r.is_active, COUNT(DISTINCT rp.permission_id) AS permission_count, COUNT(DISTINCT ur.user_id) AS user_count')
            ->join('role_permissions rp', 'rp.role_id = r.id', 'left')
            ->join('user_roles ur', 'ur.role_id = r.id', 'left')
            ->groupBy('r.id')->orderBy('r.name', 'ASC')->get()->getResultArray();

        return view('Modules\\Authorization\\Views\\roles\\index', ['title' => 'Roles y permisos', 'roles' => $roles]);
    }

    public function show(int $id)
    {
        $db = db_connect();
        $role = $db->table('roles')->where('id', $id)->get()->getRowArray();
        if ($role === null) throw PageNotFoundException::forPageNotFound('Rol no encontrado.');

        $grouped = [];
        foreach ($db->table('permissions')->where('is_active', 1)->orderBy('module')->orderBy('action')->get()->getResultArray() as $permission) {
            $grouped[$permission['module']][] = $permission;
        }

        $assigned = array_map('intval', array_column(
            $db->table('role_permissions')->select('permission_id')->where('role_id', $id)->get()->getResultArray(),
            'permission_id'
        ));

        $audit = $db->table('authorization_audit_log a')
            ->select('a.action, a.before_payload, a.after_payload, a.created_at, u.name AS actor_name')
            ->join('admin_users u', 'u.id = a.actor_user_id', 'left')
            ->where('a.target_type', 'role')->where('a.target_id', $id)
            ->orderBy('a.id', 'DESC')->limit(15)->get()->getResultArray();

        return view('Modules\\Authorization\\Views\\roles\\show', [
            'title' => 'Administrar rol', 'role' => $role,
            'permissionsByModule' => $grouped, 'assignedPermissionIds' => $assigned, 'audit' => $audit,
        ]);
    }

    public function updatePermissions(int $id)
    {
        $db = db_connect();
        $role = $db->table('roles')->where('id', $id)->get()->getRowArray();
        if ($role === null) throw PageNotFoundException::forPageNotFound('Rol no encontrado.');

        $requested = array_values(array_unique(array_filter(array_map('intval', (array) $this->request->getPost('permission_ids')))));
        $valid = $requested === [] ? [] : array_map('intval', array_column(
            $db->table('permissions')->select('id')->whereIn('id', $requested)->where('is_active', 1)->get()->getResultArray(), 'id'
        ));
        if (count($valid) !== count($requested)) return redirect()->back()->with('error', 'Existen permisos no válidos.');

        if (($role['code'] ?? '') === 'ADMINISTRATOR') {
            $required = $db->table('permissions')->where('code', 'authorization.manage')->get()->getRowArray();
            if ($required && ! in_array((int) $required['id'], $valid, true)) {
                return redirect()->back()->with('error', 'ADMINISTRATOR debe conservar authorization.manage.');
            }
        }

        $before = array_map('intval', array_column($db->table('role_permissions')->select('permission_id')->where('role_id', $id)->get()->getResultArray(), 'permission_id'));
        sort($before); sort($valid);
        $db->transStart();
        $db->table('role_permissions')->where('role_id', $id)->delete();
        foreach ($valid as $permissionId) {
            $db->table('role_permissions')->insert(['role_id' => $id, 'permission_id' => $permissionId, 'created_at' => date('Y-m-d H:i:s')]);
        }
        $this->audit($id, 'role.permissions.updated', $before, $valid);
        $db->transComplete();
        if (! $db->transStatus()) return redirect()->back()->with('error', 'No fue posible actualizar el rol.');

        $currentUserId = (int) session()->get('admin_user_id');
        if ($db->table('user_roles')->where(['user_id' => $currentUserId, 'role_id' => $id])->countAllResults() > 0) {
            (new AuthorizationService())->warmSession($currentUserId);
        }
        return redirect()->to(site_url('admin/access/roles/' . $id))->with('success', 'Permisos actualizados correctamente.');
    }

    public function updateStatus(int $id)
    {
        $db = db_connect();
        $role = $db->table('roles')->where('id', $id)->get()->getRowArray();
        if ($role === null) throw PageNotFoundException::forPageNotFound('Rol no encontrado.');
        $active = (int) $this->request->getPost('is_active');
        if (! in_array($active, [0, 1], true)) return redirect()->back()->with('error', 'Estado inválido.');
        if (($role['code'] ?? '') === 'ADMINISTRATOR' && $active === 0) return redirect()->back()->with('error', 'ADMINISTRATOR no puede desactivarse.');

        $db->transStart();
        $db->table('roles')->where('id', $id)->update(['is_active' => $active, 'updated_at' => date('Y-m-d H:i:s')]);
        $this->audit($id, 'role.status.updated', [(int) $role['is_active']], [$active]);
        $db->transComplete();
        return redirect()->to(site_url('admin/access/roles/' . $id))->with('success', $active ? 'Rol activado.' : 'Rol desactivado.');
    }

    private function audit(int $roleId, string $action, array $before, array $after): void
    {
        db_connect()->table('authorization_audit_log')->insert([
            'actor_user_id' => (int) session()->get('admin_user_id') ?: null,
            'target_type' => 'role', 'target_id' => $roleId, 'action' => $action,
            'before_payload' => json_encode($before), 'after_payload' => json_encode($after),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}

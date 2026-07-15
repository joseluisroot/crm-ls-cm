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
            ->orderBy('u.name', 'ASC')->get()->getResultArray();

        $roleRows = $db->table('user_roles ur')
            ->select('ur.user_id, r.code, r.name')
            ->join('roles r', 'r.id = ur.role_id')
            ->where('r.is_active', 1)->orderBy('r.name', 'ASC')->get()->getResultArray();

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

    public function create()
    {
        return view('Modules\Authorization\Views\users\form', [
            'title' => 'Crear usuario',
            'mode' => 'create',
            'user' => null,
            'roles' => $this->activeRoles(),
            'assignedRoleIds' => [],
        ]);
    }

    public function store()
    {
        $name = trim((string) $this->request->getPost('name'));
        $email = strtolower(trim((string) $this->request->getPost('email')));
        $password = (string) $this->request->getPost('password');
        $passwordConfirmation = (string) $this->request->getPost('password_confirmation');
        $status = strtolower(trim((string) $this->request->getPost('status')));
        $roleIds = $this->requestedRoleIds();

        if ($message = $this->validateUserInput($name, $email, $status, $roleIds, $password, $passwordConfirmation, true)) {
            return redirect()->back()->withInput()->with('error', $message);
        }

        $db = db_connect();
        if ($db->table('admin_users')->where('email', $email)->countAllResults() > 0) {
            return redirect()->back()->withInput()->with('error', 'Ya existe un usuario con ese correo electrónico.');
        }

        $db->transStart();
        $db->table('admin_users')->insert([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $this->legacyRoleValue($roleIds),
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $userId = (int) $db->insertID();
        $this->replaceRoles($userId, $roleIds, (int) session()->get('admin_user_id'));
        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->withInput()->with('error', 'No fue posible crear el usuario.');
        }

        return redirect()->to(site_url('admin/access/users/' . $userId))
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(int $id)
    {
        $user = $this->findUser($id);
        return view('Modules\Authorization\Views\users\form', [
            'title' => 'Editar usuario',
            'mode' => 'edit',
            'user' => $user,
            'roles' => $this->activeRoles(),
            'assignedRoleIds' => $this->assignedRoleIds($id),
        ]);
    }

    public function update(int $id)
    {
        $this->findUser($id);
        $name = trim((string) $this->request->getPost('name'));
        $email = strtolower(trim((string) $this->request->getPost('email')));
        $status = strtolower(trim((string) $this->request->getPost('status')));
        $roleIds = $this->requestedRoleIds();

        if ($message = $this->validateUserInput($name, $email, $status, $roleIds)) {
            return redirect()->back()->withInput()->with('error', $message);
        }

        $currentUserId = (int) session()->get('admin_user_id');
        if ($id === $currentUserId && ($status !== 'active' || ! $this->containsAdministratorRole($roleIds))) {
            return redirect()->back()->withInput()->with('error', 'No puedes desactivar tu cuenta ni retirar tu propio rol de administrador.');
        }

        $db = db_connect();
        if ($db->table('admin_users')->where('email', $email)->where('id !=', $id)->countAllResults() > 0) {
            return redirect()->back()->withInput()->with('error', 'Ya existe otro usuario con ese correo electrónico.');
        }

        $db->transStart();
        $db->table('admin_users')->where('id', $id)->update([
            'name' => $name,
            'email' => $email,
            'role' => $this->legacyRoleValue($roleIds),
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->replaceRoles($id, $roleIds, $currentUserId);
        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->withInput()->with('error', 'No fue posible actualizar el usuario.');
        }

        if ($id === $currentUserId) {
            session()->set(['admin_user_name' => $name, 'admin_user_email' => $email]);
            (new AuthorizationService())->warmSession($id);
        }

        return redirect()->to(site_url('admin/access/users/' . $id))->with('success', 'Usuario actualizado correctamente.');
    }

    public function resetPassword(int $id)
    {
        $this->findUser($id);
        $password = (string) $this->request->getPost('password');
        $confirmation = (string) $this->request->getPost('password_confirmation');

        if (strlen($password) < 10) {
            return redirect()->back()->with('error', 'La contraseña debe contener al menos 10 caracteres.');
        }
        if ($password !== $confirmation) {
            return redirect()->back()->with('error', 'La confirmación de contraseña no coincide.');
        }

        db_connect()->table('admin_users')->where('id', $id)->update([
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(site_url('admin/access/users/' . $id))->with('success', 'Contraseña restablecida correctamente.');
    }

    public function show(int $id)
    {
        $user = $this->findUser($id);
        $authorization = new AuthorizationService();

        return view('Modules\Authorization\Views\users\show', [
            'title' => 'Administrar acceso',
            'user' => $user,
            'roles' => $this->activeRoles(),
            'assignedRoleIds' => $this->assignedRoleIds($id),
            'effectivePermissions' => $authorization->permissionsForUser($id, true),
        ]);
    }

    public function updateStatus(int $id)
    {
        $status = strtolower(trim((string) $this->request->getPost('status')));
        if (! in_array($status, ['active', 'inactive'], true)) {
            return redirect()->back()->with('error', 'Estado de usuario inválido.');
        }
        if ($id === (int) session()->get('admin_user_id') && $status !== 'active') {
            return redirect()->back()->with('error', 'No puedes desactivar tu propia cuenta.');
        }
        $this->findUser($id);
        db_connect()->table('admin_users')->where('id', $id)->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);

        return redirect()->to(site_url('admin/access/users/' . $id))
            ->with('success', $status === 'active' ? 'Usuario activado correctamente.' : 'Usuario desactivado correctamente.');
    }

    public function syncRoles(int $id)
    {
        $this->findUser($id);
        $roleIds = $this->requestedRoleIds();
        if ($roleIds === []) {
            return redirect()->back()->with('error', 'Debes asignar al menos un rol.');
        }
        $currentUserId = (int) session()->get('admin_user_id');
        if ($id === $currentUserId && ! $this->containsAdministratorRole($roleIds)) {
            return redirect()->back()->with('error', 'No puedes retirar tu propio rol de administrador.');
        }

        $db = db_connect();
        $db->transStart();
        $this->replaceRoles($id, $roleIds, $currentUserId);
        $db->table('admin_users')->where('id', $id)->update(['role' => $this->legacyRoleValue($roleIds), 'updated_at' => date('Y-m-d H:i:s')]);
        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->with('error', 'No fue posible actualizar los roles.');
        }
        if ($id === $currentUserId) {
            (new AuthorizationService())->warmSession($id);
        }
        return redirect()->to(site_url('admin/access/users/' . $id))->with('success', 'Roles actualizados correctamente.');
    }

    private function validateUserInput(string $name, string $email, string $status, array $roleIds, string $password = '', string $confirmation = '', bool $requirePassword = false): ?string
    {
        if (mb_strlen($name) < 3) return 'El nombre debe contener al menos 3 caracteres.';
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) return 'El correo electrónico no es válido.';
        if (! in_array($status, ['active', 'inactive'], true)) return 'El estado seleccionado no es válido.';
        if ($roleIds === [] || count($this->validRoleIds($roleIds)) !== count($roleIds)) return 'Debes seleccionar al menos un rol válido.';
        if ($requirePassword && strlen($password) < 10) return 'La contraseña debe contener al menos 10 caracteres.';
        if ($requirePassword && $password !== $confirmation) return 'La confirmación de contraseña no coincide.';
        return null;
    }

    private function requestedRoleIds(): array
    {
        return array_values(array_unique(array_filter(array_map('intval', (array) $this->request->getPost('role_ids')), static fn (int $id): bool => $id > 0)));
    }

    private function activeRoles(): array
    {
        return db_connect()->table('roles')->select('id, code, name, description')->where('is_active', 1)->orderBy('name', 'ASC')->get()->getResultArray();
    }

    private function validRoleIds(array $roleIds): array
    {
        if ($roleIds === []) return [];
        return array_map('intval', array_column(db_connect()->table('roles')->select('id')->whereIn('id', $roleIds)->where('is_active', 1)->get()->getResultArray(), 'id'));
    }

    private function assignedRoleIds(int $userId): array
    {
        return array_map('intval', array_column(db_connect()->table('user_roles')->select('role_id')->where('user_id', $userId)->get()->getResultArray(), 'role_id'));
    }

    private function replaceRoles(int $userId, array $roleIds, int $assignedBy): void
    {
        $db = db_connect();
        $db->table('user_roles')->where('user_id', $userId)->delete();
        foreach ($roleIds as $roleId) {
            $db->table('user_roles')->insert([
                'user_id' => $userId,
                'role_id' => $roleId,
                'assigned_by' => $assignedBy > 0 ? $assignedBy : null,
                'assigned_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function findUser(int $id): array
    {
        $user = db_connect()->table('admin_users')->select('id, name, email, status, last_login_at, created_at, updated_at')->where('id', $id)->get()->getRowArray();
        if ($user === null) throw PageNotFoundException::forPageNotFound('Usuario no encontrado.');
        return $user;
    }

    private function containsAdministratorRole(array $roleIds): bool
    {
        return $roleIds !== [] && db_connect()->table('roles')->whereIn('id', $roleIds)->where('code', 'ADMINISTRATOR')->countAllResults() > 0;
    }

    private function legacyRoleValue(array $roleIds): string
    {
        return $this->containsAdministratorRole($roleIds) ? 'admin' : 'operator';
    }
}

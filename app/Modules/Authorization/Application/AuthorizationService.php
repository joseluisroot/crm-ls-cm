<?php

declare(strict_types=1);

namespace Modules\Authorization\Application;

use CodeIgniter\Database\BaseConnection;

final class AuthorizationService
{
    private const SESSION_PERMISSIONS_KEY = 'authorization_permissions';
    private const SESSION_ROLES_KEY = 'authorization_roles';

    public function __construct(private readonly ?BaseConnection $db = null)
    {
    }

    public function can(int $userId, string $permission): bool
    {
        $permission = trim($permission);
        if ($userId <= 0 || $permission === '') {
            return false;
        }

        return in_array($permission, $this->permissionsForUser($userId), true);
    }

    /** @return string[] */
    public function permissionsForUser(int $userId, bool $refresh = false): array
    {
        if ($userId <= 0) {
            return [];
        }

        if (! $refresh && $this->isCurrentSessionUser($userId)) {
            $cached = session()->get(self::SESSION_PERMISSIONS_KEY);
            if (is_array($cached)) {
                return array_values(array_unique(array_map('strval', $cached)));
            }
        }

        $db = $this->database();
        if (! $db->tableExists('user_roles') || ! $db->tableExists('role_permissions') || ! $db->tableExists('permissions')) {
            return $this->legacyAdministratorPermissions($userId);
        }

        $rows = $db->table('permissions p')
            ->select('DISTINCT p.code', false)
            ->join('role_permissions rp', 'rp.permission_id = p.id')
            ->join('roles r', 'r.id = rp.role_id')
            ->join('user_roles ur', 'ur.role_id = r.id')
            ->where('ur.user_id', $userId)
            ->where('p.is_active', 1)
            ->where('r.is_active', 1)
            ->orderBy('p.code', 'ASC')
            ->get()
            ->getResultArray();

        $permissions = array_values(array_unique(array_column($rows, 'code')));

        // Compatibilidad temporal: un administrador legado no debe quedar bloqueado
        // durante la transición hacia user_roles.
        if ($permissions === []) {
            $permissions = $this->legacyAdministratorPermissions($userId);
        }

        if ($this->isCurrentSessionUser($userId)) {
            session()->set(self::SESSION_PERMISSIONS_KEY, $permissions);
            session()->set(self::SESSION_ROLES_KEY, $this->rolesForUser($userId));
        }

        return $permissions;
    }

    /** @return string[] */
    public function rolesForUser(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        $db = $this->database();
        if (! $db->tableExists('user_roles') || ! $db->tableExists('roles')) {
            return $this->isLegacyAdministrator($userId) ? ['ADMINISTRATOR'] : [];
        }

        $rows = $db->table('roles r')
            ->select('r.code')
            ->join('user_roles ur', 'ur.role_id = r.id')
            ->where('ur.user_id', $userId)
            ->where('r.is_active', 1)
            ->orderBy('r.code', 'ASC')
            ->get()
            ->getResultArray();

        $roles = array_values(array_unique(array_column($rows, 'code')));

        return $roles !== [] ? $roles : ($this->isLegacyAdministrator($userId) ? ['ADMINISTRATOR'] : []);
    }

    public function warmSession(int $userId): void
    {
        $this->clearSessionCache();
        $this->permissionsForUser($userId, true);
    }

    public function clearSessionCache(): void
    {
        session()->remove([self::SESSION_PERMISSIONS_KEY, self::SESSION_ROLES_KEY]);
    }

    private function database(): BaseConnection
    {
        return $this->db ?? db_connect();
    }

    private function isCurrentSessionUser(int $userId): bool
    {
        return (int) session()->get('admin_user_id') === $userId;
    }

    /** @return string[] */
    private function legacyAdministratorPermissions(int $userId): array
    {
        if (! $this->isLegacyAdministrator($userId)) {
            return [];
        }

        $db = $this->database();
        if (! $db->tableExists('permissions')) {
            return [];
        }

        return array_values(array_column(
            $db->table('permissions')
                ->select('code')
                ->where('is_active', 1)
                ->orderBy('code', 'ASC')
                ->get()
                ->getResultArray(),
            'code'
        ));
    }

    private function isLegacyAdministrator(int $userId): bool
    {
        $db = $this->database();
        if (! $db->tableExists('admin_users')) {
            return false;
        }

        $user = $db->table('admin_users')
            ->select('role, status')
            ->where('id', $userId)
            ->get()
            ->getRowArray();

        return $user !== null
            && ($user['status'] ?? null) === 'active'
            && strtolower((string) ($user['role'] ?? '')) === 'admin';
    }
}

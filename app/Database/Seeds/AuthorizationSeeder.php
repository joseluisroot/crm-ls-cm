<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

final class AuthorizationSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');
        $roles = [
            ['code' => 'ADMINISTRATOR', 'name' => 'Administrador', 'description' => 'Acceso completo a CIAC.'],
            ['code' => 'COORDINATOR', 'name' => 'Coordinador', 'description' => 'Coordina la operación y varios equipos.'],
            ['code' => 'SUPERVISOR', 'name' => 'Supervisor', 'description' => 'Supervisa, asigna y da seguimiento a un equipo.'],
            ['code' => 'OPERATOR', 'name' => 'Operador', 'description' => 'Atiende interacciones y casos asignados.'],
            ['code' => 'AUDITOR', 'name' => 'Auditor', 'description' => 'Consulta información y trazabilidad sin modificarla.'],
            ['code' => 'DEVELOPER', 'name' => 'Soporte técnico', 'description' => 'Gestiona observabilidad, integración y workflows.'],
        ];

        foreach ($roles as $role) {
            if (! $this->db->table('roles')->where('code', $role['code'])->countAllResults()) {
                $this->db->table('roles')->insert($role + ['uuid' => $this->uuidV4(), 'is_system' => 1, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now]);
            }
        }

        $permissions = [
            'dashboard.view',
            'operations.view', 'operations.view_team', 'operations.view_own', 'operations.assign', 'operations.update', 'operations.reply', 'operations.close',
            'cases.view', 'cases.view_team', 'cases.view_own', 'cases.create', 'cases.assign', 'cases.update', 'cases.close',
            'citizens.view', 'citizens.edit', 'conversations.view', 'messenger.reply', 'facebook.reply', 'facebook.like',
            'publications.view', 'engagement.view', 'analytics.view', 'analytics.team', 'notifications.view',
            'integration.view', 'replay.view', 'replay.execute', 'workflow.view', 'workflow.manage',
            'authorization.view', 'authorization.manage', 'teams.manage',
        ];

        foreach ($permissions as $code) {
            [$module, $action] = explode('.', $code, 2);
            if (! $this->db->table('permissions')->where('code', $code)->countAllResults()) {
                $this->db->table('permissions')->insert([
                    'uuid' => $this->uuidV4(), 'code' => $code, 'module' => $module, 'action' => $action,
                    'name' => ucwords(str_replace(['.', '_'], ' ', $code)), 'is_active' => 1,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        $matrix = [
            'ADMINISTRATOR' => ['*'],
            'COORDINATOR' => ['dashboard.view', 'operations.view', 'operations.assign', 'operations.update', 'operations.reply', 'operations.close', 'cases.view', 'cases.create', 'cases.assign', 'cases.update', 'cases.close', 'citizens.view', 'citizens.edit', 'conversations.view', 'messenger.reply', 'facebook.reply', 'facebook.like', 'publications.view', 'engagement.view', 'analytics.view', 'analytics.team', 'notifications.view', 'workflow.view', 'teams.manage'],
            'SUPERVISOR' => ['dashboard.view', 'operations.view_team', 'operations.assign', 'operations.update', 'operations.reply', 'operations.close', 'cases.view_team', 'cases.create', 'cases.assign', 'cases.update', 'cases.close', 'citizens.view', 'conversations.view', 'messenger.reply', 'facebook.reply', 'facebook.like', 'publications.view', 'engagement.view', 'analytics.team', 'notifications.view'],
            'OPERATOR' => ['dashboard.view', 'operations.view_own', 'operations.update', 'operations.reply', 'operations.close', 'cases.view_own', 'cases.create', 'cases.update', 'citizens.view', 'conversations.view', 'messenger.reply', 'facebook.reply', 'facebook.like', 'notifications.view'],
            'AUDITOR' => ['dashboard.view', 'operations.view', 'cases.view', 'citizens.view', 'conversations.view', 'publications.view', 'engagement.view', 'analytics.view', 'integration.view', 'replay.view'],
            'DEVELOPER' => ['dashboard.view', 'integration.view', 'replay.view', 'replay.execute', 'workflow.view', 'workflow.manage', 'analytics.view'],
        ];

        $allPermissionIds = array_column($this->db->table('permissions')->select('id')->where('is_active', 1)->get()->getResultArray(), 'id');
        foreach ($matrix as $roleCode => $codes) {
            $role = $this->db->table('roles')->where('code', $roleCode)->get()->getRowArray();
            if (! $role) continue;
            $permissionIds = $codes === ['*'] ? $allPermissionIds : array_column($this->db->table('permissions')->select('id')->whereIn('code', $codes)->get()->getResultArray(), 'id');
            foreach ($permissionIds as $permissionId) {
                $key = ['role_id' => $role['id'], 'permission_id' => $permissionId];
                if (! $this->db->table('role_permissions')->where($key)->countAllResults()) $this->db->table('role_permissions')->insert($key + ['created_at' => $now]);
            }
        }

        $supervisor = $this->db->table('roles')->where('code', 'SUPERVISOR')->get()->getRowArray();
        if ($supervisor) {
            $globalIds = array_column($this->db->table('permissions')->select('id')->whereIn('code', ['operations.view', 'cases.view'])->get()->getResultArray(), 'id');
            if ($globalIds !== []) $this->db->table('role_permissions')->where('role_id', $supervisor['id'])->whereIn('permission_id', $globalIds)->delete();
        }

        $adminRole = $this->db->table('roles')->where('code', 'ADMINISTRATOR')->get()->getRowArray();
        if ($adminRole) {
            foreach ($this->db->table('admin_users')->select('id')->where('role', 'admin')->get()->getResultArray() as $admin) {
                $key = ['user_id' => $admin['id'], 'role_id' => $adminRole['id']];
                if (! $this->db->table('user_roles')->where($key)->countAllResults()) $this->db->table('user_roles')->insert($key + ['assigned_at' => $now]);
            }
        }
    }

    private function uuidV4(): string
    {
        $data = random_bytes(16); $data[6] = chr((ord($data[6]) & 0x0f) | 0x40); $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

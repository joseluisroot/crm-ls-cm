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
            $existing = $this->db->table('roles')->where('code', $role['code'])->get()->getRowArray();
            if (! $existing) {
                $this->db->table('roles')->insert($role + [
                    'uuid' => service('uuid')->uuid4()->toString(),
                    'is_system' => 1,
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $permissions = [
            'dashboard.view',
            'operations.view', 'operations.view_own', 'operations.assign', 'operations.update', 'operations.reply', 'operations.close',
            'cases.view', 'cases.view_own', 'cases.create', 'cases.assign', 'cases.update', 'cases.close',
            'citizens.view', 'citizens.edit',
            'conversations.view', 'messenger.reply', 'facebook.reply', 'facebook.like',
            'publications.view', 'engagement.view',
            'analytics.view', 'analytics.team',
            'notifications.view',
            'integration.view', 'replay.view', 'replay.execute',
            'workflow.view', 'workflow.manage',
            'authorization.view', 'authorization.manage',
        ];

        foreach ($permissions as $code) {
            [$module, $action] = explode('.', $code, 2);
            $existing = $this->db->table('permissions')->where('code', $code)->get()->getRowArray();
            if (! $existing) {
                $this->db->table('permissions')->insert([
                    'uuid' => service('uuid')->uuid4()->toString(),
                    'code' => $code,
                    'module' => $module,
                    'action' => $action,
                    'name' => ucwords(str_replace(['.', '_'], ' ', $code)),
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $matrix = [
            'ADMINISTRATOR' => ['*'],
            'COORDINATOR' => ['dashboard.view', 'operations.view', 'operations.assign', 'operations.update', 'operations.reply', 'operations.close', 'cases.view', 'cases.create', 'cases.assign', 'cases.update', 'cases.close', 'citizens.view', 'citizens.edit', 'conversations.view', 'messenger.reply', 'facebook.reply', 'facebook.like', 'publications.view', 'engagement.view', 'analytics.view', 'analytics.team', 'notifications.view', 'workflow.view'],
            'SUPERVISOR' => ['dashboard.view', 'operations.view', 'operations.assign', 'operations.update', 'operations.reply', 'operations.close', 'cases.view', 'cases.create', 'cases.assign', 'cases.update', 'cases.close', 'citizens.view', 'conversations.view', 'messenger.reply', 'facebook.reply', 'facebook.like', 'publications.view', 'engagement.view', 'analytics.team', 'notifications.view'],
            'OPERATOR' => ['dashboard.view', 'operations.view_own', 'operations.update', 'operations.reply', 'operations.close', 'cases.view_own', 'cases.create', 'cases.update', 'citizens.view', 'conversations.view', 'messenger.reply', 'facebook.reply', 'facebook.like', 'notifications.view'],
            'AUDITOR' => ['dashboard.view', 'operations.view', 'cases.view', 'citizens.view', 'conversations.view', 'publications.view', 'engagement.view', 'analytics.view', 'integration.view', 'replay.view'],
            'DEVELOPER' => ['dashboard.view', 'integration.view', 'replay.view', 'replay.execute', 'workflow.view', 'workflow.manage', 'analytics.view'],
        ];

        $allPermissionIds = array_column($this->db->table('permissions')->select('id')->where('is_active', 1)->get()->getResultArray(), 'id');
        foreach ($matrix as $roleCode => $codes) {
            $role = $this->db->table('roles')->where('code', $roleCode)->get()->getRowArray();
            if (! $role) continue;
            $permissionIds = $codes === ['*']
                ? $allPermissionIds
                : array_column($this->db->table('permissions')->select('id')->whereIn('code', $codes)->get()->getResultArray(), 'id');
            foreach ($permissionIds as $permissionId) {
                $exists = $this->db->table('role_permissions')->where(['role_id' => $role['id'], 'permission_id' => $permissionId])->countAllResults();
                if (! $exists) $this->db->table('role_permissions')->insert(['role_id' => $role['id'], 'permission_id' => $permissionId, 'created_at' => $now]);
            }
        }

        $adminRole = $this->db->table('roles')->where('code', 'ADMINISTRATOR')->get()->getRowArray();
        if ($adminRole) {
            $admins = $this->db->table('admin_users')->select('id')->where('role', 'admin')->get()->getResultArray();
            foreach ($admins as $admin) {
                $exists = $this->db->table('user_roles')->where(['user_id' => $admin['id'], 'role_id' => $adminRole['id']])->countAllResults();
                if (! $exists) $this->db->table('user_roles')->insert(['user_id' => $admin['id'], 'role_id' => $adminRole['id'], 'assigned_at' => $now]);
            }
        }
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateTeamsAndTeamScope extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'uuid' => ['type' => 'CHAR', 'constraint' => 36],
            'code' => ['type' => 'VARCHAR', 'constraint' => 80],
            'name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'description' => ['type' => 'TEXT', 'null' => true],
            'supervisor_user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('uuid');
        $this->forge->addUniqueKey('code');
        $this->forge->addKey('supervisor_user_id');
        $this->forge->addForeignKey('supervisor_user_id', 'admin_users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('teams', true);

        $this->forge->addField([
            'team_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'assigned_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'joined_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey(['team_id', 'user_id'], true);
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('team_id', 'teams', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'admin_users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('assigned_by', 'admin_users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('team_members', true);

        $this->seedTeamPermissions();
    }

    public function down()
    {
        $this->forge->dropTable('team_members', true);
        $this->forge->dropTable('teams', true);
    }

    private function seedTeamPermissions(): void
    {
        if (! $this->db->tableExists('permissions') || ! $this->db->tableExists('roles')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        foreach (['operations.view_team', 'cases.view_team', 'teams.manage'] as $code) {
            if ($this->db->table('permissions')->where('code', $code)->countAllResults() > 0) {
                continue;
            }
            [$module, $action] = explode('.', $code, 2);
            $this->db->table('permissions')->insert([
                'uuid' => $this->uuidV4(),
                'code' => $code,
                'module' => $module,
                'action' => $action,
                'name' => ucwords(str_replace(['.', '_'], ' ', $code)),
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->grant('ADMINISTRATOR', ['operations.view_team', 'cases.view_team', 'teams.manage']);
        $this->grant('COORDINATOR', ['teams.manage']);
        $this->grant('SUPERVISOR', ['operations.view_team', 'cases.view_team']);
        $this->revoke('SUPERVISOR', ['operations.view', 'cases.view']);
    }

    private function grant(string $roleCode, array $permissionCodes): void
    {
        $role = $this->db->table('roles')->where('code', $roleCode)->get()->getRowArray();
        if (! $role) return;
        foreach ($permissionCodes as $code) {
            $permission = $this->db->table('permissions')->where('code', $code)->get()->getRowArray();
            if (! $permission) continue;
            $key = ['role_id' => $role['id'], 'permission_id' => $permission['id']];
            if ($this->db->table('role_permissions')->where($key)->countAllResults() === 0) {
                $this->db->table('role_permissions')->insert($key + ['created_at' => date('Y-m-d H:i:s')]);
            }
        }
    }

    private function revoke(string $roleCode, array $permissionCodes): void
    {
        $role = $this->db->table('roles')->where('code', $roleCode)->get()->getRowArray();
        if (! $role) return;
        $permissionIds = array_column($this->db->table('permissions')->select('id')->whereIn('code', $permissionCodes)->get()->getResultArray(), 'id');
        if ($permissionIds !== []) {
            $this->db->table('role_permissions')->where('role_id', $role['id'])->whereIn('permission_id', $permissionIds)->delete();
        }
    }

    private function uuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

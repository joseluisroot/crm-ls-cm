<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateAuthorizationFoundation extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'uuid' => ['type' => 'CHAR', 'constraint' => 36],
            'code' => ['type' => 'VARCHAR', 'constraint' => 80],
            'name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'description' => ['type' => 'TEXT', 'null' => true],
            'is_system' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('uuid');
        $this->forge->addUniqueKey('code');
        $this->forge->createTable('roles', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'uuid' => ['type' => 'CHAR', 'constraint' => 36],
            'code' => ['type' => 'VARCHAR', 'constraint' => 120],
            'module' => ['type' => 'VARCHAR', 'constraint' => 80],
            'action' => ['type' => 'VARCHAR', 'constraint' => 80],
            'name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'description' => ['type' => 'TEXT', 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('uuid');
        $this->forge->addUniqueKey('code');
        $this->forge->addKey(['module', 'action']);
        $this->forge->createTable('permissions', true);

        $this->forge->addField([
            'role_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'permission_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey(['role_id', 'permission_id'], true);
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('permission_id', 'permissions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('role_permissions', true);

        $this->forge->addField([
            'user_id' => ['type' => 'INT', 'unsigned' => true],
            'role_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'assigned_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'assigned_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey(['user_id', 'role_id'], true);
        $this->forge->addKey('role_id');
        $this->forge->addForeignKey('user_id', 'admin_users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('assigned_by', 'admin_users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('user_roles', true);
    }

    public function down()
    {
        $this->forge->dropTable('user_roles', true);
        $this->forge->dropTable('role_permissions', true);
        $this->forge->dropTable('permissions', true);
        $this->forge->dropTable('roles', true);
    }
}

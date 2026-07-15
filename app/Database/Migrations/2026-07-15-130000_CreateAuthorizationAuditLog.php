<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateAuthorizationAuditLog extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'actor_user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'target_type' => ['type' => 'VARCHAR', 'constraint' => 40],
            'target_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'action' => ['type' => 'VARCHAR', 'constraint' => 80],
            'before_payload' => ['type' => 'LONGTEXT', 'null' => true],
            'after_payload' => ['type' => 'LONGTEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['target_type', 'target_id']);
        $this->forge->addKey('actor_user_id');
        $this->forge->addForeignKey('actor_user_id', 'admin_users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('authorization_audit_log', true);
    }

    public function down()
    {
        $this->forge->dropTable('authorization_audit_log', true);
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateSlaFoundation extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'code' => ['type' => 'VARCHAR', 'constraint' => 80],
            'name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'first_response_minutes' => ['type' => 'INT', 'unsigned' => true, 'default' => 60],
            'resolution_minutes' => ['type' => 'INT', 'unsigned' => true, 'default' => 1440],
            'warning_percent' => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 80],
            'is_default' => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->createTable('sla_policies', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'work_item_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'policy_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'assigned_user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'started_at' => ['type' => 'DATETIME'],
            'first_response_due_at' => ['type' => 'DATETIME', 'null' => true],
            'resolution_due_at' => ['type' => 'DATETIME', 'null' => true],
            'first_response_at' => ['type' => 'DATETIME', 'null' => true],
            'resolved_at' => ['type' => 'DATETIME', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'RUNNING'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('work_item_id');
        $this->forge->addKey(['assigned_user_id', 'status']);
        $this->forge->addKey('first_response_due_at');
        $this->forge->addKey('resolution_due_at');
        $this->forge->createTable('work_item_sla', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'work_item_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'event_type' => ['type' => 'VARCHAR', 'constraint' => 50],
            'occurred_at' => ['type' => 'DATETIME'],
            'metadata' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['work_item_id', 'event_type']);
        $this->forge->addKey(['user_id', 'occurred_at']);
        $this->forge->createTable('work_item_time_events', true);

        $now = date('Y-m-d H:i:s');
        if (! $this->db->table('sla_policies')->where('code', 'STANDARD')->countAllResults()) {
            $this->db->table('sla_policies')->insert([
                'code' => 'STANDARD', 'name' => 'Atención estándar',
                'first_response_minutes' => 60, 'resolution_minutes' => 1440,
                'warning_percent' => 80, 'is_default' => 1, 'is_active' => 1,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropTable('work_item_time_events', true);
        $this->forge->dropTable('work_item_sla', true);
        $this->forge->dropTable('sla_policies', true);
    }
}

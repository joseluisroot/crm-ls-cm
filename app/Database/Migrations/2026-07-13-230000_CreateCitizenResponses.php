<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateCitizenResponses extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'work_item_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'draft_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'user_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'channel' => ['type' => 'VARCHAR', 'constraint' => 40],
            'recipient_external_id' => ['type' => 'VARCHAR', 'constraint' => 191],
            'body' => ['type' => 'TEXT'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'PENDING'],
            'external_response_id' => ['type' => 'VARCHAR', 'constraint' => 191, 'null' => true],
            'provider_response_json' => ['type' => 'LONGTEXT', 'null' => true],
            'error_message' => ['type' => 'TEXT', 'null' => true],
            'first_response_ms' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'sent_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['work_item_id', 'created_at']);
        $this->forge->addKey(['status', 'created_at']);
        $this->forge->addForeignKey('work_item_id', 'work_items', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('draft_id', 'response_drafts', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('citizen_responses', true);
    }

    public function down()
    {
        $this->forge->dropTable('citizen_responses', true);
    }
}

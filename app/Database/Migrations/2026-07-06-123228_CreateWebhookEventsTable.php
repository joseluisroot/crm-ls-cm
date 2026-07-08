<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWebhookEventsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'platform' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'facebook'],
            'event_type' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'sender_id' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'recipient_id' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'raw_payload' => ['type' => 'JSON', 'null' => true],
            'processed' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('sender_id');
        $this->forge->createTable('webhook_events');
    }

    public function down()
    {
        $this->forge->dropTable('webhook_events');
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExternalMessageIdToMessagesTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('messages', [
            'external_message_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'message_type',
            ],
        ]);

        $this->db->query('CREATE INDEX idx_messages_external_message_id ON messages (external_message_id)');
    }

    public function down()
    {
        $this->forge->dropColumn('messages', 'external_message_id');
    }
}

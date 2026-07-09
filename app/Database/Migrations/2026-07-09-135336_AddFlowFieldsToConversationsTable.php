<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFlowFieldsToConversationsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('conversations', [
            'state' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
                'default'    => 'NEW',
                'after'      => 'status',
            ],
            'welcomed_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'last_message_at',
            ],
            'last_flow_payload' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
                'after' => 'welcomed_at',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('conversations', [
            'state',
            'welcomed_at',
            'last_flow_payload',
        ]);
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConversationContextTable extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            'conversation_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],

            'context_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],

            'context_value' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

        ]);

        $this->forge->addKey('id', true);

        $this->forge->addKey([
            'conversation_id',
            'context_key'
        ]);

        $this->forge->createTable('conversation_context');
    }

    public function down()
    {
        $this->forge->dropTable('conversation_context');
    }
}

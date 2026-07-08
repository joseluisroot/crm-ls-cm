<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMessagesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'conversation_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'direction'       => ['type' => 'ENUM', 'constraint' => ['inbound', 'outbound']],
            'message_type'    => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'text'],
            'body'            => ['type' => 'TEXT', 'null' => true],
            'raw_payload'     => ['type' => 'JSON', 'null' => true],
            'sentiment'       => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'category'        => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'priority'        => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'normal'],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('conversation_id', 'conversations', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('messages');
    }

    public function down()
    {
        $this->forge->dropTable('messages');
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConversationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'citizen_id'     => ['type' => 'BIGINT', 'unsigned' => true],
            'channel'        => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'messenger'],
            'status'         => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'open'],
            'last_message_at'=> ['type' => 'DATETIME', 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('citizen_id', 'citizens', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('conversations');
    }

    public function down()
    {
        $this->forge->dropTable('conversations');
    }
}

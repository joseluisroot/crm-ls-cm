<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateWorkItemNotes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'work_item_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'case_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'author_user_id' => ['type' => 'INT', 'unsigned' => true],
            'note_type' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'GENERAL'],
            'body' => ['type' => 'TEXT'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['work_item_id', 'created_at']);
        $this->forge->addKey(['case_id', 'created_at']);
        $this->forge->addKey(['author_user_id', 'created_at']);
        $this->forge->createTable('work_item_notes', true);
    }

    public function down()
    {
        $this->forge->dropTable('work_item_notes', true);
    }
}

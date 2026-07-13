<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateResponseDrafts extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'work_item_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'user_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'channel' => ['type' => 'VARCHAR', 'constraint' => 40],
            'body' => ['type' => 'TEXT'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'DRAFT'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('work_item_id');
        $this->forge->addKey(['status', 'updated_at']);
        $this->forge->addForeignKey('work_item_id', 'work_items', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('response_drafts', true);
    }

    public function down()
    {
        $this->forge->dropTable('response_drafts', true);
    }
}

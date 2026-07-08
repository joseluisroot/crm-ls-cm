<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCasesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'citizen_id'   => ['type' => 'BIGINT', 'unsigned' => true],
            'category_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'status_id'    => ['type' => 'INT', 'unsigned' => true],
            'title'        => ['type' => 'VARCHAR', 'constraint' => 200],
            'description'  => ['type' => 'TEXT'],
            'priority'     => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'normal'],
            'sentiment'    => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'neutral'],
            'assigned_to'  => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'closed_at'    => ['type' => 'DATETIME', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('citizen_id', 'citizens', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('status_id', 'case_statuses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('cases');
    }

    public function down()
    {
        $this->forge->dropTable('cases');
    }
}

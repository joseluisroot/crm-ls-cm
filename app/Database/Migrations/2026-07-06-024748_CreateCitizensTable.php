<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCitizensTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'facebook_id'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'name'            => ['type' => 'VARCHAR', 'constraint' => 180],
            'phone'           => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'email'           => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'municipality'    => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'community'       => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'sentiment_score' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            'status'          => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'active'],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('facebook_id');
        $this->forge->createTable('citizens');
    }

    public function down()
    {
        $this->forge->dropTable('citizens');
    }
}

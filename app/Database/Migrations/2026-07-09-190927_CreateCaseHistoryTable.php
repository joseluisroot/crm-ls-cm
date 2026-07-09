<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCaseHistoryTable extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [

                'type' => 'BIGINT',

                'unsigned' => true,

                'auto_increment' => true,

            ],

            'case_id' => [

                'type' => 'BIGINT',

                'unsigned' => true,

            ],

            'event' => [

                'type' => 'VARCHAR',

                'constraint' => 120,

            ],

            'description' => [

                'type' => 'TEXT',

                'null' => true,

            ],

            'performed_by' => [

                'type' => 'VARCHAR',

                'constraint' => 120,

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

        $this->forge->addKey('case_id');

        $this->forge->createTable('case_history');
    }

    public function down()
    {
        $this->forge->dropTable('case_history');
    }
}

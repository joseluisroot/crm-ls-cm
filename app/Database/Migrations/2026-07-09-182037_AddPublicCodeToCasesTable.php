<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPublicCodeToCasesTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('cases', [
            'public_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'null'       => true,
                'after'      => 'id',
            ],
        ]);

        $this->db->query('CREATE UNIQUE INDEX idx_cases_public_code ON cases (public_code)');
    }

    public function down()
    {
        $this->forge->dropColumn('cases', 'public_code');
    }
}

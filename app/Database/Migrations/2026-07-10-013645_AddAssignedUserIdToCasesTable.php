<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAssignedUserIdToCasesTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('cases', [
            'assigned_user_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'assigned_to',
            ],
        ]);

        $this->forge->addKey('assigned_user_id');

        $this->forge->addForeignKey(
            'assigned_user_id',
            'admin_users',
            'id',
            'SET NULL',
            'CASCADE',
            'cases_assigned_user_id_foreign'
        );

        $this->forge->processIndexes('cases');
    }

    public function down()
    {
        $this->forge->dropForeignKey(
            'cases',
            'cases_assigned_user_id_foreign'
        );

        $this->forge->dropColumn(
            'cases',
            'assigned_user_id'
        );
    }
}

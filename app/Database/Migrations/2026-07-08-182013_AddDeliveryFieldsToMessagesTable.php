<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeliveryFieldsToMessagesTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('messages', [
            'sent_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'not_sent',
                'after'      => 'priority',
            ],
            'sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'sent_status',
            ],
            'delivery_error' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'sent_at',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('messages', ['sent_status', 'sent_at', 'delivery_error']);
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class AddReplayFieldsToIntegrationEvents extends Migration
{
    public function up()
    {
        $this->forge->addColumn('integration_events', [
            'original_event_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'id'],
            'replay_attempt' => ['type' => 'INT', 'unsigned' => true, 'default' => 0, 'after' => 'original_event_id'],
            'replayed_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'processed_at'],
        ]);

        $this->forge->addKey('original_event_id');
        $this->forge->addForeignKey('original_event_id', 'integration_events', 'id', 'CASCADE', 'SET NULL');
        $this->forge->processIndexes('integration_events');
    }

    public function down()
    {
        $this->forge->dropForeignKey('integration_events', 'integration_events_original_event_id_foreign');
        $this->forge->dropColumn('integration_events', ['original_event_id', 'replay_attempt', 'replayed_at']);
    }
}

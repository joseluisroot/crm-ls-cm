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

        $this->db->query('CREATE INDEX idx_integration_events_original_event_id ON integration_events (original_event_id)');
    }

    public function down()
    {
        $this->db->query('DROP INDEX idx_integration_events_original_event_id ON integration_events');
        $this->forge->dropColumn('integration_events', ['original_event_id', 'replay_attempt', 'replayed_at']);
    }
}

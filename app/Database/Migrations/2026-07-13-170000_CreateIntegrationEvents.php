<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateIntegrationEvents extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'uuid' => ['type' => 'CHAR', 'constraint' => 36],
            'source' => ['type' => 'VARCHAR', 'constraint' => 40],
            'event_type' => ['type' => 'VARCHAR', 'constraint' => 100, 'default' => 'UNKNOWN'],
            'event_version' => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 1],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'RECEIVED'],
            'external_event_id' => ['type' => 'VARCHAR', 'constraint' => 191, 'null' => true],
            'correlation_id' => ['type' => 'CHAR', 'constraint' => 36],
            'endpoint' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'request_ip' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'signature' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'payload_json' => ['type' => 'LONGTEXT'],
            'headers_json' => ['type' => 'LONGTEXT', 'null' => true],
            'processing_trace_json' => ['type' => 'LONGTEXT', 'null' => true],
            'publication_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'citizen_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'work_item_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'case_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'processing_time_ms' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'error_message' => ['type' => 'TEXT', 'null' => true],
            'received_at' => ['type' => 'DATETIME'],
            'processed_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('uuid');
        $this->forge->addKey('correlation_id');
        $this->forge->addKey(['source', 'event_type']);
        $this->forge->addKey('status');
        $this->forge->addKey('external_event_id');
        $this->forge->addKey('received_at');
        $this->forge->createTable('integration_events', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('integration_events', true);
    }
}

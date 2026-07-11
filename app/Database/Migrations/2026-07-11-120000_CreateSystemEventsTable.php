<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSystemEventsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'event_uuid' => [
                'type'       => 'CHAR',
                'constraint' => 36,
            ],
            'event_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'module' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
            ],
            'entity_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'entity_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'correlation_id' => [
                'type'       => 'CHAR',
                'constraint' => 36,
                'null'       => true,
            ],
            'causation_id' => [
                'type'       => 'CHAR',
                'constraint' => 36,
                'null'       => true,
            ],
            'payload_json' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'metadata_json' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'event_version' => [
                'type'       => 'SMALLINT',
                'unsigned'   => true,
                'default'    => 1,
            ],
            'published_by' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'published_at' => [
                'type' => 'DATETIME',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('event_uuid');
        $this->forge->addKey('event_name');
        $this->forge->addKey(['module', 'event_name']);
        $this->forge->addKey(['entity_type', 'entity_id']);
        $this->forge->addKey('correlation_id');
        $this->forge->addKey('published_at');
        $this->forge->createTable('system_events');
    }

    public function down(): void
    {
        $this->forge->dropTable('system_events');
    }
}

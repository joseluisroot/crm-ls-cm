<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateCitizenSocialIdentitiesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'uuid' => ['type' => 'CHAR', 'constraint' => 36],
            'citizen_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'channel' => ['type' => 'VARCHAR', 'constraint' => 30],
            'external_id' => ['type' => 'VARCHAR', 'constraint' => 190],
            'display_name' => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'actor_type' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'CITIZEN'],
            'confidence' => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 100],
            'metadata_json' => ['type' => 'LONGTEXT', 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('uuid');
        $this->forge->addUniqueKey(['channel', 'external_id']);
        $this->forge->addKey('citizen_id');
        $this->forge->addKey(['channel', 'is_active']);
        $this->forge->addForeignKey('citizen_id', 'citizens', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('citizen_social_identities');
    }

    public function down(): void
    {
        $this->forge->dropTable('citizen_social_identities', true);
    }
}

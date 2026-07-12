<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCitizenOperationsFoundation extends Migration
{
    public function up(): void
    {
        $this->createCatalog('work_item_statuses', [
            ['code' => 'NEW', 'name' => 'Nuevo', 'is_terminal' => 0, 'sort_order' => 10],
            ['code' => 'PENDING', 'name' => 'Pendiente', 'is_terminal' => 0, 'sort_order' => 20],
            ['code' => 'ASSIGNED', 'name' => 'Asignado', 'is_terminal' => 0, 'sort_order' => 30],
            ['code' => 'IN_PROGRESS', 'name' => 'En proceso', 'is_terminal' => 0, 'sort_order' => 40],
            ['code' => 'WAITING_CITIZEN', 'name' => 'Esperando ciudadano', 'is_terminal' => 0, 'sort_order' => 50],
            ['code' => 'WAITING_INTERNAL', 'name' => 'Esperando gestión interna', 'is_terminal' => 0, 'sort_order' => 60],
            ['code' => 'CASE_CREATED', 'name' => 'Caso creado', 'is_terminal' => 0, 'sort_order' => 70],
            ['code' => 'RESOLVED', 'name' => 'Resuelto', 'is_terminal' => 0, 'sort_order' => 80],
            ['code' => 'CLOSED', 'name' => 'Cerrado', 'is_terminal' => 1, 'sort_order' => 90],
            ['code' => 'ARCHIVED', 'name' => 'Archivado', 'is_terminal' => 1, 'sort_order' => 100],
        ], true);

        $this->createCatalog('work_item_priorities', [
            ['code' => 'LOW', 'name' => 'Baja', 'sort_order' => 10],
            ['code' => 'NORMAL', 'name' => 'Normal', 'sort_order' => 20],
            ['code' => 'HIGH', 'name' => 'Alta', 'sort_order' => 30],
            ['code' => 'CRITICAL', 'name' => 'Crítica', 'sort_order' => 40],
        ]);

        $this->createCatalog('work_item_channels', [
            ['code' => 'FACEBOOK', 'name' => 'Facebook', 'sort_order' => 10],
            ['code' => 'MESSENGER', 'name' => 'Messenger', 'sort_order' => 20],
            ['code' => 'INSTAGRAM', 'name' => 'Instagram', 'sort_order' => 30],
            ['code' => 'WHATSAPP', 'name' => 'WhatsApp', 'sort_order' => 40],
            ['code' => 'EMAIL', 'name' => 'Correo electrónico', 'sort_order' => 50],
            ['code' => 'WEB', 'name' => 'Web', 'sort_order' => 60],
            ['code' => 'SYSTEM', 'name' => 'Sistema', 'sort_order' => 70],
        ]);

        $this->createCatalog('work_item_origin_types', [
            ['code' => 'FACEBOOK_COMMENT', 'name' => 'Comentario de Facebook', 'sort_order' => 10],
            ['code' => 'FACEBOOK_REACTION', 'name' => 'Reacción de Facebook', 'sort_order' => 20],
            ['code' => 'MESSENGER_MESSAGE', 'name' => 'Mensaje de Messenger', 'sort_order' => 30],
            ['code' => 'INSTAGRAM_COMMENT', 'name' => 'Comentario de Instagram', 'sort_order' => 40],
            ['code' => 'WHATSAPP_MESSAGE', 'name' => 'Mensaje de WhatsApp', 'sort_order' => 50],
            ['code' => 'EMAIL', 'name' => 'Correo electrónico', 'sort_order' => 60],
            ['code' => 'FORM', 'name' => 'Formulario', 'sort_order' => 70],
            ['code' => 'SYSTEM_ALERT', 'name' => 'Alerta del sistema', 'sort_order' => 80],
            ['code' => 'AI_RECOMMENDATION', 'name' => 'Recomendación de IA', 'sort_order' => 90],
        ]);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'uuid' => ['type' => 'CHAR', 'constraint' => 36],
            'citizen_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'origin_type_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'origin_id' => ['type' => 'VARCHAR', 'constraint' => 190],
            'channel_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'status_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'priority_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 190],
            'summary' => ['type' => 'TEXT', 'null' => true],
            'assigned_user_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'case_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'workflow_execution_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'opened_at' => ['type' => 'DATETIME', 'null' => true],
            'sla_due_at' => ['type' => 'DATETIME', 'null' => true],
            'first_response_at' => ['type' => 'DATETIME', 'null' => true],
            'resolved_at' => ['type' => 'DATETIME', 'null' => true],
            'closed_at' => ['type' => 'DATETIME', 'null' => true],
            'metadata_json' => ['type' => 'LONGTEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('uuid');
        $this->forge->addUniqueKey(['origin_type_id', 'origin_id']);
        $this->forge->addKey(['status_id', 'priority_id']);
        $this->forge->addKey(['citizen_id', 'created_at']);
        $this->forge->addKey(['assigned_user_id', 'status_id']);
        $this->forge->addForeignKey('origin_type_id', 'work_item_origin_types', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('channel_id', 'work_item_channels', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('status_id', 'work_item_statuses', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('priority_id', 'work_item_priorities', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('citizen_id', 'citizens', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('case_id', 'cases', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('work_items');
    }

    private function createCatalog(string $table, array $rows, bool $withTerminal = false): void
    {
        $fields = [
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'code' => ['type' => 'VARCHAR', 'constraint' => 60],
            'name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'sort_order' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ];

        if ($withTerminal) {
            $fields['is_terminal'] = ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0];
        }

        $this->forge->addField($fields);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->createTable($table);

        $now = date('Y-m-d H:i:s');
        foreach ($rows as &$row) {
            $row['is_active'] = 1;
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }
        unset($row);

        $this->db->table($table)->insertBatch($rows);
    }

    public function down(): void
    {
        $this->forge->dropTable('work_items', true);
        $this->forge->dropTable('work_item_origin_types', true);
        $this->forge->dropTable('work_item_channels', true);
        $this->forge->dropTable('work_item_priorities', true);
        $this->forge->dropTable('work_item_statuses', true);
    }
}

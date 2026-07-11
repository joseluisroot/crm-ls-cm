<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWorkflowRuntimeInspectorTables extends Migration
{
    public function up(): void
    {
        $this->createExecutionNodes();
        $this->createExecutionLogs();
        $this->createExecutionSnapshots();
        $this->createExecutionPayloads();
        $this->createExecutionVariables();
    }

    private function createExecutionNodes(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'workflow_execution_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'event_uuid' => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'node_key' => ['type' => 'VARCHAR', 'constraint' => 120],
            'node_name' => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => true],
            'node_type' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 40],
            'attempt' => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'started_at' => ['type' => 'DATETIME', 'null' => true],
            'finished_at' => ['type' => 'DATETIME', 'null' => true],
            'duration_ms' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'error_class' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'error_message' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('workflow_execution_id');
        $this->forge->addKey(['workflow_execution_id', 'node_key']);
        $this->forge->addKey('status');
        $this->forge->addForeignKey('workflow_execution_id', 'workflow_executions', 'id', 'CASCADE', 'CASCADE', 'runtime_nodes_execution_foreign');
        $this->forge->createTable('workflow_execution_nodes');
    }

    private function createExecutionLogs(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'workflow_execution_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'workflow_execution_node_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'event_uuid' => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'level' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'info'],
            'message' => ['type' => 'TEXT'],
            'context_json' => ['type' => 'LONGTEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('workflow_execution_id');
        $this->forge->addKey('workflow_execution_node_id');
        $this->forge->addKey(['workflow_execution_id', 'level']);
        $this->forge->addForeignKey('workflow_execution_id', 'workflow_executions', 'id', 'CASCADE', 'CASCADE', 'runtime_logs_execution_foreign');
        $this->forge->addForeignKey('workflow_execution_node_id', 'workflow_execution_nodes', 'id', 'CASCADE', 'SET NULL', 'runtime_logs_node_foreign');
        $this->forge->createTable('workflow_execution_logs');
    }

    private function createExecutionSnapshots(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'workflow_execution_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'workflow_execution_node_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'event_uuid' => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'snapshot_type' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'node.completed'],
            'snapshot_json' => ['type' => 'LONGTEXT'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('workflow_execution_id');
        $this->forge->addForeignKey('workflow_execution_id', 'workflow_executions', 'id', 'CASCADE', 'CASCADE', 'runtime_snapshots_execution_foreign');
        $this->forge->addForeignKey('workflow_execution_node_id', 'workflow_execution_nodes', 'id', 'CASCADE', 'SET NULL', 'runtime_snapshots_node_foreign');
        $this->forge->createTable('workflow_execution_snapshots');
    }

    private function createExecutionPayloads(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'workflow_execution_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'workflow_execution_node_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'event_uuid' => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'direction' => ['type' => 'VARCHAR', 'constraint' => 10],
            'channel' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'payload_json' => ['type' => 'LONGTEXT', 'null' => true],
            'headers_json' => ['type' => 'LONGTEXT', 'null' => true],
            'status_code' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('workflow_execution_id');
        $this->forge->addKey('workflow_execution_node_id');
        $this->forge->addForeignKey('workflow_execution_id', 'workflow_executions', 'id', 'CASCADE', 'CASCADE', 'runtime_payloads_execution_foreign');
        $this->forge->addForeignKey('workflow_execution_node_id', 'workflow_execution_nodes', 'id', 'CASCADE', 'SET NULL', 'runtime_payloads_node_foreign');
        $this->forge->createTable('workflow_execution_payloads');
    }

    private function createExecutionVariables(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'workflow_execution_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'workflow_execution_node_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'event_uuid' => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'variable_name' => ['type' => 'VARCHAR', 'constraint' => 160],
            'variable_type' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'old_value_json' => ['type' => 'LONGTEXT', 'null' => true],
            'new_value_json' => ['type' => 'LONGTEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('workflow_execution_id');
        $this->forge->addKey(['workflow_execution_id', 'variable_name']);
        $this->forge->addForeignKey('workflow_execution_id', 'workflow_executions', 'id', 'CASCADE', 'CASCADE', 'runtime_variables_execution_foreign');
        $this->forge->addForeignKey('workflow_execution_node_id', 'workflow_execution_nodes', 'id', 'CASCADE', 'SET NULL', 'runtime_variables_node_foreign');
        $this->forge->createTable('workflow_execution_variables');
    }

    public function down(): void
    {
        $this->forge->dropTable('workflow_execution_variables', true);
        $this->forge->dropTable('workflow_execution_payloads', true);
        $this->forge->dropTable('workflow_execution_snapshots', true);
        $this->forge->dropTable('workflow_execution_logs', true);
        $this->forge->dropTable('workflow_execution_nodes', true);
    }
}

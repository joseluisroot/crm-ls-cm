<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDynamicWorkflowTables extends Migration
{
    public function up()
    {
        $this->createWorkflowsTable();
        $this->createWorkflowVersionsTable();
        $this->createWorkflowNodesTable();
        $this->createWorkflowTransitionsTable();
        $this->createWorkflowExecutionsTable();
    }

    private function createWorkflowsTable(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 160,
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 160,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'channel' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'all',
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'default'    => 'draft',
            ],
            'active_version_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'created_by' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->addKey('status');
        $this->forge->createTable('workflows');
    }

    private function createWorkflowVersionsTable(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'workflow_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'version_number' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'default'    => 'draft',
            ],
            'start_node_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'published_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_by' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('workflow_id');
        $this->forge->addUniqueKey([
            'workflow_id',
            'version_number',
        ]);

        $this->forge->addForeignKey(
            'workflow_id',
            'workflows',
            'id',
            'CASCADE',
            'CASCADE',
            'workflow_versions_workflow_foreign'
        );

        $this->forge->createTable('workflow_versions');
    }

    private function createWorkflowNodesTable(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'workflow_version_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'node_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 160,
            ],
            'node_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
            ],
            'message_text' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'context_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'configuration' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'position_x' => [
                'type'    => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
            ],
            'position_y' => [
                'type'    => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
            ],
            'is_terminal' => [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('workflow_version_id');
        $this->forge->addUniqueKey([
            'workflow_version_id',
            'node_key',
        ]);

        $this->forge->addForeignKey(
            'workflow_version_id',
            'workflow_versions',
            'id',
            'CASCADE',
            'CASCADE',
            'workflow_nodes_version_foreign'
        );

        $this->forge->createTable('workflow_nodes');
    }

    private function createWorkflowTransitionsTable(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'workflow_version_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'source_node_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'target_node_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'label' => [
                'type'       => 'VARCHAR',
                'constraint' => 160,
                'null'       => true,
            ],
            'payload' => [
                'type'       => 'VARCHAR',
                'constraint' => 160,
                'null'       => true,
            ],
            'condition_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'default'    => 'always',
            ],
            'condition_value' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('workflow_version_id');
        $this->forge->addKey('source_node_key');
        $this->forge->addKey('payload');

        $this->forge->addForeignKey(
            'workflow_version_id',
            'workflow_versions',
            'id',
            'CASCADE',
            'CASCADE',
            'workflow_transitions_version_foreign'
        );

        $this->forge->createTable('workflow_transitions');
    }

    private function createWorkflowExecutionsTable(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'workflow_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'workflow_version_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'conversation_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'current_node_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'default'    => 'running',
            ],
            'started_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'last_interaction_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'metadata' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('workflow_id');
        $this->forge->addKey('workflow_version_id');
        $this->forge->addKey('conversation_id');
        $this->forge->addKey('status');

        $this->forge->addForeignKey(
            'workflow_id',
            'workflows',
            'id',
            'CASCADE',
            'CASCADE',
            'workflow_executions_workflow_foreign'
        );

        $this->forge->addForeignKey(
            'workflow_version_id',
            'workflow_versions',
            'id',
            'CASCADE',
            'CASCADE',
            'workflow_executions_version_foreign'
        );

        $this->forge->createTable('workflow_executions');
    }

    public function down()
    {
        $this->forge->dropTable('workflow_executions', true);
        $this->forge->dropTable('workflow_transitions', true);
        $this->forge->dropTable('workflow_nodes', true);
        $this->forge->dropTable('workflow_versions', true);
        $this->forge->dropTable('workflows', true);
    }
}

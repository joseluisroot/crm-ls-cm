<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWorkflowSimulationsTable extends Migration
{
    public function up()
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
            'current_node_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'default'    => 'running',
            ],
            'context_data' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'execution_log' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'last_error' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_by' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'started_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'completed_at' => [
                'type' => 'DATETIME',
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
        $this->forge->addKey('status');

        $this->forge->addForeignKey(
            'workflow_id',
            'workflows',
            'id',
            'CASCADE',
            'CASCADE',
            'workflow_simulations_workflow_foreign'
        );

        $this->forge->addForeignKey(
            'workflow_version_id',
            'workflow_versions',
            'id',
            'CASCADE',
            'CASCADE',
            'workflow_simulations_version_foreign'
        );

        $this->forge->createTable('workflow_simulations');
    }

    public function down()
    {
        $this->forge->dropTable('workflow_simulations', true);
    }
}

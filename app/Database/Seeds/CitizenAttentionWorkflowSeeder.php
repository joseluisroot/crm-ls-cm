<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CitizenAttentionWorkflowSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $existing = $this->db
            ->table('workflows')
            ->where('slug', 'citizen-attention')
            ->get()
            ->getRowArray();

        if ($existing) {
            throw new \RuntimeException(
                'El workflow citizen-attention ya existe con ID '
                . $existing['id']
            );
        }

        $this->db->transBegin();

        try {
            /*
             * 1. Workflow
             */
            $workflowInserted = $this->db
                ->table('workflows')
                ->insert([
                    'name' => 'Atención Ciudadana',
                    'slug' => 'citizen-attention',
                    'description' => 'Flujo principal de atención, escucha y captura de necesidades ciudadanas.',
                    'channel' => 'all',
                    'status' => 'draft',
                    'active_version_id' => null,
                    'created_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            $this->assertDatabaseOperation(
                $workflowInserted,
                'insertar el workflow'
            );

            $workflowId = (int) $this->db->insertID();

            if ($workflowId <= 0) {
                throw new \RuntimeException(
                    'El workflow fue insertado, pero no se obtuvo su ID.'
                );
            }

            /*
             * 2. Versión
             */
            $versionInserted = $this->db
                ->table('workflow_versions')
                ->insert([
                    'workflow_id' => $workflowId,
                    'version_number' => 1,
                    'status' => 'draft',
                    'start_node_key' => 'welcome',
                    'published_at' => null,
                    'created_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            $this->assertDatabaseOperation(
                $versionInserted,
                'insertar la versión del workflow'
            );

            $versionId = (int) $this->db->insertID();

            if ($versionId <= 0) {
                throw new \RuntimeException(
                    'La versión fue insertada, pero no se obtuvo su ID.'
                );
            }

            /*
             * 3. Nodos
             */
            $nodes = [
                [
                    'workflow_version_id' => $versionId,
                    'node_key' => 'welcome',
                    'name' => 'Bienvenida',
                    'node_type' => 'quick_replies',
                    'message_text' => "👋 ¡Hola!\n\nGracias por comunicarte. Tu mensaje será escuchado, registrado y atendido con respeto.\n\n¿Cómo podemos ayudarte hoy?",
                    'context_key' => null,
                    'configuration' => json_encode(
                        [],
                        JSON_UNESCAPED_UNICODE
                    ),
                    'position_x' => 100,
                    'position_y' => 100,
                    'is_terminal' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'workflow_version_id' => $versionId,
                    'node_key' => 'ask_municipality',
                    'name' => 'Solicitar municipio',
                    'node_type' => 'capture_text',
                    'message_text' => '📍 ¿En qué municipio ocurre la situación?',
                    'context_key' => 'municipality',
                    'configuration' => json_encode([
                        'required' => true,
                        'minimum_length' => 2,
                    ]),
                    'position_x' => 100,
                    'position_y' => 260,
                    'is_terminal' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'workflow_version_id' => $versionId,
                    'node_key' => 'ask_community',
                    'name' => 'Solicitar comunidad',
                    'node_type' => 'capture_text',
                    'message_text' => '🏘️ ¿En qué comunidad, colonia, cantón o caserío ocurre?',
                    'context_key' => 'community',
                    'configuration' => json_encode([
                        'required' => true,
                        'minimum_length' => 1,
                    ]),
                    'position_x' => 100,
                    'position_y' => 420,
                    'is_terminal' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'workflow_version_id' => $versionId,
                    'node_key' => 'ask_description',
                    'name' => 'Solicitar descripción',
                    'node_type' => 'capture_text',
                    'message_text' => '📝 Cuéntanos qué está ocurriendo y cómo afecta a tu comunidad.',
                    'context_key' => 'description',
                    'configuration' => json_encode([
                        'required' => true,
                        'minimum_length' => 5,
                    ]),
                    'position_x' => 100,
                    'position_y' => 580,
                    'is_terminal' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'workflow_version_id' => $versionId,
                    'node_key' => 'create_case',
                    'name' => 'Crear caso',
                    'node_type' => 'action',
                    'message_text' => null,
                    'context_key' => null,
                    'configuration' => json_encode([
                        'action' => 'create_case',
                        'category' => 'necesidad-comunitaria',
                    ]),
                    'position_x' => 100,
                    'position_y' => 740,
                    'is_terminal' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'workflow_version_id' => $versionId,
                    'node_key' => 'completed',
                    'name' => 'Finalización',
                    'node_type' => 'end',
                    'message_text' => "❤️ Gracias por confiar en nosotros.\n\nTu reporte fue registrado y será incorporado al proceso de seguimiento ciudadano.",
                    'context_key' => null,
                    'configuration' => json_encode(
                        [],
                        JSON_UNESCAPED_UNICODE
                    ),
                    'position_x' => 100,
                    'position_y' => 900,
                    'is_terminal' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ];

            $nodesInserted = $this->db
                ->table('workflow_nodes')
                ->insertBatch($nodes);

            if ($nodesInserted === false || $nodesInserted !== count($nodes)) {
                $error = $this->db->error();

                throw new \RuntimeException(
                    'Error al insertar los nodos. '
                    . 'Esperados: ' . count($nodes)
                    . ', insertados: ' . var_export($nodesInserted, true)
                    . '. MySQL: ' . ($error['code'] ?? 'sin código')
                    . ' - ' . ($error['message'] ?? 'sin mensaje')
                );
            }

            /*
             * 4. Transiciones
             */
            $transitions = [
                [
                    'workflow_version_id' => $versionId,
                    'source_node_key' => 'welcome',
                    'target_node_key' => 'ask_municipality',
                    'label' => 'Reportar necesidad',
                    'payload' => 'OPTION_NEED',
                    'condition_type' => 'payload_equals',
                    'condition_value' => 'OPTION_NEED',
                    'sort_order' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'workflow_version_id' => $versionId,
                    'source_node_key' => 'ask_municipality',
                    'target_node_key' => 'ask_community',
                    'label' => 'Continuar',
                    'payload' => null,
                    'condition_type' => 'always',
                    'condition_value' => null,
                    'sort_order' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'workflow_version_id' => $versionId,
                    'source_node_key' => 'ask_community',
                    'target_node_key' => 'ask_description',
                    'label' => 'Continuar',
                    'payload' => null,
                    'condition_type' => 'always',
                    'condition_value' => null,
                    'sort_order' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'workflow_version_id' => $versionId,
                    'source_node_key' => 'ask_description',
                    'target_node_key' => 'create_case',
                    'label' => 'Registrar caso',
                    'payload' => null,
                    'condition_type' => 'always',
                    'condition_value' => null,
                    'sort_order' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'workflow_version_id' => $versionId,
                    'source_node_key' => 'create_case',
                    'target_node_key' => 'completed',
                    'label' => 'Finalizar',
                    'payload' => null,
                    'condition_type' => 'always',
                    'condition_value' => null,
                    'sort_order' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ];

            $transitionsInserted = $this->db
                ->table('workflow_transitions')
                ->insertBatch($transitions);

            if (
                $transitionsInserted === false
                || $transitionsInserted !== count($transitions)
            ) {
                $error = $this->db->error();

                throw new \RuntimeException(
                    'Error al insertar las transiciones. '
                    . 'Esperadas: ' . count($transitions)
                    . ', insertadas: '
                    . var_export($transitionsInserted, true)
                    . '. MySQL: ' . ($error['code'] ?? 'sin código')
                    . ' - ' . ($error['message'] ?? 'sin mensaje')
                );
            }

            if ($this->db->transStatus() === false) {
                $error = $this->db->error();

                throw new \RuntimeException(
                    'La transacción quedó marcada como fallida. '
                    . ($error['code'] ?? 'sin código')
                    . ' - '
                    . ($error['message'] ?? 'sin mensaje')
                );
            }

            $this->db->transCommit();

            echo PHP_EOL;
            echo 'Workflow creado correctamente.' . PHP_EOL;
            echo 'Workflow ID: ' . $workflowId . PHP_EOL;
            echo 'Version ID: ' . $versionId . PHP_EOL;
            echo 'Nodos: ' . count($nodes) . PHP_EOL;
            echo 'Transiciones: ' . count($transitions) . PHP_EOL;
        } catch (\Throwable $e) {
            $this->db->transRollback();

            throw $e;
        }
    }

    private function assertDatabaseOperation(
        mixed $result,
        string $operation
    ): void {
        if ($result !== false) {
            return;
        }

        $error = $this->db->error();

        throw new \RuntimeException(
            'Error al ' . $operation . '. '
            . 'Código MySQL: ' . ($error['code'] ?? 'desconocido')
            . ' | Mensaje: ' . ($error['message'] ?? 'sin detalle')
        );
    }
}

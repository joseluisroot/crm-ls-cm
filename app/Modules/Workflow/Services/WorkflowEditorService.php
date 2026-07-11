<?php

namespace Modules\Workflow\Services;

use Modules\Workflow\Models\WorkflowNodeModel;
use Modules\Workflow\Models\WorkflowTransitionModel;
use Modules\Workflow\Models\WorkflowVersionModel;
use Modules\Workflow\Support\WorkflowException;
use Modules\Workflow\Support\WorkflowNodeTypes;
use Modules\Workflow\Support\WorkflowStatus;

class WorkflowEditorService
{
    public function createNode(
        int $versionId,
        array $data
    ): int {
        $version = $this->getEditableVersion($versionId);

        $nodeKey = $this->normalizeKey(
            (string) ($data['node_key'] ?? '')
        );

        $name = trim((string) ($data['name'] ?? ''));
        $nodeType = trim((string) ($data['node_type'] ?? ''));

        if ($nodeKey === '') {
            throw new WorkflowException(
                'La clave del nodo es obligatoria.'
            );
        }

        if ($name === '') {
            throw new WorkflowException(
                'El nombre del nodo es obligatorio.'
            );
        }

        if (!in_array($nodeType, WorkflowNodeTypes::all(), true)) {
            throw new WorkflowException(
                'El tipo de nodo seleccionado no es válido.'
            );
        }

        $nodeModel = new WorkflowNodeModel();

        $existing = $nodeModel
            ->where('workflow_version_id', $versionId)
            ->where('node_key', $nodeKey)
            ->first();

        if ($existing) {
            throw new WorkflowException(
                'Ya existe un nodo con esa clave.'
            );
        }

        $configuration = $this->buildConfiguration(
            $nodeType,
            $data
        );

        $nodeId = (int) $nodeModel->insert([
            'workflow_version_id' => $versionId,
            'node_key'            => $nodeKey,
            'name'                => $name,
            'node_type'           => $nodeType,
            'message_text'        => $this->nullableText(
                $data['message_text'] ?? null
            ),
            'context_key'         => $this->nullableText(
                $data['context_key'] ?? null
            ),
            'configuration'       => json_encode(
                $configuration,
                JSON_UNESCAPED_UNICODE
            ),
            'position_x'          => (float) ($data['position_x'] ?? 0),
            'position_y'          => (float) ($data['position_y'] ?? 0),
            'is_terminal'         => !empty($data['is_terminal']) ? 1 : 0,
        ]);

        if ($nodeId <= 0) {
            throw new WorkflowException(
                'No fue posible crear el nodo.'
            );
        }

        if (!empty($data['is_start_node'])) {
            (new WorkflowVersionModel())->update(
                $version['id'],
                [
                    'start_node_key' => $nodeKey,
                ]
            );
        }

        return $nodeId;
    }

    public function updateNode(
        int $versionId,
        int $nodeId,
        array $data
    ): bool {
        $version = $this->getEditableVersion($versionId);

        $nodeModel = new WorkflowNodeModel();

        $node = $nodeModel
            ->where('id', $nodeId)
            ->where('workflow_version_id', $versionId)
            ->first();

        if (!$node) {
            throw new WorkflowException(
                'El nodo seleccionado no existe.'
            );
        }

        $nodeKey = $this->normalizeKey(
            (string) ($data['node_key'] ?? '')
        );

        $name = trim((string) ($data['name'] ?? ''));
        $nodeType = trim((string) ($data['node_type'] ?? ''));

        if (
            $nodeKey === ''
            || $name === ''
            || !in_array($nodeType, WorkflowNodeTypes::all(), true)
        ) {
            throw new WorkflowException(
                'Los datos principales del nodo no son válidos.'
            );
        }

        $duplicate = $nodeModel
            ->where('workflow_version_id', $versionId)
            ->where('node_key', $nodeKey)
            ->where('id !=', $nodeId)
            ->first();

        if ($duplicate) {
            throw new WorkflowException(
                'Otro nodo ya utiliza esa clave.'
            );
        }

        $oldKey = $node['node_key'];

        $db = db_connect();
        $db->transBegin();

        try {
            $updated = $nodeModel->update($nodeId, [
                'node_key'      => $nodeKey,
                'name'          => $name,
                'node_type'     => $nodeType,
                'message_text'  => $this->nullableText(
                    $data['message_text'] ?? null
                ),
                'context_key'   => $this->nullableText(
                    $data['context_key'] ?? null
                ),
                'configuration' => json_encode(
                    $this->buildConfiguration($nodeType, $data),
                    JSON_UNESCAPED_UNICODE
                ),
                'position_x'    => (float) ($data['position_x'] ?? 0),
                'position_y'    => (float) ($data['position_y'] ?? 0),
                'is_terminal'   => !empty($data['is_terminal']) ? 1 : 0,
            ]);

            if (!$updated) {
                throw new WorkflowException(
                    'No fue posible actualizar el nodo.'
                );
            }

            if ($oldKey !== $nodeKey) {
                (new WorkflowTransitionModel())
                    ->where('workflow_version_id', $versionId)
                    ->where('source_node_key', $oldKey)
                    ->set('source_node_key', $nodeKey)
                    ->update();

                (new WorkflowTransitionModel())
                    ->where('workflow_version_id', $versionId)
                    ->where('target_node_key', $oldKey)
                    ->set('target_node_key', $nodeKey)
                    ->update();

                if ($version['start_node_key'] === $oldKey) {
                    (new WorkflowVersionModel())->update(
                        $versionId,
                        [
                            'start_node_key' => $nodeKey,
                        ]
                    );
                }
            }

            if (!empty($data['is_start_node'])) {
                (new WorkflowVersionModel())->update(
                    $versionId,
                    [
                        'start_node_key' => $nodeKey,
                    ]
                );
            }

            $db->transCommit();

            return true;
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }
    }

    public function deleteNode(
        int $versionId,
        int $nodeId
    ): bool {
        $version = $this->getEditableVersion($versionId);

        $nodeModel = new WorkflowNodeModel();

        $node = $nodeModel
            ->where('id', $nodeId)
            ->where('workflow_version_id', $versionId)
            ->first();

        if (!$node) {
            throw new WorkflowException(
                'El nodo seleccionado no existe.'
            );
        }

        $db = db_connect();
        $db->transBegin();

        try {
            (new WorkflowTransitionModel())
                ->where('workflow_version_id', $versionId)
                ->groupStart()
                ->where('source_node_key', $node['node_key'])
                ->orWhere('target_node_key', $node['node_key'])
                ->groupEnd()
                ->delete();

            $deleted = $nodeModel->delete($nodeId);

            if (!$deleted) {
                throw new WorkflowException(
                    'No fue posible eliminar el nodo.'
                );
            }

            if ($version['start_node_key'] === $node['node_key']) {
                (new WorkflowVersionModel())->update(
                    $versionId,
                    [
                        'start_node_key' => null,
                    ]
                );
            }

            $db->transCommit();

            return true;
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }
    }

    public function createTransition(
        int $versionId,
        array $data
    ): int {
        $this->getEditableVersion($versionId);

        $source = trim(
            (string) ($data['source_node_key'] ?? '')
        );

        $target = trim(
            (string) ($data['target_node_key'] ?? '')
        );

        if ($source === '' || $target === '') {
            throw new WorkflowException(
                'Debes seleccionar un nodo origen y un nodo destino.'
            );
        }

        $nodeModel = new WorkflowNodeModel();

        $sourceNode = $nodeModel
            ->where('workflow_version_id', $versionId)
            ->where('node_key', $source)
            ->first();

        $targetNode = $nodeModel
            ->where('workflow_version_id', $versionId)
            ->where('node_key', $target)
            ->first();

        if (!$sourceNode || !$targetNode) {
            throw new WorkflowException(
                'El nodo origen o destino no pertenece a esta versión.'
            );
        }

        $conditionType = trim(
            (string) ($data['condition_type'] ?? 'always')
        );

        $allowedConditions = [
            'always',
            'payload_equals',
            'text_equals',
        ];

        if (!in_array($conditionType, $allowedConditions, true)) {
            throw new WorkflowException(
                'La condición de transición no es válida.'
            );
        }

        $transitionId = (int) (
        new WorkflowTransitionModel()
        )->insert([
                'workflow_version_id' => $versionId,
                'source_node_key'     => $source,
                'target_node_key'     => $target,
                'label'               => $this->nullableText(
                    $data['label'] ?? null
                ),
                'payload'             => $this->nullableText(
                    $data['payload'] ?? null
                ),
                'condition_type'      => $conditionType,
                'condition_value'     => $this->nullableText(
                    $data['condition_value'] ?? null
                ),
                'sort_order'          => max(
                    0,
                    (int) ($data['sort_order'] ?? 0)
                ),
            ]);

        if ($transitionId <= 0) {
            throw new WorkflowException(
                'No fue posible crear la transición.'
            );
        }

        return $transitionId;
    }

    public function updateTransition(
        int $versionId,
        int $transitionId,
        array $data
    ): bool {
        $this->getEditableVersion($versionId);

        $transitionModel = new WorkflowTransitionModel();

        $transition = $transitionModel
            ->where('id', $transitionId)
            ->where('workflow_version_id', $versionId)
            ->first();

        if (!$transition) {
            throw new WorkflowException(
                'La transición seleccionada no existe.'
            );
        }

        $source = trim(
            (string) ($data['source_node_key'] ?? '')
        );

        $target = trim(
            (string) ($data['target_node_key'] ?? '')
        );

        $nodeModel = new WorkflowNodeModel();

        $sourceNode = $nodeModel
            ->where('workflow_version_id', $versionId)
            ->where('node_key', $source)
            ->first();

        $targetNode = $nodeModel
            ->where('workflow_version_id', $versionId)
            ->where('node_key', $target)
            ->first();

        if (!$sourceNode || !$targetNode) {
            throw new WorkflowException(
                'El nodo origen o destino no pertenece a esta versión.'
            );
        }

        $conditionType = trim(
            (string) ($data['condition_type'] ?? 'always')
        );

        if (!in_array($conditionType, [
            'always',
            'payload_equals',
            'text_equals',
        ], true)) {
            throw new WorkflowException(
                'El tipo de condición no es válido.'
            );
        }

        $conditionValue = $this->nullableText(
            $data['condition_value'] ?? null
        );

        if (
            in_array($conditionType, [
                'payload_equals',
                'text_equals',
            ], true)
            && $conditionValue === null
        ) {
            throw new WorkflowException(
                'La condición seleccionada necesita un valor.'
            );
        }

        return (bool) $transitionModel->update(
            $transitionId,
            [
                'source_node_key' => $source,
                'target_node_key' => $target,
                'label' => $this->nullableText(
                    $data['label'] ?? null
                ),
                'payload' => $this->nullableText(
                    $data['payload'] ?? null
                ),
                'condition_type' => $conditionType,
                'condition_value' => $conditionValue,
                'sort_order' => max(
                    0,
                    (int) ($data['sort_order'] ?? 0)
                ),
            ]
        );
    }

    public function deleteTransition(
        int $versionId,
        int $transitionId
    ): bool {
        $this->getEditableVersion($versionId);

        $transition = (new WorkflowTransitionModel())
            ->where('id', $transitionId)
            ->where('workflow_version_id', $versionId)
            ->first();

        if (!$transition) {
            throw new WorkflowException(
                'La transición seleccionada no existe.'
            );
        }

        return (bool) (
        new WorkflowTransitionModel()
        )->delete($transitionId);
    }

    private function getEditableVersion(
        int $versionId
    ): array {
        $version = (new WorkflowVersionModel())->find($versionId);

        if (!$version) {
            throw new WorkflowException(
                'La versión seleccionada no existe.'
            );
        }

        if ($version['status'] !== WorkflowStatus::DRAFT) {
            throw new WorkflowException(
                'Solo se pueden modificar versiones en borrador.'
            );
        }

        return $version;
    }

    private function buildConfiguration(
        string $nodeType,
        array $data
    ): array {
        if ($nodeType === WorkflowNodeTypes::CAPTURE_TEXT) {
            return [
                'required' => !empty($data['required']),
                'minimum_length' => max(
                    0,
                    (int) ($data['minimum_length'] ?? 0)
                ),
            ];
        }

        if ($nodeType === WorkflowNodeTypes::ACTION) {
            return [
                'action' => trim(
                    (string) ($data['action'] ?? '')
                ),
                'category' => $this->nullableText(
                    $data['category'] ?? null
                ),
            ];
        }

        return [];
    }

    private function normalizeKey(string $value): string
    {
        $value = mb_strtolower(trim($value));

        $value = preg_replace(
            '/[^a-z0-9_]+/u',
            '_',
            $value
        );

        return trim((string) $value, '_');
    }

    private function nullableText(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}
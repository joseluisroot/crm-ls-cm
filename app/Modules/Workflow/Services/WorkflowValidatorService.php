<?php

namespace Modules\Workflow\Services;

use Modules\Workflow\DTO\WorkflowValidationResultDTO;
use Modules\Workflow\Models\WorkflowNodeModel;
use Modules\Workflow\Models\WorkflowTransitionModel;
use Modules\Workflow\Models\WorkflowVersionModel;
use Modules\Workflow\Support\WorkflowActions;
use Modules\Workflow\Support\WorkflowNodeTypes;

class WorkflowValidatorService
{
    public function validateVersion(
        int $versionId
    ): WorkflowValidationResultDTO {
        $result = new WorkflowValidationResultDTO();

        $version = (new WorkflowVersionModel())->find($versionId);

        if (!$version) {
            $result->addError(
                'VERSION_NOT_FOUND',
                'La versión seleccionada no existe.'
            );

            return $result;
        }

        $nodes = (new WorkflowNodeModel())
            ->where('workflow_version_id', $versionId)
            ->findAll();

        $transitions = (new WorkflowTransitionModel())
            ->where('workflow_version_id', $versionId)
            ->orderBy('sort_order', 'ASC')
            ->findAll();

        if (empty($nodes)) {
            $result->addError(
                'NO_NODES',
                'La versión no contiene nodos.'
            );

            return $result;
        }

        $nodesByKey = [];

        foreach ($nodes as $node) {
            $nodeKey = trim((string) $node['node_key']);

            if ($nodeKey === '') {
                $result->addError(
                    'EMPTY_NODE_KEY',
                    'Existe un nodo sin clave técnica.',
                    [
                        'node_id' => $node['id'],
                        'node_name' => $node['name'],
                    ]
                );

                continue;
            }

            if (isset($nodesByKey[$nodeKey])) {
                $result->addError(
                    'DUPLICATE_NODE_KEY',
                    'Existen nodos con la misma clave técnica.',
                    [
                        'node_key' => $nodeKey,
                    ]
                );
            }

            $nodesByKey[$nodeKey] = $node;
        }

        $this->validateStartNode(
            $version,
            $nodesByKey,
            $result
        );

        $this->validateNodeDefinitions(
            $nodes,
            $result
        );

        $this->validateTransitions(
            $transitions,
            $nodesByKey,
            $result
        );

        $this->validateConnectivity(
            $version,
            $nodes,
            $transitions,
            $nodesByKey,
            $result
        );

        $this->validateTerminalNodes(
            $nodes,
            $transitions,
            $result
        );

        $this->validateCycles(
            $version,
            $transitions,
            $nodesByKey,
            $result
        );

        $result->addInformation(
            'SUMMARY',
            sprintf(
                'La versión contiene %d nodos y %d transiciones.',
                count($nodes),
                count($transitions)
            )
        );

        return $result;
    }

    private function validateStartNode(
        array $version,
        array $nodesByKey,
        WorkflowValidationResultDTO $result
    ): void {
        $startNodeKey = trim(
            (string) ($version['start_node_key'] ?? '')
        );

        if ($startNodeKey === '') {
            $result->addError(
                'START_NODE_NOT_CONFIGURED',
                'La versión no tiene un nodo inicial configurado.'
            );

            return;
        }

        if (!isset($nodesByKey[$startNodeKey])) {
            $result->addError(
                'START_NODE_NOT_FOUND',
                'El nodo inicial configurado no existe.',
                [
                    'start_node_key' => $startNodeKey,
                ]
            );
        }
    }

    private function validateNodeDefinitions(
        array $nodes,
        WorkflowValidationResultDTO $result
    ): void {
        foreach ($nodes as $node) {
            $nodeType = $node['node_type'] ?? '';

            if (!in_array(
                $nodeType,
                WorkflowNodeTypes::all(),
                true
            )) {
                $result->addError(
                    'INVALID_NODE_TYPE',
                    'El nodo tiene un tipo no soportado.',
                    [
                        'node_key' => $node['node_key'],
                        'node_type' => $nodeType,
                    ]
                );

                continue;
            }

            $configuration = json_decode(
                $node['configuration'] ?? '{}',
                true
            );

            if (!is_array($configuration)) {
                $result->addError(
                    'INVALID_NODE_CONFIGURATION',
                    'La configuración del nodo no contiene JSON válido.',
                    [
                        'node_key' => $node['node_key'],
                    ]
                );

                $configuration = [];
            }

            if (
                in_array($nodeType, [
                    WorkflowNodeTypes::MESSAGE,
                    WorkflowNodeTypes::QUICK_REPLIES,
                    WorkflowNodeTypes::CAPTURE_TEXT,
                    WorkflowNodeTypes::END,
                ], true)
                && trim((string) ($node['message_text'] ?? '')) === ''
            ) {
                $result->addWarning(
                    'EMPTY_MESSAGE',
                    'El nodo no tiene un mensaje configurado.',
                    [
                        'node_key' => $node['node_key'],
                        'node_type' => $nodeType,
                    ]
                );
            }

            if (
                $nodeType === WorkflowNodeTypes::CAPTURE_TEXT
                && trim((string) ($node['context_key'] ?? '')) === ''
            ) {
                $result->addError(
                    'CAPTURE_WITHOUT_CONTEXT_KEY',
                    'El nodo de captura no tiene una variable de contexto.',
                    [
                        'node_key' => $node['node_key'],
                    ]
                );
            }

            if ($nodeType === WorkflowNodeTypes::ACTION) {
                $action = trim(
                    (string) ($configuration['action'] ?? '')
                );

                if ($action === '') {
                    $result->addError(
                        'ACTION_NOT_CONFIGURED',
                        'El nodo de acción no tiene una acción configurada.',
                        [
                            'node_key' => $node['node_key'],
                        ]
                    );
                } elseif (!in_array(
                    $action,
                    WorkflowActions::all(),
                    true
                )) {
                    $result->addError(
                        'INVALID_ACTION',
                        'El nodo utiliza una acción no reconocida.',
                        [
                            'node_key' => $node['node_key'],
                            'action' => $action,
                        ]
                    );
                } elseif (!in_array(
                    $action,
                    WorkflowActions::implemented(),
                    true
                )) {
                    $result->addWarning(
                        'ACTION_NOT_IMPLEMENTED',
                        'La acción está registrada, pero todavía no está implementada en el Runtime.',
                        [
                            'node_key' => $node['node_key'],
                            'action' => $action,
                        ]
                    );
                }
            }

            if (
                $nodeType === WorkflowNodeTypes::END
                && (int) ($node['is_terminal'] ?? 0) !== 1
            ) {
                $result->addWarning(
                    'END_NODE_NOT_TERMINAL',
                    'El nodo final debería marcarse como terminal.',
                    [
                        'node_key' => $node['node_key'],
                    ]
                );
            }

            if (
                (int) ($node['is_terminal'] ?? 0) === 1
                && !in_array($nodeType, [
                    WorkflowNodeTypes::END,
                    WorkflowNodeTypes::MESSAGE,
                ], true)
            ) {
                $result->addWarning(
                    'UNUSUAL_TERMINAL_NODE',
                    'El nodo está marcado como terminal, pero su tipo no es final ni mensaje.',
                    [
                        'node_key' => $node['node_key'],
                        'node_type' => $nodeType,
                    ]
                );
            }
        }
    }

    private function validateTransitions(
        array $transitions,
        array $nodesByKey,
        WorkflowValidationResultDTO $result
    ): void {
        foreach ($transitions as $transition) {
            $source = trim(
                (string) $transition['source_node_key']
            );

            $target = trim(
                (string) $transition['target_node_key']
            );

            if (!isset($nodesByKey[$source])) {
                $result->addError(
                    'TRANSITION_SOURCE_NOT_FOUND',
                    'La transición tiene un nodo origen inexistente.',
                    [
                        'transition_id' => $transition['id'],
                        'source_node_key' => $source,
                    ]
                );
            }

            if (!isset($nodesByKey[$target])) {
                $result->addError(
                    'TRANSITION_TARGET_NOT_FOUND',
                    'La transición tiene un nodo destino inexistente.',
                    [
                        'transition_id' => $transition['id'],
                        'target_node_key' => $target,
                    ]
                );
            }

            if ($source === $target) {
                $result->addWarning(
                    'SELF_TRANSITION',
                    'La transición regresa al mismo nodo.',
                    [
                        'transition_id' => $transition['id'],
                        'node_key' => $source,
                    ]
                );
            }

            $conditionType = $transition['condition_type']
                ?? 'always';

            if (!in_array($conditionType, [
                'always',
                'payload_equals',
                'text_equals',
            ], true)) {
                $result->addError(
                    'INVALID_TRANSITION_CONDITION',
                    'La transición tiene un tipo de condición no soportado.',
                    [
                        'transition_id' => $transition['id'],
                        'condition_type' => $conditionType,
                    ]
                );
            }

            if (
                in_array($conditionType, [
                    'payload_equals',
                    'text_equals',
                ], true)
                && trim(
                    (string) ($transition['condition_value'] ?? '')
                ) === ''
            ) {
                $result->addError(
                    'TRANSITION_CONDITION_WITHOUT_VALUE',
                    'La condición de la transición requiere un valor.',
                    [
                        'transition_id' => $transition['id'],
                    ]
                );
            }
        }

        foreach ($nodesByKey as $nodeKey => $node) {
            if (
                $node['node_type']
                !== WorkflowNodeTypes::QUICK_REPLIES
            ) {
                continue;
            }

            $options = array_filter(
                $transitions,
                static fn(array $transition): bool =>
                    $transition['source_node_key'] === $nodeKey
                    && !empty($transition['payload'])
            );

            if (empty($options)) {
                $result->addError(
                    'QUICK_REPLIES_WITHOUT_OPTIONS',
                    'El nodo de opciones rápidas no tiene opciones configuradas.',
                    [
                        'node_key' => $nodeKey,
                    ]
                );
            }
        }
    }

    private function validateConnectivity(
        array $version,
        array $nodes,
        array $transitions,
        array $nodesByKey,
        WorkflowValidationResultDTO $result
    ): void {
        $startNodeKey = trim(
            (string) ($version['start_node_key'] ?? '')
        );

        if (
            $startNodeKey === ''
            || !isset($nodesByKey[$startNodeKey])
        ) {
            return;
        }

        $adjacency = [];

        foreach ($transitions as $transition) {
            $adjacency[$transition['source_node_key']][] =
                $transition['target_node_key'];
        }

        $visited = [];
        $queue = [$startNodeKey];

        while (!empty($queue)) {
            $current = array_shift($queue);

            if (isset($visited[$current])) {
                continue;
            }

            $visited[$current] = true;

            foreach ($adjacency[$current] ?? [] as $target) {
                if (!isset($visited[$target])) {
                    $queue[] = $target;
                }
            }
        }

        foreach ($nodes as $node) {
            if (!isset($visited[$node['node_key']])) {
                $result->addWarning(
                    'UNREACHABLE_NODE',
                    'El nodo no es alcanzable desde el nodo inicial.',
                    [
                        'node_key' => $node['node_key'],
                        'node_name' => $node['name'],
                    ]
                );
            }
        }
    }

    private function validateTerminalNodes(
        array $nodes,
        array $transitions,
        WorkflowValidationResultDTO $result
    ): void {
        $terminalNodes = array_filter(
            $nodes,
            static fn(array $node): bool =>
                (int) ($node['is_terminal'] ?? 0) === 1
                || $node['node_type'] === WorkflowNodeTypes::END
        );

        if (empty($terminalNodes)) {
            $result->addError(
                'NO_TERMINAL_NODE',
                'El workflow no tiene un nodo final.'
            );
        }

        $outgoingCount = [];

        foreach ($transitions as $transition) {
            $source = $transition['source_node_key'];

            $outgoingCount[$source] =
                ($outgoingCount[$source] ?? 0) + 1;
        }

        foreach ($nodes as $node) {
            $nodeKey = $node['node_key'];
            $isTerminal =
                (int) ($node['is_terminal'] ?? 0) === 1
                || $node['node_type'] === WorkflowNodeTypes::END;

            if ($isTerminal && !empty($outgoingCount[$nodeKey])) {
                $result->addWarning(
                    'TERMINAL_NODE_WITH_OUTPUT',
                    'El nodo terminal tiene transiciones de salida.',
                    [
                        'node_key' => $nodeKey,
                    ]
                );
            }

            if (
                !$isTerminal
                && empty($outgoingCount[$nodeKey])
            ) {
                $result->addError(
                    'NON_TERMINAL_WITHOUT_OUTPUT',
                    'El nodo no es terminal y no tiene transición de salida.',
                    [
                        'node_key' => $nodeKey,
                        'node_type' => $node['node_type'],
                    ]
                );
            }
        }
    }

    private function validateCycles(
        array $version,
        array $transitions,
        array $nodesByKey,
        WorkflowValidationResultDTO $result
    ): void {
        $startNodeKey = trim(
            (string) ($version['start_node_key'] ?? '')
        );

        if (
            $startNodeKey === ''
            || !isset($nodesByKey[$startNodeKey])
        ) {
            return;
        }

        $adjacency = [];

        foreach ($transitions as $transition) {
            $adjacency[$transition['source_node_key']][] =
                $transition['target_node_key'];
        }

        $visited = [];
        $recursionStack = [];

        $hasCycle = $this->detectCycle(
            $startNodeKey,
            $adjacency,
            $visited,
            $recursionStack
        );

        if ($hasCycle) {
            $result->addWarning(
                'WORKFLOW_CYCLE_DETECTED',
                'El workflow contiene al menos un ciclo. Verifica que no provoque conversaciones infinitas.'
            );
        }
    }

    private function detectCycle(
        string $nodeKey,
        array $adjacency,
        array &$visited,
        array &$recursionStack
    ): bool {
        if (!isset($visited[$nodeKey])) {
            $visited[$nodeKey] = true;
            $recursionStack[$nodeKey] = true;

            foreach ($adjacency[$nodeKey] ?? [] as $target) {
                if (
                    !isset($visited[$target])
                    && $this->detectCycle(
                        $target,
                        $adjacency,
                        $visited,
                        $recursionStack
                    )
                ) {
                    return true;
                }

                if (!empty($recursionStack[$target])) {
                    return true;
                }
            }
        }

        $recursionStack[$nodeKey] = false;

        return false;
    }
}
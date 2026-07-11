<?php

namespace Modules\Workflow\Services;

use Modules\Workflow\DTO\WorkflowResponseDTO;
use Modules\Workflow\Models\WorkflowSimulationModel;
use Modules\Workflow\Repositories\WorkflowRepository;
use Modules\Workflow\Support\WorkflowException;
use Modules\Workflow\Support\WorkflowNodeTypes;
class WorkflowSimulatorService
{
    private WorkflowRepository $repository;
    private WorkflowSimulationModel $simulationModel;

    public function __construct()
    {
        $this->repository = new WorkflowRepository();
        $this->simulationModel = new WorkflowSimulationModel();
    }

    public function start(
        string $workflowSlug,
        ?int $createdBy = null
    ): WorkflowResponseDTO {
        $workflow = $this->repository->findPublishedWorkflow(
            $workflowSlug,
            'messenger'
        );

        if (!$workflow) {
            throw new WorkflowException(
                'No existe un workflow publicado con ese slug.'
            );
        }

        $versionId = (int) ($workflow['active_version_id'] ?? 0);
        $version = $this->repository->findVersion($versionId);

        if (!$version || empty($version['start_node_key'])) {
            throw new WorkflowException(
                'La versión activa no tiene un nodo inicial válido.'
            );
        }

        $simulationId = (int) $this->simulationModel->insert([
            'workflow_id'         => $workflow['id'],
            'workflow_version_id' => $versionId,
            'current_node_key'    => $version['start_node_key'],
            'status'              => 'running',
            'context_data'        => json_encode([]),
            'execution_log'       => json_encode([]),
            'created_by'          => $createdBy,
            'started_at'          => date('Y-m-d H:i:s'),
        ]);

        return $this->render($simulationId);
    }

    public function current(
        int $simulationId
    ): WorkflowResponseDTO {
        return $this->render($simulationId);
    }

    public function handle(
        int $simulationId,
        ?string $text = null,
        ?string $payload = null
    ): WorkflowResponseDTO {
        $simulation = $this->findSimulation($simulationId);

        if ($simulation['status'] !== 'running') {
            throw new WorkflowException(
                'La simulación ya no se encuentra activa.'
            );
        }

        $node = $this->findCurrentNode($simulation);

        if ($node['node_type'] === WorkflowNodeTypes::CAPTURE_TEXT) {
            $this->captureText($simulation, $node, $text);
        }

        $transitions = $this->repository->findTransitions(
            (int) $simulation['workflow_version_id'],
            $node['node_key']
        );

        $transition = $this->resolveTransition(
            $transitions,
            $text,
            $payload
        );

        if (!$transition) {
            return $this->render(
                $simulationId,
                'No pudimos identificar la opción. Intenta nuevamente.'
            );
        }

        $this->appendLog($simulationId, [
            'source_node' => $node['node_key'],
            'target_node' => $transition['target_node_key'],
            'text'        => $text,
            'payload'     => $payload,
            'timestamp'   => date('Y-m-d H:i:s'),
        ]);

        $this->simulationModel->update($simulationId, [
            'current_node_key' => $transition['target_node_key'],
        ]);

        return $this->processAutomaticNodes($simulationId);
    }

    private function processAutomaticNodes(
        int $simulationId
    ): WorkflowResponseDTO {
        $simulation = $this->findSimulation($simulationId);
        $node = $this->findCurrentNode($simulation);

        if ($node['node_type'] !== WorkflowNodeTypes::ACTION) {
            return $this->render($simulationId);
        }

        $configuration = json_decode(
            $node['configuration'] ?? '{}',
            true
        ) ?: [];

        $this->appendLog($simulationId, [
            'node'      => $node['node_key'],
            'action'    => $configuration['action'] ?? null,
            'simulated' => true,
            'timestamp' => date('Y-m-d H:i:s'),
        ]);

        $transitions = $this->repository->findTransitions(
            (int) $simulation['workflow_version_id'],
            $node['node_key']
        );

        $transition = $this->resolveTransition(
            $transitions,
            null,
            null
        );

        if (!$transition) {
            throw new WorkflowException(
                'El nodo de acción no tiene una transición de salida.'
            );
        }

        $this->simulationModel->update($simulationId, [
            'current_node_key' => $transition['target_node_key'],
        ]);

        return $this->processAutomaticNodes($simulationId);
    }

    private function captureText(
        array $simulation,
        array $node,
        ?string $text
    ): void {
        $value = trim((string) $text);
        $configuration = json_decode(
            $node['configuration'] ?? '{}',
            true
        ) ?: [];

        if (
            ($configuration['required'] ?? false)
            && $value === ''
        ) {
            throw new WorkflowException(
                'Este dato es obligatorio.'
            );
        }

        $minimumLength = (int) (
            $configuration['minimum_length'] ?? 0
        );

        if (
            $minimumLength > 0
            && mb_strlen($value) < $minimumLength
        ) {
            throw new WorkflowException(
                'La respuesta es demasiado corta.'
            );
        }

        if (empty($node['context_key'])) {
            return;
        }

        $context = json_decode(
            $simulation['context_data'] ?? '{}',
            true
        ) ?: [];

        $context[$node['context_key']] = $value;

        $this->simulationModel->update(
            (int) $simulation['id'],
            [
                'context_data' => json_encode(
                    $context,
                    JSON_UNESCAPED_UNICODE
                ),
            ]
        );
    }

    private function render(
        int $simulationId,
        ?string $overrideText = null
    ): WorkflowResponseDTO {
        $simulation = $this->findSimulation($simulationId);
        $node = $this->findCurrentNode($simulation);

        $transitions = $this->repository->findTransitions(
            (int) $simulation['workflow_version_id'],
            $node['node_key']
        );

        $quickReplies = [];

        if ($node['node_type'] === WorkflowNodeTypes::QUICK_REPLIES) {
            foreach ($transitions as $transition) {
                if (empty($transition['payload'])) {
                    continue;
                }

                $quickReplies[] = [
                    'title'   => $transition['label'] ?? 'Seleccionar',
                    'payload' => $transition['payload'],
                ];
            }
        }

        $completed =
            $node['node_type'] === WorkflowNodeTypes::END
            || (int) ($node['is_terminal'] ?? 0) === 1;

        if ($completed) {
            $this->simulationModel->update($simulationId, [
                'status'       => 'completed',
                'completed_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $context = json_decode(
            $simulation['context_data'] ?? '{}',
            true
        ) ?: [];

        return new WorkflowResponseDTO(
            text: $overrideText ?? $node['message_text'],
            quickReplies: $quickReplies,
            executionId: $simulationId,
            currentNodeKey: $node['node_key'],
            completed: $completed,
            metadata: [
                'simulation' => true,
                'context'    => $context,
                'node_type'  => $node['node_type'],
                'node_name'  => $node['name'],
            ]
        );
    }

    private function findSimulation(int $simulationId): array
    {
        $simulation = $this->simulationModel->find($simulationId);

        if (!$simulation) {
            throw new WorkflowException(
                'La simulación solicitada no existe.'
            );
        }

        return $simulation;
    }

    private function findCurrentNode(array $simulation): array
    {
        $node = $this->repository->findNode(
            (int) $simulation['workflow_version_id'],
            $simulation['current_node_key']
        );

        if (!$node) {
            throw new WorkflowException(
                'El nodo actual de la simulación no existe.'
            );
        }

        return $node;
    }

    private function appendLog(
        int $simulationId,
        array $entry
    ): void {
        $simulation = $this->findSimulation($simulationId);

        $log = json_decode(
            $simulation['execution_log'] ?? '[]',
            true
        ) ?: [];

        $log[] = $entry;

        $this->simulationModel->update($simulationId, [
            'execution_log' => json_encode(
                $log,
                JSON_UNESCAPED_UNICODE
            ),
        ]);
    }

    private function resolveTransition(
        array $transitions,
        ?string $text,
        ?string $payload
    ): ?array {
        foreach ($transitions as $transition) {
            $type = $transition['condition_type'] ?? 'always';

            if ($type === 'always') {
                return $transition;
            }

            if (
                $type === 'payload_equals'
                && trim((string) $payload)
                === trim((string) $transition['condition_value'])
            ) {
                return $transition;
            }

            if (
                $type === 'text_equals'
                && mb_strtolower(trim((string) $text))
                === mb_strtolower(
                    trim((string) $transition['condition_value'])
                )
            ) {
                return $transition;
            }
        }

        return null;
    }


}
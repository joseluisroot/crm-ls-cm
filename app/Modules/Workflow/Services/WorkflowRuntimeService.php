<?php

namespace Modules\Workflow\Services;

use Modules\ConversationEngine\Services\ConversationContextService;
use Modules\Workflow\DTO\WorkflowResponseDTO;
use Modules\Workflow\Repositories\WorkflowRepository;
use Modules\Workflow\Support\WorkflowException;
use Modules\Workflow\Support\WorkflowNodeTypes;
use Modules\Workflow\Support\WorkflowStatus;

class WorkflowRuntimeService
{
    private WorkflowRepository $repository;
    private ConversationContextService $contextService;
    private WorkflowActionExecutor $actionExecutor;

    public function __construct(
        ?WorkflowRepository $repository = null,
        ?ConversationContextService $contextService = null,
        ?WorkflowActionExecutor $actionExecutor = null
    ) {
        $this->repository = $repository
            ?? new WorkflowRepository();

        $this->contextService = $contextService
            ?? new ConversationContextService();

        $this->actionExecutor = $actionExecutor
            ?? new WorkflowActionExecutor();
    }

    public function start(
        int $conversationId,
        string $workflowSlug,
        string $channel = 'messenger'
    ): WorkflowResponseDTO {
        $workflow = $this->repository->findPublishedWorkflow(
            $workflowSlug,
            $channel
        );

        if (!$workflow) {
            throw new WorkflowException(
                'No existe un workflow publicado para este canal.'
            );
        }

        $versionId = (int) ($workflow['active_version_id'] ?? 0);

        if ($versionId <= 0) {
            throw new WorkflowException(
                'El workflow no tiene una versión activa.'
            );
        }

        $version = $this->repository->findVersion($versionId);

        if (!$version || $version['status'] !== WorkflowStatus::PUBLISHED) {
            throw new WorkflowException(
                'La versión activa del workflow no está publicada.'
            );
        }

        $runningExecution = $this->repository->findRunningExecution(
            $conversationId,
            (int) $workflow['id']
        );

        if ($runningExecution) {
            return $this->renderCurrentNode($runningExecution);
        }

        $executionId = $this->repository->createExecution([
            'workflow_id'        => $workflow['id'],
            'workflow_version_id'=> $version['id'],
            'conversation_id'    => $conversationId,
            'current_node_key'   => $version['start_node_key'],
            'status'             => WorkflowStatus::EXECUTION_RUNNING,
            'started_at'         => date('Y-m-d H:i:s'),
            'last_interaction_at'=> date('Y-m-d H:i:s'),
            'metadata'           => json_encode([
                'channel' => $channel,
            ]),
        ]);

        $execution = $this->repository->findExecution($executionId);

        if (!$execution) {
            throw new WorkflowException(
                'No fue posible iniciar la ejecución del workflow.'
            );
        }

        return $this->renderCurrentNode($execution);
    }

    public function handle(
        int $conversationId,
        ?string $text = null,
        ?string $payload = null
    ): WorkflowResponseDTO {
        $execution = $this->repository->findRunningExecution(
            $conversationId
        );

        if (!$execution) {
            throw new WorkflowException(
                'La conversación no tiene un workflow activo.'
            );
        }

        $currentNode = $this->getCurrentNode($execution);

        if ($currentNode['node_type'] === WorkflowNodeTypes::CAPTURE_TEXT) {
            $this->captureText(
                $execution,
                $currentNode,
                $text
            );

            return $this->advanceByTransition(
                $execution,
                $currentNode,
                $text,
                $payload
            );
        }

        return $this->advanceByTransition(
            $execution,
            $currentNode,
            $text,
            $payload
        );
    }

    private function captureText(
        array $execution,
        array $node,
        ?string $text
    ): void {
        $value = trim((string) $text);

        $configuration = json_decode(
            $node['configuration'] ?? '{}',
            true
        ) ?: [];

        $required = (bool) ($configuration['required'] ?? false);
        $minimumLength = (int) (
            $configuration['minimum_length'] ?? 0
        );

        if ($required && $value === '') {
            throw new WorkflowException(
                'El dato solicitado es obligatorio.'
            );
        }

        if (
            $minimumLength > 0
            && mb_strlen($value) < $minimumLength
        ) {
            throw new WorkflowException(
                'La respuesta es demasiado corta.'
            );
        }

        if (!empty($node['context_key'])) {
            $this->contextService->put(
                (int) $execution['conversation_id'],
                $node['context_key'],
                $value
            );
        }
    }

    private function advanceByTransition(
        array $execution,
        array $currentNode,
        ?string $text,
        ?string $payload
    ): WorkflowResponseDTO {
        $transitions = $this->repository->findTransitions(
            (int) $execution['workflow_version_id'],
            $currentNode['node_key']
        );

        $transition = $this->resolveTransition(
            $transitions,
            $text,
            $payload
        );

        if (!$transition) {
            return $this->renderCurrentNode(
                $execution,
                'No pudimos identificar la opción seleccionada. Por favor intenta nuevamente.'
            );
        }

        $this->repository->updateExecution(
            (int) $execution['id'],
            [
                'current_node_key'    => $transition['target_node_key'],
                'last_interaction_at' => date('Y-m-d H:i:s'),
            ]
        );

        $updatedExecution = $this->repository->findExecution(
            (int) $execution['id']
        );

        if (!$updatedExecution) {
            throw new WorkflowException(
                'No fue posible actualizar el workflow.'
            );
        }

        return $this->processAutomaticNodes($updatedExecution);
    }

    private function processAutomaticNodes(
        array $execution
    ): WorkflowResponseDTO {
        $node = $this->getCurrentNode($execution);

        if ($node['node_type'] === WorkflowNodeTypes::ACTION) {
            $actionResult = $this->actionExecutor->execute(
                $node,
                (int) $execution['conversation_id']
            );

            $transitions = $this->repository->findTransitions(
                (int) $execution['workflow_version_id'],
                $node['node_key']
            );

            $transition = $this->resolveTransition(
                $transitions,
                null,
                null
            );

            if (!$transition) {
                throw new WorkflowException(
                    'El nodo de acción no tiene transición de salida.'
                );
            }

            $metadata = json_decode(
                $execution['metadata'] ?? '{}',
                true
            ) ?: [];

            $metadata = array_merge($metadata, $actionResult);

            $this->repository->updateExecution(
                (int) $execution['id'],
                [
                    'current_node_key'    => $transition['target_node_key'],
                    'last_interaction_at' => date('Y-m-d H:i:s'),
                    'metadata'            => json_encode($metadata),
                ]
            );

            $execution = $this->repository->findExecution(
                (int) $execution['id']
            );

            return $this->processAutomaticNodes($execution);
        }

        return $this->renderCurrentNode($execution);
    }

    private function renderCurrentNode(
        array $execution,
        ?string $overrideText = null
    ): WorkflowResponseDTO {
        $node = $this->getCurrentNode($execution);

        $transitions = $this->repository->findTransitions(
            (int) $execution['workflow_version_id'],
            $node['node_key']
        );

        $quickReplies = [];

        if ($node['node_type'] === WorkflowNodeTypes::QUICK_REPLIES) {
            foreach ($transitions as $transition) {
                if (empty($transition['payload'])) {
                    continue;
                }

                $quickReplies[] = [
                    'title'   => $transition['label']
                        ?? 'Seleccionar',
                    'payload' => $transition['payload'],
                ];
            }
        }

        $completed =
            $node['node_type'] === WorkflowNodeTypes::END
            || (int) ($node['is_terminal'] ?? 0) === 1;

        if ($completed) {
            $this->repository->updateExecution(
                (int) $execution['id'],
                [
                    'status'       => WorkflowStatus::EXECUTION_COMPLETED,
                    'completed_at' => date('Y-m-d H:i:s'),
                ]
            );
        }

        $metadata = json_decode(
            $execution['metadata'] ?? '{}',
            true
        ) ?: [];

        return new WorkflowResponseDTO(
            text: $overrideText ?? $node['message_text'],
            quickReplies: $quickReplies,
            executionId: (int) $execution['id'],
            currentNodeKey: $node['node_key'],
            completed: $completed,
            caseId: isset($metadata['case_id'])
                ? (int) $metadata['case_id']
                : null,
            metadata: $metadata
        );
    }

    private function getCurrentNode(array $execution): array
    {
        $node = $this->repository->findNode(
            (int) $execution['workflow_version_id'],
            $execution['current_node_key']
        );

        if (!$node) {
            throw new WorkflowException(
                'El nodo actual del workflow no existe.'
            );
        }

        return $node;
    }

    private function resolveTransition(
        array $transitions,
        ?string $text,
        ?string $payload
    ): ?array {
        foreach ($transitions as $transition) {
            $conditionType = $transition['condition_type']
                ?? 'always';

            if ($conditionType === 'always') {
                return $transition;
            }

            if (
                $conditionType === 'payload_equals'
                && trim((string) $payload)
                === trim((string) $transition['condition_value'])
            ) {
                return $transition;
            }

            if (
                $conditionType === 'text_equals'
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
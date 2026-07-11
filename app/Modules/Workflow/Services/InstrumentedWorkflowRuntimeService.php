<?php

namespace Modules\Workflow\Services;

use Modules\Workflow\DTO\WorkflowResponseDTO;
use Modules\Workflow\Repositories\WorkflowRepository;
use Throwable;

class InstrumentedWorkflowRuntimeService
{
    public function __construct(
        private readonly WorkflowRuntimeService $runtime,
        private readonly WorkflowRepository $repository,
        private readonly WorkflowRuntimeEventPublisher $publisher,
    ) {
    }

    public function start(
        int $conversationId,
        string $workflowSlug,
        string $channel = 'messenger'
    ): WorkflowResponseDTO {
        $response = $this->runtime->start(
            $conversationId,
            $workflowSlug,
            $channel
        );

        $execution = $this->repository->findExecution($response->executionId);

        if ($execution) {
            $this->publisher->workflowStarted($execution);
            $node = $this->repository->findNode(
                (int) $execution['workflow_version_id'],
                $response->currentNodeKey
            );

            if ($node) {
                $startedAt = microtime(true);
                $this->publisher->nodeStarted($execution, $node);
                $this->publisher->nodeCompleted(
                    $execution,
                    $node,
                    $startedAt,
                    [
                        'snapshot' => [
                            'response' => $this->responseSnapshot($response),
                            'execution' => $execution,
                        ],
                    ]
                );
            }

            if ($response->completed) {
                $this->publisher->workflowCompleted($execution);
            }
        }

        return $response;
    }

    public function handle(
        int $conversationId,
        ?string $text = null,
        ?string $payload = null
    ): WorkflowResponseDTO {
        $execution = $this->repository->findRunningExecution($conversationId);
        $node = $execution ? $this->repository->findNode(
            (int) $execution['workflow_version_id'],
            $execution['current_node_key']
        ) : null;
        $startedAt = microtime(true);

        if ($execution && $node) {
            $this->publisher->nodeStarted($execution, $node);
        }

        try {
            $response = $this->runtime->handle(
                $conversationId,
                $text,
                $payload
            );

            if ($execution && $node) {
                $context = (new ConversationContextService())->all(
                    (int) $execution['conversation_id']
                );

                $this->publisher->nodeCompleted(
                    $execution,
                    $node,
                    $startedAt,
                    [
                        'variables' => $this->variables($context),
                        'snapshot' => [
                            'input' => [
                                'text' => $text,
                                'payload' => $payload,
                            ],
                            'context' => $context,
                            'response' => $this->responseSnapshot($response),
                        ],
                    ]
                );
            }

            if ($response->completed) {
                $completedExecution = $this->repository->findExecution(
                    $response->executionId
                ) ?? $execution;

                if ($completedExecution) {
                    $this->publisher->workflowCompleted($completedExecution);
                }
            }

            return $response;
        } catch (Throwable $error) {
            if ($execution && $node) {
                $this->publisher->nodeFailed(
                    $execution,
                    $node,
                    $startedAt,
                    $error
                );
                $this->publisher->workflowFailed($execution, $error);
            }

            throw $error;
        }
    }

    private function responseSnapshot(WorkflowResponseDTO $response): array
    {
        return [
            'execution_id' => $response->executionId,
            'current_node_key' => $response->currentNodeKey,
            'completed' => $response->completed,
            'case_id' => $response->caseId,
            'metadata' => $response->metadata,
        ];
    }

    private function variables(array $context): array
    {
        $variables = [];

        foreach ($context as $name => $value) {
            $variables[] = [
                'name' => (string) $name,
                'type' => get_debug_type($value),
                'old_value' => null,
                'new_value' => $value,
            ];
        }

        return $variables;
    }
}

<?php

namespace Modules\Flow\Services;

use Config\Workflow as WorkflowConfig;
use Modules\Conversations\Models\MessageModel;
use Modules\Flow\DTO\FlowAdapterResponseDTO;
use Modules\Workflow\Services\WorkflowRuntimeService;
use Modules\Workflow\Support\WorkflowException;
use Throwable;

class FlowAdapterService
{
    private WorkflowConfig $config;

    public function __construct(
        ?WorkflowConfig $config = null
    ) {
        $this->config = $config ?? config('Workflow');
    }

    public function handle(array $context): FlowAdapterResponseDTO
    {
        if (!$this->config->dynamicEngineEnabled) {
            return $this->handleLegacyFlow($context);
        }

        try {
            return $this->handleDynamicWorkflow($context);
        } catch (Throwable $e) {
            log_message(
                'error',
                'Dynamic Workflow Engine error: ' . $e->getMessage()
            );

            if (!$this->config->fallbackToLegacyFlow) {
                throw $e;
            }

            log_message(
                'warning',
                'Se utilizará el Flow Engine clásico como fallback.'
            );

            return $this->handleLegacyFlow($context);
        }
    }

    private function handleLegacyFlow(
        array $context
    ): FlowAdapterResponseDTO {
        $outboundMessageId = (new FlowEngineService())
            ->handle($context);

        return new FlowAdapterResponseDTO(
            outboundMessageId: $outboundMessageId,
            engine: 'legacy'
        );
    }

    private function handleDynamicWorkflow(
        array $context
    ): FlowAdapterResponseDTO {
        $conversation = $context['conversation'] ?? null;

        if (!$conversation || empty($conversation['id'])) {
            throw new WorkflowException(
                'No existe una conversación válida para ejecutar el workflow.'
            );
        }

        $conversationId = (int) $conversation['id'];
        $text = $context['text'] ?? null;
        $payload = $context['payload'] ?? null;
        $channel = $conversation['channel'] ?? 'messenger';

        $runtime = new WorkflowRuntimeService();

        $runningExecution = $this->hasRunningExecution(
            $conversationId
        );

        if ($runningExecution) {
            $response = $runtime->handle(
                conversationId: $conversationId,
                text: $text,
                payload: $payload
            );
        } else {
            $response = $runtime->start(
                conversationId: $conversationId,
                workflowSlug: $this->config->defaultWorkflowSlug,
                channel: $channel
            );
        }

        $outboundMessageId = $this->createOutboundMessage(
            conversationId: $conversationId,
            category: $context['category'] ?? 'pending',
            text: $response->text,
            quickReplies: $response->quickReplies,
            metadata: [
                'generated_by' => 'dynamic_workflow_engine',
                'workflow_execution_id' => $response->executionId,
                'workflow_node_key' => $response->currentNodeKey,
                'workflow_completed' => $response->completed,
                'case_id' => $response->caseId,
                'engine' => 'dynamic',
            ]
        );

        return new FlowAdapterResponseDTO(
            text: $response->text,
            quickReplies: $response->quickReplies,
            caseId: $response->caseId,
            outboundMessageId: $outboundMessageId,
            engine: 'dynamic',
            completed: $response->completed,
            metadata: $response->metadata
        );
    }

    private function hasRunningExecution(
        int $conversationId
    ): bool {
        return db_connect()
                ->table('workflow_executions')
                ->where('conversation_id', $conversationId)
                ->where('status', 'running')
                ->countAllResults() > 0;
    }

    private function createOutboundMessage(
        int $conversationId,
        string $category,
        ?string $text,
        array $quickReplies,
        array $metadata
    ): ?int {
        if ($text === null || trim($text) === '') {
            return null;
        }

        $messageId = (new MessageModel())->insert([
            'conversation_id' => $conversationId,
            'direction' => 'outbound',
            'message_type' => 'text',
            'body' => $text,
            'raw_payload' => json_encode([
                'sent' => false,
                'auto_reply' => true,
                'quick_replies' => $quickReplies,
                ...$metadata,
            ], JSON_UNESCAPED_UNICODE),
            'sentiment' => 'system',
            'category' => $category,
            'priority' => 'normal',
            'sent_status' => 'suggested',
            'sent_at' => null,
            'delivery_error' => null,
        ]);

        return $messageId ? (int) $messageId : null;
    }
}
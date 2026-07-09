<?php

namespace Modules\Flow\Services;

use Modules\Conversations\Models\ConversationModel;
use Modules\Conversations\Models\MessageModel;
use Modules\Flow\DTO\FlowResponseDTO;
use Modules\Flow\Support\FlowStates;
use Modules\Flow\Workflows\WelcomeWorkflow;
use Modules\Flow\Workflows\NeedWorkflow;
use Modules\Flow\Workflows\SimpleCategoryWorkflow;

class FlowEngineService
{
    public function handle(array $context): ?int
    {
        $conversation = $context['conversation'];
        $category = $context['category'] ?? 'pending';

        $response = $this->resolveWorkflow($conversation, $category, $context['text'] ?? null);

        if (!$response || !$response->text) {
            return null;
        }

        $outboundMessageId = (new MessageModel())->insert([
            'conversation_id' => $conversation['id'],
            'direction' => 'outbound',
            'message_type' => 'text',
            'body' => $response->text,
            'raw_payload' => json_encode([
                'generated_by' => 'flow_engine',
                'auto_reply' => true,
                'quick_replies' => $response->quickReplies,
                'sent' => false,
            ]),
            'sentiment' => 'system',
            'category' => $category,
            'priority' => 'normal',
            'sent_status' => 'suggested',
            'sent_at' => null,
            'delivery_error' => null,
        ]);

        (new ConversationModel())->update($conversation['id'], [
            'state' => $response->nextState ?? ($conversation['state'] ?? FlowStates::ATTENDING),
            'welcomed_at' => ($conversation['welcomed_at'] ?? null) ?: date('Y-m-d H:i:s'),
            'last_flow_payload' => $category,
        ]);

        return $outboundMessageId ? (int)$outboundMessageId : null;
    }

    private function resolveWorkflow(array $conversation, string $category, ?string $text = null): ?FlowResponseDTO
    {
        $welcome = new WelcomeWorkflow();

        if ($welcome->canRun($conversation, $category)) {
            return $welcome->run();
        }

        $need = new NeedWorkflow();

        if ($need->canRun($conversation, $category)) {
            return $need->run($conversation, $text, $category);
        }

        $simple = new SimpleCategoryWorkflow();

        if ($simple->canRun($conversation, $category)) {
            return $simple->run($category);
        }

        return null;
    }
}
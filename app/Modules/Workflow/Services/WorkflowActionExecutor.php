<?php

namespace Modules\Workflow\Services;

use Modules\CaseEngine\Services\CaseEngineService;
use Modules\ConversationEngine\Services\ConversationContextService;
use Modules\Conversations\Models\ConversationModel;
use Modules\Workflow\Support\WorkflowException;

class WorkflowActionExecutor
{
    public function execute(
        array $node,
        int $conversationId
    ): array {
        $configuration = json_decode(
            $node['configuration'] ?? '{}',
            true
        ) ?: [];

        $action = $configuration['action'] ?? null;

        return match ($action) {
            'create_case' => $this->createCase(
                $conversationId,
                $configuration
            ),
            default => throw new WorkflowException(
                'Acción de workflow no soportada: ' . ($action ?? 'vacía')
            ),
        };
    }

    private function createCase(
        int $conversationId,
        array $configuration
    ): array {
        $contextService = new ConversationContextService();

        // Evita crear dos veces el mismo caso durante una ejecución.
        $existingCaseId = $contextService->get(
            $conversationId,
            'workflow_case_id'
        );

        if (!empty($existingCaseId)) {
            return [
                'case_id' => (int) $existingCaseId,
                'already_existed' => true,
            ];
        }

        $conversation = (new ConversationModel())
            ->find($conversationId);

        if (!$conversation) {
            throw new WorkflowException(
                'No existe la conversación asociada al workflow.'
            );
        }

        $context = $contextService->all($conversationId);

        $municipality = trim(
            (string) ($context['municipality'] ?? '')
        );

        $community = trim(
            (string) ($context['community'] ?? '')
        );

        $description = trim(
            (string) ($context['description'] ?? '')
        );

        if (
            $municipality === ''
            || $community === ''
            || $description === ''
        ) {
            throw new WorkflowException(
                'No existe suficiente contexto para crear el caso.'
            );
        }

        $category = $configuration['category']
            ?? 'necesidad-comunitaria';

        $structuredDescription =
            "Municipio: {$municipality}\n"
            . "Comunidad: {$community}\n\n"
            . $description;

        $caseId = (new CaseEngineService())->createCaseFromMessage(
            citizenId: (int) $conversation['citizen_id'],
            categorySlug: $category,
            messageText: $structuredDescription
        );

        if (!$caseId) {
            throw new WorkflowException(
                'El Case Engine no pudo crear el caso.'
            );
        }

        $contextService->put(
            $conversationId,
            'workflow_case_id',
            (int) $caseId
        );

        return [
            'case_id' => (int) $caseId,
            'already_existed' => false,
        ];
    }
}
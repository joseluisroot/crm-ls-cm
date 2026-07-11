<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Modules\Workflow\Services\WorkflowRuntimeService;

class TestWorkflow extends BaseCommand
{
    protected $group = 'CIAC';
    protected $name = 'workflow:test';
    protected $description = 'Prueba el inicio de un workflow dinámico.';

    protected $usage = 'workflow:test [conversation-id]';

    protected $arguments = [
        'conversation-id' => 'ID de una conversación existente.',
    ];

    public function run(array $params)
    {
        $conversationId = isset($params[0])
            ? (int) $params[0]
            : 0;

        if ($conversationId <= 0) {
            CLI::error(
                'Uso: php spark workflow:test 5'
            );

            return;
        }

        try {
            $response = (new WorkflowRuntimeService())->start(
                conversationId: $conversationId,
                workflowSlug: 'citizen-attention',
                channel: 'messenger'
            );

            CLI::write(
                json_encode(
                    $response->toArray(),
                    JSON_PRETTY_PRINT
                    | JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                ),
                'green'
            );
        } catch (\Throwable $e) {
            CLI::error($e->getMessage());
        }
    }
}
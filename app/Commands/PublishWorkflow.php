<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Modules\Workflow\Models\WorkflowModel;
use Modules\Workflow\Models\WorkflowVersionModel;
use Modules\Workflow\Services\WorkflowPublishingService;
use Throwable;

class PublishWorkflow extends BaseCommand
{

    protected $group = 'CIAC';
    protected $name = 'workflow:publish';
    protected $description = 'Publica una versión de un flujo dinámico.';

    protected $usage = 'workflow:publish [workflow-slug] [version-number]';

    protected $arguments = [
        'workflow-slug' => 'Slug del flujo.',
        'version-number' => 'Número de versión.',
    ];

    public function run(array $params)
    {
        $slug = $params[0] ?? null;
        $versionNumber = isset($params[1])
            ? (int) $params[1]
            : 0;

        if (!$slug || $versionNumber <= 0) {
            CLI::error(
                'Uso: php spark workflow:publish citizen-attention 1'
            );

            return;
        }

        $workflow = (new WorkflowModel())
            ->where('slug', $slug)
            ->first();

        if (!$workflow) {
            CLI::error('No existe un workflow con el slug indicado.');
            return;
        }

        $version = (new WorkflowVersionModel())
            ->where('workflow_id', $workflow['id'])
            ->where('version_number', $versionNumber)
            ->first();

        if (!$version) {
            CLI::error('No existe la versión indicada.');
            return;
        }

        try {
            (new WorkflowPublishingService())->publish(
                (int) $workflow['id'],
                (int) $version['id']
            );

            CLI::write(
                sprintf(
                    'Workflow "%s" versión %d publicado correctamente.',
                    $workflow['name'],
                    $versionNumber
                ),
                'green'
            );
        } catch (\Throwable $e) {
            CLI::error($e->getMessage());
        }
    }
}
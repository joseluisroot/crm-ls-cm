<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
class WorkflowEngineStatus extends BaseCommand
{

    protected $group = 'CIAC';
    protected $name = 'workflow:status';
    protected $description = 'Muestra el estado del Dynamic Workflow Engine.';
    private $write;

    public function run(array $params)
    {
        $config = config('Workflow');

        CLI::write(
            'Dynamic Workflow Engine: '
            . ($config->dynamicEngineEnabled ? 'ACTIVO' : 'INACTIVO'),
            $config->dynamicEngineEnabled ? 'green' : 'yellow'
        );

        $this->write = CLI::write(
            'Workflow principal: '
            . $config->defaultWorkflowSlug
        );
        $this->write;

        CLI::write(
            'Fallback clásico: '
            . ($config->fallbackToLegacyFlow ? 'ACTIVO' : 'INACTIVO')
        );
    }
}
<?php

namespace Modules\Workflow\Models;

use CodeIgniter\Model;

class WorkflowSimulationModel extends Model
{
    protected $table = 'workflow_simulations';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'workflow_id',
        'workflow_version_id',
        'current_node_key',
        'status',
        'context_data',
        'execution_log',
        'last_error',
        'created_by',
        'started_at',
        'completed_at',
    ];

    protected $useTimestamps = true;
}
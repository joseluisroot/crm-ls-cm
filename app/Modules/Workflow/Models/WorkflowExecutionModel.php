<?php

namespace Modules\Workflow\Models;

use CodeIgniter\Model;

class WorkflowExecutionModel extends Model
{
    protected $table = 'workflow_executions';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'workflow_id',
        'workflow_version_id',
        'conversation_id',
        'current_node_key',
        'status',
        'started_at',
        'completed_at',
        'last_interaction_at',
        'metadata',
    ];

    protected $useTimestamps = true;
}
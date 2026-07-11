<?php

namespace Modules\Workflow\Models;

use CodeIgniter\Model;

class WorkflowExecutionNodeModel extends Model
{
    protected $table = 'workflow_execution_nodes';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'workflow_execution_id',
        'event_uuid',
        'node_key',
        'node_name',
        'node_type',
        'status',
        'attempt',
        'started_at',
        'finished_at',
        'duration_ms',
        'error_class',
        'error_message',
    ];
}

<?php

namespace Modules\Workflow\Models;

use CodeIgniter\Model;

class WorkflowExecutionLogModel extends Model
{
    protected $table = 'workflow_execution_logs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'workflow_execution_id',
        'workflow_execution_node_id',
        'event_uuid',
        'level',
        'message',
        'context_json',
        'created_at',
    ];
}

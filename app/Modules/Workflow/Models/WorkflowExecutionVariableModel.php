<?php

namespace Modules\Workflow\Models;

use CodeIgniter\Model;

class WorkflowExecutionVariableModel extends Model
{
    protected $table = 'workflow_execution_variables';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'workflow_execution_id',
        'workflow_execution_node_id',
        'event_uuid',
        'variable_name',
        'variable_type',
        'old_value_json',
        'new_value_json',
        'created_at',
    ];
}

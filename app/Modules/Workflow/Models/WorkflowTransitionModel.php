<?php

namespace Modules\Workflow\Models;

use CodeIgniter\Model;

class WorkflowTransitionModel extends Model
{
    protected $table = 'workflow_transitions';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'workflow_version_id',
        'source_node_key',
        'target_node_key',
        'label',
        'payload',
        'condition_type',
        'condition_value',
        'sort_order',
    ];

    protected $useTimestamps = true;
}
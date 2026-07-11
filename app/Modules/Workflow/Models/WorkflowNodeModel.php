<?php

namespace Modules\Workflow\Models;

use CodeIgniter\Model;

class WorkflowNodeModel extends Model
{
    protected $table = 'workflow_nodes';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'workflow_version_id',
        'node_key',
        'name',
        'node_type',
        'message_text',
        'context_key',
        'configuration',
        'position_x',
        'position_y',
        'is_terminal',
    ];

    protected $useTimestamps = true;
}
<?php

namespace Modules\Workflow\Models;

use CodeIgniter\Model;

class WorkflowVersionModel extends Model
{
    protected $table = 'workflow_versions';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'workflow_id',
        'version_number',
        'status',
        'start_node_key',
        'published_at',
        'created_by',
    ];

    protected $useTimestamps = true;
}
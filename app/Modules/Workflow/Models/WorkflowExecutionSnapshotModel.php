<?php

namespace Modules\Workflow\Models;

use CodeIgniter\Model;

class WorkflowExecutionSnapshotModel extends Model
{
    protected $table = 'workflow_execution_snapshots';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'workflow_execution_id',
        'workflow_execution_node_id',
        'event_uuid',
        'snapshot_type',
        'snapshot_json',
        'created_at',
    ];
}

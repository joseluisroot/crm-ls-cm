<?php

namespace Modules\Workflow\Models;

use CodeIgniter\Model;

class WorkflowExecutionPayloadModel extends Model
{
    protected $table = 'workflow_execution_payloads';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'workflow_execution_id',
        'workflow_execution_node_id',
        'event_uuid',
        'direction',
        'channel',
        'payload_json',
        'headers_json',
        'status_code',
        'created_at',
    ];
}

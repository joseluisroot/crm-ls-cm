<?php

namespace Modules\Operations\Infrastructure\Models;

use CodeIgniter\Model;

class WorkItemModel extends Model
{
    protected $table = 'work_items';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'uuid',
        'citizen_id',
        'origin_type_id',
        'origin_id',
        'channel_id',
        'status_id',
        'priority_id',
        'title',
        'summary',
        'assigned_user_id',
        'case_id',
        'workflow_execution_id',
        'opened_at',
        'sla_due_at',
        'first_response_at',
        'resolved_at',
        'closed_at',
        'metadata_json',
    ];
}

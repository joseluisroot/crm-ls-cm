<?php

namespace Modules\Workflow\Models;

use CodeIgniter\Model;

class WorkflowModel extends Model
{
    protected $table = 'workflows';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'name',
        'slug',
        'description',
        'channel',
        'status',
        'active_version_id',
        'created_by',
    ];

    protected $useTimestamps = true;
}
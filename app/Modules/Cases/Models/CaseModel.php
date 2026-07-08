<?php

namespace Modules\Cases\Models;

use CodeIgniter\Model;

class CaseModel extends Model
{
    protected $table = 'cases';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'citizen_id',
        'category_id',
        'status_id',
        'title',
        'description',
        'priority',
        'sentiment',
        'assigned_to',
        'closed_at',
    ];

    protected $useTimestamps = true;
}

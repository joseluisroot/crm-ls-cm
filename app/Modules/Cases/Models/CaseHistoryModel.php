<?php

namespace Modules\Cases\Models;

use CodeIgniter\Model;

class CaseHistoryModel extends Model
{
    protected $table = 'case_history';

    protected $primaryKey = 'id';

    protected $allowedFields = [
        'case_id',
        'event',
        'description',
        'performed_by',
    ];

    protected $useTimestamps = true;
}

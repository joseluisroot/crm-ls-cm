<?php

namespace App\Models;

use CodeIgniter\Model;

class CaseStatusModel extends Model
{
    protected $table = 'case_statuses';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'name',
        'slug',
    ];

    protected $useTimestamps = true;
}

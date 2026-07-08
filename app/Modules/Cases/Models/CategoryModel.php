<?php

namespace Modules\Cases\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'name',
        'slug',
    ];

    protected $useTimestamps = true;
}

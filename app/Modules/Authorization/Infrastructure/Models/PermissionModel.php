<?php

namespace Modules\Authorization\Infrastructure\Models;

use CodeIgniter\Model;

final class PermissionModel extends Model
{
    protected $table = 'permissions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['uuid', 'code', 'module', 'action', 'name', 'description', 'is_active'];
}

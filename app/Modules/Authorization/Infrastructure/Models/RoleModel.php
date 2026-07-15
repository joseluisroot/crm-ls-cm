<?php

namespace Modules\Authorization\Infrastructure\Models;

use CodeIgniter\Model;

final class RoleModel extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['uuid', 'code', 'name', 'description', 'is_system', 'is_active'];
}

<?php

namespace Modules\Authorization\Infrastructure\Models;

use CodeIgniter\Model;

final class RolePermissionModel extends Model
{
    protected $table = 'role_permissions';
    protected $primaryKey = 'role_id';
    protected $returnType = 'array';
    protected $useAutoIncrement = false;
    protected $allowedFields = ['role_id', 'permission_id', 'created_at'];
}

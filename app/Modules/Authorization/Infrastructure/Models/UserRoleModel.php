<?php

namespace Modules\Authorization\Infrastructure\Models;

use CodeIgniter\Model;

final class UserRoleModel extends Model
{
    protected $table = 'user_roles';
    protected $primaryKey = 'user_id';
    protected $returnType = 'array';
    protected $useAutoIncrement = false;
    protected $allowedFields = ['user_id', 'role_id', 'assigned_by', 'assigned_at'];
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class UserRoleModel extends Model
{
    protected $table = 'user_roles';
    protected $primaryKey = 'id'; // Assuming database has an auto-increment ID for the junction row, or we ignore PK operations
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = ['user_id', 'role_id'];
    protected $useTimestamps = false; // Usually junction tables do not have timestamps unless specified
}

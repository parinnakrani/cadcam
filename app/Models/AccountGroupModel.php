<?php

namespace App\Models;

class AccountGroupModel extends BaseModel
{
    protected $table            = 'account_groups';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'company_id', 'group_name', 'parent_id', 'type', 'description', 'is_active', 'is_deleted'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'is_deleted';

    // Validation
    protected $validationRules      = [
        'company_id' => 'required|integer',
        'group_name' => 'required|min_length[3]|max_length[100]',
        'type'       => 'required|in_list[Asset,Liability,Income,Expense]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}

<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * RoleModel
 *
 * Handles Role CRUD and Permission logic.
 * strictly adheres to .antigravity rules.
 */
class RoleModel extends Model
{
    protected $table            = 'roles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false; // Manual handling
    protected $protectFields    = true;
    protected $allowedFields    = [
        'company_id',
        'role_name',
        'role_description',
        'permissions',
        'is_system_role',
        'is_active',
        'is_deleted'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at'; // Not in schema, using 'is_deleted' flag manually

    // Validation
    protected $validationRules = [
        'role_name'        => 'required|min_length[3]|max_length[100]',
        'company_id'       => 'required|integer',
        'is_deleted'       => 'permit_empty|in_list[0,1]'
    ];
    
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $beforeInsert = ['encodePermissions'];
    protected $beforeUpdate = ['encodePermissions'];
    protected $afterFind    = ['decodePermissions'];

    /**
     * Encode permissions array to JSON string before saving
     */
    protected function encodePermissions(array $data): array
    {
        if (isset($data['data']['permissions']) && is_array($data['data']['permissions'])) {
            $data['data']['permissions'] = json_encode($data['data']['permissions']);
        }

        return $data;
    }

    /**
     * Decode JSON string to array after fetching
     */
    protected function decodePermissions(array $data): array
    {
        if (!isset($data['data'])) {
            return $data;
        }

        // Handle singleton find (associative array)
        if (isset($data['data']['permissions'])) {
            $data['data']['permissions'] = json_decode($data['data']['permissions'], true) ?? [];
            return $data;
        }

        // Handle findAll (array of arrays)
        foreach ($data['data'] as $key => $row) {
            if (isset($row['permissions'])) {
                $data['data'][$key]['permissions'] = json_decode($row['permissions'], true) ?? [];
            }
        }

        return $data;
    }

    /**
     * Apply Company Filter
     * Shows roles for current company AND System roles (company_id = 0).
     */
    protected function applyCompanyFilter(): self
    {
        $session = session();
        $companyId = $session->get('company_id');
        
        // If logged in properly
        if ($companyId !== null) {
            // Apply filter: (company_id = X OR company_id = 0)
            // Note: Since CI4 chaining with OR can be tricky, using group logic
            $this->groupStart()
                 ->where('company_id', $companyId)
                 ->orWhere('company_id', 0)
                 ->groupEnd();
        }
        
        return $this;
    }

    /**
     * Override findAll to apply filters
     */
    public function findAll(?int $limit = 0, int $offset = 0): array
    {
        $this->applyCompanyFilter();
        $this->where('is_deleted', 0);
        
        return parent::findAll($limit, $offset);
    }

    /**
     * Get numeric list of permissions for a role
     */
    public function getPermissions(int $roleId): array
    {
        $role = $this->find($roleId);

        if (!$role || !isset($role['permissions'])) {
            return [];
        }

        // decodePermissions callback already handled decoding if using find()
        // But if find() bypassed callbacks (rare), we safeguard
        if (is_string($role['permissions'])) {
            return json_decode($role['permissions'], true) ?? [];
        }

        return $role['permissions'];
    }

    /**
     * Check if a role has a specific permission
     * Supports wildcard '*'
     * Supports 'module.*' wildcards (e.g. 'invoices.*')
     */
    public function hasPermission(int $roleId, string $permission): bool
    {
        $permissions = $this->getPermissions($roleId);

        // Check for Global Wildcard
        if (in_array('*', $permissions)) {
            return true;
        }

        // Check strict match
        if (in_array($permission, $permissions)) {
            return true;
        }

        // Check Module Wildcard (e.g. input 'invoices.create', has 'invoices.*')
        $parts = explode('.', $permission);
        if (count($parts) > 1) {
            $moduleWildcard = $parts[0] . '.*';
            if (in_array($moduleWildcard, $permissions)) {
                return true;
            }
        }

        return false;
    }
}

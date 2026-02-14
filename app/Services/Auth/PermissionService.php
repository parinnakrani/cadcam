<?php

namespace App\Services\Auth;

use App\Models\RoleModel;
use Config\Services;

class PermissionService
{
    protected $roleModel;
    protected $db;
    protected $session;

    public function __construct(?RoleModel $roleModel = null)
    {
        $this->roleModel = $roleModel ?? new RoleModel();
        $this->db        = \Config\Database::connect();
        $this->session   = Services::session();
    }

    /**
     * Get merged permissions for a user (from all roles)
     *
     * @param int $userId
     * @return array List of permission strings
     */
    public function getUserPermissions(int $userId): array
    {
        // 1. Check Session Cache for Current User
        if ($this->session->has('user_id') && $this->session->get('user_id') == $userId) {
            if ($this->session->has('permissions')) {
                return $this->session->get('permissions');
            }
        }

        // 2. Fetch all roles for user
        $roles = $this->db->table('user_roles')
                          ->select('role_id')
                          ->where('user_id', $userId)
                          ->get()
                          ->getResult();

        if (empty($roles)) {
            return [];
        }

        // 3. Merge permissions from all roles
        $allPermissions = [];
        foreach ($roles as $row) {
            $rolePerms = $this->roleModel->getPermissions((int) $row->role_id);
            if (!empty($rolePerms)) {
                $allPermissions = array_merge($allPermissions, $rolePerms);
            }
        }

        // 4. Unique values
        $allPermissions = array_unique($allPermissions);

        // 5. Cache if current user
        if ($this->session->has('user_id') && $this->session->get('user_id') == $userId) {
            $this->session->set('permissions', $allPermissions);
            
            // Allow refreshing 'is_super_admin' based on new permissions scan
            if (in_array('*', $allPermissions)) {
                $this->session->set('is_super_admin', true);
            }
        }

        return $allPermissions;
    }

    /**
     * Check if user has specific permission
     *
     * @param int $userId
     * @param string $permission
     * @return bool
     */
    public function hasPermission(int $userId, string $permission): bool
    {
        $permissions = $this->getUserPermissions($userId);

        return $this->checkPermissionLogic($permissions, $permission);
    }

    /**
     * Check if CURRENT user has specific permission
     *
     * @param string $permission
     * @return bool
     */
    public function can(string $permission): bool
    {
        if (!$this->session->has('user_id')) {
            return false;
        }

        $userId = $this->session->get('user_id');
        
        // Super Admin Bypass (Session Flag)
        if ($this->session->get('is_super_admin')) {
            return true;
        }

        return $this->hasPermission($userId, $permission);
    }

    /**
     * Core permission check logic with Wildcards
     *
     * @param array $userPermissions
     * @param string $requiredPermission
     * @return bool
     */
    protected function checkPermissionLogic(array $userPermissions, string $requiredPermission): bool
    {
        // 1. Check Global Wildcard
        if (in_array('*', $userPermissions)) {
            return true;
        }

        // 2. Check Strict Match
        if (in_array($requiredPermission, $userPermissions)) {
            return true;
        }

        // 3. Check Module Wildcard (e.g. 'invoices.*' matches 'invoices.create')
        $parts = explode('.', $requiredPermission);
        if (count($parts) > 1) {
            $moduleWildcard = $parts[0] . '.*';
            if (in_array($moduleWildcard, $userPermissions)) {
                return true;
            }
        }

        return false;
    }
}

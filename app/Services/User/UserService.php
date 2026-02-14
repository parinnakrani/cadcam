<?php

namespace App\Services\User;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\UserRoleModel;
use App\Services\Audit\AuditService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Validation\ValidationInterface;
use Config\Services;
use Exception;

/**
 * Service for managing Users and Roles.
 */
class UserService
{
    protected UserModel $userModel;
    protected RoleModel $roleModel;
    protected UserRoleModel $userRoleModel;
    protected AuditService $auditService;
    protected BaseConnection $db;
    protected ValidationInterface $validation;

    public function __construct(
        UserModel $userModel = null,
        RoleModel $roleModel = null,
        UserRoleModel $userRoleModel = null,
        AuditService $auditService = null,
        BaseConnection $db = null
    ) {
        $this->userModel     = $userModel ?? new UserModel();
        $this->roleModel     = $roleModel ?? new RoleModel();
        $this->userRoleModel = $userRoleModel ?? new UserRoleModel();
        $this->auditService  = $auditService ?? new AuditService();
        $this->db            = $db ?? \Config\Database::connect();
        $this->validation    = Services::validation();
    }

    /**
     * Create a new user with role assignment.
     *
     * @param array $data User data (including 'role_ids' array optionally)
     * @return int User ID
     * @throws Exception
     */
    public function createUser(array $data): int
    {
        $session = session();
        $isSuperAdmin = $session->get('is_super_admin');
        $sessionCompanyId = $session->get('company_id');

        // Enforce Company Isolation
        if (!$isSuperAdmin) {
            $data['company_id'] = $sessionCompanyId;
        } else {
            // Super Admin must provide company_id if creating a company user
            if (empty($data['company_id'])) {
                // If not provided, fallback to session or error? 
                // Let's assume passed company_id is mandatory for super admin context switch
                if (empty($sessionCompanyId)) {
                     throw new Exception("Company ID is required.");
                }
                $data['company_id'] = $sessionCompanyId;
            }
        }

        // Validate basic fields via Service logic (or rely on Model validation, but Service is better for business rules)
        // Checks: username unique, email unique via Model rules usually.
        // We let Model insert handle validation exceptions if configured, or check manually here?
        // CodeIgniter Model::insert returns false on validation failure. We check `errors()`.

        $this->db->transStart();

        try {
            // 1. Prepare Data
            // Password hashing is handled by UserModel callback `hashPassword`
            // Ensure status defaults
            $data['employment_status'] = $data['employment_status'] ?? 'Active';
            $data['is_deleted'] = 0;
            $data['failed_login_attempts'] = 0;

            // 2. Insert User
            if (!$this->userModel->insert($data)) {
                $errors = $this->userModel->errors();
                throw new Exception(implode(', ', $errors));
            }
            $userId = $this->userModel->getInsertID();

            // 3. Assign Roles if provided
            if (isset($data['role_ids']) && is_array($data['role_ids'])) {
                $this->assignRoles($userId, $data['role_ids']);
            }

            // 4. Audit
            $this->auditService->log(
                'user_create',
                "Created user: {$data['username']}",
                ['user_id' => $userId, 'company_id' => $data['company_id']]
            );

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new DatabaseException("Failed to create user transaction.");
            }

            return $userId;

        } catch (Exception $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    /**
     * Update existing user.
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws Exception
     */
    public function updateUser(int $id, array $data): bool
    {
        $existing = $this->userModel->find($id);
        if (!$existing) {
            throw new Exception("User not found.");
        }

        // Validate Company Ownership
        $session = session();
        $isSuperAdmin = $session->get('is_super_admin');
        $sessionCompanyId = $session->get('company_id');

        if (!$isSuperAdmin && $existing['company_id'] != $sessionCompanyId) {
            throw new Exception("Unauthorized access to user.");
        }

        // Immutable Fields Check
        if (isset($data['username']) && $data['username'] !== $existing['username']) {
             // Exception: Maybe allow username change? Prompt says "Cannot update: username".
             throw new Exception("Username cannot be changed.");
        }
        if (isset($data['company_id']) && $data['company_id'] != $existing['company_id']) {
             if (!$isSuperAdmin) throw new Exception("Company cannot be changed.");
        }
        
        // Remove immutable fields to prevent accidental overwrite if check passed
        unset($data['username']);
        unset($data['company_id']); 

        $this->db->transStart();

        try {
            // Update User
            // If password is in data, Model handles hashing.
            // If empty password provided, remove it to avoid hashing empty string?
            if (isset($data['password']) && empty($data['password'])) {
                unset($data['password']);
            }

            // Ensure ID is passed for validation rules (is_unique[...,id,{id}])
            $data['id'] = $id;

            if (!$this->userModel->update($id, $data)) {
                $errors = $this->userModel->errors();
                throw new Exception(implode(', ', $errors));
            }

            // Sync Roles if provided
            if (isset($data['role_ids']) && is_array($data['role_ids'])) {
                $this->assignRoles($id, $data['role_ids']);
            }

            // Audit
            $this->auditService->log(
                'user_update',
                "Updated user ID: {$id}",
                ['user_id' => $id, 'changes' => array_keys($data)]
            );

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new DatabaseException("Failed to update user.");
            }

            return true;

        } catch (Exception $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    /**
     * Delete (Soft Delete) a user.
     *
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function deleteUser(int $id): bool
    {
        $existing = $this->userModel->find($id);
        if (!$existing) {
            throw new Exception("User not found.");
        }

        // Check Transactions (Invoices, Challans, Payments)
        // We check existence of tables first to avoid errors during setup phase
        $hasTransactions = false;

        if ($this->db->tableExists('invoices')) {
            if ($this->db->table('invoices')->where('created_by', $id)->countAllResults() > 0) $hasTransactions = true;
        }
        if (!$hasTransactions && $this->db->tableExists('challans')) {
             if ($this->db->table('challans')->where('created_by', $id)->countAllResults() > 0) $hasTransactions = true;
        }

        if ($hasTransactions) {
            throw new Exception("Cannot delete user with associated transactions.");
        }

        $this->db->transStart();

        try {
            // Soft Delete
            $this->userModel->update($id, ['is_deleted' => 1, 'employment_status' => 'Terminated']);
            
            // Should we revoke roles? Maybe not strictly required for soft delete, 
            // but good practice to disable access logic.
            // But soft delete flag usually handles it.

            // Audit
            $this->auditService->log(
                'user_delete',
                "Soft deleted user ID: {$id}",
                ['user_id' => $id]
            );

            $this->db->transComplete();

            return true;

        } catch (Exception $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    /**
     * Assign roles to a user (Replace existing).
     *
     * @param int $userId
     * @param array $roleIds
     * @return bool
     * @throws Exception
     */
    public function assignRoles(int $userId, array $roleIds): bool
    {
        // Validate User Existence
        $user = $this->userModel->find($userId);
        if (!$user) {
            throw new Exception("User not found.");
        }

        // Assuming clean slate assignment (delete all old, add new)
        // We use builder or UserRoleModel if available
        
        // Delete existing
        $this->userRoleModel->where('user_id', $userId)->delete();

        if (empty($roleIds)) {
            return true;
        }

        // Prepare batch
        $batch = [];
        foreach ($roleIds as $roleId) {
            $batch[] = [
                'user_id' => $userId,
                'role_id' => $roleId
            ];
        }

        if (!$this->userRoleModel->insertBatch($batch)) {
             throw new Exception("Failed to assign roles.");
        }
        
        // Audit
        $this->auditService->log(
            'user_assign_roles',
            "Assigned roles to user ID: {$userId}",
            ['role_ids' => $roleIds]
        );

        return true;
    }

    /**
     * Get all roles for a user.
     *
     * @param int $userId
     * @return array
     */
    public function getUserRoles(int $userId): array
    {
        return $this->roleModel
            ->select('roles.*')
            ->join('user_roles', 'user_roles.role_id = roles.id')
            ->where('user_roles.user_id', $userId)
            ->findAll();
    }

    /**
     * Get paginated users with filters.
     *
     * @param array $filters
     * @param int $perPage
     * @return array
     */
    public function getFilteredUsers(array $filters = [], int $perPage = 10): ?array
    {
        // Select & Join for Roles
        $this->userModel->select('users.*, GROUP_CONCAT(roles.role_name SEPARATOR ", ") as role_names')
            ->join('user_roles', 'user_roles.user_id = users.id', 'left')
            ->join('roles', 'roles.id = user_roles.role_id', 'left')
            ->groupBy('users.id');
        
        // Filter: Search
        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $this->userModel->groupStart()
                ->like('users.username', $s)
                ->orLike('users.email', $s)
                ->orLike('users.full_name', $s)
                ->orLike('users.mobile_number', $s)
                ->groupEnd();
        }

        // Filter: Role
        if (!empty($filters['role'])) {
            // Filter by role ID (note: this might limit role_names to just the matched role if using standard WHERE)
            $this->userModel->where('user_roles.role_id', $filters['role']);
        }

        // Filter: Status
        if (!empty($filters['status'])) {
            $this->userModel->where('users.employment_status', $filters['status']);
        }

        return $this->userModel->paginate($perPage);
    }

    /**
     * Get users for DataTables server-side processing.
     *
     * @param array $params DataTables parameters
     * @return array
     */
    public function getDataTableUsers(array $params): array
    {
        $session = session();
        $isSuperAdmin = $session->get('is_super_admin');
        $companyId = $session->get('company_id');

        $db = \Config\Database::connect();

        // Get total records count (before any filtering)
        $totalBuilder = $db->table('users');
        $totalBuilder->where('users.is_deleted', 0);
        if (!$isSuperAdmin && !empty($companyId)) {
            $totalBuilder->where('users.company_id', $companyId);
        }
        $totalCount = $totalBuilder->countAllResults();

        // Build filtered query
        $builder = $db->table('users');
        $builder->select('users.*, GROUP_CONCAT(roles.role_name SEPARATOR ", ") as role_names')
            ->join('user_roles', 'user_roles.user_id = users.id', 'left')
            ->join('roles', 'roles.id = user_roles.role_id', 'left')
            ->where('users.is_deleted', 0);

        // Company isolation
        if (!$isSuperAdmin && !empty($companyId)) {
            $builder->where('users.company_id', $companyId);
        }

        // Apply search filter
        if (!empty($params['search'])) {
            $s = $params['search'];
            $builder->groupStart()
                ->like('users.username', $s)
                ->orLike('users.email', $s)
                ->orLike('users.full_name', $s)
                ->orLike('users.mobile_number', $s)
                ->groupEnd();
        }

        // Apply role filter
        if (!empty($params['role'])) {
            $builder->where('user_roles.role_id', $params['role']);
        }

        // Apply status filter
        if (!empty($params['status'])) {
            $builder->where('users.employment_status', $params['status']);
        }

        // Group by user id
        $builder->groupBy('users.id');

        // Get filtered count using a separate count query
        $countBuilder = $db->table('users');
        $countBuilder->select('COUNT(DISTINCT users.id) as cnt')
            ->join('user_roles', 'user_roles.user_id = users.id', 'left')
            ->join('roles', 'roles.id = user_roles.role_id', 'left')
            ->where('users.is_deleted', 0);
        
        if (!$isSuperAdmin && !empty($companyId)) {
            $countBuilder->where('users.company_id', $companyId);
        }
        if (!empty($params['search'])) {
            $s = $params['search'];
            $countBuilder->groupStart()
                ->like('users.username', $s)
                ->orLike('users.email', $s)
                ->orLike('users.full_name', $s)
                ->orLike('users.mobile_number', $s)
                ->groupEnd();
        }
        if (!empty($params['role'])) {
            $countBuilder->where('user_roles.role_id', $params['role']);
        }
        if (!empty($params['status'])) {
            $countBuilder->where('users.employment_status', $params['status']);
        }
        $filteredCount = $countBuilder->get()->getRow()->cnt ?? 0;

        // Apply ordering
        $orderColumn = $params['orderColumn'] ?? 'full_name';
        $orderDir = $params['orderDir'] ?? 'ASC';
        
        // Map column names to actual table columns
        $columnMap = [
            'id' => 'users.id',
            'full_name' => 'users.full_name',
            'email' => 'users.email',
            'role_names' => 'role_names',
            'employment_status' => 'users.employment_status',
            'mobile_number' => 'users.mobile_number'
        ];
        $orderBy = $columnMap[$orderColumn] ?? 'users.full_name';
        $builder->orderBy($orderBy, $orderDir);

        // Apply pagination
        $start = (int) ($params['start'] ?? 0);
        $length = (int) ($params['length'] ?? 10);
        $builder->limit($length, $start);

        // Execute query
        $data = $builder->get()->getResultArray();

        return [
            'data'            => $data,
            'totalRecords'    => $totalCount,
            'filteredRecords' => (int) $filteredCount
        ];
    }


    /**
     * Get user statistics for dashboard cards.
     * 
     * @return array
     */
    public function getUserStats(): array
    {
        $session = session();
        $isSuperAdmin = $session->get('is_super_admin');
        $companyId = $session->get('company_id');
        
        $builder = $this->userModel->builder();
        if (!$isSuperAdmin && !empty($companyId)) {
            $builder->where('company_id', $companyId);
        }
        $builder->where('is_deleted', 0);
        
        // We clone builder for counts? No, builder state is mutable.
        // We verify BaseModel scope.
        // Easiest is to use Model methods.
        
        $stats = [
            'total'    => $this->userModel->countAllResults(false), // countAllResults resets builder? defaults reset: true.
            // But applyCompanyFilter needs to be applied manually if using countAllResults() on raw model?
            // BaseModel overrides find/findAll/paginate, but NOT countAllResults usually.
            // Let's check BaseModel. 
            // It ONLY overrides find, findAll, paginate.
            // So countAllResults DOES NOT have filtering!
            // I must manually filter here.
        ];
        
        // Using manual builder for stats
        $base = $this->userModel->builder();
        if (!$isSuperAdmin && $companyId) {
             $base->where('company_id', $companyId);
        }
        $base->where('is_deleted', 0);
        
        $total = $base->countAllResults(false);
        $active = $base->where('employment_status', 'Active')->countAllResults(false);
        $inactive = $base->where('employment_status', 'Terminated')->countAllResults(false); // or 'Inactive'
        
        // We need to reset or clone.
        // countAllResults(false) keeps the Where clauses? 
        // "If false, validation rules are not run" - No, parameter is reset. default true.
        // Documentation: countAllResults([bool $reset = true])
        
        // Correct approach:

        return [
            'total'    => $this->getCount(['users.is_deleted' => 0]),
            'active'   => $this->getCount(['users.is_deleted' => 0, 'users.employment_status' => 'Active']),
            'inactive' => $this->getCount(['users.is_deleted' => 0, 'users.employment_status' => 'Terminated']),
            'pending'  => $this->getCount(['users.is_deleted' => 0, 'users.employment_status' => 'Pending']), 
        ];
    }
    
    private function getCount(array $where) {
        $session = session();
        $isSuperAdmin = $session->get('is_super_admin');
        $companyId = $session->get('company_id');
        
        $builder = $this->userModel->builder();
        if (!$isSuperAdmin && $companyId) {
            $builder->where('users.company_id', $companyId);
        }
        return $builder->where($where)->countAllResults();
    }

    /**
     * Search users by query string.
     *
     * @param string $query
     * @return array
     */
    public function searchUsers(string $query): array
    {
        $session = session();
        $isSuperAdmin = $session->get('is_super_admin');
        $companyId = $session->get('company_id');

        $builder = $this->userModel->builder();
        
        // Isolation
        if (!$isSuperAdmin && !empty($companyId)) {
            $builder->where('company_id', $companyId);
        }
        
        $builder->where('is_deleted', 0);

        if (!empty($query)) {
            $builder->groupStart()
                ->like('username', $query)
                ->orLike('full_name', $query)
                ->orLike('email', $query)
                ->orLike('mobile_number', $query)
                ->groupEnd();
        }

        return $builder->limit(20)->get()->getResultArray();
    }

    /**
     * Change user password.
     *
     * @param int $userId
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool
     * @throws Exception
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        $user = $this->userModel->find($userId);
        if (!$user) {
            throw new Exception("User not found.");
        }

        // Verify Current
        if (!password_verify($currentPassword, $user['password_hash'])) {
            throw new Exception("Incorrect current password.");
        }

        // Update (Model hashes via callback)
        $this->userModel->update($userId, ['password' => $newPassword]);
        
        $this->auditService->log('password_change', "Password changed for user ID: {$userId}");

        return true;
    }

    /**
     * Admin reset password.
     *
     * @param int $userId
     * @param string $newPassword
     * @return bool
     * @throws Exception
     */
    public function resetPassword(int $userId, string $newPassword): bool
    {
        $user = $this->userModel->find($userId);
        if (!$user) {
             throw new Exception("User not found.");
        }
        
        // Permission check should be done by Controller/Filter, 
        // but strictly Service logic might require `can('reset_password')` check.
        // Assuming Controller handles authorization.

        $this->userModel->update($userId, ['password' => $newPassword]);
        
        $this->auditService->log('password_reset', "Password reset for user ID: {$userId} by admin");

        return true;
    }

    public function getPager()
    {
        return $this->userModel->pager;
    }
}

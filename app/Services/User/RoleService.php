<?php

namespace App\Services\User;

use App\Models\RoleModel;
use App\Models\UserRoleModel;
use App\Services\Audit\AuditService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Validation\ValidationInterface;
use Config\Services;
use Exception;

/**
 * Service for managing Roles and Permissions.
 */
class RoleService
{
  protected RoleModel $roleModel;
  protected UserRoleModel $userRoleModel;
  protected AuditService $auditService;
  protected BaseConnection $db;
  protected ValidationInterface $validation;

  public function __construct(
    RoleModel $roleModel = null,
    UserRoleModel $userRoleModel = null,
    AuditService $auditService = null,
    BaseConnection $db = null
  ) {
    $this->roleModel     = $roleModel ?? new RoleModel();
    $this->userRoleModel = $userRoleModel ?? new UserRoleModel();
    $this->auditService  = $auditService ?? new AuditService();
    $this->db            = $db ?? \Config\Database::connect();
    $this->validation    = Services::validation();
  }

  /**
   * Get all roles available for current context (filtered by model).
   * 
   * @return array
   */
  public function getAllRoles(): array
  {
    return $this->roleModel->findAll();
  }

  /**
   * Create a new custom role.
   *
   * @param array $data
   * @return int Role ID
   * @throws Exception
   */
  public function createRole(array $data): int
  {
    $session = session();
    $isSuperAdmin = $session->get('is_super_admin');
    $companyId = $session->get('company_id');

    // Isolation
    if (empty($data['company_id']) && !empty($companyId)) {
      $data['company_id'] = $companyId;
    }

    if (!$isSuperAdmin) {
      $data['is_system_role'] = 0; // Regular admins cannot create system roles
    } else {
      // Super admin can optionally create system roles if flagged, else default to 0
      if (!isset($data['is_system_role'])) {
        $data['is_system_role'] = 0;
      }
      if (empty($data['company_id']) && $data['is_system_role'] == 0) {
        // Non-system roles must verify company
        // Assuming Super Admin can create role for specific company
        // If no company provided, fail unless system role
        throw new Exception("Company ID required for non-system roles.");
      }
    }

    // Validate
    if (empty($data['role_name']) || strlen($data['role_name']) < 3) {
      throw new Exception("Role name is required and must be at least 3 characters.");
    }

    $this->db->transStart();

    try {
      // Model handles JSON encoding of 'permissions' in beforeInsert callback
      // Ensure permissions is array
      if (isset($data['permissions']) && !is_array($data['permissions'])) {
        throw new Exception("Permissions must be an array.");
      }

      if (!$this->roleModel->insert($data)) {
        $errors = $this->roleModel->errors();
        throw new Exception(implode(', ', $errors));
      }
      $roleId = $this->roleModel->getInsertID();

      // Audit
      $this->auditService->log(
        'Roles',
        'ROLE_CREATE',
        'Role',
        $roleId,
        null,
        ['role_name' => $data['role_name'], 'company_id' => $data['company_id'] ?? 'System']
      );

      $this->db->transComplete();

      if ($this->db->transStatus() === false) {
        throw new DatabaseException("Failed to create role.");
      }

      return $roleId;
    } catch (Exception $e) {
      $this->db->transRollback();
      throw $e;
    }
  }

  /**
   * Update an existing role.
   *
   * @param int $id
   * @param array $data
   * @return bool
   * @throws Exception
   */
  public function updateRole(int $id, array $data): bool
  {
    $role = $this->roleModel->find($id);
    if (!$role) {
      throw new Exception("Role not found.");
    }

    // Check System Role
    if ($role['is_system_role']) {
      throw new Exception("Cannot modify system roles.");
    }

    // Check Ownership
    $session = session();
    $isSuperAdmin = $session->get('is_super_admin');
    $companyId = $session->get('company_id');

    if (!$isSuperAdmin && $role['company_id'] != $companyId) {
      throw new Exception("Unauthorized access to role.");
    }

    // Prevent updating critical fields
    unset($data['is_system_role']);
    unset($data['company_id']);

    $this->db->transStart();

    try {
      if (!$this->roleModel->update($id, $data)) {
        $errors = $this->roleModel->errors();
        throw new Exception(implode(', ', $errors));
      }

      // Audit
      $this->auditService->log(
        'Roles',
        'ROLE_UPDATE',
        'Role',
        $id,
        null,
        ['changes' => array_keys($data)]
      );

      $this->db->transComplete();

      return true;
    } catch (Exception $e) {
      $this->db->transRollback();
      throw $e;
    }
  }

  /**
   * Delete (Soft Delete) a role.
   *
   * @param int $id
   * @return bool
   * @throws Exception
   */
  public function deleteRole(int $id): bool
  {
    $role = $this->roleModel->find($id);
    if (!$role) {
      throw new Exception("Role not found.");
    }

    if ($role['is_system_role']) {
      throw new Exception("Cannot delete system roles.");
    }

    // Check for active assignments
    $assignedCount = $this->userRoleModel->where('role_id', $id)->countAllResults();
    if ($assignedCount > 0) {
      throw new Exception("Cannot delete role associated with {$assignedCount} users.");
    }

    $this->db->transStart();

    try {
      $this->roleModel->update($id, ['is_deleted' => 1]);

      $this->auditService->log(
        'Roles',
        'ROLE_DELETE',
        'Role',
        $id,
        null,
        ['action' => 'Soft deleted']
      );

      $this->db->transComplete();
      return true;
    } catch (Exception $e) {
      $this->db->transRollback();
      throw $e;
    }
  }

  /**
   * Get role permissions.
   *
   * @param int $roleId
   * @return array
   */
  public function getRolePermissions(int $roleId): array
  {
    return $this->roleModel->getPermissions($roleId);
  }

  /**
   * Update permissions for a role.
   *
   * @param int $roleId
   * @param array $permissions
   * @return bool
   * @throws Exception
   */
  public function updatePermissions(int $roleId, array $permissions): bool
  {
    // Reuse updateRole logic which handles ownership and system checks
    return $this->updateRole($roleId, ['permissions' => $permissions]);
  }

  /**
   * Get all available system permissions.
   * Returns hierarchical array grouped by module → sub_module → actions.
   * Pulls from the `permissions` DB table.
   *
   * @return array [module => [sub_module => [[permission, label, action], ...], ...], ...]
   */
  public function getAvailablePermissions(): array
  {
    $db = \Config\Database::connect();
    $rows = $db->table('permissions')
      ->select('module, sub_module, action, permission, label')
      ->orderBy('module', 'ASC')
      ->orderBy('sub_module', 'ASC')
      ->orderBy('id', 'ASC')
      ->get()
      ->getResultArray();

    $grouped = [];
    foreach ($rows as $row) {
      $module = $row['module'];
      $subModule = $row['sub_module'];
      $grouped[$module][$subModule][] = [
        'permission' => $row['permission'],
        'label'      => $row['label'],
        'action'     => $row['action'],
      ];
    }

    return $grouped;
  }
}

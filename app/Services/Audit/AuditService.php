<?php

namespace App\Services\Audit;

use App\Models\AuditLogModel;

class AuditService
{
  protected $auditModel;
  protected $session;
  protected $request;

  public function __construct()
  {
    $this->auditModel = new AuditLogModel();
    $this->session = session();
    $this->request = service('request');
  }

  /**
   * Core log method that writes to the database
   */
  public function log(string $module, string $actionType, string $recordType = null, int $recordId = null, $beforeData = null, $afterData = null): int
  {
    // Get User ID from session
    $user = $this->session->get('user');
    $userId = $user['id'] ?? 0; // Default to 0 if not logged in (might fail FK if user 0 doesn't exist, ensure system user 0 or valid ID)
    $companyId = $user['company_id'] ?? 0;

    // If strict FK on user_id, we must have a valid ID.
    // We assume the application ensures a user is logged in for most actions.
    // If userId is 0 and no user 0 exists, this insert will fail. 
    // We'll perform a check or try/catch around insert if needed, but for now assuming valid session.

    $data = [
      'company_id'  => !empty($companyId) ? $companyId : null,
      'user_id'     => !empty($userId) ? $userId : null,
      'module'      => $module,
      'action_type' => $actionType,
      'record_type' => $recordType,
      'record_id'   => $recordId,
      'before_data' => $beforeData ? json_encode($beforeData) : null,
      'after_data'  => $afterData ? json_encode($afterData) : null,
      'ip_address'  => $this->request->getIPAddress(),
      'user_agent'  => (string)$this->request->getUserAgent(),
    ];

    // Basic error handling to prevent app crash if audit log fails
    try {
      return $this->auditModel->insert($data);
    } catch (\Exception $e) {
      // Log error to system log but don't stop execution
      log_message('error', 'Audit Log Insert Failed: ' . $e->getMessage());
      return 0;
    }
  }

  /**
   * Log creation of a record
   */
  public function logCreate(string $module, string $recordType, int $recordId, array $data): int
  {
    return $this->log($module, 'create', $recordType, $recordId, null, $data);
  }

  /**
   * Log update of a record
   */
  public function logUpdate(string $module, string $recordType, int $recordId, array $beforeData, array $afterData): int
  {
    return $this->log($module, 'update', $recordType, $recordId, $beforeData, $afterData);
  }

  /**
   * Log deletion of a record
   */
  public function logDelete(string $module, string $recordType, int $recordId, array $beforeData): int
  {
    return $this->log($module, 'delete', $recordType, $recordId, $beforeData, null);
  }

  /**
   * Log viewing of a record (use sparingly for sensitive data)
   */
  public function logView(string $module, string $recordType, int $recordId): int
  {
    return $this->log($module, 'view', $recordType, $recordId);
  }

  /**
   * Log printing of a record
   */
  public function logPrint(string $module, string $recordType, int $recordId): int
  {
    return $this->log($module, 'print', $recordType, $recordId);
  }

  /**
   * Log export of data
   */
  public function logExport(string $module, string $recordType, array $filters = []): int
  {
    // For export, record_id is usually null
    return $this->log($module, 'export', $recordType, null, null, $filters);
  }

  /**
   * Log access denied attempts
   */
  public function logAccessDenied(string $module, string $action, string $details = ''): int
  {
    return $this->log($module, 'access_denied', null, null, null, ['action' => $action, 'details' => $details]);
  }

  /**
   * Log company switching
   */
  public function logCompanySwitch(int $fromCompanyId, int $toCompanyId): int
  {
    return $this->log('User', 'switch_company', 'Company', $toCompanyId, ['from_company_id' => $fromCompanyId], ['to_company_id' => $toCompanyId]);
  }

  /**
   * Retrieve audit trail for a specific record
   */
  public function getAuditTrail(string $recordType, int $recordId): array
  {
    return $this->auditModel->getAuditTrail($recordType, $recordId);
  }
}

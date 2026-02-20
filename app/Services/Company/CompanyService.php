<?php

namespace App\Services\Company;

use App\Models\CompanyModel;
use App\Models\StateModel;
use App\Models\UserModel;
use App\Services\Audit\AuditService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Validation\ValidationInterface;
use Config\Services;
use Exception;

/**
 * Service for managing Companies.
 */
class CompanyService
{
  protected CompanyModel $companyModel;
  protected StateModel $stateModel;
  protected ?UserModel $userModel; // Optional dependency if needed, or instantiate
  protected AuditService $auditService;
  protected BaseConnection $db;
  protected ValidationInterface $validation;

  public function __construct(
    CompanyModel $companyModel = null,
    StateModel $stateModel = null,
    AuditService $auditService = null,
    BaseConnection $db = null
  ) {
    $this->companyModel = $companyModel ?? new CompanyModel();
    $this->stateModel   = $stateModel ?? new StateModel();
    $this->auditService = $auditService ?? new AuditService();
    $this->db           = $db ?? \Config\Database::connect();
    $this->validation   = Services::validation();

    // Dynamic loading of UserModel to avoid circular dependencies if any, or just standard new
    $this->userModel = model(UserModel::class);
  }

  /**
   * Create a new company.
   *
   * @param array $data
   * @return int Company ID
   * @throws Exception
   */
  public function createCompany(array $data): int
  {
    $this->db->transStart();

    try {
      // 1. Basic Validation (Required fields check before logic)
      if (empty($data['company_name'])) {
        throw new Exception("Company Name is required.");
      }

      // 2. Default Values
      $data['last_invoice_number'] = 0;
      $data['last_challan_number'] = 0;
      $data['invoice_prefix']      = $data['invoice_prefix'] ?? 'INV';
      $data['challan_prefix']      = $data['challan_prefix'] ?? 'CHN';
      $data['tax_rate']            = $data['tax_rate'] ?? 3.00;
      $data['is_active']           = 1;
      $data['is_deleted']          = 0;

      // 3. Generate Code if missing
      if (empty($data['company_code'])) {
        $data['company_code'] = $this->generateCompanyCode();
      }

      // 4. Validate GST/PAN Uniqueness and Format via Model Rules or Service Logic
      // The model insert() will trigger validation rules defined in CompanyModel.
      // However, we can do explicit checks if needed.
      if (!empty($data['gst_number'])) {
        if (!$this->validateGSTNumber($data['gst_number'])) {
          throw new Exception("Invalid or Duplicate GST Number.");
        }
      }
      if (!empty($data['pan_number'])) {
        if (!$this->validatePANNumber($data['pan_number'])) {
          throw new Exception("Invalid PAN Number.");
        }
      }

      // 5. Insert
      if (!$this->companyModel->insert($data)) {
        $errors = $this->companyModel->errors();
        throw new Exception(implode(', ', $errors));
      }

      $companyId = $this->companyModel->getInsertID();

      // 6. Audit
      $this->auditService->log(
        'Company',
        'create',
        'Company',
        $companyId,
        null,
        ['company_name' => $data['company_name'], 'company_code' => $data['company_code']]
      );

      $this->db->transComplete();

      if ($this->db->transStatus() === false) {
        throw new DatabaseException("Failed to create company transaction.");
      }

      return $companyId;
    } catch (Exception $e) {
      $this->db->transRollback();
      throw $e;
    }
  }

  /**
   * Update existing company.
   *
   * @param int $id
   * @param array $data
   * @return bool
   * @throws Exception
   */
  public function updateCompany(int $id, array $data): bool
  {
    $existing = $this->companyModel->find($id);
    if (!$existing) {
      throw new Exception("Company not found.");
    }

    // Immutable fields
    unset($data['company_code']);
    unset($data['gst_number']); // Prompt says immutable

    $this->db->transStart();

    try {
      // Update
      if (!$this->companyModel->update($id, $data)) {
        $errors = $this->companyModel->errors();
        throw new Exception(implode(', ', $errors));
      }

      // Audit
      $this->auditService->log(
        'Company',
        'update',
        'Company',
        $id,
        null,
        ['changed_fields' => array_keys($data)]
      );

      $this->db->transComplete();

      if ($this->db->transStatus() === false) {
        throw new DatabaseException("Failed to update company.");
      }

      return true;
    } catch (Exception $e) {
      $this->db->transRollback();
      throw $e;
    }
  }

  /**
   * Delete (Soft Delete) a company.
   *
   * @param int $id
   * @return bool
   * @throws Exception
   */
  public function deleteCompany(int $id): bool
  {
    $existing = $this->companyModel->find($id);
    if (!$existing) {
      throw new Exception("Company not found.");
    }

    // Check dependencies (Users, etc.)
    // Using UserModel if available, or raw query
    $userCount = $this->db->table('users')->where('company_id', $id)->countAllResults();
    if ($userCount > 0) {
      throw new Exception("Cannot delete company with active users/data.");
    }

    // Also check if any invoices exist (if table exists)
    // Assuming 'invoices' table
    if ($this->db->tableExists('invoices')) {
      $invoiceCount = $this->db->table('invoices')->where('company_id', $id)->countAllResults();
      if ($invoiceCount > 0) {
        throw new Exception("Cannot delete company with existing invoices.");
      }
    }

    $this->db->transStart();

    try {
      // Soft Delete
      $this->companyModel->update($id, ['is_deleted' => 1, 'is_active' => 0]);

      // Audit
      $this->auditService->log(
        'Company',
        'delete',
        'Company',
        $id,
        ['company_id' => $id],
        null
      );

      $this->db->transComplete();

      return true;
    } catch (Exception $e) {
      $this->db->transRollback();
      throw $e;
    }
  }

  /**
   * Get company by ID with State.
   *
   * @param int $id
   * @return array|null
   */
  public function getCompanyById(int $id): ?array
  {
    // Join with States
    $company = $this->companyModel
      ->select('companies.*, states.state_name')
      ->join('states', 'states.id = companies.state_id', 'left')
      ->where('companies.id', $id)
      ->where('companies.is_deleted', 0)
      ->first();

    return $company;
  }

  /**
   * Validate GST Number.
   *
   * @param string $gst
   * @return bool
   */
  public function validateGSTNumber(string $gst): bool
  {
    // Format Check
    if (!$this->companyModel->validateGSTNumber($gst)) {
      return false; // Invalid format
    }

    // Uniqueness Check (Context-dependent: on create, it must be unique. On update, handled by update logic excluding self)
    // Here we just check if it exists in DB generally.
    $exists = $this->companyModel->where('gst_number', $gst)->countAllResults() > 0;

    // If exists, strictly it's "valid format but duplicate".
    // The prompt asks "Return validation result".
    // Usually validation implies "Is it okay to use?".
    // If it exists, it's NOT okay to use for a NEW company.
    // We return false if duplicate.
    if ($exists) {
      return false;
    }

    return true;
  }

  /**
   * Validate PAN Number.
   *
   * @param string $pan
   * @return bool
   */
  public function validatePANNumber(string $pan): bool
  {
    return $this->companyModel->validatePANNumber($pan);
  }

  /**
   * Generate unique company code.
   *
   * @return string
   */
  private function generateCompanyCode(): string
  {
    // Get last code
    $lastCompany = $this->companyModel
      ->orderBy('id', 'DESC')
      ->first();

    $lastCode = $lastCompany['company_code'] ?? 'COMP000';

    // Extract number (assuming COMPxxx format)
    if (preg_match('/COMP(\d+)/', $lastCode, $matches)) {
      $num = (int)$matches[1];
      $nextNum = $num + 1;
    } else {
      $nextNum = 1;
    }

    return 'COMP' . str_pad((string)$nextNum, 3, '0', STR_PAD_LEFT);
  }
}

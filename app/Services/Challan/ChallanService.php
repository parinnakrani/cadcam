<?php

namespace App\Services\Challan;

use App\Models\ChallanModel;
use App\Models\ChallanLineModel;
use App\Models\AccountModel;
use App\Models\CashCustomerModel;
use App\Models\CompanyModel;
use App\Services\Challan\ChallanCalculationService;
use App\Services\Audit\AuditService;
use Exception;

/**
 * ChallanService
 *
 * Handles all business logic for challan (job order) management.
 * Follows the established service layer pattern from ProductService.
 *
 * Responsibilities:
 * - CRUD operations with validation
 * - Sequential challan number generation (thread-safe via DB lock)
 * - Customer validation (Account XOR Cash)
 * - Status workflow enforcement
 * - Totals recalculation from lines
 * - Invoice linkage
 * - Audit logging for all state-changing operations
 *
 * Dependencies:
 * - ChallanModel: challan record access
 * - ChallanLineModel: line item access + totals
 * - AccountModel: validate account customers
 * - CashCustomerModel: validate cash customers
 * - CompanyModel: challan number generation (thread-safe)
 * - ChallanCalculationService: amount + tax calculations
 * - AuditService: audit logging
 */
class ChallanService
{
  protected ChallanModel $challanModel;
  protected ChallanLineModel $challanLineModel;
  protected AccountModel $accountModel;
  protected CashCustomerModel $cashCustomerModel;
  protected CompanyModel $companyModel;
  protected ChallanCalculationService $calculationService;
  protected AuditService $auditService;

  public function __construct(
    ChallanModel $challanModel,
    ChallanLineModel $challanLineModel,
    AccountModel $accountModel,
    CashCustomerModel $cashCustomerModel,
    CompanyModel $companyModel,
    ChallanCalculationService $calculationService,
    AuditService $auditService
  ) {
    $this->challanModel        = $challanModel;
    $this->challanLineModel    = $challanLineModel;
    $this->accountModel        = $accountModel;
    $this->cashCustomerModel   = $cashCustomerModel;
    $this->companyModel        = $companyModel;
    $this->calculationService  = $calculationService;
    $this->auditService        = $auditService;
  }

    // =========================================================================
    // CRUD OPERATIONS
    // =========================================================================

  /**
   * Create a new challan.
   *
   * Flow:
   * 1. Validate input data
   * 2. Auto-set company_id + created_by from session
   * 3. Validate customer exists and belongs to company
   * 4. Generate sequential challan number (thread-safe)
   * 5. Insert challan record
   * 6. If lines provided, create lines + recalculate totals
   * 7. Audit log
   *
   * @param array $data Challan data
   * @return int Created challan ID
   * @throws Exception
   */
  public function createChallan(array $data): int
  {
    $session   = session();
    $companyId = $session->get('company_id');
    $userId    = $session->get('user_id');

    if (empty($companyId)) {
      throw new Exception('Company ID not found in session.');
    }
    if (empty($userId)) {
      throw new Exception('User ID not found in session.');
    }

    // 1. Auto-set tenant and creator
    $data['company_id'] = $companyId;
    $data['created_by'] = $userId;

    // 2. Validate input
    $this->validateChallanData($data);

    // 3. Validate customer
    $this->validateCustomer($data['customer_type'], $data, $companyId);

    // 4. Generate challan number (thread-safe)
    $data['challan_number'] = $this->generateNextChallanNumber($companyId);

    // 5. Set defaults
    $data['challan_status'] = $data['challan_status'] ?? 'Draft';

    // Extract lines before insert (not a challan column)
    $lines = $data['lines'] ?? [];
    unset($data['lines']);

    // Calculate initial totals to satisfy model validation rules
    // Get company default tax rate for new challan
    $defaultTaxRate = $this->calculationService->getTaxRate();
    $data['tax_percent'] = $defaultTaxRate;

    if (!empty($lines)) {
      // Calculate totals from provided lines using the default tax rate
      $calculationResult = $this->calculationService->calculateAll($lines, $defaultTaxRate);
      $totals = $calculationResult['totals'];

      $data['subtotal_amount'] = $totals['subtotal_amount'];
      $data['tax_amount']      = $totals['tax_amount'];
      $data['total_amount']    = $totals['total_amount'];
      $data['total_weight']    = $totals['total_weight'];
    } else {
      // Default to 0 if no lines
      $data['subtotal_amount'] = 0.00;
      $data['tax_amount']      = 0.00;
      $data['total_amount']    = 0.00;
      $data['total_weight']    = 0.000;
    }

    // Remove non-DB fields
    unset($data['account_name'], $data['customer_name']);

    // 6. DB Transaction
    $db = \Config\Database::connect();
    $db->transStart();

    try {
      $challanId = $this->challanModel->insert($data);

      if (!$challanId) {
        $db->transRollback();
        $errors = $this->challanModel->errors();
        $errorMsg = !empty($errors) ? implode(', ', $errors) : 'Unknown insert error';
        throw new Exception("Failed to create challan: {$errorMsg}");
      }

      // 7. Create lines if provided
      if (!empty($lines)) {
        $this->createChallanLines($challanId, $lines);
        $this->recalculateTotals($challanId);
      }

      // 8. Audit log
      $this->auditService->log(
        'CHALLAN_CREATE',
        "Created Challan: {$data['challan_number']} ({$data['challan_type']})",
        [
          'company_id'  => $companyId,
          'user_id'     => $userId,
          'challan_id'  => $challanId,
          'challan_number' => $data['challan_number'],
          'challan_type'   => $data['challan_type'],
          'customer_type'  => $data['customer_type'],
        ]
      );

      $db->transComplete();

      if ($db->transStatus() === false) {
        throw new Exception('Transaction failed during challan creation.');
      }

      return $challanId;
    } catch (Exception $e) {
      $db->transRollback();
      throw $e;
    }
  }

  /**
   * Update an existing challan.
   *
   * Invoiced challans cannot be edited.
   *
   * @param int   $id   Challan ID
   * @param array $data Updated fields
   * @return bool
   * @throws Exception
   */
  public function updateChallan(int $id, array $data): bool
  {
    $session   = session();
    $companyId = $session->get('company_id');
    $userId    = $session->get('user_id');

    // 1. Validate existence and ownership
    $challan = $this->challanModel->find($id);
    if (!$challan) {
      throw new Exception("Challan not found: {$id}");
    }
    if ((int)$challan['company_id'] !== (int)$companyId) {
      throw new Exception("Challan not found: {$id}");
    }

    // 2. Check if invoiced
    if (!$this->challanModel->canEdit($id)) {
      throw new Exception('Cannot edit an invoiced challan.');
    }

    // 3. Store before-state for audit
    $beforeData = $challan;

    // 4. If customer changed, validate new customer
    $customerType = $data['customer_type'] ?? $challan['customer_type'];
    if (
      isset($data['customer_type']) ||
      isset($data['account_id']) ||
      isset($data['cash_customer_id'])
    ) {
      $this->validateCustomer($customerType, $data, $companyId);
    }

    // 5. Prepare update data — only allow specific fields
    $updateData = [];
    $editableFields = [
      'challan_date',
      'challan_type',
      'customer_type',
      'account_id',
      'cash_customer_id',
      'notes',
      'delivery_date',
    ];
    foreach ($editableFields as $field) {
      if (array_key_exists($field, $data)) {
        $updateData[$field] = $data[$field];
      }
    }

    // Clear the opposite customer ID if customer_type changed
    if (isset($data['customer_type'])) {
      if ($data['customer_type'] === 'Account') {
        $updateData['cash_customer_id'] = null;
      } elseif ($data['customer_type'] === 'Cash') {
        $updateData['account_id'] = null;
      }
    }

    if (empty($updateData)) {
      return true; // Nothing to update
    }

    // 6. DB Transaction
    $db = \Config\Database::connect();
    $db->transStart();

    $updateData['updated_at'] = date('Y-m-d H:i:s');
    $db->table('challans')->where('id', $id)->update($updateData);

    // 7. Audit log
    $this->auditService->log(
      'CHALLAN_UPDATE',
      "Updated Challan: {$challan['challan_number']}",
      [
        'company_id'  => $companyId,
        'user_id'     => $userId,
        'challan_id'  => $id,
        'before'      => $beforeData,
        'changes'     => $updateData,
      ]
    );

    $db->transComplete();

    if ($db->transStatus() === false) {
      throw new Exception('Database update failed (Transaction rolled back).');
    }

    return true;
  }

  /**
   * Delete a challan (soft delete).
   *
   * Invoiced challans cannot be deleted.
   * Lines are hard-deleted (challan_lines has no is_deleted column).
   *
   * @param int $id Challan ID
   * @return bool
   * @throws Exception
   */
  public function deleteChallan(int $id): bool
  {
    $session   = session();
    $companyId = $session->get('company_id');
    $userId    = $session->get('user_id');

    // 1. Validate existence and ownership
    $challan = $this->challanModel->find($id);
    if (!$challan) {
      throw new Exception("Challan not found: {$id}");
    }
    if ((int)$challan['company_id'] !== (int)$companyId) {
      throw new Exception("Challan not found: {$id}");
    }

    // 2. Check if can delete
    if (!$this->challanModel->canDelete($id)) {
      throw new Exception('Cannot delete an invoiced challan.');
    }

    // 3. DB Transaction
    $db = \Config\Database::connect();
    $db->transStart();

    // Soft delete challan
    $db->table('challans')->where('id', $id)->update([
      'is_deleted' => 1,
      'updated_at' => date('Y-m-d H:i:s'),
    ]);

    // Hard delete lines (challan_lines has no is_deleted column)
    $this->challanLineModel->deleteLinesByChallanId($id);

    // 4. Audit log
    $this->auditService->log(
      'CHALLAN_DELETE',
      "Deleted Challan: {$challan['challan_number']} ({$challan['challan_type']})",
      [
        'company_id'  => $companyId,
        'user_id'     => $userId,
        'challan_id'  => $id,
        'challan_data' => $challan,
      ]
    );

    $db->transComplete();

    if ($db->transStatus() === false) {
      throw new Exception('Database delete failed (Transaction rolled back).');
    }

    return true;
  }

    // =========================================================================
    // RETRIEVAL METHODS
    // =========================================================================

  /**
   * Get a challan by ID with customer details.
   *
   * @param int $id
   * @return array|null
   * @throws Exception
   */
  public function getChallanById(int $id): ?array
  {
    $session   = session();
    $companyId = $session->get('company_id');

    $challan = $this->challanModel->getChallanWithCustomer($id);

    if (!$challan) {
      return null;
    }

    // Double-check company ownership (BaseModel already filters, but be explicit)
    if ((int)$challan['company_id'] !== (int)$companyId) {
      return null;
    }

    return $challan;
  }

  /**
   * Get a challan with all line items and customer details.
   *
   * @param int $id
   * @return array|null
   * @throws Exception
   */
  public function getChallanWithLines(int $id): ?array
  {
    $session   = session();
    $companyId = $session->get('company_id');

    $challan = $this->challanModel->getChallanWithLines($id);

    if (!$challan) {
      return null;
    }

    if ((int)$challan['company_id'] !== (int)$companyId) {
      return null;
    }

    return $challan;
  }

  /**
   * Get all challans (list view) with optional filters.
   *
   * @param array $filters Optional: status, customer_type, challan_type, date_from, date_to
   * @return array
   */
  public function getChallans(array $filters = []): array
  {
    // Start with the model (BaseModel handles company filter + is_deleted)
    if (!empty($filters['status'])) {
      $this->challanModel->where('challans.challan_status', $filters['status']);
    }

    if (!empty($filters['challan_type'])) {
      $this->challanModel->where('challans.challan_type', $filters['challan_type']);
    }

    if (!empty($filters['customer_type'])) {
      $this->challanModel->where('challans.customer_type', $filters['customer_type']);
    }

    if (!empty($filters['date_from'])) {
      $this->challanModel->where('challans.challan_date >=', $filters['date_from']);
    }

    if (!empty($filters['date_to'])) {
      $this->challanModel->where('challans.challan_date <=', $filters['date_to']);
    }

    if (!empty($filters['account_id'])) {
      $this->challanModel->where('challans.account_id', (int)$filters['account_id']);
    }

    if (!empty($filters['cash_customer_id'])) {
      $this->challanModel->where('challans.cash_customer_id', (int)$filters['cash_customer_id']);
    }

    $this->challanModel->orderBy('challans.challan_date', 'DESC');
    $this->challanModel->orderBy('challans.id', 'DESC');

    return $this->challanModel->findAll();
  }

  /**
   * Get challans by status.
   *
   * @param string $status
   * @return array
   */
  public function getChallansByStatus(string $status): array
  {
    return $this->challanModel->getChallansByStatus($status);
  }

  /**
   * Get pending (invoiceable) challans for a specific customer.
   *
   * @param int    $customerId
   * @param string $customerType 'Account' or 'Cash'
   * @return array
   */
  public function getPendingChallansForCustomer(int $customerId, string $customerType): array
  {
    return $this->challanModel->getPendingChallans($customerId, $customerType);
  }

  /**
   * Search challans by number or notes.
   *
   * @param string $query
   * @return array
   */
  public function searchChallans(string $query): array
  {
    return $this->challanModel->searchChallans($query);
  }

    // =========================================================================
    // LINE MANAGEMENT
    // =========================================================================

  /**
   * Create challan lines for a challan.
   *
   * Inserts lines with sequential line numbers and recalculates totals.
   *
   * @param int   $challanId
   * @param array $lines Array of line data
   * @return bool
   * @throws Exception
   */
  public function createChallanLines(int $challanId, array $lines): bool
  {
    if (empty($lines)) {
      return true;
    }

    $this->challanLineModel->insertBulkLines($challanId, $lines);

    return true;
  }

  /**
   * Add a single line to an existing challan.
   *
   * @param int   $challanId
   * @param array $lineData
   * @return int  Inserted line ID
   * @throws Exception
   */
  public function addLine(int $challanId, array $lineData): int
  {
    $session   = session();
    $companyId = $session->get('company_id');

    // Validate challan exists and can be edited
    $challan = $this->challanModel->find($challanId);
    if (!$challan || (int)$challan['company_id'] !== (int)$companyId) {
      throw new Exception("Challan not found: {$challanId}");
    }
    if (!$this->challanModel->canEdit($challanId)) {
      throw new Exception('Cannot add lines to an invoiced challan.');
    }

    // Auto-set line number
    $lineData['challan_id']  = $challanId;
    $lineData['line_number'] = $this->challanLineModel->getNextLineNumber($challanId);

    $db = \Config\Database::connect();
    $db->transStart();

    $lineId = $this->challanLineModel->insert($lineData);

    if (!$lineId) {
      $db->transRollback();
      $errors = $this->challanLineModel->errors();
      $errorMsg = !empty($errors) ? implode(', ', $errors) : 'Unknown insert error';
      throw new Exception("Failed to add line: {$errorMsg}");
    }

    // Recalculate challan totals
    $this->recalculateTotals($challanId);

    // Audit
    $this->auditService->log(
      'CHALLAN_LINE_ADD',
      "Added line #{$lineData['line_number']} to Challan: {$challan['challan_number']}",
      [
        'company_id' => $companyId,
        'user_id'    => $session->get('user_id'),
        'challan_id' => $challanId,
        'line_id'    => $lineId,
      ]
    );

    $db->transComplete();

    if ($db->transStatus() === false) {
      throw new Exception('Transaction failed while adding line.');
    }

    return $lineId;
  }

  /**
   * Update an existing line item.
   *
   * @param int   $challanId
   * @param int   $lineId
   * @param array $lineData
   * @return bool
   * @throws Exception
   */
  public function updateLine(int $challanId, int $lineId, array $lineData): bool
  {
    $session   = session();
    $companyId = $session->get('company_id');

    // Validate challan
    $challan = $this->challanModel->find($challanId);
    if (!$challan || (int)$challan['company_id'] !== (int)$companyId) {
      throw new Exception("Challan not found: {$challanId}");
    }
    if (!$this->challanModel->canEdit($challanId)) {
      throw new Exception('Cannot edit lines of an invoiced challan.');
    }

    // Validate line belongs to this challan
    $line = $this->challanLineModel->find($lineId);
    if (!$line || (int)$line['challan_id'] !== $challanId) {
      throw new Exception("Line not found: {$lineId}");
    }

    // Remove protected fields
    unset($lineData['challan_id'], $lineData['line_number'], $lineData['id']);

    $db = \Config\Database::connect();
    $db->transStart();

    $lineData['updated_at'] = date('Y-m-d H:i:s');
    $db->table('challan_lines')->where('id', $lineId)->update($lineData);

    // Recalculate totals
    $this->recalculateTotals($challanId);

    $this->auditService->log(
      'CHALLAN_LINE_UPDATE',
      "Updated line #{$line['line_number']} in Challan: {$challan['challan_number']}",
      [
        'company_id' => $companyId,
        'user_id'    => $session->get('user_id'),
        'challan_id' => $challanId,
        'line_id'    => $lineId,
        'changes'    => $lineData,
      ]
    );

    $db->transComplete();

    if ($db->transStatus() === false) {
      throw new Exception('Transaction failed while updating line.');
    }

    return true;
  }

  /**
   * Delete a line from a challan.
   *
   * Resequences remaining lines and recalculates totals.
   *
   * @param int $challanId
   * @param int $lineId
   * @return bool
   * @throws Exception
   */
  public function deleteLine(int $challanId, int $lineId): bool
  {
    $session   = session();
    $companyId = $session->get('company_id');

    // Validate challan
    $challan = $this->challanModel->find($challanId);
    if (!$challan || (int)$challan['company_id'] !== (int)$companyId) {
      throw new Exception("Challan not found: {$challanId}");
    }
    if (!$this->challanModel->canEdit($challanId)) {
      throw new Exception('Cannot delete lines from an invoiced challan.');
    }

    // Validate line belongs to this challan
    $line = $this->challanLineModel->find($lineId);
    if (!$line || (int)$line['challan_id'] !== $challanId) {
      throw new Exception("Line not found: {$lineId}");
    }

    $db = \Config\Database::connect();
    $db->transStart();

    // Hard delete the line
    $this->challanLineModel->deleteLine($lineId);

    // Resequence remaining lines
    $this->challanLineModel->resequenceLines($challanId);

    // Recalculate totals
    $this->recalculateTotals($challanId);

    $this->auditService->log(
      'CHALLAN_LINE_DELETE',
      "Deleted line #{$line['line_number']} from Challan: {$challan['challan_number']}",
      [
        'company_id' => $companyId,
        'user_id'    => $session->get('user_id'),
        'challan_id' => $challanId,
        'line_id'    => $lineId,
        'line_data'  => $line,
      ]
    );

    $db->transComplete();

    if ($db->transStatus() === false) {
      throw new Exception('Transaction failed while deleting line.');
    }

    return true;
  }

  /**
   * Replace all lines for a challan (full update).
   *
   * Deletes existing lines, inserts new ones, recalculates totals.
   *
   * @param int   $challanId
   * @param array $lines
   * @return bool
   * @throws Exception
   */
  public function replaceLines(int $challanId, array $lines): bool
  {
    $session   = session();
    $companyId = $session->get('company_id');

    // Validate challan
    $challan = $this->challanModel->find($challanId);
    if (!$challan || (int)$challan['company_id'] !== (int)$companyId) {
      throw new Exception("Challan not found: {$challanId}");
    }
    if (!$this->challanModel->canEdit($challanId)) {
      throw new Exception('Cannot replace lines of an invoiced challan.');
    }

    $db = \Config\Database::connect();
    $db->transStart();

    $this->challanLineModel->replaceAllLines($challanId, $lines);
    $this->recalculateTotals($challanId);

    $this->auditService->log(
      'CHALLAN_LINES_REPLACE',
      "Replaced all lines in Challan: {$challan['challan_number']}",
      [
        'company_id' => $companyId,
        'user_id'    => $session->get('user_id'),
        'challan_id' => $challanId,
        'line_count' => count($lines),
      ]
    );

    $db->transComplete();

    if ($db->transStatus() === false) {
      throw new Exception('Transaction failed while replacing lines.');
    }

    return true;
  }

    // =========================================================================
    // STATUS MANAGEMENT
    // =========================================================================

  /**
   * Update challan status with workflow enforcement.
   *
   * Valid transitions are defined in ChallanModel::$validTransitions.
   *
   * @param int    $id
   * @param string $newStatus
   * @return bool
   * @throws Exception
   */
  public function updateChallanStatus(int $id, string $newStatus): bool
  {
    $session   = session();
    $companyId = $session->get('company_id');
    $userId    = $session->get('user_id');

    // 1. Validate challan exists
    $challan = $this->challanModel->find($id);
    if (!$challan || (int)$challan['company_id'] !== (int)$companyId) {
      throw new Exception("Challan not found: {$id}");
    }

    $currentStatus = $challan['challan_status'];

    // 2. Validate transition
    if (!$this->challanModel->isValidTransition($currentStatus, $newStatus)) {
      throw new Exception(
        "Invalid status transition: '{$currentStatus}' → '{$newStatus}'. "
          . "Challan {$challan['challan_number']} cannot change from {$currentStatus} to {$newStatus}."
      );
    }

    // 3. Update status
    $db = \Config\Database::connect();
    $db->transStart();

    $this->challanModel->updateStatus($id, $newStatus);

    // 4. Audit
    $this->auditService->log(
      'CHALLAN_STATUS_CHANGE',
      "Challan {$challan['challan_number']}: {$currentStatus} → {$newStatus}",
      [
        'company_id'    => $companyId,
        'user_id'       => $userId,
        'challan_id'    => $id,
        'from_status'   => $currentStatus,
        'to_status'     => $newStatus,
      ]
    );

    $db->transComplete();

    if ($db->transStatus() === false) {
      throw new Exception('Transaction failed during status update.');
    }

    return true;
  }

    // =========================================================================
    // TOTALS RECALCULATION
    // =========================================================================

  /**
   * Recalculate challan totals from its line items.
   *
   * Aggregates amounts from challan_lines and updates the challan record.
   *
   * @param int $challanId
   * @return bool
   */
  public function recalculateTotals(int $challanId): bool
  {
    $lineTotals = $this->challanLineModel->getTotalsForChallan($challanId);
    $challan    = $this->challanModel->find($challanId);

    // Use stored tax_percent if valid, otherwise fetch company default
    if (isset($challan['tax_percent']) && is_numeric($challan['tax_percent'])) {
      $taxRate = (float)$challan['tax_percent'];
    } else {
      $taxRate = $this->calculationService->getTaxRate();
    }

    // Use ChallanCalculationService for tax-aware totals
    $subtotal  = (float)$lineTotals['total_amount'];
    $taxAmount = round($subtotal * ($taxRate / 100), 2);

    $totals = [
      'total_weight'    => $lineTotals['total_gold_weight'],
      'subtotal_amount' => $subtotal,
      'tax_amount'      => $taxAmount,
      'total_amount'    => round($subtotal + $taxAmount, 2),
      'tax_percent'     => $taxRate,
    ];

    return $this->challanModel->updateTotals($challanId, $totals);
  }

    // =========================================================================
    // INVOICE LINKAGE
    // =========================================================================

  /**
   * Mark a challan as invoiced and link it to the generated invoice.
   *
   * Called by InvoiceService during invoice creation.
   *
   * @param int $challanId
   * @param int $invoiceId
   * @return bool
   * @throws Exception
   */
  public function markAsInvoiced(int $challanId, int $invoiceId): bool
  {
    $session   = session();
    $companyId = $session->get('company_id');
    $userId    = $session->get('user_id');

    // Validate challan exists
    $challan = $this->challanModel->find($challanId);
    if (!$challan || (int)$challan['company_id'] !== (int)$companyId) {
      throw new Exception("Challan not found: {$challanId}");
    }

    // Cannot invoice an already invoiced challan
    if (!empty($challan['invoice_generated']) && (int)$challan['invoice_generated'] === 1) {
      throw new Exception("Challan {$challan['challan_number']} is already invoiced.");
    }

    $this->challanModel->markAsInvoiced($challanId, $invoiceId);

    $this->auditService->log(
      'CHALLAN_INVOICED',
      "Challan {$challan['challan_number']} converted to Invoice #{$invoiceId}",
      [
        'company_id' => $companyId,
        'user_id'    => $userId,
        'challan_id' => $challanId,
        'invoice_id' => $invoiceId,
      ]
    );

    return true;
  }

    // =========================================================================
    // CHALLAN NUMBER GENERATION
    // =========================================================================

  /**
   * Generate the next sequential challan number for a company.
   *
   * Uses CompanyModel::getNextChallanNumber() which provides thread-safe
   * generation via SELECT ... FOR UPDATE on companies.last_challan_number.
   *
   * Format: {challan_prefix}{number} (e.g., CH-1, CH-2, ...)
   * The prefix comes from companies.challan_prefix (default 'CH-').
   * The sequential number comes from companies.last_challan_number.
   *
   * MUST be called within an active DB transaction.
   *
   * @param int $companyId
   * @return string Generated challan number
   * @throws Exception
   */
  private function generateNextChallanNumber(int $companyId): string
  {
    // CompanyModel::getNextChallanNumber() uses FOR UPDATE locking
    // and auto-increments companies.last_challan_number
    $nextNumber = $this->companyModel->getNextChallanNumber($companyId);

    // Get the company's challan prefix (default: 'CH-')
    $company = $this->companyModel->find($companyId);
    $prefix  = $company['challan_prefix'] ?? 'CH-';

    $challanNumber = $prefix . str_pad((string)$nextNumber, 4, '0', STR_PAD_LEFT);

    // Final uniqueness check (defensive)
    if (!$this->challanModel->isUniqueChallanNumber($challanNumber, $companyId)) {
      throw new Exception("Generated challan number '{$challanNumber}' is not unique. Please try again.");
    }

    return $challanNumber;
  }

    // =========================================================================
    // VALIDATION
    // =========================================================================

  /**
   * Validate challan data before create/update.
   *
   * @param array $data
   * @throws Exception
   */
  private function validateChallanData(array $data): void
  {
    // Required fields
    $required = ['challan_date', 'challan_type', 'customer_type'];
    foreach ($required as $field) {
      if (empty($data[$field])) {
        throw new Exception("Field '{$field}' is required.");
      }
    }

    // Validate challan_type
    $validTypes = ['Rhodium', 'Meena', 'Wax'];
    if (!in_array($data['challan_type'], $validTypes, true)) {
      throw new Exception(
        "Invalid challan type: '{$data['challan_type']}'. Must be one of: "
          . implode(', ', $validTypes)
      );
    }

    // Validate customer_type
    $validCustomerTypes = ['Account', 'Cash'];
    if (!in_array($data['customer_type'], $validCustomerTypes, true)) {
      throw new Exception(
        "Invalid customer type: '{$data['customer_type']}'. Must be 'Account' or 'Cash'."
      );
    }

    // Validate challan_date format
    $date = date_create($data['challan_date']);
    if (!$date) {
      throw new Exception("Invalid challan date format: '{$data['challan_date']}'.");
    }

    // Validate customer ID is provided based on type
    if ($data['customer_type'] === 'Account' && empty($data['account_id'])) {
      throw new Exception('Account ID is required when customer type is Account.');
    }
    if ($data['customer_type'] === 'Cash' && empty($data['cash_customer_id'])) {
      throw new Exception('Cash Customer ID is required when customer type is Cash.');
    }
  }

  /**
   * Validate that the customer exists and belongs to the same company.
   *
   * @param string $customerType 'Account' or 'Cash'
   * @param array  $data         Must contain account_id or cash_customer_id
   * @param int    $companyId
   * @throws Exception
   */
  private function validateCustomer(string $customerType, array $data, int $companyId): void
  {
    if ($customerType === 'Account') {
      if (empty($data['account_id'])) {
        throw new Exception('Account ID is required for Account customer type.');
      }

      $account = $this->accountModel->find((int)$data['account_id']);

      if (!$account) {
        throw new Exception("Account not found: {$data['account_id']}");
      }
      if ((int)$account['company_id'] !== (int)$companyId) {
        throw new Exception("Account does not belong to your company.");
      }
      if (!empty($account['is_deleted']) && (int)$account['is_deleted'] === 1) {
        throw new Exception("Account has been deleted: {$data['account_id']}");
      }
    } elseif ($customerType === 'Cash') {
      if (empty($data['cash_customer_id'])) {
        throw new Exception('Cash Customer ID is required for Cash customer type.');
      }

      $cashCustomer = $this->cashCustomerModel->find((int)$data['cash_customer_id']);

      if (!$cashCustomer) {
        throw new Exception("Cash Customer not found: {$data['cash_customer_id']}");
      }
      if ((int)$cashCustomer['company_id'] !== (int)$companyId) {
        throw new Exception("Cash Customer does not belong to your company.");
      }
      if (!empty($cashCustomer['is_deleted']) && (int)$cashCustomer['is_deleted'] === 1) {
        throw new Exception("Cash Customer has been deleted: {$data['cash_customer_id']}");
      }
    }
  }
}

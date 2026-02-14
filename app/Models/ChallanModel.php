<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * ChallanModel
 *
 * Model for managing manufacturing job order challans.
 * Extends BaseModel for automatic company isolation and soft deletes.
 *
 * Business Context:
 * - Challans represent Rhodium, Meena, or Wax manufacturing job orders.
 * - Each challan belongs to either an Account customer or a Cash customer (XOR).
 * - Status lifecycle: Draft → Pending → In Progress → Completed → Invoiced.
 * - Challan numbers are sequential and unique per company.
 * - Once invoiced, a challan cannot be deleted or modified.
 *
 * Relationships (via joins):
 * - account_id     → accounts.id
 * - cash_customer_id → cash_customers.id
 * - invoice_id     → invoices.id
 * - created_by     → users.id
 * - challan_lines  → challan_lines.challan_id (one-to-many)
 */
class ChallanModel extends BaseModel
{
  protected $table         = 'challans';
  protected $primaryKey    = 'id';
  protected $useTimestamps = true;
  protected $returnType    = 'array';

  protected $allowedFields = [
    'company_id',
    'challan_number',
    'challan_date',
    'challan_type',
    'customer_type',
    'account_id',
    'cash_customer_id',
    'challan_status',
    'total_weight',
    'subtotal_amount',
    'tax_percent',
    'tax_amount',
    'total_amount',
    'invoice_generated',
    'invoice_id',
    'notes',
    'delivery_date',
    'created_by',
    'is_deleted'
    // created_at, updated_at handled automatically by CI4
  ];

  protected $validationRules = [
    'company_id'      => 'required|integer',
    'challan_number'  => 'required|max_length[50]',
    'challan_date'    => 'required|valid_date',
    'challan_type'    => 'required|in_list[Rhodium,Meena,Wax]',
    'customer_type'   => 'required|in_list[Account,Cash]',
    'challan_status'  => 'required|in_list[Draft,Pending,In Progress,Completed,Invoiced]',
    'total_weight'    => 'permit_empty|decimal',
    'subtotal_amount' => 'required|decimal',
    'total_amount'    => 'required|decimal',
  ];

    // =========================================================================
    // VALID STATUS TRANSITIONS
    // =========================================================================

  /**
   * Defines valid status transitions.
   * Key = current status, Value = array of allowed next statuses.
   */
  protected array $validTransitions = [
    'Draft'       => ['Pending', 'Cancelled'],
    'Pending'     => ['In Progress', 'Draft', 'Cancelled'],
    'In Progress' => ['Completed', 'Cancelled'],
    'Completed'   => ['Invoiced'],
    'Invoiced'    => [], // Terminal state — no further transitions
  ];

    // =========================================================================
    // RELATIONSHIP QUERIES
    // =========================================================================

  /**
   * Get a single challan with customer details and creator name.
   *
   * Joins:
   * - LEFT JOIN accounts        → for Account customers
   * - LEFT JOIN cash_customers  → for Cash customers
   * - JOIN users                → for created_by name
   *
   * @param int $id Challan ID
   * @return array|null Challan with customer data, or null if not found
   */
  public function getChallanWithCustomer(int $id): ?array
  {
    $this->select(
      'challans.*, '
        . 'accounts.account_name, accounts.mobile_number AS account_mobile, '
        . 'accounts.gst_number AS account_gst, '
        . 'cash_customers.customer_name, cash_customers.mobile_number AS cash_mobile, '
        . 'users.full_name AS created_by_name'
    );

    $this->join('accounts', 'accounts.id = challans.account_id', 'left');
    $this->join('cash_customers', 'cash_customers.id = challans.cash_customer_id', 'left');
    $this->join('users', 'users.id = challans.created_by', 'left');

    $this->where('challans.id', $id);

    // BaseModel::first() triggers findAll(1) which applies company filter + is_deleted
    return $this->first();
  }

  /**
   * Get a single challan with customer details AND all its line items.
   *
   * Calls getChallanWithCustomer() for header data, then fetches
   * all challan_lines rows for this challan and merges them in.
   *
   * @param int $id Challan ID
   * @return array|null Challan array with 'lines' key, or null
   */
  public function getChallanWithLines(int $id): ?array
  {
    // 1. Get challan header + customer info
    $challan = $this->getChallanWithCustomer($id);

    if (!$challan) {
      return null;
    }

    // 2. Get challan lines from challan_lines table
    $lines = $this->db->table('challan_lines')
      ->where('challan_id', $id)
      ->orderBy('line_number', 'ASC')
      ->get()
      ->getResultArray();

    // 3. Decode JSON columns in each line
    foreach ($lines as &$line) {
      if (!empty($line['product_ids']) && is_string($line['product_ids'])) {
        $line['product_ids'] = json_decode($line['product_ids'], true) ?? [];
      }
      if (!empty($line['process_ids']) && is_string($line['process_ids'])) {
        $line['process_ids'] = json_decode($line['process_ids'], true) ?? [];
      }
      if (!empty($line['process_prices']) && is_string($line['process_prices'])) {
        $line['process_prices'] = json_decode($line['process_prices'], true) ?? [];
      }
    }
    unset($line); // break reference

    $challan['lines'] = $lines;

    return $challan;
  }

    // =========================================================================
    // STATUS-BASED QUERIES
    // =========================================================================

  /**
   * Get all challans filtered by a specific status.
   *
   * @param string $status One of: Draft, Pending, In Progress, Completed, Invoiced
   * @return array
   */
  public function getChallansByStatus(string $status): array
  {
    $this->where($this->table . '.challan_status', $status);
    $this->orderBy($this->table . '.challan_date', 'DESC');

    // findAll() in BaseModel applies company filter + is_deleted = 0
    return $this->findAll();
  }

  /**
   * Get challans that are eligible for invoicing.
   *
   * Filters: status IN (Pending, In Progress, Completed), invoice_generated = FALSE.
   * Optionally filters by a specific customer.
   *
   * @param int|null    $customerId   Customer ID (account_id or cash_customer_id)
   * @param string|null $customerType 'Account' or 'Cash'
   * @return array
   */
  public function getPendingChallans(?int $customerId = null, ?string $customerType = null): array
  {
    $this->whereIn($this->table . '.challan_status', ['Pending', 'In Progress', 'Completed']);
    $this->where($this->table . '.invoice_generated', 0);

    // If specific customer requested, filter by customer type + ID
    if ($customerId !== null && $customerType !== null) {
      if ($customerType === 'Account') {
        $this->where($this->table . '.account_id', $customerId);
      } elseif ($customerType === 'Cash') {
        $this->where($this->table . '.cash_customer_id', $customerId);
      }
    }

    $this->orderBy($this->table . '.challan_date', 'ASC');

    // findAll() in BaseModel applies company filter + is_deleted = 0
    return $this->findAll();
  }

  /**
   * Get all challans for a specific customer (for customer history/ledger).
   *
   * @param int    $customerId   Customer ID
   * @param string $customerType 'Account' or 'Cash'
   * @return array
   */
  public function getChallansByCustomer(int $customerId, string $customerType): array
  {
    if ($customerType === 'Account') {
      $this->where($this->table . '.account_id', $customerId);
    } elseif ($customerType === 'Cash') {
      $this->where($this->table . '.cash_customer_id', $customerId);
    }

    $this->orderBy($this->table . '.challan_date', 'DESC');

    // findAll() in BaseModel applies company filter + is_deleted = 0
    return $this->findAll();
  }

    // =========================================================================
    // TOTALS & STATUS UPDATES
    // =========================================================================

  /**
   * Update calculated totals on a challan.
   *
   * Called by ChallanCalculationService after line items change.
   * Uses DB Builder directly to bypass model validation
   * (totals are pre-calculated by the service).
   *
   * @param int   $challanId
   * @param array $totals Keys: total_weight, subtotal_amount, tax_amount, total_amount
   * @return bool
   */
  public function updateTotals(int $challanId, array $totals): bool
  {
    $updateData = [];

    // Only set fields that are provided
    $allowedTotalFields = ['total_weight', 'subtotal_amount', 'tax_amount', 'total_amount'];
    foreach ($allowedTotalFields as $field) {
      if (array_key_exists($field, $totals)) {
        $updateData[$field] = $totals[$field];
      }
    }

    if (empty($updateData)) {
      return true; // Nothing to update
    }

    $updateData['updated_at'] = date('Y-m-d H:i:s');

    return $this->db->table($this->table)
      ->where('id', $challanId)
      ->update($updateData);
  }

  /**
   * Mark a challan as invoiced and link it to the generated invoice.
   *
   * Called within a transaction by InvoiceService after invoice creation.
   * Uses DB Builder directly for atomic update.
   *
   * @param int $challanId
   * @param int $invoiceId
   * @return bool
   */
  public function markAsInvoiced(int $challanId, int $invoiceId): bool
  {
    return $this->db->table($this->table)
      ->where('id', $challanId)
      ->update([
        'challan_status'    => 'Invoiced',
        'invoice_generated' => 1,
        'invoice_id'        => $invoiceId,
        'updated_at'        => date('Y-m-d H:i:s'),
      ]);
  }

  /**
   * Update the status of a challan.
   *
   * Uses DB Builder directly to bypass model validation overhead.
   *
   * @param int    $challanId
   * @param string $newStatus
   * @return bool
   */
  public function updateStatus(int $challanId, string $newStatus): bool
  {
    return $this->db->table($this->table)
      ->where('id', $challanId)
      ->update([
        'challan_status' => $newStatus,
        'updated_at'     => date('Y-m-d H:i:s'),
      ]);
  }

    // =========================================================================
    // VALIDATION HELPERS
    // =========================================================================

  /**
   * Check if a challan can be deleted.
   *
   * A challan CANNOT be deleted if:
   * - invoice_generated = TRUE (already converted to invoice)
   *
   * @param int $challanId
   * @return bool TRUE if can delete, FALSE if invoiced
   */
  public function canDelete(int $challanId): bool
  {
    $challan = $this->find($challanId);

    if (!$challan) {
      return false; // Not found = cannot delete
    }

    // Cannot delete if already invoiced
    if (!empty($challan['invoice_generated']) && (int)$challan['invoice_generated'] === 1) {
      return false;
    }

    return true;
  }

  /**
   * Check if a challan can be edited.
   *
   * A challan CANNOT be edited if:
   * - invoice_generated = TRUE
   * - challan_status = 'Invoiced'
   *
   * @param int $challanId
   * @return bool TRUE if can edit, FALSE otherwise
   */
  public function canEdit(int $challanId): bool
  {
    $challan = $this->find($challanId);

    if (!$challan) {
      return false;
    }

    // Cannot edit if invoiced
    if (!empty($challan['invoice_generated']) && (int)$challan['invoice_generated'] === 1) {
      return false;
    }

    if ($challan['challan_status'] === 'Invoiced') {
      return false;
    }

    return true;
  }

  /**
   * Validate whether a status transition is allowed.
   *
   * @param string $currentStatus
   * @param string $newStatus
   * @return bool
   */
  public function isValidTransition(string $currentStatus, string $newStatus): bool
  {
    if (!isset($this->validTransitions[$currentStatus])) {
      return false;
    }

    return in_array($newStatus, $this->validTransitions[$currentStatus], true);
  }

  /**
   * Check if a challan number is unique for the given company.
   *
   * @param string   $challanNumber
   * @param int      $companyId
   * @param int|null $excludeId  Challan ID to exclude (for updates)
   * @return bool TRUE if unique, FALSE if duplicate exists
   */
  public function isUniqueChallanNumber(string $challanNumber, int $companyId, ?int $excludeId = null): bool
  {
    $builder = $this->builder();
    $builder->where('company_id', $companyId);
    $builder->where('challan_number', $challanNumber);
    $builder->where('is_deleted', 0);

    if ($excludeId !== null) {
      $builder->where('id !=', $excludeId);
    }

    return $builder->countAllResults() === 0;
  }

    // =========================================================================
    // SEARCH
    // =========================================================================

  /**
   * Search challans by challan number or notes (for autocomplete / list search).
   *
   * @param string $query Search term
   * @return array Matching challans (max 20)
   */
  public function searchChallans(string $query): array
  {
    $this->groupStart();
    $this->like($this->table . '.challan_number', $query);
    $this->orLike($this->table . '.notes', $query);
    $this->groupEnd();

    $this->limit(20);

    // findAll() in BaseModel applies company filter + is_deleted = 0
    return $this->findAll();
  }

    // =========================================================================
    // USAGE CHECKS
    // =========================================================================

  /**
   * Check if a specific account has any challans (for account deletion check).
   *
   * @param int $accountId
   * @return bool TRUE if account is used in challans
   */
  public function isAccountUsed(int $accountId): bool
  {
    return $this->db->table($this->table)
      ->where('account_id', $accountId)
      ->where('is_deleted', 0)
      ->countAllResults() > 0;
  }

  /**
   * Check if a specific cash customer has any challans (for deletion check).
   *
   * @param int $cashCustomerId
   * @return bool TRUE if cash customer is used in challans
   */
  public function isCashCustomerUsed(int $cashCustomerId): bool
  {
    return $this->db->table($this->table)
      ->where('cash_customer_id', $cashCustomerId)
      ->where('is_deleted', 0)
      ->countAllResults() > 0;
  }
}

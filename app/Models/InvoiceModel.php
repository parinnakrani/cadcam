<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * InvoiceModel
 * 
 * Handles invoice data operations with automatic payment tracking,
 * multi-tenant filtering, and relationship management.
 * 
 * Business Rules:
 * - Multi-tenant isolation via company_id
 * - Soft delete (is_deleted flag)
 * - Automatic payment status calculation
 * - Cannot delete invoices with payments
 * - Invoice status workflow: Draft → Posted → Partially Paid → Paid → Delivered → Closed
 */
class InvoiceModel extends Model
{
  protected $table            = 'invoices';
  protected $primaryKey       = 'id';
  protected $useAutoIncrement = true;
  protected $returnType       = 'array';
  protected $useSoftDeletes   = false; // We handle soft deletes manually
  protected $protectFields    = true;

  protected $allowedFields = [
    'company_id',
    'invoice_number',
    'invoice_type',
    'invoice_date',
    'due_date',
    'account_id',
    'cash_customer_id',
    'billing_address',
    'shipping_address',
    'reference_number',
    'challan_ids',
    'subtotal',
    'tax_rate',
    'tax_amount',
    'cgst_amount',
    'sgst_amount',
    'igst_amount',
    'grand_total',
    'total_paid',
    'amount_due',
    'invoice_status',
    'payment_status',
    'gold_adjustment_applied',
    'gold_adjustment_date',
    'gold_adjustment_amount',
    'gold_rate_used',
    'notes',
    'terms_conditions',
    'created_by',
    'updated_by',
    'is_deleted'
  ];

  protected $useTimestamps = true;
  protected $createdField  = 'created_at';
  protected $updatedField  = 'updated_at';

  protected $validationRules = [
    'company_id'      => 'required|integer',
    'invoice_number'  => 'required|max_length[50]',
    'invoice_type'    => 'required|in_list[Accounts Invoice,Cash Invoice,Wax Invoice]',
    'invoice_date'    => 'required|valid_date',
    'subtotal'        => 'required|decimal',
    'tax_rate'        => 'required|decimal',
    'grand_total'     => 'required|decimal',
    'invoice_status'  => 'required|in_list[Draft,Posted,Partially Paid,Paid,Delivered,Closed]',
    'payment_status'  => 'required|in_list[Pending,Partial Paid,Paid]',
    'created_by'      => 'required|integer',
  ];

  protected $validationMessages = [
    'company_id' => [
      'required' => 'Company ID is required',
    ],
    'invoice_number' => [
      'required' => 'Invoice number is required',
    ],
    'invoice_type' => [
      'required' => 'Invoice type is required',
      'in_list'  => 'Invalid invoice type',
    ],
    'invoice_date' => [
      'required'   => 'Invoice date is required',
      'valid_date' => 'Invalid invoice date format',
    ],
  ];

  protected $skipValidation = false;
  protected $cleanValidationRules = true;

  // Callbacks
  protected $allowCallbacks = true;
  protected $beforeInsert   = ['applyCompanyFilter'];
  protected $beforeUpdate   = ['applyCompanyFilter'];
  protected $beforeFind     = ['applyCompanyFilter'];

  /**
   * Apply company filter automatically for multi-tenant isolation
   * 
   * @param array $data
   * @return array
   */
  protected function applyCompanyFilter(array $data): array
  {
    $session = session();
    $companyId = $session->get('company_id');

    if ($companyId && isset($data['data'])) {
      // For insert/update operations
      if (!isset($data['data']['company_id'])) {
        $data['data']['company_id'] = $companyId;
      }
    } elseif ($companyId && !isset($data['id'])) {
      // For find operations
      $this->where($this->table . '.company_id', $companyId);
    }

    return $data;
  }

  /**
   * Override findAll to apply company filter and exclude soft-deleted records
   * 
   * @param int $limit
   * @param int $offset
   * @return array
   */
  public function findAll(int $limit = 0, int $offset = 0): array
  {
    $session = session();
    $companyId = $session->get('company_id');

    $builder = $this->builder();

    if ($companyId) {
      $builder->where($this->table . '.company_id', $companyId);
    }

    $builder->where($this->table . '.is_deleted', 0);

    if ($limit > 0) {
      $builder->limit($limit, $offset);
    }

    return $builder->get()->getResultArray();
  }

  /**
   * Get invoice with customer details
   * Joins either accounts or cash_customers table based on invoice type
   * 
   * @param int $id Invoice ID
   * @return array|null Invoice with customer data
   */
  public function getInvoiceWithCustomer(int $id): ?array
  {
    $session = session();
    $companyId = $session->get('company_id');

    $builder = $this->db->table('invoices');
    $builder->select('invoices.*');
    $builder->where('invoices.id', $id);
    $builder->where('invoices.is_deleted', 0);

    if ($companyId) {
      $builder->where('invoices.company_id', $companyId);
    }

    $invoice = $builder->get()->getRowArray();

    if (!$invoice) {
      return null;
    }

    // Join customer data based on invoice type
    if ($invoice['account_id']) {
      // Get account customer
      $accountBuilder = $this->db->table('accounts');
      $accountBuilder->select('
                id as customer_id,
                account_code,
                account_name as customer_name,
                business_name,
                contact_person,
                mobile,
                email,
                billing_address_line1,
                billing_address_line2,
                billing_city,
                billing_state_id,
                billing_pincode,
                gst_number,
                pan_number,
                "Account" as customer_type
            ');
      $accountBuilder->where('id', $invoice['account_id']);
      $customer = $accountBuilder->get()->getRowArray();

      if ($customer) {
        $invoice['customer'] = $customer;
      }
    } elseif ($invoice['cash_customer_id']) {
      // Get cash customer
      $cashBuilder = $this->db->table('cash_customers');
      $cashBuilder->select('
                id as customer_id,
                customer_name,
                mobile_number as mobile,
                email,
                address_line1,
                address_line2,
                city,
                state_id,
                pincode,
                "Cash" as customer_type
            ');
      $cashBuilder->where('id', $invoice['cash_customer_id']);
      $customer = $cashBuilder->get()->getRowArray();

      if ($customer) {
        $invoice['customer'] = $customer;
      }
    }

    return $invoice;
  }

  /**
   * Get invoice with all line items and customer details
   * 
   * @param int $id Invoice ID
   * @return array|null Complete invoice data with lines and customer
   */
  public function getInvoiceWithLines(int $id): ?array
  {
    // Get invoice with customer
    $invoice = $this->getInvoiceWithCustomer($id);

    if (!$invoice) {
      return null;
    }

    // Get invoice lines
    $linesBuilder = $this->db->table('invoice_lines');
    $linesBuilder->select('invoice_lines.*');
    $linesBuilder->where('invoice_id', $id);
    $linesBuilder->orderBy('line_number', 'ASC');
    $lines = $linesBuilder->get()->getResultArray();

    // Decode JSON fields in lines
    foreach ($lines as &$line) {
      if (isset($line['product_ids']) && is_string($line['product_ids'])) {
        $line['product_ids'] = json_decode($line['product_ids'], true);
      }
      if (isset($line['products_json']) && is_string($line['products_json'])) {
        $line['products_json'] = json_decode($line['products_json'], true);
      }
      if (isset($line['process_ids']) && is_string($line['process_ids'])) {
        $line['process_ids'] = json_decode($line['process_ids'], true);
      }
      if (isset($line['processes_json']) && is_string($line['processes_json'])) {
        $line['processes_json'] = json_decode($line['processes_json'], true);
      }
      if (isset($line['process_prices']) && is_string($line['process_prices'])) {
        $line['process_prices'] = json_decode($line['process_prices'], true);
      }

      // Populate product names
      $line['products'] = [];
      if (!empty($line['product_ids']) && is_array($line['product_ids'])) {
        $productModel = new \App\Models\ProductModel();
        $line['products'] = $productModel->select('id, product_name')->whereIn('id', $line['product_ids'])->findAll();
      }

      // Populate process names
      $line['processes'] = [];
      if (!empty($line['process_ids']) && is_array($line['process_ids'])) {
        $processModel = new \App\Models\ProcessModel();
        $line['processes'] = $processModel->select('id, process_name')->whereIn('id', $line['process_ids'])->findAll();
      }
    }

    $invoice['lines'] = $lines;

    // Decode challan_ids if present
    if (isset($invoice['challan_ids']) && is_string($invoice['challan_ids'])) {
      $invoice['challan_ids'] = json_decode($invoice['challan_ids'], true);
    }

    return $invoice;
  }

  /**
   * Update payment status based on amount paid
   * 
   * Business Rules:
   * - amount_due = grand_total - total_paid
   * - payment_status: Pending (0 paid), Partial Paid (0 < paid < total), Paid (paid >= total)
   * - If fully paid, update invoice_status to 'Paid'
   * 
   * @param int $invoiceId Invoice ID
   * @param float $totalPaid Total amount paid (cumulative)
   * @return bool Success status
   */
  public function updatePaymentStatus(int $invoiceId, float $totalPaid): bool
  {
    // Get current invoice
    $invoice = $this->find($invoiceId);

    if (!$invoice) {
      return false;
    }

    $grandTotal = (float) $invoice['grand_total'];
    $amountDue = $grandTotal - $totalPaid;

    // Ensure amount_due is not negative
    if ($amountDue < 0) {
      $amountDue = 0;
    }

    // Determine payment status
    if ($amountDue == 0 || $totalPaid >= $grandTotal) {
      $paymentStatus = 'Paid';
      $invoiceStatus = 'Paid'; // Auto-update invoice status when fully paid
    } elseif ($totalPaid > 0) {
      $paymentStatus = 'Partial Paid';
      $invoiceStatus = $invoice['invoice_status']; // Keep current status

      // If current status is Draft, move to Posted
      if ($invoiceStatus === 'Draft') {
        $invoiceStatus = 'Posted';
      }
    } else {
      $paymentStatus = 'Pending';
      $invoiceStatus = $invoice['invoice_status']; // Keep current status
    }

    // Update invoice
    $updateData = [
      'total_paid'      => $totalPaid,
      'amount_due'      => $amountDue,
      'payment_status'  => $paymentStatus,
      'invoice_status'  => $invoiceStatus,
      'updated_by'      => session()->get('user_id'),
    ];

    return $this->update($invoiceId, $updateData);
  }

  /**
   * Get outstanding invoices (unpaid or partially paid)
   * 
   * @param int|null $customerId Customer ID (account_id or cash_customer_id)
   * @param string|null $customerType 'Account' or 'Cash'
   * @return array List of outstanding invoices
   */
  public function getOutstandingInvoices(?int $customerId = null, ?string $customerType = null): array
  {
    $session = session();
    $companyId = $session->get('company_id');

    $builder = $this->db->table('invoices');
    $builder->select('invoices.*');
    $builder->where('invoices.amount_due >', 0);
    $builder->where('invoices.payment_status !=', 'Paid');
    $builder->where('invoices.is_deleted', 0);

    if ($companyId) {
      $builder->where('invoices.company_id', $companyId);
    }

    // Apply customer filter if provided
    if ($customerId && $customerType) {
      if ($customerType === 'Account') {
        $builder->where('invoices.account_id', $customerId);
      } elseif ($customerType === 'Cash') {
        $builder->where('invoices.cash_customer_id', $customerId);
      }
    }

    $builder->orderBy('invoices.due_date', 'ASC');
    $builder->orderBy('invoices.invoice_date', 'ASC');

    return $builder->get()->getResultArray();
  }

  /**
   * Check if invoice can be deleted
   * 
   * Business Rule: Cannot delete invoices with payments
   * 
   * @param int $invoiceId Invoice ID
   * @return bool True if can delete, false otherwise
   */
  public function canDelete(int $invoiceId): bool
  {
    $invoice = $this->find($invoiceId);

    if (!$invoice) {
      return false;
    }

    // Check if any payment has been made
    $totalPaid = (float) $invoice['total_paid'];

    return $totalPaid == 0;
  }

  /**
   * Mark invoice as delivered
   * 
   * @param int $invoiceId Invoice ID
   * @return bool Success status
   */
  public function markAsDelivered(int $invoiceId): bool
  {
    $invoice = $this->find($invoiceId);

    if (!$invoice) {
      return false;
    }

    // Update invoice status
    $updateData = [
      'invoice_status' => 'Delivered',
      'updated_by'     => session()->get('user_id'),
    ];

    return $this->update($invoiceId, $updateData);
  }

  /**
   * Soft delete invoice
   * 
   * Business Rule: Cannot delete invoices with payments
   * 
   * @param int $id Invoice ID
   * @param bool $purge Not used (we always soft delete)
   * @return bool Success status
   */
  public function delete($id = null, bool $purge = false): bool
  {
    // Check if can delete
    if (!$this->canDelete($id)) {
      return false;
    }

    // Soft delete
    $updateData = [
      'is_deleted' => 1,
      'updated_by' => session()->get('user_id'),
    ];

    return $this->update($id, $updateData);
  }

  /**
   * Get invoices by status
   * 
   * @param string $status Invoice status
   * @return array List of invoices
   */
  public function getInvoicesByStatus(string $status): array
  {
    $session = session();
    $companyId = $session->get('company_id');

    $builder = $this->builder();
    $builder->where('invoice_status', $status);
    $builder->where('is_deleted', 0);

    if ($companyId) {
      $builder->where($this->table . '.company_id', $companyId);
    }

    $builder->orderBy('invoice_date', 'DESC');

    return $builder->get()->getResultArray();
  }

  /**
   * Get invoices by payment status
   * 
   * @param string $paymentStatus Payment status
   * @return array List of invoices
   */
  public function getInvoicesByPaymentStatus(string $paymentStatus): array
  {
    $session = session();
    $companyId = $session->get('company_id');

    $builder = $this->builder();
    $builder->where('payment_status', $paymentStatus);
    $builder->where('is_deleted', 0);

    if ($companyId) {
      $builder->where($this->table . '.company_id', $companyId);
    }

    $builder->orderBy('invoice_date', 'DESC');

    return $builder->get()->getResultArray();
  }

  /**
   * Get invoices by date range
   * 
   * @param string $startDate Start date (Y-m-d)
   * @param string $endDate End date (Y-m-d)
   * @return array List of invoices
   */
  public function getInvoicesByDateRange(string $startDate, string $endDate): array
  {
    $session = session();
    $companyId = $session->get('company_id');

    $builder = $this->builder();
    $builder->where('invoice_date >=', $startDate);
    $builder->where('invoice_date <=', $endDate);
    $builder->where('is_deleted', 0);

    if ($companyId) {
      $builder->where($this->table . '.company_id', $companyId);
    }

    $builder->orderBy('invoice_date', 'DESC');

    return $builder->get()->getResultArray();
  }

  /**
   * Get total sales for a date range
   * 
   * @param string $startDate Start date (Y-m-d)
   * @param string $endDate End date (Y-m-d)
   * @return float Total sales amount
   */
  public function getTotalSales(string $startDate, string $endDate): float
  {
    $session = session();
    $companyId = $session->get('company_id');

    $builder = $this->db->table('invoices');
    $builder->selectSum('grand_total', 'total_sales');
    $builder->where('invoice_date >=', $startDate);
    $builder->where('invoice_date <=', $endDate);
    $builder->where('is_deleted', 0);
    $builder->whereIn('invoice_status', ['Posted', 'Partially Paid', 'Paid', 'Delivered', 'Closed']);

    if ($companyId) {
      $builder->where($this->table . '.company_id', $companyId);
    }

    $result = $builder->get()->getRowArray();

    return (float) ($result['total_sales'] ?? 0);
  }

  /**
   * Get total outstanding amount
   * 
   * @return float Total outstanding amount
   */
  public function getTotalOutstanding(): float
  {
    $session = session();
    $companyId = $session->get('company_id');

    $builder = $this->db->table('invoices');
    $builder->selectSum('amount_due', 'total_outstanding');
    $builder->where('amount_due >', 0);
    $builder->where('payment_status !=', 'Paid');
    $builder->where('is_deleted', 0);

    if ($companyId) {
      $builder->where($this->table . '.company_id', $companyId);
    }

    $result = $builder->get()->getRowArray();

    return (float) ($result['total_outstanding'] ?? 0);
  }
}

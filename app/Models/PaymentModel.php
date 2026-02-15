<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
  protected $table            = 'payments';
  protected $primaryKey       = 'id';
  protected $useAutoIncrement = true;
  protected $returnType       = 'array';
  protected $useSoftDeletes   = false; // Handling is_deleted manually as per instructions
  protected $protectFields    = true;
  protected $allowedFields    = [
    'company_id',
    'payment_number',
    'invoice_id',
    'customer_type',
    'account_id',
    'cash_customer_id',
    'payment_date',
    'payment_amount',
    'payment_mode',
    'cheque_number',
    'cheque_date',
    'bank_name',
    'transaction_reference',
    'notes',
    'received_by',
    'is_deleted',
    'created_at',
    'updated_at'
  ];

  protected $useTimestamps = true;
  protected $createdField  = 'created_at';
  protected $updatedField  = 'updated_at';

  protected $validationRules = [
    'payment_date'   => 'required|valid_date',
    'invoice_id'     => 'required|integer',
    'payment_amount' => 'required|decimal|greater_than[0]',
    'payment_mode'   => 'required|in_list[Cash,Cheque,Bank Transfer,UPI,Card,Other]'
  ];

  protected $validationMessages = [];
  protected $skipValidation     = false;

  /**
   * Get all payments for a specific invoice
   */
  public function getPaymentsByInvoice(int $invoiceId): array
  {
    $this->applyCompanyFilter();
    return $this->where('invoice_id', $invoiceId)
      ->where('is_deleted', 0)
      ->orderBy('payment_date', 'ASC')
      ->findAll();
  }

  /**
   * Calculate total amount paid for an invoice
   */
  public function getTotalPaidForInvoice(int $invoiceId): float
  {
    $this->applyCompanyFilter();
    $result = $this->selectSum('payment_amount')
      ->where('invoice_id', $invoiceId)
      ->where('is_deleted', 0)
      ->first();

    return (float) ($result['payment_amount'] ?? 0.00);
  }

  /**
   * Get payments by customer (Account or Cash Customer)
   */
  public function getPaymentsByCustomer(int $customerId, string $customerType, $fromDate = null, $toDate = null): array
  {
    $this->applyCompanyFilter();

    // Filter by customer type
    if ($customerType === 'Account') {
      $this->where('customer_type', 'Account')
        ->where('account_id', $customerId);
    } else {
      $this->where('customer_type', 'Cash')
        ->where('cash_customer_id', $customerId);
    }

    // Optional date range
    if ($fromDate) {
      $this->where('payment_date >=', $fromDate);
    }
    if ($toDate) {
      $this->where('payment_date <=', $toDate);
    }

    return $this->where('is_deleted', 0)
      ->orderBy('payment_date', 'DESC')
      ->findAll();
  }

  /**
   * Apply company_id filter from session
   */
  public function applyCompanyFilter()
  {
    if (session()->has('company_id')) {
      $this->where('company_id', session()->get('company_id'));
    }
  }
}

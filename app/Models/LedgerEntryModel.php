<?php

namespace App\Models;

use CodeIgniter\Model;

class LedgerEntryModel extends Model
{
  protected $table            = 'ledger_entries';
  protected $primaryKey       = 'id';
  protected $useAutoIncrement = true;
  protected $returnType       = 'array';
  protected $useSoftDeletes   = false; // Ledger is append-only
  protected $protectFields    = true;
  protected $allowedFields    = [
    'company_id',
    'account_id',
    'cash_customer_id',
    'entry_date',
    'reference_type',
    'reference_id',
    'reference_number',
    'description',
    'debit_amount',
    'credit_amount',
    'balance_after',
    'created_at',
  ];

  // Dates
  protected $useTimestamps = false; // We manage created_at manually or via DB default
  protected $dateFormat    = 'datetime';
  protected $createdField  = 'created_at';
  protected $updatedField  = '';
  protected $deletedField  = '';

  // Validation
  protected $validationRules      = [
    'company_id'      => 'required|integer',
    'entry_date'      => 'required|valid_date',
    'reference_type'  => 'required|in_list[opening_balance,invoice,payment,gold_adjustment]',
    'debit_amount'    => 'required|decimal',
    'credit_amount'   => 'required|decimal',
  ];
  protected $validationMessages   = [];
  protected $skipValidation       = false;
  protected $cleanValidationRules = true;

  /**
   * Apply automatic company_id filtering based on session.
   * 
   * @return $this
   */
  protected function applyCompanyFilter(): self
  {
    $session = session();
    $isSuperAdmin = $session->get('is_super_admin');
    $companyId    = $session->get('company_id');

    if (!$isSuperAdmin && !empty($companyId)) {
      $this->where('company_id', $companyId);
    }

    return $this;
  }

  /**
   * Override findAll to apply company filter.
   */
  public function findAll(int $limit = 0, int $offset = 0)
  {
    $this->applyCompanyFilter();
    return parent::findAll($limit, $offset);
  }

  /**
   * Get ledger entries for a specific account.
   */
  public function getLedgerForAccount(int $accountId, string $fromDate = null, string $toDate = null): array
  {
    $this->applyCompanyFilter();
    $this->where('account_id', $accountId);

    if ($fromDate) {
      $this->where('entry_date >=', $fromDate);
    }
    if ($toDate) {
      $this->where('entry_date <=', $toDate);
    }

    return $this->orderBy('entry_date', 'ASC')
      ->orderBy('id', 'ASC')
      ->findAll();
  }

  /**
   * Get ledger entries for a specific cash customer.
   */
  public function getLedgerForCashCustomer(int $cashCustomerId, string $fromDate = null, string $toDate = null): array
  {
    $this->applyCompanyFilter();
    $this->where('cash_customer_id', $cashCustomerId);

    if ($fromDate) {
      $this->where('entry_date >=', $fromDate);
    }
    if ($toDate) {
      $this->where('entry_date <=', $toDate);
    }

    return $this->orderBy('entry_date', 'ASC')
      ->orderBy('id', 'ASC')
      ->findAll();
  }

  /**
   * Calculate opening balance (SUM(debit) - SUM(credit)) before a specific date.
   */
  public function getOpeningBalance(int $customerId, string $customerType, string $beforeDate): float
  {
    $this->applyCompanyFilter();

    if ($customerType === 'Account') {
      $this->where('account_id', $customerId);
    } elseif ($customerType === 'Cash') {
      $this->where('cash_customer_id', $customerId);
    } else {
      return 0.00;
    }

    $this->where('entry_date <', $beforeDate);

    // Calculate totals
    $result = $this->selectSum('debit_amount')
      ->selectSum('credit_amount')
      ->first();

    if (!$result) {
      return 0.00;
    }

    $totalDebit  = (float)($result['debit_amount'] ?? 0);
    $totalCredit = (float)($result['credit_amount'] ?? 0);

    return $totalDebit - $totalCredit;
  }

  /**
   * Calculate current balance (SUM(debit) - SUM(credit)) for all time.
   */
  public function getCurrentBalance(int $customerId, string $customerType): float
  {
    $this->applyCompanyFilter();

    if ($customerType === 'Account') {
      $this->where('account_id', $customerId);
    } elseif ($customerType === 'Cash') {
      $this->where('cash_customer_id', $customerId);
    } else {
      return 0.00;
    }

    // Calculate totals
    $result = $this->selectSum('debit_amount')
      ->selectSum('credit_amount')
      ->first();

    if (!$result) {
      return 0.00;
    }

    $totalDebit  = (float)($result['debit_amount'] ?? 0);
    $totalCredit = (float)($result['credit_amount'] ?? 0);

    return $totalDebit - $totalCredit;
  }
}

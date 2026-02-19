<?php

namespace App\Models;

class CashCustomerModel extends BaseModel
{
  protected $table            = 'cash_customers';
  protected $primaryKey       = 'id';
  protected $useAutoIncrement = true;
  protected $returnType       = 'array';
  protected $useSoftDeletes   = false;
  protected $protectFields    = true;

  // Allowed Fields
  protected $allowedFields    = [
    'company_id',
    'customer_name',
    'mobile_number',
    'mobile',
    'email',
    'address_line1',
    'address_line2',
    'city',
    'state_id',
    'pincode',
    'notes',
    'is_active',
    'is_deleted',
    'current_balance'
  ];

  // Dates
  protected $useTimestamps = true;
  protected $dateFormat    = 'datetime';
  protected $createdField  = 'created_at';
  protected $updatedField  = 'updated_at';
  protected $deletedField  = 'is_deleted';

  // Validation
  protected $validationRules      = [
    'customer_name' => 'required|min_length[3]|max_length[255]',
    'mobile_number' => 'required|regex_match[/^[0-9]{10}$/]', // Assuming 10 digits
    'email'         => 'permit_empty|valid_email'
  ];
  protected $validationMessages   = [];
  protected $skipValidation       = false;
  protected $cleanValidationRules = true;

  // Cash customers are shared across all companies - no company_id filter
  // Override BaseModel methods to skip company filter

  /**
   * Override findAll to skip company filter for cash customers.
   */
  public function findAll(int $limit = 0, int $offset = 0)
  {
    // Skip applyCompanyFilter(), only apply is_deleted check
    $this->where($this->table . '.is_deleted', 0);
    return \CodeIgniter\Model::findAll($limit, $offset);
  }

  /**
   * Override find to skip company filter for cash customers.
   */
  public function find($id = null)
  {
    // Skip applyCompanyFilter(), only apply is_deleted check
    $this->where($this->table . '.is_deleted', 0);
    return \CodeIgniter\Model::find($id);
  }

  /**
   * Get active cash customers.
   * 
   * @return array
   */
  public function getActiveCashCustomers(): array
  {
    $this->where('is_active', 1);
    $this->orderBy('customer_name', 'ASC');

    return $this->findAll();
  }

  /**
   * Find cash customer by name and mobile (Deduplication).
   * 
   * @param string $name
   * @param string $mobile
   * @return array|null
   */
  public function findByNameAndMobile(string $name, string $mobile): ?array
  {
    $this->where('customer_name', $name);
    $this->where('mobile_number', $mobile);
    $this->where('is_deleted', 0);

    return $this->first();
  }

  /**
   * Search cash customers for autocomplete.
   * 
   * @param string $query
   * @return array
   */
  public function searchCashCustomers(string $query): array
  {
    $this->where('is_active', 1);

    $this->groupStart();
    $this->like('customer_name', $query);
    $this->orLike('mobile_number', $query);
    $this->groupEnd();

    $this->limit(20);

    return $this->findAll();
  }

  /**
   * Check if cash customer is used in transactions.
   * 
   * @param int $customerId
   * @return bool
   */
  public function isCashCustomerUsedInTransactions(int $customerId): bool
  {
    try {
      // Check Invoices (if 'cash_customer_id' column exists) -> Invoices usually link to 'accounts' OR 'cash_customers'.
      // Assuming separate column or logic.
      // If invoices table doesn't have cash_customer_id, this check fails or returns false.
      // I'll check 'invoices' table.
      $invoices = $this->db->table('invoices')->where('cash_customer_id', $customerId)->countAllResults();
      if ($invoices > 0) return true;

      // Check Challans
      $challans = $this->db->table('challans')->where('cash_customer_id', $customerId)->countAllResults();
      if ($challans > 0) return true;
    } catch (\Throwable $e) {
      // Tables or columns might not exist yet
      return false;
    }

    return false;
  }
}

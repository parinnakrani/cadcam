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
    'is_deleted'
  ];

  // Dates
  protected $useTimestamps = true;
  protected $dateFormat    = 'datetime';
  protected $createdField  = 'created_at';
  protected $updatedField  = 'updated_at';
  protected $deletedField  = 'is_deleted';

  // Validation
  protected $validationRules      = [
    'company_id'    => 'required|integer',
    'customer_name' => 'required|min_length[3]|max_length[255]',
    'mobile_number' => 'required|regex_match[/^[0-9]{10}$/]', // Assuming 10 digits
    'email'         => 'permit_empty|valid_email'
  ];
  protected $validationMessages   = [];
  protected $skipValidation       = false;
  protected $cleanValidationRules = true;
    
    // BaseModel handles findAll/find company filtering and is_deleted check.

  /**
   * Get active cash customers.
   * 
   * @return array
   */
  public function getActiveCashCustomers(): array
  {
    $this->applyCompanyFilter();
    $this->where('is_active', 1);
    $this->where('is_deleted', 0);
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
    $this->applyCompanyFilter();
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
    $this->applyCompanyFilter();
    $this->where('is_active', 1);
    $this->where('is_deleted', 0);

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

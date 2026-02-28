<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * GoldRateModel
 *
 * Model for managing daily gold rates.
 * Extends BaseModel for company isolation and soft delete handling.
 */
class GoldRateModel extends BaseModel
{
  protected $table            = 'gold_rates';
  protected $primaryKey       = 'id';
  protected $useAutoIncrement = true;
  protected $returnType       = 'array'; // Return arrays by default
  protected $useSoftDeletes   = false; // We manage is_deleted manually via BaseModel logic

  // Allowed fields
  protected $allowedFields    = [
    'company_id',
    'rate_date',
    'metal_type',
    'rate_per_gram',
    'created_by',
    'updated_by',
    'is_deleted'
    // timestamp fields created_at/updated_at handled automatically
  ];

  // Dates
  protected $useTimestamps = true;
  protected $dateFormat    = 'datetime';
  protected $createdField  = 'created_at';
  protected $updatedField  = 'updated_at';
  protected $deletedField  = 'deleted_at'; // Not used in this schema, we user is_deleted bool

  // Validation
  protected $validationRules      = [
    'company_id'    => 'required|integer',
    'rate_date'     => 'required|valid_date',
    'metal_type'    => 'required|in_list[22K,24K,Silver]',
    'rate_per_gram' => 'required|decimal|greater_than[0]',
  ];

  protected $validationMessages   = [];
  protected $skipValidation       = false;

  /**
   * Get the latest gold rate for a specific metal type.
   *
   * @param int $companyId
   * @param string $metalType
   * @return float|null
   */
  public function getLatestRate(int $companyId, string $metalType = '22K'): ?float
  {
    $result = $this->where('company_id', $companyId)
      ->where('metal_type', $metalType)
      ->where('is_deleted', 0)
      ->orderBy('rate_date', 'DESC')
      ->orderBy('created_at', 'DESC')
      ->first(); // Returns array or null based on returnType

    return $result ? (float) $result['rate_per_gram'] : null;
  }

  /**
   * Get gold rate for a specific date.
   * If no rate exists for that date, fetch the most recent rate prior to that date.
   *
   * @param int $companyId
   * @param string $date (Y-m-d)
   * @param string $metalType
   * @return float|null
   */
  public function getRateByDate(int $companyId, string $date, string $metalType = '22K'): ?float
  {
    // First try to find exact date match
    $exact = $this->where('company_id', $companyId)
      ->where('rate_date', $date)
      ->where('metal_type', $metalType)
      ->where('is_deleted', 0)
      ->first();

    if ($exact) {
      return (float) $exact['rate_per_gram'];
    }

    // If not found, find latest rate BEFORE this date
    $previous = $this->where('company_id', $companyId)
      ->where('rate_date <=', $date)
      ->where('metal_type', $metalType)
      ->where('is_deleted', 0)
      ->orderBy('rate_date', 'DESC')
      ->orderBy('created_at', 'DESC')
      ->first();

    return $previous ? (float) $previous['rate_per_gram'] : null;
  }

  /**
   * Get complete rate history for a company within a date range.
   *
   * @param int $companyId
   * @param string $fromDate
   * @param string $toDate
   * @return array
   */
  public function getRateHistory(int $companyId, string $fromDate, string $toDate): array
  {
    return $this->select('gold_rates.*, users.full_name as user_name')
      ->join('users', 'users.id = gold_rates.created_by', 'left')
      ->where('gold_rates.company_id', $companyId)
      ->where('gold_rates.rate_date >=', $fromDate)
      ->where('gold_rates.rate_date <=', $toDate)
      ->where('gold_rates.is_deleted', 0)
      ->orderBy('gold_rates.rate_date', 'DESC')
      ->orderBy('gold_rates.created_at', 'DESC')
      ->findAll();
  }

  /**
   * Check if a rate entry already exists for a specific date and metal type.
   *
   * @param int $companyId
   * @param string $date
   * @param string $metalType
   * @return bool
   */
  public function checkRateExists(int $companyId, string $date, string $metalType): bool
  {
    $count = $this->where('company_id', $companyId)
      ->where('rate_date', $date)
      ->where('metal_type', $metalType)
      ->where('is_deleted', 0)
      ->countAllResults();

    return $count > 0;
  }
}

<?php

namespace App\Services\Master;

use App\Models\GoldRateModel;
use App\Services\Audit\AuditService;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Events\Events;

/**
 * GoldRateService
 *
 * Service for managing daily gold rates and history.
 */
class GoldRateService
{
  protected $goldRateModel;
  protected $auditService;
  protected $db;

  public function __construct()
  {
    $this->goldRateModel = new GoldRateModel();
    $this->auditService = new AuditService();
    $this->db = \Config\Database::connect();
  }

  /**
   * Create a new gold rate entry.
   *
   * @param array $data
   * @return int rate_id
   * @throws \Exception
   */
  public function createRate(array $data): int
  {
    $session = session();
    $companyId = $session->get('company_id');

    if (empty($companyId)) {
      throw new \Exception("Company ID not found in session.");
    }

    // Note: Multiple entries per day per metal type are allowed.
    // The most recent entry (by created_at) is used as the active rate.

    // Prepare data with company_id
    $insertData = [
      'company_id'    => $companyId,
      'rate_date'     => $data['rate_date'],
      'metal_type'    => $data['metal_type'],
      'rate_per_gram' => $data['rate_per_gram'],
      'created_by'    => $session->get('user_id') ?? 1, // Capture user if available
      'is_deleted'    => 0,
      'created_at'    => date('Y-m-d H:i:s')
    ];

    // Service transaction
    $this->db->transStart();

    try {
      $rateId = $this->goldRateModel->insert($insertData);

      if (!$rateId) {
        // Check for validation errors from model
        $errors = $this->goldRateModel->errors();
        throw new \Exception(implode(', ', $errors));
      }

      // Audit Log
      $this->auditService->logCreate('Master', 'GoldRate', $rateId, $insertData);

      $this->db->transComplete();

      if ($this->db->transStatus() === false) {
        throw new DatabaseException("Transaction failed while creating gold rate.");
      }

      return $rateId;
    } catch (\Exception $e) {
      $this->db->transRollback();
      log_message('error', '[GoldRateService::createRate] ' . $e->getMessage());
      throw $e;
    }
  }

  /**
   * Update an existing gold rate.
   *
   * @param int $id
   * @param array $data
   * @return bool
   * @throws \Exception
   */
  public function updateRate(int $id, array $data): bool
  {
    $session = session();
    $companyId = $session->get('company_id');

    // Check if rate exists and belongs to company
    $rate = $this->goldRateModel->find($id);
    if (!$rate || $rate['company_id'] != $companyId || $rate['is_deleted'] == 1) {
      throw new \Exception("Gold rate not found or access denied.");
    }

    // Validate: cannot change date to future (though usually date isn't editable, assume it might be)
    if (isset($data['rate_date'])) {
      $today = date('Y-m-d');
      if ($data['rate_date'] > $today) {
        throw new \Exception("Cannot set rate date to future.");
      }
    }

    /* 
           TODO: Check if rate is used in any invoice. 
           This requires InvoiceModel. Since Invoice logic is complex and might not be fully implemented in this context,
           we will assume a placeholder check or skip for now based on current instructions focusing on Master data.
           Ideally: InvoiceService->isRateUsed($rate['rate_date'], $rate['metal_type'], $rate['rate_per_gram'])
        */

    $updateData = [
      'rate_per_gram' => $data['rate_per_gram'],
      'updated_by'    => $session->get('user_id') ?? 0,
      'updated_at'    => date('Y-m-d H:i:s')
    ];

    // Allow updating metal type or date if provided, though typically locked
    if (isset($data['metal_type'])) $updateData['metal_type'] = $data['metal_type'];
    if (isset($data['rate_date'])) $updateData['rate_date'] = $data['rate_date'];

    $this->db->transStart();

    try {
      $this->goldRateModel->update($id, $updateData);

      // Audit Log
      $this->auditService->logUpdate('Master', 'GoldRate', $id, $rate, $updateData);

      $this->db->transComplete();

      return $this->db->transStatus();
    } catch (\Exception $e) {
      $this->db->transRollback();
      log_message('error', '[GoldRateService::updateRate] ' . $e->getMessage());
      throw $e;
    }
  }

  /**
   * Get the latest rate for today (or most recent).
   *
   * @param string $metalType
   * @return float|null
   */
  public function getLatestRate(string $metalType = '22K'): ?float
  {
    $companyId = session()->get('company_id');
    if (!$companyId) return null;

    return $this->goldRateModel->getLatestRate($companyId, $metalType);
  }

  /**
   * Get rate for a specific date.
   *
   * @param string $date
   * @param string $metalType
   * @return float|null
   */
  public function getRateByDate(string $date, string $metalType = '22K'): ?float
  {
    $companyId = session()->get('company_id');
    if (!$companyId) return null;

    return $this->goldRateModel->getRateByDate($companyId, $date, $metalType);
  }

  /**
   * Check if today's rate is entered for a specific metal type.
   *
   * @param string $metalType
   * @return bool
   */
  public function checkIfTodayRateEntered(string $metalType = '22K'): bool
  {
    $companyId = session()->get('company_id');
    if (!$companyId) return false;

    $today = date('Y-m-d');
    return $this->goldRateModel->checkRateExists($companyId, $today, $metalType);
  }

  /**
   * Get rate history for charting/reporting.
   *
   * @param string $fromDate
   * @param string $toDate
   * @return array
   */
  public function getRateHistory(string $fromDate, string $toDate): array
  {
    $companyId = session()->get('company_id');
    if (!$companyId) return [];

    return $this->goldRateModel->getRateHistory($companyId, $fromDate, $toDate);
  }
}

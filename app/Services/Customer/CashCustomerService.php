<?php

namespace App\Services\Customer;

use App\Models\CashCustomerModel;
use App\Models\StateModel;
use App\Services\Audit\AuditService;
use App\Services\Validation\ValidationService;
use CodeIgniter\Database\Exceptions\DatabaseException;

class CashCustomerService
{
  private $cashCustomerModel;
  private $stateModel;
  private $validationService;
  private $auditService;
  private $db;

  public function __construct(
    CashCustomerModel $cashCustomerModel,
    StateModel $stateModel,
    ValidationService $validationService,
    AuditService $auditService
  ) {
    $this->cashCustomerModel = $cashCustomerModel;
    $this->stateModel = $stateModel;
    $this->validationService = $validationService;
    $this->auditService = $auditService;
    $this->db = \Config\Database::connect();
  }

  /**
   * Find or create cash customer (Quick Add).
   * 
   * @param string $name
   * @param string $mobile
   * @param array $additionalData
   * @return int
   */
  public function findOrCreate(string $name, string $mobile, array $additionalData = []): int
  {
    $name = trim($name);
    $mobile = trim($mobile);

    $existing = $this->cashCustomerModel->findByNameAndMobile($name, $mobile);

    if ($existing) {
      return $existing['id'];
    }

    // Create new
    $data = [
      'customer_name' => $name,
      'mobile'        => $mobile,
      'mobile_number' => $mobile,
    ];
    // Merge additional data (address etc)
    $data = array_merge($data, $additionalData);

    return $this->createCashCustomer($data);
  }

  /**
   * Create cash customer.
   * 
   * @param array $data
   * @return int
   * @throws \Exception
   */
  public function createCashCustomer(array $data): int
  {
    $session = session();
    $data['company_id'] = $session->get('company_id');

    if (empty($data['company_id'])) {
      throw new \Exception('Company ID not found in session.');
    }

    // Ensure mobile_number is set
    if (empty($data['mobile_number']) && !empty($data['mobile'])) {
      $data['mobile_number'] = $data['mobile'];
    }

    $this->validateCashCustomerData($data);

    // Check Duplicate
    $existing = $this->cashCustomerModel->findByNameAndMobile($data['customer_name'], $data['mobile_number']);
    if ($existing) {
      throw new \Exception('Customer already exists with this name and mobile combination.');
    }

    // Validate State
    if (!empty($data['state_id'])) {
      if (!$this->stateModel->find($data['state_id'])) {
        throw new \Exception('Invalid State ID.');
      }
    } else {
      // Ensure empty state_id is null
      if (array_key_exists('state_id', $data)) {
        $data['state_id'] = null;
      }
    }

    $this->db->transStart();

    $customerId = $this->cashCustomerModel->insert($data, true);

    if (!$customerId) {
      throw new \Exception('Failed to create customer: ' . implode(', ', $this->cashCustomerModel->errors()));
    }

    $this->auditService->logCrud('cash_customer', 'create', $customerId, null, $data);

    $this->db->transComplete();

    return $customerId;
  }

  /**
   * Update cash customer.
   * 
   * @param int $id
   * @param array $data
   * @return bool
   * @throws \Exception
   */
  public function updateCashCustomer(int $id, array $data): bool
  {
    $existing = $this->getCashCustomerById($id);
    if (!$existing) {
      throw new \Exception('Customer not found.');
    }

    // Check Duplicate if name/mobile changed
    if (
      (isset($data['customer_name']) && $data['customer_name'] !== $existing['customer_name']) ||
      (isset($data['mobile']) && $data['mobile'] !== $existing['mobile'])
    ) {
      $name = $data['customer_name'] ?? $existing['customer_name'];
      $mobile = $data['mobile'] ?? $existing['mobile'];

      $duplicate = $this->cashCustomerModel->findByNameAndMobile($name, $mobile);
      if ($duplicate && $duplicate['id'] != $id) {
        throw new \Exception('Customer already exists with this name and mobile.');
      }
    }

    // Normalize state_id
    if (array_key_exists('state_id', $data) && empty($data['state_id'])) {
      $data['state_id'] = null;
    }

    $data['updated_at'] = date('Y-m-d H:i:s');

    $this->db->transStart();

    if (!$this->cashCustomerModel->update($id, $data)) {
      throw new \Exception('Update failed: ' . implode(', ', $this->cashCustomerModel->errors()));
    }

    $this->auditService->logCrud('cash_customer', 'update', $id, $existing, $data);

    $this->db->transComplete();

    return $this->db->transStatus();
  }

  /**
   * Delete (Soft Delete).
   * 
   * @param int $id
   * @return bool
   * @throws \Exception
   */
  public function deleteCashCustomer(int $id): bool
  {
    $existing = $this->getCashCustomerById($id);
    if (!$existing) {
      throw new \Exception('Customer not found.');
    }

    if ($this->cashCustomerModel->isCashCustomerUsedInTransactions($id)) {
      throw new \Exception('Cannot delete customer with transactions.');
    }

    $this->db->transStart();

    $this->cashCustomerModel->delete($id);

    $this->auditService->logCrud('cash_customer', 'delete', $id, $existing, null);

    $this->db->transComplete();

    return $this->db->transStatus();
  }

  /**
   * Merge duplicate customers.
   * Moves all transactions from secondary to primary, then deletes secondary.
   * 
   * @param int $primaryId
   * @param int $secondaryId
   * @return bool
   * @throws \Exception
   */
  public function mergeDuplicates(int $primaryId, int $secondaryId): bool
  {
    if ($primaryId === $secondaryId) {
      throw new \Exception('Cannot merge customer into itself.');
    }

    $primary = $this->getCashCustomerById($primaryId);
    $secondary = $this->getCashCustomerById($secondaryId);

    if (!$primary || !$secondary) {
      throw new \Exception('One or both customers not found.');
    }

    $this->db->transStart();

    try {
      // Update Invoices
      $this->db->table('invoices')->where('cash_customer_id', $secondaryId)->update(['cash_customer_id' => $primaryId]);

      // Update Challans
      $this->db->table('challans')->where('cash_customer_id', $secondaryId)->update(['cash_customer_id' => $primaryId]);

      // Soft Delete Secondary
      $this->cashCustomerModel->delete($secondaryId);

      // Audit Log
      $auditData = ['primary_id' => $primaryId, 'secondary_id' => $secondaryId];
      $this->auditService->logCrud('cash_customer', 'merge', $primaryId, null, $auditData);

      $this->db->transComplete();

      return $this->db->transStatus();
    } catch (\Throwable $e) {
      $this->db->transRollback();
      log_message('error', 'Merge Customers Error: ' . $e->getMessage());
      throw $e;
    }
  }

  public function getCashCustomerById(int $id): ?array
  {
    return $this->cashCustomerModel->find($id);
  }

  public function getActiveCashCustomers(): array
  {
    return $this->cashCustomerModel->getActiveCashCustomers();
  }

  public function searchCashCustomers(string $query): array
  {
    return $this->cashCustomerModel->searchCashCustomers($query);
  }

  private function validateCashCustomerData(array $data): void
  {
    if (empty($data['customer_name'])) {
      throw new \Exception('Customer Name is required.');
    }
    if (empty($data['mobile_number'])) {
      throw new \Exception('Mobile Number is required.');
    }

    if (!preg_match('/^[0-9]{10}$/', $data['mobile_number'])) {
      throw new \Exception('Mobile number must be 10 digits.');
    }

    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
      throw new \Exception('Invalid email address.');
    }
  }
}

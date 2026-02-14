<?php

namespace App\Services\Master;

use App\Models\ProcessModel;
use App\Services\Audit\AuditService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Validation\Exceptions\ValidationException;
use Exception;

/**
 * ProcessService
 *
 * Handles logic for manufacturing processes including CRUD, rate tracking, and calculations.
 */
class ProcessService
{
    protected $processModel;
    protected $auditService;
    protected $db;

    public function __construct(
        ProcessModel $processModel,
        AuditService $auditService
    ) {
        $this->processModel = $processModel;
        $this->auditService = $auditService;
    }

    /**
     * Create a new process.
     *
     * @param array $data
     * @return int Process ID
     * @throws ValidationException
     * @throws Exception
     */
    public function createProcess(array $data): int
    {
        $session = session();
        $companyId = $session->get('company_id');

        if (!$companyId) {
            throw new Exception("Company ID not found in session for createProcess.");
        }

        $data['company_id'] = $companyId;
        
        // 1. Validate Data
        $this->validateProcessData($data);

        // 2. Check Unique Process Code
        if (!$this->checkUniqueProcessCode($data['process_code'], $companyId)) {
            throw new ValidationException("Process Code '{$data['process_code']}' already exists.");
        }

        // 3. Insert Process
        $this->db = \Config\Database::connect();
        $this->db->transStart();

        $processId = $this->processModel->insert($data);
        
        if (!$processId) {
            $this->db->transRollback();
            throw new Exception("Failed to insert process.");
        }

        // 4. Audit Log
        $this->auditService->log(
            'PROCESS_CREATE',
            "Created Process: {$data['process_name']} ({$data['process_code']}) - Rate: {$data['rate_per_unit']}",
            [
                'company_id' => $companyId,
                'user_id'    => $session->get('user_id'),
                'process_id' => $processId,
                'data'       => $data
            ]
        );

        $this->db->transComplete();

        return $processId;
    }

    /**
     * Update an existing process.
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws PageNotFoundException
     * @throws ValidationException
     * @throws Exception
     */
    public function updateProcess(int $id, array $data): bool
    {
        $session = session();
        $companyId = $session->get('company_id');

        // 1. Validate Existence and Ownership
        $process = $this->processModel->find($id);
        if (!$process || $process['company_id'] != $companyId || $process['is_deleted']) {
            throw new PageNotFoundException("Process not found: $id");
        }

        // 2. Validate Data
        if (isset($data['process_code'])) {
            if (!preg_match('/^[a-zA-Z0-9\-_]+$/', $data['process_code'])) {
                throw new ValidationException("Invalid Process Code Format.");
            }
            if ($data['process_code'] !== $process['process_code']) {
                 if (!$this->checkUniqueProcessCode($data['process_code'], $companyId, $id)) {
                     throw new ValidationException("Process Code '{$data['process_code']}' already exists.");
                 }
            }
        }
        
        if (isset($data['rate_per_unit'])) {
            if ($data['rate_per_unit'] <= 0 || $data['rate_per_unit'] > 1000000) {
                throw new ValidationException("Rate must be between 0 and 1,000,000.");
            }
        }
        
        if (isset($data['process_type'])) {
             // Validate Enum
             $allowed = ['Rhodium', 'Meena', 'Wax', 'Polish', 'Coating', 'Other'];
             if (!in_array($data['process_type'], $allowed)) {
                 throw new ValidationException("Invalid Process Type.");
             }
        }

        $this->db = \Config\Database::connect();
        $this->db->transStart();

        // 3. Check Price Change
        // Using strict comparison for float might be tricky, but casting helps.
        // Or specific delta check. simple inequality is fine for business logic usually.
        $priceChanged = false;
        if (isset($data['rate_per_unit']) && (float)$data['rate_per_unit'] !== (float)$process['rate_per_unit']) {
            $priceChanged = true;
            // Log Price Change specifically
            $this->auditService->log(
                'PROCESS_PRICE_CHANGE',
                "Price changed for {$process['process_name']}: {$process['rate_per_unit']} -> {$data['rate_per_unit']}",
                [
                    'company_id' => $companyId,
                    'user_id'    => $session->get('user_id'),
                    'process_id' => $id,
                    'old_rate'   => $process['rate_per_unit'],
                    'new_rate'   => $data['rate_per_unit']
                ]
            );
        }

        // 4. Update Record - Use Builder
        // Clean data for builder (remove CSRF token etc)
        unset($data[csrf_token()]);

        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->table('processes')->where('id', $id)->update($data);
        
        // 5. Audit Log Update
        $this->auditService->log(
            'PROCESS_UPDATE',
            "Updated Process: {$process['process_name']} ({$id})",
            [
                'company_id' => $companyId,
                'user_id'    => $session->get('user_id'),
                'process_id' => $id,
                'before'     => $process,
                'after'      => $data
            ]
        );

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
             throw new Exception("Database update failed.");
        }

        return true;
    }

    /**
     * Delete a process (Soft Delete).
     *
     * @param int $id
     * @return bool
     * @throws PageNotFoundException
     * @throws Exception ("ProcessInUseException")
     */
    public function deleteProcess(int $id): bool
    {
        $session = session();
        $companyId = $session->get('company_id');

        // 1. Validate
        $process = $this->processModel->find($id);
        if (!$process || $process['company_id'] != $companyId || $process['is_deleted']) {
            throw new PageNotFoundException("Process not found: $id");
        }

        // 2. Check Usage
        if ($this->processModel->isProcessUsedInTransactions($id)) {
            throw new Exception("Cannot delete process used in transactions.");
        }

        $this->db = \Config\Database::connect();
        $this->db->transStart();

        // 3. Soft Delete - Use Builder
        $this->db->table('processes')->where('id', $id)->update([
            'is_deleted' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // 4. Audit Log
        $this->auditService->log(
            'PROCESS_DELETE',
            "Deleted Process: {$process['process_name']} ({$process['process_code']})",
            [
                'company_id' => $companyId,
                'user_id'    => $session->get('user_id'),
                'process_id' => $id,
                'data'       => $process
            ]
        );
        
        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
             throw new Exception("Database delete failed.");
        }

        return true;
    }

    /**
     * Get active processes for list/dropdown.
     *
     * @param string|null $processType
     * @return array
     */
    public function getActiveProcesses(string $processType = null): array
    {
        return $this->processModel->getActiveProcesses($processType);
    }
    
    /**
     * Get active processes by type.
     *
     * @param string $type
     * @return array
     */
    public function getProcessesByType(string $type): array
    {
        return $this->processModel->getProcessesByType($type);
    }

    /**
     * Get process by ID with validation.
     *
     * @param int $id
     * @return array|null
     */
    public function getProcessById(int $id): ?array
    {
        $session = session();
        $companyId = $session->get('company_id');

        $process = $this->processModel->find($id);
        
        if (!$process) {
            return null;
        }

        // Ensure company ownership (Model handles it via find but safe to recheck)
        if ($process['company_id'] != $companyId || $process['is_deleted']) {
            return null;
        }
        
        return $process;
    }

    /**
     * Calculate total rate from multiple processes.
     *
     * @param array $processIds
     * @param array $quantities Associative array [process_id => quantity] or indexed aligned? 
     *                          Prompt says "If quantities array provided: rate * quantity".
     *                          Usually keyed by ID is safest. Assuming [process_id => qty].
     * @return float
     */
    public function calculateTotalProcessRate(array $processIds, array $quantities = []): float
    {
        if (empty($processIds)) {
            return 0.0;
        }

        // Fetch all processes efficiently
        // Model doesn't have whereIn easily available via find() directly for array of IDs strictly?
        // find([1, 2]) works in CI4.
        $processes = $this->processModel->find($processIds);
        
        if (empty($processes)) {
            return 0.0;
        }

        $total = 0.0;
        
        foreach ($processes as $process) {
            $pid = $process['id'];
            $rate = (float)$process['rate_per_unit'];
            
            // Determine quantity
            // If quantities array has key $pid, use it.
            // If quantities is indexed array matching processIds order, that's dangerous if find() reorders.
            // Assuming associative [pid => qty].
            $qty = isset($quantities[$pid]) ? (float)$quantities[$pid] : 1.0;
            
            $total += ($rate * $qty);
        }
        
        return $total;
    }

    /**
     * Validate process data structure.
     *
     * @param array $data
     * @throws ValidationException
     */
    private function validateProcessData(array $data): void
    {
        // Required fields
        $required = ['process_code', 'process_name', 'process_type', 'rate_per_unit', 'unit_of_measure'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new ValidationException("Field '$field' is required.");
            }
        }

        // Validate process_code format
        if (!preg_match('/^[a-zA-Z0-9\-_]+$/', $data['process_code'])) {
            throw new ValidationException("Process Code must contain only letters, numbers, hyphens, and underscores.");
        }

        // Validate process_type
        $allowedTypes = ['Rhodium', 'Meena', 'Wax', 'Polish', 'Coating', 'Other'];
        if (!in_array($data['process_type'], $allowedTypes)) {
             throw new ValidationException("Invalid Process Type: {$data['process_type']}");
        }

        // Validate rate_per_unit
        if (!is_numeric($data['rate_per_unit']) || $data['rate_per_unit'] <= 0 || $data['rate_per_unit'] > 1000000) {
            throw new ValidationException("Rate must be a number between 0 and 1,000,000.");
        }
    }

    /**
     * Check if process code is unique for the company.
     *
     * @param string $code
     * @param int $companyId
     * @param int|null $excludeId
     * @return bool
     */
    private function checkUniqueProcessCode(string $code, int $companyId, int $excludeId = null): bool
    {
        $builder = $this->processModel->builder();
        $builder->where('company_id', $companyId);
        $builder->where('process_code', $code);
        $builder->where('is_deleted', 0);
        
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        
        return $builder->countAllResults() === 0;
    }
}

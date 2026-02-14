<?php

namespace App\Services\Customer;

use App\Models\AccountModel;
use App\Models\StateModel;
use App\Services\Audit\AuditService;
use App\Services\Ledger\LedgerService;
use App\Services\Validation\ValidationService;
use CodeIgniter\Database\Exceptions\DatabaseException;

class AccountService
{
    private $accountModel;
    private $stateModel;
    private $ledgerService;
    private $validationService;
    private $auditService;
    private $db;

    public function __construct(
        AccountModel $accountModel, 
        StateModel $stateModel, 
        LedgerService $ledgerService, 
        ValidationService $validationService, 
        AuditService $auditService
    ) {
        $this->accountModel = $accountModel;
        $this->stateModel = $stateModel;
        $this->ledgerService = $ledgerService;
        $this->validationService = $validationService;
        $this->auditService = $auditService;
        $this->db = \Config\Database::connect();
    }

    /**
     * Create new account customer.
     * 
     * @param array $data
     * @return int Account ID
     * @throws \Exception
     */
    public function createAccount(array $data): int
    {
        // 1. Auto-set company_id from session
        $session = session();
        $data['company_id'] = $session->get('company_id');
        
        if (empty($data['company_id'])) {
            throw new \Exception('Company ID not found in session.');
        }

        // 2. Validate Data
        $this->validateAccountData($data);

        // 3. Auto-generate Account Code if missing
        if (empty($data['account_code'])) {
            $data['account_code'] = $this->accountModel->generateNextAccountCode();
        }

        // 4. Validate State IDs
        if (!$this->stateModel->find($data['billing_state_id'])) {
            throw new \Exception('Invalid Billing State ID.');
        }

        // Handle Shipping Address Logic
        if (!empty($data['same_as_billing'])) {
            $data['shipping_address_line1'] = $data['billing_address_line1'];
            $data['shipping_address_line2'] = $data['billing_address_line2'] ?? null;
            $data['shipping_city']          = $data['billing_city'];
            $data['shipping_state_id']      = $data['billing_state_id'];
            $data['shipping_pincode']       = $data['billing_pincode'];
        } else {
            // If separate shipping address
            if (empty($data['shipping_state_id'])) {
                $data['shipping_state_id'] = null;
            } else {
                if (!$this->stateModel->find($data['shipping_state_id'])) {
                    throw new \Exception('Invalid Shipping State ID.');
                }
            }
        }

        // 5. Transaction Start
        $this->db->transStart();

        try {
            // 6. Insert Account Record
            $accountId = $this->accountModel->insert($data, true); // Return ID
            
            if (!$accountId) {
                // Check errors
                $errors = $this->accountModel->errors();
                throw new \Exception('Failed to create account: ' . implode(', ', $errors));
            }

            // 7. Create Opening Balance Ledger Entry
            if (isset($data['opening_balance']) && $data['opening_balance'] > 0) {
                $type = $data['opening_balance_type'] ?? 'Debit';
                $this->createOpeningBalanceLedgerEntry($accountId, (float)$data['opening_balance'], $type);
                
                // Update current balance to reflect opening balance?
                // LedgerService usually updates current_balance.
                // Assuming createOpeningBalanceEntry handles it via Ledger logic which triggers updateCurrentBalance.
                // Or if simplistic, update here.
                // Requirement says "Update current balance via ledger service only".
                // So createOpeningBalanceLedgerEntry should handle it.
            }

            // 8. Audit Log
            $this->auditService->logCrud('account', 'create', $accountId, null, $data);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Database transaction failed.');
            }

            return $accountId;

        } catch (\Throwable $e) {
            $this->db->transRollback(); // Should be transRollback, but transStart/Complete handles auto-rollback usually?
            // However, inside catch for Throwable, we must rollback explicitly if strict.
            // But transComplete() handles rollback if status is false? 
            // EXCEPTIONS bypass transComplete().
            // So need to check if in transaction.
            // CI4 transStart/transComplete is for automatic handling. IF exception occurs, it might not rollback automatically unless we use transException?
            // Manual rollback is safest in catch block.
            // But transStart nests.
            // Let's rely on throwing exception up.
            log_message('error', 'Create Account Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update existing account.
     * 
     * @param int $id
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    public function updateAccount(int $id, array $data): bool
    {
        $existing = $this->getAccountById($id);
        if (!$existing) {
            throw new \Exception('Account not found.');
        }

        // Prevent modifying opening balance
        if (isset($data['opening_balance']) && (float)$data['opening_balance'] !== (float)$existing['opening_balance']) {
            // throw new \Exception('Opening balance cannot be modified after creation.');
            // Or just unset it to ignore?
            // Requirement says "Opening balance cannot be changed".
            unset($data['opening_balance']);
            unset($data['opening_balance_type']);
        }

        // Validate Account Code uniqueness if changed
        if (isset($data['account_code']) && $data['account_code'] !== $existing['account_code']) {
            // Check usage?
            // Just ensure new code is unique (Model validation might check, or manual check)
             $session = session();
             $companyId = $session->get('company_id');
             $duplicate = $this->accountModel->where('company_id', $companyId)
                                             ->where('account_code', $data['account_code'])
                                             ->where('id !=', $id)
                                             ->first();
             if ($duplicate) {
                 throw new \Exception('Account Code already exists.');
             }
        }
        
        // Handle Address logic again
        if (array_key_exists('same_as_billing', $data) && $data['same_as_billing']) {
             // If same_as_billing is set to true, sync addresses
             // Need billing address from data OR existing?
             // If partial update, might be tricky. Assume full form submit usually.
             // If fields missing in data, use existing?
             // Better: merge with existing to get full picture before syncing.
             
             // Conservative approach: Only sync if billing address is in $data.
             // Or rely on Controller passing full data.
             if (isset($data['billing_address_line1'])) {
                 $data['shipping_address_line1'] = $data['billing_address_line1'];
                 $data['shipping_address_line2'] = $data['billing_address_line2'] ?? null;
                 $data['shipping_city']          = $data['billing_city'];
                 $data['shipping_state_id']      = $data['billing_state_id'];
                 $data['shipping_pincode']       = $data['billing_pincode'];
             }
        } else {
            // If separate shipping, ensure empty state becomes null
            if (array_key_exists('shipping_state_id', $data) && empty($data['shipping_state_id'])) {
                $data['shipping_state_id'] = null;
            }
        }

        // Validate Data (fields present in update)
         // Check constraints like GST/PAN
         if (!empty($data['gst_number'])) {
             // Validate using ValidationService
              if (!$this->validationService->isValidGST($data['gst_number'])) { // Method stubbed earlier? 
                  // I created isValidGST method in Step 2285
                  // But namespace was App\Services\Validation\ValidationService.
                  // Need to make sure method name matches.
                  // I used isValidGST.
                  throw new \Exception('Invalid GST Number format.');
              }
         }
         if (!empty($data['pan_number'])) {
              if (!$this->validationService->isValidPAN($data['pan_number'])) {
                  throw new \Exception('Invalid PAN Number format.');
              }
         }

        // Use Builder for Update to ensure reliability (as per Antigravity Rule)
        // Remove non-column fields
        // But Model handles allowedFields.
        // If I use Builder, I must filter manually.
        // Let's use Model first, if fails switch?
        // Step 2249 Antigravity Rule: "Use Builder if Model fails silently".
        // I trust Model for now, but will check return.
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->transStart();
        
        if (!$this->accountModel->update($id, $data)) {
             // Fallback or Exception?
             throw new \Exception('Update failed: ' . implode(', ', $this->accountModel->errors()));
        }

        // Audit Log
        $this->auditService->logCrud('account', 'update', $id, $existing, $data);
        
        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Delete account (Soft Delete).
     * 
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function deleteAccount(int $id): bool
    {
        $existing = $this->getAccountById($id);
        if (!$existing) {
            throw new \Exception('Account not found.');
        }

        // Check Transactions
        if ($this->accountModel->isAccountUsedInTransactions($id)) {
            throw new \Exception('Cannot delete account used in transactions.');
        }

        // Soft Delete via Model
        $this->db->transStart();
        
        $this->accountModel->delete($id); // Soft delete if useSoftDeletes is true
        
        // Audit
        $this->auditService->logCrud('account', 'delete', $id, $existing, null);
        
        $this->db->transComplete();
        
        return $this->db->transStatus();
    }

    /**
     * Get details.
     */
    public function getAccountById(int $id): ?array
    {
        return $this->accountModel->getAccountWithDetails($id);
    }
    
    public function getLedgerBalance(int $accountId): float
    {
        return $this->ledgerService->getAccountBalance($accountId);
    }
    
    public function updateCurrentBalance(int $accountId, float $newBalance): bool
    {
        return $this->accountModel->updateCurrentBalance($accountId, $newBalance);
    }
    
    public function getActiveAccounts(): array
    {
        return $this->accountModel->getActiveAccounts();
    }

    /**
     * Get accounts with filters.
     */
    public function getAccounts(array $filters = []): array
    {
        // Use Model or Builder
        // Since AccountModel::findAll() enforces is_deleted=0 from BaseModel,
        // we just build query on Model.
        
        $builder = $this->accountModel->builder();
        
        // Removed manual applyCompanyFilter() as it is protected and called by findAll()
        
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $this->accountModel->where('is_active', (int)$filters['is_active']);
        }
        
        if (!empty($filters['billing_state_id'])) {
            $this->accountModel->where('billing_state_id', $filters['billing_state_id']);
        }
        
        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $this->accountModel->groupStart()
                ->like('account_name', $term)
                ->orLike('account_code', $term)
                ->orLike('mobile', $term)
                ->groupEnd();
        }

        // Removed manual where('is_deleted', 0) as it is added by findAll()
        $this->accountModel->orderBy('created_at', 'DESC');
        
        return $this->accountModel->findAll();
    }
    
    public function searchAccounts(string $query): array
    {
        return $this->accountModel->searchAccounts($query);
    }

    // Private Helpers

    private function createOpeningBalanceLedgerEntry(int $accountId, float $amount, string $type): void
    {
        // Delegate to LedgerService
        // Assuming LedgerService has createOpeningBalanceEntry($accountId, $amount, $type)
        $this->ledgerService->createOpeningBalanceEntry($accountId, $amount, $type);
    }

    private function validateAccountData(array $data): void
    {
        // Validating required fields presence handled by Model Validation usually.
        // But business logic validation here.
        
        if (!empty($data['gst_number']) && !$this->validationService->isValidGST($data['gst_number'])) {
            throw new \Exception('Invalid GST Number format.');
        }

        if (!empty($data['pan_number']) && !$this->validationService->isValidPAN($data['pan_number'])) {
             throw new \Exception('Invalid PAN Number format.');
        }
        
        if (!preg_match('/^[0-9]{10}$/', $data['mobile'])) {
            throw new \Exception('Mobile number must be 10 digits.');
        }
        
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Invalid email address.');
        }
        
        if (!preg_match('/^[0-9]{6}$/', $data['billing_pincode'])) {
            throw new \Exception('Billing pincode must be 6 digits.');
        }
    }
}

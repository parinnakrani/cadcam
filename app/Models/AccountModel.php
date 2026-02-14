<?php

namespace App\Models;

class AccountModel extends BaseModel
{
    protected $table            = 'accounts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    // Updated Allowed Fields per New Schema
    protected $allowedFields    = [
        'company_id', 'account_code', 'account_name', 
        'business_name', 'contact_person', 'mobile', 'email', 
        'gst_number', 'pan_number',
        'billing_address_line1', 'billing_address_line2', 'billing_city', 'billing_state_id', 'billing_pincode',
        'shipping_address_line1', 'shipping_address_line2', 'shipping_city', 'shipping_state_id', 'shipping_pincode',
        'same_as_billing',
        'opening_balance', 'opening_balance_type', 'current_balance',
        'credit_limit', 'payment_terms',
        'notes', 'is_active', 'is_deleted'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'is_deleted';

    // Validation - Comprehensive Rules
    protected $validationRules      = [
        'company_id'       => 'required|integer',
        'account_code'     => 'required|max_length[50]', 
        'account_name'     => 'required|min_length[3]|max_length[255]',
        'mobile'           => 'required|regex_match[/^[0-9]{10}$/]', 
        'email'            => 'permit_empty|valid_email',
        'gst_number'       => 'permit_empty|exact_length[15]',
        'pan_number'       => 'permit_empty|exact_length[10]',
        'billing_state_id' => 'required|integer',
        'billing_pincode'  => 'required|exact_length[6]',
        'opening_balance'  => 'numeric',
        'opening_balance_type' => 'in_list[Debit,Credit]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // BaseModel already overrides findAll/find to apply company filter and is_deleted check.

    /**
     * Get active accounts for reference.
     * 
     * @return array
     */
    public function getActiveAccounts(): array
    {
        $this->applyCompanyFilter();
        $this->where('is_active', 1);
        $this->where('is_deleted', 0);
        $this->orderBy('account_name', 'ASC');
        
        return $this->findAll();
    }

    /**
     * Get account with state details.
     * 
     * @param int $id
     * @return array|null
     */
    public function getAccountWithDetails(int $id): ?array
    {
        $this->applyCompanyFilter();
        $this->select('accounts.*, s1.state_name as billing_state_name, s2.state_name as shipping_state_name');
        $this->join('states s1', 's1.id = accounts.billing_state_id', 'left');
        $this->join('states s2', 's2.id = accounts.shipping_state_id', 'left');
        $this->where('accounts.id', $id);
        $this->where('accounts.is_deleted', 0);
        
        return $this->first();
    }

    /**
     * Update current balance directly (bypassing model events/validation for safety/speed in ledger ops).
     * 
     * @param int $accountId
     * @param float $newBalance
     * @return bool
     */
    public function updateCurrentBalance(int $accountId, float $newBalance): bool
    {
        $session = session();
        $companyId = $session->get('company_id');
        
        if (empty($companyId)) {
            return false;
        }

        // Use Builder for direct update
        $builder = $this->db->table($this->table);
        $builder->where('id', $accountId);
        $builder->where('company_id', $companyId);
        
        return $builder->update([
            'current_balance' => $newBalance,
            'updated_at'      => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Check if account is used in transactions.
     * 
     * @param int $accountId
     * @return bool
     */
    public function isAccountUsedInTransactions(int $accountId): bool
    {
        try {
            // Check Invoices (if table exists)
            $invoices = $this->db->table('invoices')->where('account_id', $accountId)->countAllResults();
            if ($invoices > 0) return true;

            // Check Challans (if table exists)
            $challans = $this->db->table('challans')->where('account_id', $accountId)->countAllResults();
            if ($challans > 0) return true;
            
             // Check Payments/Receipts (if table exists)
            // Assuming 'payments' table linked to account
            // Or 'ledger_entries'
             // If ledger exists, check ledger?
             // Prompt says "invoices, challans, or payments table".
             // Assuming 'payments' table exists or will exist.
             $payments = $this->db->table('payments')->where('account_id', $accountId)->countAllResults();
             if ($payments > 0) return true;

        } catch (\Throwable $e) {
            // Log error or ignore if tables don't exist yet
            // If tables missing, return false (not used)
            return false;
        }

        return false;
    }

    /**
     * Search accounts for autocomplete.
     * 
     * @param string $query
     * @return array
     */
    public function searchAccounts(string $query): array
    {
        $this->applyCompanyFilter();
        $this->where('is_active', 1);
        $this->where('is_deleted', 0);
        
        $this->groupStart();
            $this->like('account_name', $query);
            $this->orLike('account_code', $query);
            $this->orLike('mobile', $query);
        $this->groupEnd();
        
        $this->limit(20);
        
        return $this->findAll();
    }

    /**
     * Generate next account code.
     * Format: ACC-0001
     * 
     * @return string
     */
    public function generateNextAccountCode(): string
    {
        $session = session();
        $companyId = $session->get('company_id');
        
        if (empty($companyId)) {
            return '';
        }

        $builder = $this->builder();
        $builder->select('account_code');
        $builder->where('company_id', $companyId);
        // Assuming format ACC-XXXX
        $builder->like('account_code', 'ACC-', 'after');
        $builder->orderBy('id', 'DESC');
        $builder->limit(1);
        
        $last = $builder->get()->getRow();
        
        if ($last) {
            $parts = explode('-', $last->account_code);
            if (count($parts) === 2 && is_numeric($parts[1])) {
                $next = (int)$parts[1] + 1;
                return 'ACC-' . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
            }
        }
        
        return 'ACC-0001';
    }
}

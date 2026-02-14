<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;

class CompanyModel extends Model
{
    protected $table            = 'companies';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false; // We use is_deleted flag manually
    protected $protectFields    = true;
    protected $allowedFields    = [
        'company_name', 
        'company_code', 
        'gst_number', 
        'pan_number',
        'address_line1', 
        'address_line2', 
        'city', 
        'state_id', 
        'pincode',
        'email', 
        'phone', 
        'mobile', 
        'website', 
        'logo',
        'last_invoice_number', 
        'last_challan_number',
        'invoice_prefix', 
        'challan_prefix', 
        'tax_rate',
        'is_active', 
        'is_deleted'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'company_name' => 'required|min_length[3]|max_length[255]',
        'company_code' => 'required|is_unique[companies.company_code,id,{id}]',
        'gst_number'   => 'required|regex_match[/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/]|is_unique[companies.gst_number,id,{id}]',
        'pan_number'   => 'required|regex_match[/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/]',
        'email'        => 'permit_empty|valid_email',
        'tax_rate'     => 'permit_empty|numeric|greater_than[0]|less_than_equal_to[100]',
    ];

    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Get the next invoice number for a company with concurrency safety.
     * MUST be called within a transaction.
     *
     * @param int $companyId
     * @return int
     * @throws Exception
     */
    public function getNextInvoiceNumber(int $companyId): int
    {
        // Ensure we are in a transaction to hold the lock
        // Note: CodeIgniter's transStatus check might be useful, 
        // but strictly the functionality requires the caller to manage the transaction boundary for multiple ops.
        // We proceed assuming the caller complies, or we enforce it.
        
        // Execute Raw SQL for locking
        // Using common table expression or direct select for update
        $sql = "SELECT last_invoice_number FROM {$this->table} WHERE id = ? FOR UPDATE";
        $query = $this->db->query($sql, [$companyId]);
        $row = $query->getRow();

        if (!$row) {
            throw new Exception("Company not found with ID: {$companyId}");
        }

        $nextNumber = (int)$row->last_invoice_number + 1;

        // Update the number
        // We use direct query to avoid model events interfering or unnecessary overhead given we have the lock
        $updateSql = "UPDATE {$this->table} SET last_invoice_number = ?, updated_at = ? WHERE id = ?";
        $this->db->query($updateSql, [$nextNumber, date('Y-m-d H:i:s'), $companyId]);

        return $nextNumber;
    }

    /**
     * Get the next challan number for a company with concurrency safety.
     * MUST be called within a transaction.
     *
     * @param int $companyId
     * @return int
     * @throws Exception
     */
    public function getNextChallanNumber(int $companyId): int
    {
        $sql = "SELECT last_challan_number FROM {$this->table} WHERE id = ? FOR UPDATE";
        $query = $this->db->query($sql, [$companyId]);
        $row = $query->getRow();

        if (!$row) {
            throw new Exception("Company not found with ID: {$companyId}");
        }

        $nextNumber = (int)$row->last_challan_number + 1;

        $updateSql = "UPDATE {$this->table} SET last_challan_number = ?, updated_at = ? WHERE id = ?";
        $this->db->query($updateSql, [$nextNumber, date('Y-m-d H:i:s'), $companyId]);

        return $nextNumber;
    }

    /**
     * Get all active, non-deleted companies.
     *
     * @return array
     */
    public function getActiveCompanies(): array
    {
        return $this->where('is_active', 1)
                    ->where('is_deleted', 0)
                    ->orderBy('company_name', 'ASC')
                    ->findAll();
    }

    /**
     * Validate GST Number format.
     *
     * @param string $gst
     * @return bool
     */
    public function validateGSTNumber(string $gst): bool
    {
        return (bool) preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', $gst);
    }

    /**
     * Validate PAN Number format.
     *
     * @param string $pan
     * @return bool
     */
    public function validatePANNumber(string $pan): bool
    {
        return (bool) preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $pan);
    }
}

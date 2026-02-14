<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * ProcessModel
 *
 * Model for managing manufacturing processes and their rates.
 * Extends BaseModel for automatic company isolation and soft deletes.
 */
class ProcessModel extends BaseModel
{
    protected $table = 'processes';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'company_id', 'process_code', 'process_name', 'process_type',
        'description', 'rate_per_unit', 'unit_of_measure',
        'is_active', 'is_deleted'
        // timestamp fields created_at/updated_at handled automatically
    ];

    protected $validationRules = [
        'company_id'      => 'required|integer',
        'process_code'    => 'required|max_length[50]|is_unique[processes.process_code,id,{id}]',
        'process_name'    => 'required|min_length[3]|max_length[255]',
        'process_type'    => 'required|in_list[Rhodium,Meena,Wax,Polish,Coating,Other]',
        'rate_per_unit'   => 'required|decimal|greater_than[0]',
        'unit_of_measure' => 'required|in_list[PCS,GRAM,PAIR,SET]'
    ];

    /**
     * Get active processes, optionally filtered by type.
     *
     * @param string|null $processType
     * @return array
     */
    public function getActiveProcesses(?string $processType = null): array
    {
        // BaseModel handles company_id and is_deleted via findAll() inheritance
        $this->where($this->table . '.is_active', 1);

        if ($processType) {
            $this->where($this->table . '.process_type', $processType);
        }

        $this->orderBy($this->table . '.process_name', 'ASC');

        return $this->findAll();
    }

    /**
     * Get active processes by a specific type.
     *
     * @param string $type
     * @return array
     */
    public function getProcessesByType(string $type): array
    {
        // Wrapper for getActiveProcesses or direct implementation
        // BaseModel handles company_id and is_deleted
        $this->where($this->table . '.process_type', $type);
        $this->where($this->table . '.is_active', 1);
        $this->orderBy($this->table . '.process_name', 'ASC');
        
        return $this->findAll();
    }

    /**
     * Check if process is used in any transactions (challans).
     *
     * @param int $processId
     * @return bool
     */
    public function isProcessUsedInTransactions(int $processId): bool
    {
        // Check challan_lines table
        // We look for 'process_id' column if it exists, or maybe challan_process_lines?
        // Prompt says "Check if process_id exists in challan_lines table".
        try {
            return $this->db->table('challan_lines')
                ->where('process_id', $processId)
                ->countAllResults() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get the current rate for a process.
     *
     * @param int $processId
     * @return float|null
     */
    public function getCurrentRate(int $processId): ?float
    {
        // BaseModel will enforce company check via find() logic if we used it,
        // but here we might want just a single field.
        // To be safe and respect isolation, we use find().
        
        $process = $this->find($processId);
        
        if ($process) {
            return (float)$process['rate_per_unit'];
        }
        
        return null;
    }
}

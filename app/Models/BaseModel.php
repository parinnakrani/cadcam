<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * BaseModel
 * 
 * Base model that provides automatic company isolation and soft delete handling.
 * All multi-tenant models should extend this class.
 */
class BaseModel extends Model
{
    /**
     * Apply automatic company_id filtering based on session.
     * 
     * @return $this
     */
    protected function applyCompanyFilter(): self
    {
        // Get session instance
        $session = session();
        
        // Retrieve context
        $isSuperAdmin = $session->get('is_super_admin');
        $companyId    = $session->get('company_id');

        // Apply filter if NOT super admin and company_id is present
        if (!$isSuperAdmin && !empty($companyId)) {
            // Qualify with table name to prevent ambiguity in joins
            $this->where($this->table . '.company_id', $companyId);
        }

        return $this;
    }

    /**
     * Override findAll to apply filters.
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function findAll(int $limit = 0, int $offset = 0)
    {
        $this->applyCompanyFilter();
        $this->where($this->table . '.is_deleted', 0);
        
        return parent::findAll($limit, $offset);
    }

    /**
     * Override find to apply filters.
     *
     * @param int|array|string|null $id
     * @return array|object|null
     */
    public function find($id = null)
    {
        $this->applyCompanyFilter();
        $this->where($this->table . '.is_deleted', 0);
        
        return parent::find($id);
    }

    /**
     * Override paginate to apply filters.
     *
     * @param int $perPage
     * @param string $group
     * @param int $page
     * @param int $segment
     * @return array|null
     */
    public function paginate(?int $perPage = null, string $group = 'default', ?int $page = null, int $segment = 0)
    {
        $this->applyCompanyFilter();
        $this->where($this->table . '.is_deleted', 0);
        
        return parent::paginate($perPage, $group, $page, $segment);
    }

    /**
     * Soft delete a record by ID.
     * Updates column is_deleted to 1 instead of removing row.
     *
     * @param int $id
     * @return bool
     */
    public function softDelete(int $id): bool
    {
        // Security: Ensure we only delete if it matches company filter
        $this->applyCompanyFilter();
        
        // We use the model's update method which triggers events
        // Validate existence implicitly via update or check first?
        // update() returns bool.
        
        // However, applyCompanyFilter works on the Query Builder. 
        // update($id, data) generally uses the ID directly and might reset builders unless handled carefully.
        // Safer approach: Use builder directly with where clauses
        
        // But to keep it simple and use Model features:
        // We can't strictly force 'where' into update($id) easily without using the builder.
        // CodeIgniter Model::update($id, $data) -> $this->doUpdate($id, $data)
        
        // Let's rely on finding it first to ensure permission?
        // OR better: use builder update with where clause.
        
        $builder = $this->builder();
        
        // Apply isolation
        // Re-implement logic since applyCompanyFilter returns $this (Model), not Builder
        $session = session();
        $isSuperAdmin = $session->get('is_super_admin');
        $companyId    = $session->get('company_id');

        if (!$isSuperAdmin && !empty($companyId)) {
            $builder->where('company_id', $companyId);
        }
        
        $builder->where($this->primaryKey, $id);
        return $builder->update(['is_deleted' => 1]);
    }
}

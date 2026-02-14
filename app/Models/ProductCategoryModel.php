<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * ProductCategoryModel
 *
 * Model for managing product categories.
 * Extends BaseModel for automatic company isolation and soft deletes.
 */
class ProductCategoryModel extends BaseModel
{
    protected $table            = 'product_categories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false; // Handled manually via is_deleted flag in BaseModel
    protected $protectFields    = true;
    protected $allowedFields    = [
        'company_id', 
        'category_name', 
        'category_code', 
        'description', 
        'display_order', 
        'is_active', 
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'category_name' => 'required|min_length[3]|max_length[100]',
        'category_code' => 'required|max_length[50]', 
        'display_order' => 'integer'
    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;

    /**
     * Get active categories for dropdowns, ordered by display_order
     */
    public function getActiveCategories(): array
    {
        // BaseModel applies company filter automatically.
        // We add active check and sorting.
        $this->where($this->table . '.is_active', 1);
        $this->where($this->table . '.is_deleted', 0);
        $this->orderBy('display_order', 'ASC');
        $this->orderBy('category_name', 'ASC');
        
        return $this->findAll();
    }

    /**
     * Search categories by name or code
     */
    public function searchCategories(string $query): array
    {
        $this->groupStart()
                ->like('category_name', $query)
                ->orLike('category_code', $query)
             ->groupEnd();
             
        $this->where($this->table . '.is_deleted', 0);
        
        return $this->findAll();
    }
}

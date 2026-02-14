<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * ProductModel
 *
 * Model for managing jewelry products.
 * Extends BaseModel for automatic company isolation and soft deletes.
 */
class ProductModel extends BaseModel
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'company_id', 'category_id', 'product_code', 'product_name',
        'description', 'image_path', 'hsn_code', 'unit_of_measure',
        'is_active', 'is_deleted'
        // created_at, updated_at handled automatically
    ];

    protected $validationRules = [
        'company_id'      => 'required|integer',
        'category_id'     => 'required|integer',
        // Note: is_unique enforces global uniqueness. For company-scoped uniqueness, 
        // manual check or custom rule is recommended. Keeping strict rule as requested.
        'product_code'    => 'required|max_length[50]|is_unique[products.product_code,id,{id}]',
        'product_name'    => 'required|min_length[3]|max_length[255]',
        'hsn_code'        => 'permit_empty|max_length[20]',
        'unit_of_measure' => 'required|in_list[PCS,PAIR,SET,GRAM]'
    ];

    /**
     * Get active products for dropdowns or lists.
     * Optionally filter by category.
     *
     * @param int|null $categoryId
     * @return array
     */
    public function getActiveProducts(int $categoryId = null): array
    {
        // BaseModel::findAll() applies company filter and is_deleted check automatically.
        // We only need to add specific conditions.
        
        $this->where($this->table . '.is_active', 1);
        
        if ($categoryId) {
            $this->where($this->table . '.category_id', $categoryId);
        }
        
        $this->orderBy($this->table . '.product_name', 'ASC');
        
        return $this->findAll(); 
    }

    /**
     * Get product with category name joined.
     *
     * @param int $id
     * @return array|null
     */
    public function getProductWithCategory(int $id): ?array
    {
        // BaseModel handles isolation and soft delete via first() -> findAll()
        
        $this->where($this->table . '.id', $id);
        
        $this->select('products.*, product_categories.category_name');
        $this->join('product_categories', 'product_categories.id = products.category_id', 'left');
        
        return $this->first();
    }

    /**
     * Check if product is used in any transactions (challans).
     *
     * @param int $productId
     * @return bool
     */
    public function isProductUsedInTransactions(int $productId): bool
    {
        // Check challan_lines table
        // countAllResults() returns int, casting to bool via > 0 comparison
        try {
            return $this->db->table('challan_lines')
                ->where('product_id', $productId)
                ->countAllResults() > 0;
        } catch (\Throwable $e) {
            // Table might not exist yet or column missing
            return false;
        }
    }

    /**
     * Search products for autocomplete.
     *
     * @param string $query
     * @return array
     */
    public function searchProducts(string $query): array
    {
        // BaseModel handles isolation and IS_DELETED=0
        $this->where($this->table . '.is_active', 1);
        
        $this->groupStart();
            $this->like($this->table . '.product_name', $query);
            $this->orLike($this->table . '.product_code', $query);
        $this->groupEnd();
        
        $this->limit(20);
        
        // Use findAll() explicitly to trigger BaseModel logic
        return $this->findAll();
    }
}

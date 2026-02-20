<?php

namespace App\Services\Master;

use App\Models\ProductCategoryModel;
use App\Models\ProductModel;
use App\Services\Audit\AuditService;
use Exception;
use CodeIgniter\Utils\UUID;

/**
 * ProductCategoryService
 *
 * Handles creation, update, and search for product categories.
 */
class ProductCategoryService
{
  protected $categoryModel;
  protected $productModel;
  protected $auditService;

  public function __construct(
    ?ProductCategoryModel $categoryModel = null,
    ?ProductModel $productModel = null,
    ?AuditService $auditService = null
  ) {
    $this->categoryModel = $categoryModel ?? new ProductCategoryModel();
    $this->productModel  = $productModel  ?? new ProductModel();
    $this->auditService  = $auditService  ?? new AuditService();
  }

  /**
   * Get active categories (optional filtering)
   */
  public function getActiveCategories(string $search = null): array
  {
    if ($search) {
      return $this->categoryModel->searchCategories($search);
    }
    return $this->categoryModel->getActiveCategories();
  }

  /**
   * Get category by ID
   */
  public function getCategoryById(int $id): ?array
  {
    return $this->categoryModel->find($id);
  }

  /**
   * Create product category
   */
  public function createCategory(array $data): int
  {
    // Check uniqueness for code within company (handled by DB constraint, but better to check)
    $existing = $this->categoryModel->where('category_code', $data['category_code'])->first();
    if ($existing) {
      throw new Exception('Category code already exists.');
    }

    $data['company_id'] = session()->get('company_id');

    if (!$this->categoryModel->insert($data)) {
      $errors = $this->categoryModel->errors();
      throw new Exception(implode(', ', $errors));
    }

    $id = $this->categoryModel->getInsertID();

    // Audit Log
    $this->auditService->log('Master', 'create', 'ProductCategory', $id, null, $data);

    return $id;
  }

  /**
   * Update product category
   */
  public function updateCategory(int $id, array $data): bool
  {
    $category = $this->getCategoryById($id);
    if (!$category) {
      throw new Exception('Category not found.');
    }

    // Code uniqueness check if changed (code is usually readonly, but if allowed)
    if (isset($data['category_code']) && $data['category_code'] !== $category['category_code']) {
      $existing = $this->categoryModel->where('category_code', $data['category_code'])->first();
      if ($existing) {
        throw new Exception('Category code already exists.');
      }
    }

    // Handle is_active correctly (checkbox sends '1' or nothing)
    // If not sent (unchecked), force 0 if field expected
    // Typically passed as '0' hidden or handled in controller. 
    // We assume valid input here.

    if (!$this->categoryModel->update($id, $data)) {
      $errors = $this->categoryModel->errors();
      throw new Exception(implode(', ', $errors));
    }

    $this->auditService->log('Master', 'update', 'ProductCategory', $id, $category, $data);

    return true;
  }

  /**
   * Delete (Soft Delete) category
   */
  public function deleteCategory(int $id): bool
  {
    $category = $this->getCategoryById($id);
    if (!$category) {
      throw new Exception('Category not found.');
    }

    // Verify usage in Products
    $productCount = $this->productModel->where('category_id', $id)->where('is_deleted', 0)->countAllResults();
    if ($productCount > 0) {
      throw new Exception("Cannot delete category. It is used by $productCount products.");
    }

    // Use soft delete via update is_deleted = 1
    if (!$this->categoryModel->update($id, ['is_deleted' => 1])) {
      throw new Exception('Failed to delete category.');
    }

    $this->auditService->log('Master', 'delete', 'ProductCategory', $id, $category, null);

    return true;
  }
}

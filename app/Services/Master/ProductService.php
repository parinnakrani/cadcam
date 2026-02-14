<?php

namespace App\Services\Master;

use App\Models\ProductModel;
use App\Models\ProductCategoryModel;
use App\Services\Audit\AuditService;
use App\Services\FileUploadService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Validation\Exceptions\ValidationException;
use CodeIgniter\HTTP\Files\UploadedFile;
use Exception;

/**
 * ProductService
 *
 * Handles logic for products including CRUD, image handling, and audit logging.
 */
class ProductService
{
    protected $productModel;
    protected $categoryModel;
    protected $fileUploadService;
    protected $auditService;

    public function __construct(
        ProductModel $productModel,
        ProductCategoryModel $categoryModel, // Assumed to exist
        FileUploadService $fileUploadService, // Assumed to exist
        AuditService $auditService
    ) {
        $this->productModel = $productModel;
        $this->categoryModel = $categoryModel;
        $this->fileUploadService = $fileUploadService;
        $this->auditService = $auditService;
    }

    /**
     * Create a new product.
     *
     * @param array $data
     * @return int Product ID
     * @throws ValidationException
     * @throws Exception
     */
    public function createProduct(array $data): int
    {
        $session = session();
        $companyId = $session->get('company_id');

        if (!$companyId) {
            throw new Exception("Company ID not found in session for createProduct.");
        }

        $data['company_id'] = $companyId;
        
        // 1. Validate Data
        $this->validateProductData($data);

        // 2. Check Category Exists
        $category = $this->categoryModel->find($data['category_id']);
        if (!$category || $category['company_id'] != $companyId || $category['is_deleted']) {
            throw new Exception("Invalid Category ID: {$data['category_id']}");
        }

        // 3. Check Unique Product Code
        if (!$this->checkUniqueProductCode($data['product_code'], $companyId)) {
            throw new ValidationException("Product Code '{$data['product_code']}' already exists.");
        }

        // 4. Handle Image Upload
        // 4. Handle Image Upload
        $imagePath = null;
        if (isset($data['image_file']) && $data['image_file'] instanceof UploadedFile) {
            if ($data['image_file']->isValid() && !$data['image_file']->hasMoved()) {
                $filename = $this->fileUploadService->uploadFile($data['image_file'], 'uploads/products');
                // FORCE path concatenation
                $imagePath = 'uploads/products/' . $filename;
            }
        }
        $data['image_path'] = $imagePath; // Set explicitly

        // 5. Insert Product
        // Remove non-field data
        unset($data['image_file']);
        
        $db = \Config\Database::connect();
        $db->transStart();

        $productId = $this->productModel->insert($data);
        
        if (!$productId) {
            $db->transRollback();
            throw new Exception("Failed to insert product.");
        }

        // 6. Audit Log
        $this->auditService->log(
            'PRODUCT_CREATE',
            "Created Product: {$data['product_name']} ({$data['product_code']})",
            [
                'company_id' => $companyId,
                'user_id'    => $session->get('user_id'),
                'product_id' => $productId,
                'data'       => $data
            ]
        );

        $db->transComplete();

        return $productId;
    }

    /**
     * Update an existing product.
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws PageNotFoundException
     * @throws ValidationException
     * @throws Exception
     */
    public function updateProduct(int $id, array $data): bool
    {
        $session = session();
        $companyId = $session->get('company_id');

        // 1. Validate Existence and Ownership
        $product = $this->productModel->find($id);
        if (!$product || $product['company_id'] != $companyId || $product['is_deleted']) {
            throw new PageNotFoundException("Product not found: $id");
        }

        // 2. Prepare Update Data
        $updateData = [];
        $fields = ['category_id', 'product_name', 'hsn_code', 'unit_of_measure', 'description', 'is_active'];
        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }
        
        // Product Code check
        if (isset($data['product_code'])) {
            if (!preg_match('/^[a-zA-Z0-9\-_]+$/', $data['product_code'])) {
                throw new ValidationException("Invalid Product Code Format.");
            }
            if ($data['product_code'] !== $product['product_code']) {
                 if (!$this->checkUniqueProductCode($data['product_code'], $companyId, $id)) {
                     throw new ValidationException("Product Code '{$data['product_code']}' already exists.");
                 }
            }
            $updateData['product_code'] = $data['product_code'];
        }

        // 3. Handle Image Upload
        if (isset($data['image_file']) && $data['image_file'] instanceof UploadedFile) {
            if ($data['image_file']->isValid() && !$data['image_file']->hasMoved()) {
                // Delete old image
                if (!empty($product['image_path'])) {
                    if (file_exists(FCPATH . $product['image_path'])) {
                         @unlink(FCPATH . $product['image_path']);
                    }
                }
                $filename = $this->fileUploadService->uploadFile($data['image_file'], 'uploads/products');
                // FORCE path concatenation
                $updateData['image_path'] = 'uploads/products/' . $filename;
            }
        }

        // 4. Update
        $db = \Config\Database::connect();
        $db->transStart();

        // Update Record - Use Builder directly to bypass Model quirks
        $updateData['updated_at'] = date('Y-m-d H:i:s');
        $db->table('products')->where('id', $id)->update($updateData);
        
        $lastQuery = $db->getLastQuery()->getQuery();
        log_message('error', 'DEBUG SQL: ' . $lastQuery);
        // Also log explicit updateData
        log_message('error', 'DEBUG UPDATE DATA: ' . json_encode($updateData));
        
        // 5. Audit Log
        $this->auditService->log(
            'PRODUCT_UPDATE',
            "Updated Product: {$product['product_name']}",
            [
                'company_id' => $companyId,
                'user_id'    => $session->get('user_id'),
                'product_id' => $id,
                'changes'    => $updateData
            ]
        );

        $db->transComplete();

        if ($db->transStatus() === false) {
             throw new Exception("Database update failed (Transaction rolled back).");
        }

        return true;
    }

    /**
     * Delete a product (Soft Delete).
     *
     * @param int $id
     * @return bool
     * @throws PageNotFoundException
     * @throws Exception ("ProductInUseException")
     */
    public function deleteProduct(int $id): bool
    {
        $session = session();
        $companyId = $session->get('company_id');

        // 1. Validate
        $product = $this->productModel->find($id);
        if (!$product || $product['company_id'] != $companyId || $product['is_deleted']) {
            throw new PageNotFoundException("Product not found: $id");
        }

        // 2. Check Usage
        if ($this->productModel->isProductUsedInTransactions($id)) {
            // "Throw ProductInUseException"
            // Since class doesn't exist, throwing generic Exception with specific message.
            throw new Exception("Cannot delete product used in transactions.");
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // 3. Soft Delete - Use Builder directly
        $db->table('products')->where('id', $id)->update([
            'is_deleted' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // 4. Delete Image (Caution: Soft delete usually keeps assets, but following existing logic)
        if (!empty($product['image_path'])) {
             if (file_exists(FCPATH . $product['image_path'])) {
                 @unlink(FCPATH . $product['image_path']);
             }
        }

        // 5. Audit Log
        $this->auditService->log(
            'PRODUCT_DELETE',
            "Deleted Product: {$product['product_name']} ({$product['product_code']})",
            [
                'company_id' => $companyId,
                'user_id'    => $session->get('user_id'),
                'product_id' => $id,
                'data'       => $product
            ]
        );
        
        $db->transComplete();

        if ($db->transStatus() === false) {
             throw new Exception("Database delete failed.");
        }

        return true;
    }

    /**
     * Get active products for list/dropdown.
     *
     * @param int|null $categoryId
     * @return array
     */
    public function getActiveProducts(int $categoryId = null): array
    {
        // Model handles filters
        return $this->productModel->getActiveProducts($categoryId);
    }

    /**
     * Get product by ID with validation.
     *
     * @param int $id
     * @return array|null
     */
    public function getProductById(int $id): ?array
    {
        $session = session();
        $companyId = $session->get('company_id');

        $product = $this->productModel->getProductWithCategory($id);
        
        if (!$product) {
            return null;
        }

        // Ensure company ownership (Model handles it but good to be explicit)
        if ($product['company_id'] != $companyId) {
            return null;
        }
        
        return $product;
    }

    /**
     * Search products.
     *
     * @param string $query
     * @return array
     */
    public function searchProducts(string $query): array
    {
        return $this->productModel->searchProducts($query);
    }

    /**
     * Validate product data structure.
     *
     * @param array $data
     * @throws ValidationException
     */
    private function validateProductData(array $data): void
    {
        // Required fields
        $required = ['product_code', 'product_name', 'category_id', 'unit_of_measure'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new ValidationException("Field '$field' is required.");
            }
        }

        // Validate product_code format (alphanumeric, hyphen, underscore only)
        if (!preg_match('/^[a-zA-Z0-9\-_]+$/', $data['product_code'])) {
            throw new ValidationException("Product Code must contain only letters, numbers, hyphens, and underscores.");
        }

        // Validate hsn_code if provided
        if (!empty($data['hsn_code']) && !ctype_alnum($data['hsn_code'])) {
             // Basic alphanumeric check for HSN
             // Keeping strict for now
             // throw new ValidationException("HSN Code must be alphanumeric.");
             // Actually, HSN can contain dots? Usually numeric. 
             // Without clear requirement, I shouldn't block valid HSNs.
             // I'll skip strict HSN check unless requested. The prompt said "Validate hsn_code format if provided".
             // I'll check alphanumeric just to be safe.
             if (!preg_match('/^[a-zA-Z0-9]+$/', $data['hsn_code'])) {
                  throw new ValidationException("HSN Code must be alphanumeric.");
             }
        }
    }

    /**
     * Check if product code is unique for the company.
     *
     * @param string $code
     * @param int $companyId
     * @param int|null $excludeId
     * @return bool
     */
    private function checkUniqueProductCode(string $code, int $companyId, int $excludeId = null): bool
    {
        // Manual query to ProductModel to bypass global scope if needed, 
        // but finding by company_id is safer with builder.
        $builder = $this->productModel->builder();
        $builder->where('company_id', $companyId);
        $builder->where('product_code', $code);
        $builder->where('is_deleted', 0);
        
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        
        return $builder->countAllResults() === 0;
    }
}

<?php

namespace App\Controllers\Masters;

use App\Controllers\BaseController;
use App\Services\Master\ProductCategoryService;
use App\Models\ProductCategoryModel;
use App\Models\ProductModel;
use App\Services\Audit\AuditService;
use CodeIgniter\Exceptions\PageNotFoundException;
use Exception;

/**
 * ProductCategoryController
 *
 * Controller for managing product categories.
 */
class ProductCategoryController extends BaseController
{
    protected $categoryService;

    public function __construct()
    {
        // Manual DI
        // Need both ProductCategoryModel and ProductModel for Service
        $this->categoryService = new ProductCategoryService(
            new ProductCategoryModel(),
            new ProductModel(),
            new AuditService()
        );
    }

    /**
     * Display list of categories.
     *
     * @return string|\CodeIgniter\HTTP\ResponseInterface
     */
    public function index()
    {
        if (!$this->hasPermission('product_category.view')) {
            throw new PageNotFoundException("Access Denied");
        }

        $search = $this->request->getGet('search');
        $categories = $this->categoryService->getActiveCategories($search);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['data' => $categories]);
        }

        $data = [
            'title'      => 'Product Categories',
            'categories' => $categories,
            'canCreate'  => $this->hasPermission('product_category.create'),
            'canEdit'    => $this->hasPermission('product_category.edit'),
            'canDelete'  => $this->hasPermission('product_category.delete')
        ];

        return view('Masters/ProductCategories/index', $data);
    }

    /**
     * Show form to create a new category.
     *
     * @return string
     */
    public function create(): string
    {
        if (!$this->hasPermission('product_category.create')) {
            throw new PageNotFoundException("Access Denied");
        }

        $data = [
            'title' => 'Create Product Category'
        ];

        return view('Masters/ProductCategories/create', $data);
    }

    /**
     * Store a new category.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function store()
    {
        if (!$this->hasPermission('product_category.create')) {
            return redirect()->back()->with('error', 'Access Denied');
        }

        try {
            $data = $this->request->getPost();
            
            // Validate input basically
            if (empty($data['category_name']) || empty($data['category_code'])) {
                session()->setFlashdata('error', 'Category Name and Code are required.');
                return redirect()->back()->withInput();
            }

            $this->categoryService->createCategory($data);

            return redirect()->to('masters/product-categories')->with('message', 'Category created successfully.');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Show form to edit a category.
     *
     * @param int $id
     * @return string
     */
    public function edit(int $id): string
    {
        if (!$this->hasPermission('product_category.edit')) {
            throw new PageNotFoundException("Access Denied");
        }

        $category = $this->categoryService->getCategoryById($id);
        
        if (!$category) {
            throw new PageNotFoundException("Category not found: $id");
        }

        $data = [
            'title'    => 'Edit Product Category',
            'category' => $category
        ];

        return view('Masters/ProductCategories/edit', $data);
    }

    /**
     * Update an existing category.
     *
     * @param int $id
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function update(int $id)
    {
        if (!$this->hasPermission('product_category.edit')) {
            return redirect()->back()->with('error', 'Access Denied');
        }

        try {
            $data = $this->request->getPost();
            
            // Handle checkbox for is_active. If unchecked, not sent. force 0.
            if (!isset($data['is_active'])) {
                $data['is_active'] = 0; // Inactive
            }

            $this->categoryService->updateCategory($id, $data);

            return redirect()->to('masters/product-categories')->with('message', 'Category updated successfully.');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete a category (Soft Delete).
     *
     * @param int $id
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function delete(int $id)
    {
        if (!$this->hasPermission('product_category.delete')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access Denied'])->setStatusCode(403);
        }

        try {
            $this->categoryService->deleteCategory($id);
            session()->setFlashdata('message', 'Category deleted successfully.');
            
            return $this->response->setJSON(['status' => 'success', 'message' => 'Category deleted successfully.']);
        } catch (Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()])->setStatusCode(400);
        }
    }
}

# AI CODING PROMPTS - TASK 02

## Master Data Management - Products, Processes & Customers

**Version:** 1.0  
**Phase:** 2 - Master Data Management (Weeks 5-6)  
**Generated:** February 10, 2026

---

## 📋 OVERVIEW

This document contains complete AI coding prompts for:

- **Task 2.2:** Product & Process Management (Subtasks 2.2.1 - 2.2.9)
- **Task 2.3:** Account & Cash Customer Management (Subtasks 2.3.1 - 2.3.8)

All prompts are production-ready and include:
✅ Complete context from PRD
✅ Database schema details
✅ Business logic requirements
✅ Validation rules
✅ Service layer architecture
✅ Controller patterns
✅ View file structure
✅ Route configuration
✅ Sidebar navigation

---

## 🎯 PHASE 2: MASTER DATA MANAGEMENT

### TASK 2.2: Product & Process Management

---

#### Subtask 2.2.1: Create product_categories Migration

\`\`\`
[PASTE .antigravity RULES FIRST]

TASK: Generate migration file for product_categories table

FILE: app/Database/Migrations/2026-01-01-000007_create_product_categories_table.php

CONTEXT:

- Product categories organize products into logical groups
- Examples: Rings, Necklaces, Bangles, Earrings, Pendants
- Each company maintains their own categories
- Categories can be active/inactive

REQUIREMENTS:
Create CodeIgniter 4 migration with:

TABLE STRUCTURE:

- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- company_id (INT, FK to companies.id, NOT NULL)
- category_name (VARCHAR 100, NOT NULL)
- category_code (VARCHAR 50, NOT NULL)
- description (TEXT, NULL)
- display_order (INT, DEFAULT 0)
- is_active (BOOLEAN, DEFAULT TRUE)
- is_deleted (BOOLEAN, DEFAULT FALSE)
- created_at (TIMESTAMP NULL)
- updated_at (TIMESTAMP NULL)

INDEXES:

- PRIMARY KEY (id)
- INDEX (company_id)
- UNIQUE (company_id, category_code)
- INDEX (category_name)

FOREIGN KEYS:

- company_id REFERENCES companies(id) ON DELETE CASCADE

METHODS REQUIRED:

- up(): Create table with all columns, indexes, constraints
- down(): Drop table

ADDITIONAL REQUIREMENTS:

- Use CodeIgniter 4 forge syntax
- Include proper error handling
- Test both up() and down() methods

DELIVERABLES:
Complete migration file ready to run with php spark migrate

ACCEPTANCE CRITERIA:

- Migration runs without errors
- Unique constraint on company_id + category_code works
- Foreign key constraint working
- Rollback works (php spark migrate:rollback)
  \`\`\`

---

#### Subtask 2.2.2: Create products Migration

\`\`\`
[PASTE .antigravity RULES FIRST]

TASK: Generate migration file for products table

FILE: app/Database/Migrations/2026-01-01-000008_create_products_table.php

CONTEXT:

- Products represent jewelry designs/items
- Each product belongs to a category
- Products have images for visual identification
- Product code must be unique per company
- Products can be active/inactive for dropdown filtering

REQUIREMENTS:
Create CodeIgniter 4 migration with:

TABLE STRUCTURE:

- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- company_id (INT, FK to companies.id, NOT NULL)
- category_id (INT, FK to product_categories.id, NOT NULL)
- product_code (VARCHAR 50, NOT NULL)
- product_name (VARCHAR 255, NOT NULL)
- description (TEXT, NULL)
- image_path (VARCHAR 255, NULL)
- hsn_code (VARCHAR 20, NULL) // For GST compliance
- unit_of_measure (VARCHAR 20, DEFAULT 'PCS') // PCS, PAIR, SET
- is_active (BOOLEAN, DEFAULT TRUE)
- is_deleted (BOOLEAN, DEFAULT FALSE)
- created_at (TIMESTAMP NULL)
- updated_at (TIMESTAMP NULL)

INDEXES:

- PRIMARY KEY (id)
- INDEX (company_id)
- INDEX (category_id)
- UNIQUE (company_id, product_code)
- INDEX (product_name)

FOREIGN KEYS:

- company_id REFERENCES companies(id) ON DELETE CASCADE
- category_id REFERENCES product_categories(id) ON DELETE RESTRICT

METHODS REQUIRED:

- up(): Create table with all columns, indexes, constraints
- down(): Drop table

ADDITIONAL REQUIREMENTS:

- Foreign key on category_id uses RESTRICT (cannot delete category with products)
- HSN code field for tax reporting compliance
- Image path stores relative path to uploaded image

DELIVERABLES:
Complete migration file

ACCEPTANCE CRITERIA:

- Migration runs successfully
- Cannot delete category if products exist (RESTRICT working)
- Unique product_code per company enforced
- Foreign keys working
  \`\`\`

---

#### Subtask 2.2.3: Create processes Migration

\`\`\`
Subtask 2.2.3: Create processes Migration
TASK: Generate migration file for processes table

FILE: app/Database/Migrations/2026-01-01-000009_create_processes_table.php

CONTEXT:

- Processes represent manufacturing operations (Rhodium, Meena, Wax, Polish, etc.)
- Each process has a price per unit (e.g., ₹50 per piece, ₹100 per gram)
- Processes are added to challan lines to calculate total amount
- Process pricing can change over time (track in audit log)

REQUIREMENTS:
Create CodeIgniter 4 migration with:

TABLE STRUCTURE:

- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- company_id (INT, FK to companies.id, NOT NULL)
- process_code (VARCHAR 50, NOT NULL)
- process_name (VARCHAR 255, NOT NULL)
- process_type (ENUM('Rhodium', 'Meena', 'Wax', 'Polish', 'Coating', 'Other'), DEFAULT 'Other')
- description (TEXT, NULL)
- rate_per_unit (DECIMAL 10,2, NOT NULL) // Price per unit
- unit_of_measure (VARCHAR 20, DEFAULT 'PCS') // PCS, GRAM, PAIR
- is_active (BOOLEAN, DEFAULT TRUE)
- is_deleted (BOOLEAN, DEFAULT FALSE)
- created_at (TIMESTAMP NULL)
- updated_at (TIMESTAMP NULL)

INDEXES:

- PRIMARY KEY (id)
- INDEX (company_id)
- UNIQUE (company_id, process_code)
- INDEX (process_name)
- INDEX (process_type)

FOREIGN KEYS:

- company_id REFERENCES companies(id) ON DELETE CASCADE

METHODS REQUIRED:

- up(): Create table with all columns, indexes, ENUM type, constraints
- down(): Drop table

ADDITIONAL REQUIREMENTS:

- Process_type ENUM helps filter processes by type
- Rate per unit used in challan calculations
- Unit of measure must match challan line calculation logic

DELIVERABLES:
Complete migration file

ACCEPTANCE CRITERIA:

- Migration runs successfully
- ENUM type working for process_type
- Unique process_code per company enforced
- Rate per unit stored with 2 decimal precision
  \`\`\`

---

#### Subtask 2.2.4: Create ProductModel

\`\`\`
Subtask 2.2.4: Create ProductModel

TASK: Generate ProductModel with relationships and image handling

FILE: app/Models/ProductModel.php

CONTEXT:

- Products represent jewelry designs
- Multi-tenant: auto-filter by company_id
- Soft delete support
- Image upload handling in service layer
- Cannot delete if used in challans/invoices

REQUIREMENTS:
Create CodeIgniter 4 Model extending \CodeIgniter\Model with:

PROPERTIES:

- protected $table = 'products';
- protected $primaryKey = 'id';
- protected $useTimestamps = true;
- protected $allowedFields = [
  'company_id', 'category_id', 'product_code', 'product_name',
  'description', 'image_path', 'hsn_code', 'unit_of_measure',
  'is_active', 'is_deleted'
  ]
- protected $validationRules = [
  'company_id' => 'required|integer',
  'category_id' => 'required|integer',
  'product_code' => 'required|max_length[50]|is_unique[products.product_code,id,{id}]',
  'product_name' => 'required|min_length[3]|max_length[255]',
  'hsn_code' => 'permit_empty|max_length[20]',
  'unit_of_measure' => 'required|in_list[PCS,PAIR,SET,GRAM]'
  ]

METHODS REQUIRED:

1. public function findAll(int $limit = 0, int $offset = 0)
   - Override parent method
   - Apply company filter (from session)
   - where('is_deleted', FALSE)
   - Return parent::findAll($limit, $offset)

2. public function find($id = null)
   - Override parent method
   - Apply company filter
   - where('is_deleted', FALSE)
   - Return parent::find($id)

3. public function getActiveProducts(int $categoryId = null): array
   - where('company_id', session company_id)
   - where('is_active', TRUE)
   - where('is_deleted', FALSE)
   - If $categoryId provided: where('category_id', $categoryId)
   - orderBy('product_name', 'ASC')
   - Return findAll()

4. public function getProductWithCategory(int $id): ?array
   - Select products.\*, product_categories.category_name
   - Join product_categories
   - where('products.id', $id)
   - Apply company filter
   - Return result or null

5. public function isProductUsedInTransactions(int $productId): bool
   - Check if product_id exists in challan_lines table
   - Return TRUE if found, FALSE otherwise

6. public function searchProducts(string $query): array
   - where('company_id', session company_id)
   - where('is_active', TRUE)
   - where('is_deleted', FALSE)
   - like('product_name', $query) OR like('product_code', $query)
   - limit(20)
   - Return results for autocomplete

ADDITIONAL REQUIREMENTS:

- Use proper type hints
- Handle null values gracefully
- Company filter auto-applied (except super admin)
- Soft delete respected in all queries

DELIVERABLES:
Complete ProductModel.php file

ACCEPTANCE CRITERIA:

- Model auto-filters by company_id
- Validation rules enforced
- Cannot delete if used in transactions
- Active products query works for dropdowns
- Search works for autocomplete
  \`\`\`

---

#### Subtask 2.2.5: Create ProcessModel

\`\`\`
Subtask 2.2.5: Create ProcessModel

TASK: Generate ProcessModel with price tracking

FILE: app/Models/ProcessModel.php

CONTEXT:

- Processes represent manufacturing operations
- Multi-tenant: auto-filter by company_id
- Soft delete support
- Price changes must be audit logged (handled in service)
- Cannot delete if used in challans

REQUIREMENTS:
Create CodeIgniter 4 Model extending \CodeIgniter\Model with:

PROPERTIES:

- protected $table = 'processes';
- protected $primaryKey = 'id';
- protected $useTimestamps = true;
- protected $allowedFields = [
  'company_id', 'process_code', 'process_name', 'process_type',
  'description', 'rate_per_unit', 'unit_of_measure',
  'is_active', 'is_deleted'
  ]
- protected $validationRules = [
  'company_id' => 'required|integer',
  'process_code' => 'required|max_length[50]|is_unique[processes.process_code,id,{id}]',
  'process_name' => 'required|min_length[3]|max_length[255]',
  'process_type' => 'required|in_list[Rhodium,Meena,Wax,Polish,Coating,Other]',
  'rate_per_unit' => 'required|decimal|greater_than[0]',
  'unit_of_measure' => 'required|in_list[PCS,GRAM,PAIR,SET]'
  ]

METHODS REQUIRED:

1. public function findAll(int $limit = 0, int $offset = 0)
   - Override parent method
   - Apply company filter (from session)
   - where('is_deleted', FALSE)
   - Return parent::findAll($limit, $offset)

2. public function find($id = null)
   - Override parent method
   - Apply company filter
   - where('is_deleted', FALSE)
   - Return parent::find($id)

3. public function getActiveProcesses(string $processType = null): array
   - where('company_id', session company_id)
   - where('is_active', TRUE)
   - where('is_deleted', FALSE)
   - If $processType provided: where('process_type', $processType)
   - orderBy('process_name', 'ASC')
   - Return findAll()

4. public function getProcessesByType(string $type): array
   - where('company_id', session company_id)
   - where('process_type', $type)
   - where('is_active', TRUE)
   - where('is_deleted', FALSE)
   - orderBy('process_name', 'ASC')
   - Return findAll()

5. public function isProcessUsedInTransactions(int $processId): bool
   - Check if process_id exists in challan_lines table
   - Return TRUE if found, FALSE otherwise

6. public function getCurrentRate(int $processId): ?float
   - Select rate_per_unit
   - where('id', $processId)
   - Return rate or null

ADDITIONAL REQUIREMENTS:

- Process type validation ensures only valid types
- Rate per unit must be greater than zero
- Company filter auto-applied
- Soft delete respected

DELIVERABLES:
Complete ProcessModel.php file

ACCEPTANCE CRITERIA:

- Model auto-filters by company_id
- Process type ENUM validation working
- Rate retrieval method works
- Cannot delete if used in transactions
- Active processes query for dropdowns
  \`\`\`

---

#### Subtask 2.2.6: Create ProductService

\`\`\`
Subtask 2.2.6: Create ProductService

TASK: Generate ProductService with business logic

FILE: app/Services/Master/ProductService.php

CONTEXT:

- Handle product CRUD operations
- Image upload/delete via FileUploadService
- Cannot delete if product used in challans/invoices
- Audit log all changes
- Validate product code uniqueness per company

REQUIREMENTS:
Create ProductService class with:

DEPENDENCIES (inject in \_\_construct):

- ProductModel
- ProductCategoryModel (to validate category exists)
- FileUploadService
- AuditService

METHODS REQUIRED:

1. public function createProduct(array $data): int
   - Validate required fields present
   - Check category exists and belongs to company
   - Auto-set company_id from session
   - Check unique product_code per company
   - If image uploaded: call FileUploadService->uploadImage()
   - Insert product record
   - Audit log create action
   - Return product ID

2. public function updateProduct(int $id, array $data): bool
   - Validate product exists and belongs to company
   - If product_code changed: check uniqueness
   - If image uploaded:
     - Delete old image (if exists)
     - Upload new image
   - Store before data for audit
   - Update product record
   - Audit log update action (include before/after)
   - Return TRUE

3. public function deleteProduct(int $id): bool
   - Validate product exists and belongs to company
   - Check if product used: ProductModel->isProductUsedInTransactions($id)
   - If used: throw exception "Cannot delete product used in transactions"
   - Soft delete: set is_deleted = TRUE
   - Delete image file if exists
   - Audit log delete action
   - Return TRUE

4. public function getActiveProducts(int $categoryId = null): array
   - Call ProductModel->getActiveProducts($categoryId)
   - Return array for dropdown

5. public function getProductById(int $id): ?array
   - Call ProductModel->getProductWithCategory($id)
   - Validate belongs to company
   - Return product data or null

6. public function searchProducts(string $query): array
   - Call ProductModel->searchProducts($query)
   - Return results (for autocomplete)

7. private function validateProductData(array $data): void
   - Check all required fields present
   - Validate product_code format (alphanumeric, hyphen, underscore only)
   - Validate hsn_code format if provided
   - Throw ValidationException if invalid

8. private function checkUniqueProductCode(string $code, int $companyId, int $excludeId = null): bool
   - Query products table
   - where('company_id', $companyId)
   - where('product_code', $code)
   - where('is_deleted', FALSE)
   - if $excludeId: where('id !=', $excludeId)
   - Return count() === 0

ERROR HANDLING:

- Throw ProductNotFoundException if product not found
- Throw ValidationException for invalid data
- Throw ProductInUseException if cannot delete
- Log all exceptions

DELIVERABLES:
Complete ProductService.php file

ACCEPTANCE CRITERIA:

- CRUD operations working
- Image upload/delete handled
- Cannot delete if product in use
- All actions audit logged
- Validation enforced
- Exceptions properly thrown
  \`\`\`

---

#### Subtask 2.2.7: Create ProcessService

\`\`\`
read .antigravity content and then
Subtask 2.2.7: Create ProcessService

TASK: Generate ProcessService with price change tracking

FILE: app/Services/Master/ProcessService.php

CONTEXT:

- Handle process CRUD operations
- Track price changes in audit log (critical for financial accuracy)
- Cannot delete if process used in challans
- Validate process code uniqueness per company
- Calculate total rate from multiple processes

REQUIREMENTS:
Create ProcessService class with:

DEPENDENCIES (inject in \_\_construct):

- ProcessModel
- AuditService

METHODS REQUIRED:

1. public function createProcess(array $data): int
   - Validate required fields present
   - Auto-set company_id from session
   - Check unique process_code per company
   - Validate rate_per_unit > 0
   - Insert process record
   - Audit log create action
   - Return process ID

2. public function updateProcess(int $id, array $data): bool
   - Validate process exists and belongs to company
   - Store before data for audit
   - If rate_per_unit changed:
     - Audit log with special flag "PRICE_CHANGE"
     - Include old rate and new rate in audit data
   - If process_code changed: check uniqueness
   - Update process record
   - Audit log update action
   - Return TRUE

3. public function deleteProcess(int $id): bool
   - Validate process exists and belongs to company
   - Check if process used: ProcessModel->isProcessUsedInTransactions($id)
   - If used: throw exception "Cannot delete process used in transactions"
   - Soft delete: set is_deleted = TRUE
   - Audit log delete action
   - Return TRUE

4. public function getActiveProcesses(string $processType = null): array
   - Call ProcessModel->getActiveProcesses($processType)
   - Return array for dropdown

5. public function getProcessById(int $id): ?array
   - Call ProcessModel->find($id)
   - Validate belongs to company
   - Return process data or null

6. public function getProcessesByType(string $type): array
   - Call ProcessModel->getProcessesByType($type)
   - Return array (for filtered dropdowns)

7. public function calculateTotalProcessRate(array $processIds, array $quantities = []): float
   - For each process ID:
     - Fetch current rate
     - If quantities array provided: rate \* quantity
     - Else: rate \* 1
   - Sum all amounts
   - Return total

8. private function validateProcessData(array $data): void
   - Check all required fields present
   - Validate process_code format
   - Validate process_type in allowed ENUM values
   - Validate rate_per_unit > 0 and <= 1000000 (sanity check)
   - Throw ValidationException if invalid

9. private function checkUniqueProcessCode(string $code, int $companyId, int $excludeId = null): bool
   - Query processes table
   - where('company_id', $companyId)
   - where('process_code', $code)
   - where('is_deleted', FALSE)
   - if $excludeId: where('id !=', $excludeId)
   - Return count() === 0

ERROR HANDLING:

- Throw ProcessNotFoundException if not found
- Throw ValidationException for invalid data
- Throw ProcessInUseException if cannot delete
- Log all exceptions

DELIVERABLES:
Complete ProcessService.php file

ACCEPTANCE CRITERIA:

- CRUD operations working
- Price changes audit logged with special flag
- Cannot delete if process in use
- Calculate total rate method works
- All actions audit logged
- Validation enforced
  \`\`\`

---

#### Subtask 2.2.8: Create ProductController

\`\`\`
read .antigravity content and then
Subtask 2.2.8: Create ProductController
TASK: Generate ProductController with CRUD endpoints

FILE: app/Controllers/Masters/ProductController.php

CONTEXT:

- Handle HTTP requests for product management
- Thin controller, business logic in ProductService
- Permission checks: product.create, product.edit, product.delete, product.view
- JSON API responses for AJAX calls
- Support both web pages and API endpoints

REQUIREMENTS:
Create CodeIgniter 4 Controller extending BaseController:

DEPENDENCIES (inject in \_\_construct):

- ProductService
- ProductCategoryModel (for category dropdown)
- PermissionService

ROUTES REQUIRED:

- GET /masters/products → index() (list all products)
- GET /masters/products/create → create() (show form)
- POST /masters/products → store() (create new)
- GET /masters/products/{id} → show() (view details)
- GET /masters/products/{id}/edit → edit() (show edit form)
- POST /masters/products/{id} → update() (update existing)
- DELETE /masters/products/{id} → delete() (soft delete)
- GET /masters/products/search → search() (autocomplete API)

METHODS REQUIRED:

1. public function index()
   - Check permission: product.view
   - Get query params: category_id, is_active, search
   - Load products via ProductService
   - If AJAX: return JSON
   - Else: load view with products data
   - View: app/Views/masters/products/index.php

2. public function create()
   - Check permission: product.create
   - Load categories dropdown
   - Load view: app/Views/masters/products/create.php
   - Pass categories data

3. public function store()
   - Check permission: product.create
   - Validate CSRF token
   - Get POST data
   - Handle file upload (image)
   - Call ProductService->createProduct($data)
   - Set flash message: "Product created successfully"
   - Redirect to /masters/products

4. public function show(int $id)
   - Check permission: product.view
   - Load product via ProductService->getProductById($id)
   - If not found: 404
   - Load view: app/Views/masters/products/show.php
   - Pass product data

5. public function edit(int $id)
   - Check permission: product.edit
   - Load product via ProductService->getProductById($id)
   - If not found: 404
   - Load categories dropdown
   - Load view: app/Views/masters/products/edit.php
   - Pass product and categories data

6. public function update(int $id)
   - Check permission: product.edit
   - Validate CSRF token
   - Get POST data
   - Handle file upload (image) if provided
   - Call ProductService->updateProduct($id, $data)
   - Set flash message: "Product updated successfully"
   - Redirect to /masters/products

7. public function delete(int $id)
   - Check permission: product.delete
   - Try: ProductService->deleteProduct($id)
   - Catch ProductInUseException: return JSON error
   - Set flash message: "Product deleted successfully"
   - Return JSON success

8. public function search()
   - Check permission: product.view
   - Get query param: q
   - Call ProductService->searchProducts($q)
   - Return JSON results for autocomplete

ERROR HANDLING:

- Catch all exceptions
- Return JSON error for AJAX requests
- Show flash message and redirect for web requests
- Log errors

DELIVERABLES:
Complete ProductController.php file

ACCEPTANCE CRITERIA:

- All CRUD operations working
- Permission checks enforced
- Image upload handled
- AJAX search working
- Flash messages shown
- Error handling robust
  \`\`\`

---

#### Subtask 2.2.9: Create ProcessController

\`\`\`
read .antigravity content and then
Subtask 2.2.9: Create ProcessController
TASK: Generate ProcessController with CRUD endpoints

FILE: app/Controllers/Masters/ProcessController.php

CONTEXT:

- Handle HTTP requests for process management
- Thin controller, business logic in ProcessService
- Permission checks: process.create, process.edit, process.delete, process.view
- JSON API responses for AJAX calls

REQUIREMENTS:
Create CodeIgniter 4 Controller extending BaseController:

DEPENDENCIES (inject in \_\_construct):

- ProcessService
- PermissionService

ROUTES REQUIRED:

- GET /masters/processes → index() (list all processes)
- GET /masters/processes/create → create() (show form)
- POST /masters/processes → store() (create new)
- GET /masters/processes/{id} → show() (view details)
- GET /masters/processes/{id}/edit → edit() (show edit form)
- POST /masters/processes/{id} → update() (update existing)
- DELETE /masters/processes/{id} → delete() (soft delete)
- GET /masters/processes/by-type/{type} → getByType() (filter API)

METHODS REQUIRED:

1. public function index()
   - Check permission: process.view
   - Get query params: process_type, is_active, search
   - Load processes via ProcessService
   - If AJAX: return JSON
   - Else: load view with processes data
   - View: app/Views/masters/processes/index.php

2. public function create()
   - Check permission: process.create
   - Load view: app/Views/masters/processes/create.php
   - Pass process types for dropdown

3. public function store()
   - Check permission: process.create
   - Validate CSRF token
   - Get POST data
   - Call ProcessService->createProcess($data)
   - Set flash message: "Process created successfully"
   - Redirect to /masters/processes

4. public function show(int $id)
   - Check permission: process.view
   - Load process via ProcessService->getProcessById($id)
   - If not found: 404
   - Load view: app/Views/masters/processes/show.php
   - Pass process data

5. public function edit(int $id)
   - Check permission: process.edit
   - Load process via ProcessService->getProcessById($id)
   - If not found: 404
   - Load view: app/Views/masters/processes/edit.php
   - Pass process data and types

6. public function update(int $id)
   - Check permission: process.edit
   - Validate CSRF token
   - Get POST data
   - Call ProcessService->updateProcess($id, $data)
   - Set flash message: "Process updated successfully"
   - If rate changed: add special message "Price updated - change logged"
   - Redirect to /masters/processes

7. public function delete(int $id)
   - Check permission: process.delete
   - Try: ProcessService->deleteProcess($id)
   - Catch ProcessInUseException: return JSON error
   - Set flash message: "Process deleted successfully"
   - Return JSON success

8. public function getByType(string $type)
   - Check permission: process.view
   - Call ProcessService->getProcessesByType($type)
   - Return JSON results

ERROR HANDLING:

- Catch all exceptions
- Return JSON error for AJAX
- Flash messages for web
- Log errors

DELIVERABLES:
Complete ProcessController.php file

ACCEPTANCE CRITERIA:

- All CRUD operations working
- Permission checks enforced
- Process type filtering works
- Price change message shown
- Error handling robust
  \`\`\`

---

### ADDITIONAL SUBTASKS FOR TASK 2.2

---

#### Subtask 2.2.10: Create Product Category Views

\`\`\`
read .antigravity content and then
Subtask 2.2.10: Create Product Category Views
TASK: Create view files for Product Category management

FILES TO CREATE:

1. app/Views/masters/product_categories/index.php
2. app/Views/masters/product_categories/create.php
3. app/Views/masters/product_categories/edit.php

CONTEXT:

- Follow existing view pattern from app/Views/users/
- Use Bootstrap 5 for styling
- Include DataTables for list view
- Form validation with client-side checks
- Breadcrumb navigation
- Flash message display

VIEW 1: index.php (List View)
REQUIREMENTS:

- Page title: "Product Categories"
- Breadcrumb: Home > Masters > Product Categories
- Action button: "Add New Category" (if user has product_category.create permission)
- DataTable with columns:
  - Category Code
  - Category Name
  - Description
  - Display Order
  - Status (Active/Inactive badge)
  - Actions (Edit, Delete buttons based on permissions)
- Filter: Active/Inactive/All dropdown
- Search: Real-time search in DataTable
- Delete confirmation modal
- AJAX delete with success/error toast

VIEW 2: create.php (Create Form)
REQUIREMENTS:

- Page title: "Add Product Category"
- Breadcrumb: Home > Masters > Product Categories > Add
- Form fields:
  - Category Code (required, auto-generate button)
  - Category Name (required)
  - Description (textarea, optional)
  - Display Order (number, default 0)
  - Is Active (checkbox, default checked)
- Client-side validation
- Cancel button (redirect to index)
- Submit button (POST to /masters/product-categories)

VIEW 3: edit.php (Edit Form)
REQUIREMENTS:

- Same as create.php but:
  - Page title: "Edit Product Category"
  - Form pre-filled with existing data
  - Category Code read-only (cannot change)
  - Submit button (POST to /masters/product-categories/{id})

COMMON ELEMENTS:

- Include header/footer from layout
- CSRF token in forms
- Error message display
- Loading spinner on submit

DELIVERABLES:
3 complete view files following CodeIgniter 4 view patterns

ACCEPTANCE CRITERIA:

- Views render correctly
- DataTable initializes
- Forms submit successfully
- Validation works
- Permissions control button visibility
  \`\`\`

---

#### Subtask 2.2.11: Create Product Views

\`\`\`
read .antigravity content and then
Subtask 2.2.11: Create Product Views
TASK: Create view files for Product management

FILES TO CREATE:

1. app/Views/masters/products/index.php
2. app/Views/masters/products/create.php
3. app/Views/masters/products/edit.php
4. app/Views/masters/products/show.php

CONTEXT:

- Follow existing view pattern
- Image upload with preview
- Category dropdown filter
- DataTables with image thumbnails

VIEW 1: index.php (List View)
REQUIREMENTS:

- Page title: "Products"
- Breadcrumb: Home > Masters > Products
- Action button: "Add New Product"
- Filters:
  - Category dropdown
  - Status dropdown (Active/Inactive/All)
- DataTable columns:
  - Image (thumbnail 50x50)
  - Product Code
  - Product Name
  - Category
  - HSN Code
  - Unit
  - Status
  - Actions
- Click on image: show full-size modal
- AJAX delete with confirmation

VIEW 2: create.php (Create Form)
REQUIREMENTS:

- Page title: "Add Product"
- Form fields:
  - Product Code (required, auto-generate option)
  - Product Name (required)
  - Category (dropdown, required)
  - Description (textarea)
  - HSN Code
  - Unit of Measure (dropdown: PCS, PAIR, SET, GRAM)
  - Image Upload (with preview)
  - Is Active (checkbox)
- Image preview on file select
- Client validation
- Submit button

VIEW 3: edit.php (Edit Form)
REQUIREMENTS:

- Same as create.php
- Show existing image with "Change Image" option
- Product Code read-only

VIEW 4: show.php (Details View)
REQUIREMENTS:

- Page title: "Product Details"
- Display all fields in read-only format
- Show image (full size)
- Action buttons: Edit, Delete (based on permissions)
- Back button

DELIVERABLES:
4 complete view files with image handling

ACCEPTANCE CRITERIA:

- Image upload works
- Preview shows correctly
- DataTable displays images
- All CRUD operations functional
  \`\`\`

---

#### Subtask 2.2.12: Create Process Views

\`\`\`
read .antigravity content and then
Subtask 2.2.12: Create Process Views
TASK: Create view files for Process management

FILES TO CREATE:

1. app/Views/masters/processes/index.php
2. app/Views/masters/processes/create.php
3. app/Views/masters/processes/edit.php
4. app/Views/masters/processes/show.php

CONTEXT:

- Follow existing view pattern
- Process type filter
- Rate display with currency formatting
- Price change history (future enhancement)

VIEW 1: index.php (List View)
REQUIREMENTS:

- Page title: "Manufacturing Processes"
- Breadcrumb: Home > Masters > Processes
- Action button: "Add New Process"
- Filters:
  - Process Type dropdown (All, Rhodium, Meena, Wax, Polish, Coating, Other)
  - Status dropdown
- DataTable columns:
  - Process Code
  - Process Name
  - Process Type (badge with color coding)
  - Rate per Unit (₹ formatted)
  - Unit
  - Status
  - Actions
- Color coding:
  - Rhodium: Blue
  - Meena: Green
  - Wax: Orange
  - Polish: Purple
  - Other: Gray

VIEW 2: create.php (Create Form)
REQUIREMENTS:

- Page title: "Add Process"
- Form fields:
  - Process Code (required, auto-generate)
  - Process Name (required)
  - Process Type (dropdown, required)
  - Description (textarea)
  - Rate per Unit (number with 2 decimals, required)
  - Unit of Measure (dropdown: PCS, GRAM, PAIR, SET)
  - Is Active (checkbox)
- Rate per unit with ₹ symbol prefix
- Client validation: rate > 0

VIEW 3: edit.php (Edit Form)
REQUIREMENTS:

- Same as create.php
- Process Code read-only
- Show "Current Rate" badge
- If rate changed: show warning message about price change logging

VIEW 4: show.php (Details View)
REQUIREMENTS:

- Page title: "Process Details"
- Display all fields
- Show audit trail for price changes (optional, future)
- Action buttons: Edit, Delete

DELIVERABLES:
4 complete view files with rate formatting

ACCEPTANCE CRITERIA:

- Process type colors working
- Rate formatted as currency
- Type filter functional
- All CRUD operations working
  \`\`\`

---

#### Subtask 2.2.13: Add Product & Process Routes

\`\`\`
read .antigravity content and then
Subtask 2.2.13: Add Product & Process Routes
TASK: Configure routes for Products and Processes

FILE: app/Config/Routes.php

CONTEXT:

- RESTful route structure
- Apply AuthFilter and PermissionFilter
- Group routes under /masters prefix
- Follow existing route pattern

REQUIREMENTS:
Add the following route groups:

PRODUCT CATEGORY ROUTES:
\`\`\`php
$routes->group('masters/product-categories', ['filter' => 'auth', 'filter' => 'permission:product_category'], function($routes) {
$routes->get('/', 'Masters\ProductCategoryController::index');
$routes->get('create', 'Masters\ProductCategoryController::create');
$routes->post('/', 'Masters\ProductCategoryController::store');
$routes->get('(:num)', 'Masters\ProductCategoryController::show/$1');
$routes->get('(:num)/edit', 'Masters\ProductCategoryController::edit/$1');
$routes->post('(:num)', 'Masters\ProductCategoryController::update/$1');
$routes->delete('(:num)', 'Masters\ProductCategoryController::delete/$1');
});
\`\`\`

PRODUCT ROUTES:
\`\`\`php
$routes->group('masters/products', ['filter' => 'auth', 'filter' => 'permission:product'], function($routes) {
$routes->get('/', 'Masters\ProductController::index');
$routes->get('create', 'Masters\ProductController::create');
$routes->post('/', 'Masters\ProductController::store');
$routes->get('search', 'Masters\ProductController::search'); // API endpoint
$routes->get('(:num)', 'Masters\ProductController::show/$1');
$routes->get('(:num)/edit', 'Masters\ProductController::edit/$1');
$routes->post('(:num)', 'Masters\ProductController::update/$1');
$routes->delete('(:num)', 'Masters\ProductController::delete/$1');
});
\`\`\`

PROCESS ROUTES:
\`\`\`php
$routes->group('masters/processes', ['filter' => 'auth', 'filter' => 'permission:process'], function($routes) {
$routes->get('/', 'Masters\ProcessController::index');
$routes->get('create', 'Masters\ProcessController::create');
$routes->post('/', 'Masters\ProcessController::store');
$routes->get('by-type/(:alpha)', 'Masters\ProcessController::getByType/$1'); // API
$routes->get('(:num)', 'Masters\ProcessController::show/$1');
$routes->get('(:num)/edit', 'Masters\ProcessController::edit/$1');
$routes->post('(:num)', 'Masters\ProcessController::update/$1');
$routes->delete('(:num)', 'Masters\ProcessController::delete/$1');
});
\`\`\`

DELIVERABLES:
Updated Routes.php with all product and process routes

ACCEPTANCE CRITERIA:

- All routes working
- Filters applied correctly
- Permission checks enforced
- RESTful structure maintained
  \`\`\`

---

#### Subtask 2.2.14: Add Products & Processes to Sidebar

\`\`\`
read .antigravity content and then
Subtask 2.2.14: Add Products & Processes to Sidebar
TASK: Add Products and Processes menu items to sidebar navigation

FILE: app/Views/layouts/sidebar.php (or main.php if sidebar is embedded)

CONTEXT:

- Add under "Masters" dropdown menu
- Show only if user has view permission
- Highlight active menu item
- Font Awesome icons

REQUIREMENTS:
Add the following menu structure:

\`\`\`html

<!-- Masters Dropdown -->
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="mastersDropdown" role="button" data-bs-toggle="dropdown">
        <i class="fas fa-database"></i> Masters
    </a>
    <ul class="dropdown-menu" aria-labelledby="mastersDropdown">
        <?php if (can('gold_rate.view')): ?>
        <li>
            <a class="dropdown-item" href="<?= base_url('masters/gold-rates') ?>">
                <i class="fas fa-coins"></i> Gold Rates
            </a>
        </li>
        <?php endif; ?>

        <?php if (can('product_category.view')): ?>
        <li>
            <a class="dropdown-item" href="<?= base_url('masters/product-categories') ?>">
                <i class="fas fa-tags"></i> Product Categories
            </a>
        </li>
        <?php endif; ?>

        <?php if (can('product.view')): ?>
        <li>
            <a class="dropdown-item" href="<?= base_url('masters/products') ?>">
                <i class="fas fa-gem"></i> Products
            </a>
        </li>
        <?php endif; ?>

        <?php if (can('process.view')): ?>
        <li>
            <a class="dropdown-item" href="<?= base_url('masters/processes') ?>">
                <i class="fas fa-cogs"></i> Processes
            </a>
        </li>
        <?php endif; ?>
    </ul>

</li>
\`\`\`

HELPER FUNCTION (if not exists):
Add to app/Helpers/permission_helper.php:

\`\`\`php
if (!function_exists('can')) {
function can(string $permission): bool {
        $permissionService = service('PermissionService');
        return $permissionService->can($permission);
}
}
\`\`\`

ACTIVE MENU HIGHLIGHTING:
Add class 'active' to current menu item based on current URL

DELIVERABLES:
Updated sidebar with Products and Processes menu items

ACCEPTANCE CRITERIA:

- Menu items visible based on permissions
- Active menu highlighting works
- Icons display correctly
- Dropdown works smoothly
  \`\`\`

---

### TASK 2.3: Account & Cash Customer Management

---

#### Subtask 2.3.1: Create accounts Migration

\`\`\`
read .antigravity content and then
Subtask 2.3.1: Create accounts Migration

TASK: Generate migration file for accounts table

FILE: app/Database/Migrations/2026-01-01-000010_create_accounts_table.php

CONTEXT:

- Account customers are businesses with credit terms
- Complete billing and shipping address capture
- GST and PAN for tax compliance
- Opening balance creates initial ledger entry
- Account code auto-generated or manual

REQUIREMENTS:
Create CodeIgniter 4 migration with:

TABLE STRUCTURE:

- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- company_id (INT, FK to companies.id, NOT NULL)
- account_code (VARCHAR 50, NOT NULL)
- account_name (VARCHAR 255, NOT NULL)
- business_name (VARCHAR 255, NULL)
- contact_person (VARCHAR 100, NULL)
- mobile (VARCHAR 20, NOT NULL)
- email (VARCHAR 100, NULL)
- gst_number (VARCHAR 15, NULL)
- pan_number (VARCHAR 10, NULL)

// Billing Address

- billing_address_line1 (VARCHAR 255, NOT NULL)
- billing_address_line2 (VARCHAR 255, NULL)
- billing_city (VARCHAR 100, NOT NULL)
- billing_state_id (INT, FK to states.id, NOT NULL)
- billing_pincode (VARCHAR 10, NOT NULL)

// Shipping Address (optional, defaults to billing)

- shipping_address_line1 (VARCHAR 255, NULL)
- shipping_address_line2 (VARCHAR 255, NULL)
- shipping_city (VARCHAR 100, NULL)
- shipping_state_id (INT, FK to states.id, NULL)
- shipping_pincode (VARCHAR 10, NULL)
- same_as_billing (BOOLEAN, DEFAULT TRUE)

// Financial

- opening_balance (DECIMAL 15,2, DEFAULT 0.00)
- opening_balance_type (ENUM('Debit', 'Credit'), DEFAULT 'Debit')
- current_balance (DECIMAL 15,2, DEFAULT 0.00)
- credit_limit (DECIMAL 15,2, DEFAULT 0.00) // Future use
- payment_terms (VARCHAR 50, NULL) // e.g., "Net 30 days"

// Metadata

- notes (TEXT, NULL)
- is_active (BOOLEAN, DEFAULT TRUE)
- is_deleted (BOOLEAN, DEFAULT FALSE)
- created_at (TIMESTAMP NULL)
- updated_at (TIMESTAMP NULL)

INDEXES:

- PRIMARY KEY (id)
- INDEX (company_id)
- UNIQUE (company_id, account_code)
- INDEX (account_name)
- INDEX (mobile)
- INDEX (billing_state_id)
- INDEX (shipping_state_id)

FOREIGN KEYS:

- company_id REFERENCES companies(id) ON DELETE CASCADE
- billing_state_id REFERENCES states(id) ON DELETE RESTRICT
- shipping_state_id REFERENCES states(id) ON DELETE RESTRICT

METHODS REQUIRED:

- up(): Create table with all columns, indexes, ENUM, constraints
- down(): Drop table

ADDITIONAL REQUIREMENTS:

- opening_balance_type determines if opening balance is receivable (Debit) or payable (Credit)
- current_balance updated by ledger entries
- GST/PAN optional (not all customers are GST registered)
- same_as_billing flag helps UI default shipping address

DELIVERABLES:
Complete migration file

ACCEPTANCE CRITERIA:

- Migration runs successfully
- ENUM type working
- Unique account_code per company enforced
- Foreign keys working
- Opening balance decimal precision correct
  \`\`\`

---

#### Subtask 2.3.2: Create cash_customers Migration

\`\`\`
read .antigravity content and then
Subtask 2.3.2: Create cash_customers Migration

TASK: Generate migration file for cash_customers table

FILE: app/Database/Migrations/2026-01-01-000011_create_cash_customers_table.php

CONTEXT:

- Cash customers are walk-in customers without credit terms
- Minimal data capture: name, mobile, address (optional)
- Deduplication on name + mobile per company
- No opening balance (all transactions COD or immediate payment)
- No credit limit

REQUIREMENTS:
Create CodeIgniter 4 migration with:

TABLE STRUCTURE:

- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- company_id (INT, FK to companies.id, NOT NULL)
- customer_name (VARCHAR 255, NOT NULL)
- mobile (VARCHAR 20, NOT NULL)
- email (VARCHAR 100, NULL)
- address_line1 (VARCHAR 255, NULL)
- address_line2 (VARCHAR 255, NULL)
- city (VARCHAR 100, NULL)
- state_id (INT, FK to states.id, NULL)
- pincode (VARCHAR 10, NULL)
- notes (TEXT, NULL)
- is_active (BOOLEAN, DEFAULT TRUE)
- is_deleted (BOOLEAN, DEFAULT FALSE)
- created_at (TIMESTAMP NULL)
- updated_at (TIMESTAMP NULL)

INDEXES:

- PRIMARY KEY (id)
- INDEX (company_id)
- UNIQUE (company_id, customer_name, mobile) // Prevents duplicates
- INDEX (customer_name)
- INDEX (mobile)
- INDEX (state_id)

FOREIGN KEYS:

- company_id REFERENCES companies(id) ON DELETE CASCADE
- state_id REFERENCES states(id) ON DELETE RESTRICT

METHODS REQUIRED:

- up(): Create table with all columns, indexes, constraints
- down(): Drop table

ADDITIONAL REQUIREMENTS:

- Unique constraint on (company_id, customer_name, mobile) prevents duplicate cash customers
- Address fields optional (many cash customers don't provide)
- No financial fields (current_balance, opening_balance) because all transactions immediate

BUSINESS LOGIC NOTE:

- Before creating cash customer, check if name+mobile exists
- If exists, reuse existing customer (handled in service layer)
- This ensures no duplicate cash customers

DELIVERABLES:
Complete migration file

ACCEPTANCE CRITERIA:

- Migration runs successfully
- Unique constraint on company_id + customer_name + mobile works
- Foreign keys working
- Optional address fields allow NULL
  \`\`\`

---

#### Subtask 2.3.3: Create AccountModel

\`\`\`
read .antigravity content and then
Subtask 2.3.3: Create AccountModel

TASK: Generate AccountModel with balance tracking

FILE: app/Models/AccountModel.php

CONTEXT:

- Account customers have opening balance
- Current balance updated by ledger entries
- Multi-tenant: auto-filter by company_id
- Soft delete support
- Cannot delete if transactions exist

REQUIREMENTS:
Create CodeIgniter 4 Model extending \CodeIgniter\Model with:

PROPERTIES:

- protected $table = 'accounts';
- protected $primaryKey = 'id';
- protected $useTimestamps = true;
- protected $allowedFields = [
  'company_id', 'account_code', 'account_name', 'business_name',
  'contact_person', 'mobile', 'email', 'gst_number', 'pan_number',
  'billing_address_line1', 'billing_address_line2', 'billing_city',
  'billing_state_id', 'billing_pincode',
  'shipping_address_line1', 'shipping_address_line2', 'shipping_city',
  'shipping_state_id', 'shipping_pincode', 'same_as_billing',
  'opening_balance', 'opening_balance_type', 'current_balance',
  'credit_limit', 'payment_terms', 'notes',
  'is_active', 'is_deleted'
  ]
- protected $validationRules = [
    'company_id' => 'required|integer',
    'account_code' => 'required|max_length[50]|is_unique[accounts.account_code,id,{id}]',
    'account_name' => 'required|min_length[3]|max_length[255]',
    'mobile' => 'required|regex_match[/^[0-9]{10}$/]',
  'email' => 'permit_empty|valid_email',
  'gst_number' => 'permit_empty|exact_length[15]',
  'pan_number' => 'permit_empty|exact_length[10]',
  'billing_state_id' => 'required|integer',
  'billing_pincode' => 'required|exact_length[6]'
  ]

METHODS REQUIRED:

1. public function findAll(int $limit = 0, int $offset = 0)
   - Override parent method
   - Apply company filter (from session)
   - where('is_deleted', FALSE)
   - Return parent::findAll($limit, $offset)

2. public function find($id = null)
   - Override parent method
   - Apply company filter
   - where('is_deleted', FALSE)
   - Return parent::find($id)

3. public function getActiveAccounts(): array
   - where('company_id', session company_id)
   - where('is_active', TRUE)
   - where('is_deleted', FALSE)
   - orderBy('account_name', 'ASC')
   - Return findAll()

4. public function getAccountWithBalance(int $id): ?array
   - Select accounts.\*, states.state_name as billing_state
   - Join states on billing_state_id
   - where('accounts.id', $id)
   - Apply company filter
   - Return result or null

5. public function updateCurrentBalance(int $accountId, float $newBalance): bool
   - Update current_balance field
   - WHERE id = $accountId AND company_id = session
   - No validation (called by ledger service)
   - Return success

6. public function isAccountUsedInTransactions(int $accountId): bool
   - Check if account_id exists in invoices table
   - OR in challans table
   - OR in payments table
   - Return TRUE if found, FALSE otherwise

7. public function searchAccounts(string $query): array
   - where('company_id', session company_id)
   - where('is_active', TRUE)
   - where('is_deleted', FALSE)
   - like('account_name', $query) OR like('account_code', $query) OR like('mobile', $query)
   - limit(20)
   - Return results for autocomplete

8. public function generateNextAccountCode(): string
   - Query MAX(account_code) from accounts where company_id
   - Extract numeric part, increment
   - Return formatted code (e.g., "ACC-0001")

ADDITIONAL REQUIREMENTS:

- Auto-filter by company_id
- Validation rules enforced
- Opening balance can be Debit or Credit
- Current balance updated via ledger service only

DELIVERABLES:
Complete AccountModel.php file

ACCEPTANCE CRITERIA:

- Model auto-filters by company_id
- Validation rules working
- Balance update method works
- Cannot delete if used in transactions
- Search works for autocomplete
- Account code generation works
  \`\`\`

---

#### Subtask 2.3.4: Create CashCustomerModel

\`\`\`
read .antigravity content and then
Subtask 2.3.4: Create CashCustomerModel

TASK: Generate CashCustomerModel with deduplication logic

FILE: app/Models/CashCustomerModel.php

CONTEXT:

- Cash customers are walk-in customers
- Deduplication on name + mobile per company
- No opening balance or credit limit
- Multi-tenant: auto-filter by company_id
- Soft delete support

REQUIREMENTS:
Create CodeIgniter 4 Model extending \CodeIgniter\Model with:

PROPERTIES:

- protected $table = 'cash_customers';
- protected $primaryKey = 'id';
- protected $useTimestamps = true;
- protected $allowedFields = [
  'company_id', 'customer_name', 'mobile', 'email',
  'address_line1', 'address_line2', 'city', 'state_id', 'pincode',
  'notes', 'is_active', 'is_deleted'
  ]
- protected $validationRules = [
    'company_id' => 'required|integer',
    'customer_name' => 'required|min_length[3]|max_length[255]',
    'mobile' => 'required|regex_match[/^[0-9]{10}$/]',
  'email' => 'permit_empty|valid_email'
  ]

METHODS REQUIRED:

1. public function findAll(int $limit = 0, int $offset = 0)
   - Override parent method
   - Apply company filter (from session)
   - where('is_deleted', FALSE)
   - Return parent::findAll($limit, $offset)

2. public function find($id = null)
   - Override parent method
   - Apply company filter
   - where('is_deleted', FALSE)
   - Return parent::find($id)

3. public function getActiveCashCustomers(): array
   - where('company_id', session company_id)
   - where('is_active', TRUE)
   - where('is_deleted', FALSE)
   - orderBy('customer_name', 'ASC')
   - Return findAll()

4. public function findByNameAndMobile(string $name, string $mobile): ?array
   - where('company_id', session company_id)
   - where('customer_name', $name)
   - where('mobile', $mobile)
   - where('is_deleted', FALSE)
   - Return first() or null

5. public function searchCashCustomers(string $query): array
   - where('company_id', session company_id)
   - where('is_active', TRUE)
   - where('is_deleted', FALSE)
   - like('customer_name', $query) OR like('mobile', $query)
   - limit(20)
   - Return results for autocomplete

6. public function isCashCustomerUsedInTransactions(int $customerId): bool
   - Check if cash_customer_id exists in invoices table
   - OR in challans table
   - Return TRUE if found, FALSE otherwise

ADDITIONAL REQUIREMENTS:

- Deduplication handled in service layer
- Auto-filter by company_id
- Validation rules enforced

DELIVERABLES:
Complete CashCustomerModel.php file

ACCEPTANCE CRITERIA:

- Model auto-filters by company_id
- Name + mobile lookup works
- Search works for autocomplete
- Cannot delete if used in transactions
- Validation working
  \`\`\`

---



---

### TASK 2.3: ACCOUNT & CASH CUSTOMER MANAGEMENT (CONTINUED)

---

#### Subtask 2.3.5: Create AccountService

```
[PASTE .antigravity RULES FIRST]

TASK: Generate AccountService with ledger integration

FILE: app/Services/Customer/AccountService.php

CONTEXT:
- Handle account customer CRUD operations
- Create opening balance ledger entry on account creation
- Update current balance via ledger service
- Cannot delete if transactions exist
- Validate GST and PAN format
- Auto-generate account code if not provided

REQUIREMENTS:
Create AccountService class with:

DEPENDENCIES (inject in __construct):
- AccountModel
- StateModel (validate state exists)
- LedgerService (for opening balance entry)
- ValidationService (for GST/PAN validation)
- AuditService

METHODS REQUIRED:

1. public function createAccount(array $data): int
   - Validate required fields present
   - Auto-set company_id from session
   - If account_code not provided: auto-generate using AccountModel->generateNextAccountCode()
   - Validate billing_state_id exists
   - If shipping address provided: validate shipping_state_id
   - If same_as_billing = TRUE: copy billing address to shipping
   - Validate GST number format if provided (ValidationService)
   - Validate PAN number format if provided (ValidationService)
   - Start DB transaction
   - Insert account record
   - If opening_balance > 0:
     - Create opening balance ledger entry via LedgerService
     - Entry type: 'Opening Balance'
     - If opening_balance_type = 'Debit': debit entry (customer owes us)
     - If opening_balance_type = 'Credit': credit entry (we owe customer)
   - Commit transaction
   - Audit log create action
   - Return account ID

2. public function updateAccount(int $id, array $data): bool
   - Validate account exists and belongs to company
   - Store before data for audit
   - If account_code changed: check uniqueness
   - Validate states if changed
   - Validate GST/PAN if changed
   - Opening balance cannot be changed after creation (business rule)
   - Update account record
   - Audit log update action (include before/after)
   - Return TRUE

3. public function deleteAccount(int $id): bool
   - Validate account exists and belongs to company
   - Check if account used: AccountModel->isAccountUsedInTransactions($id)
   - If used: throw exception "Cannot delete account with transactions"
   - Soft delete: set is_deleted = TRUE
   - Audit log delete action
   - Return TRUE

4. public function getAccountById(int $id): ?array
   - Call AccountModel->getAccountWithBalance($id)
   - Validate belongs to company
   - Return account data with balance or null

5. public function getLedgerBalance(int $accountId): float
   - Call LedgerService->getAccountBalance($accountId)
   - Return current balance

6. public function updateCurrentBalance(int $accountId, float $newBalance): bool
   - Call AccountModel->updateCurrentBalance($accountId, $newBalance)
   - Return success

7. public function getActiveAccounts(): array
   - Call AccountModel->getActiveAccounts()
   - Return array for dropdown

8. public function searchAccounts(string $query): array
   - Call AccountModel->searchAccounts($query)
   - Return results for autocomplete

9. private function createOpeningBalanceLedgerEntry(int $accountId, float $amount, string $type): void
   - Call LedgerService->createOpeningBalanceEntry()
   - Pass: account_id, amount, type (Debit/Credit)
   - Entry date = account creation date
   - Description = "Opening Balance"

10. private function validateAccountData(array $data): void
    - Check all required fields present
    - Validate mobile format (10 digits)
    - Validate email format if provided
    - Validate pincode format (6 digits)
    - Throw ValidationException if invalid

ERROR HANDLING:
- Throw AccountNotFoundException if not found
- Throw ValidationException for invalid data
- Throw AccountInUseException if cannot delete
- Rollback transaction on error
- Log all exceptions

DELIVERABLES:
Complete AccountService.php file

ACCEPTANCE CRITERIA:
- CRUD operations working
- Opening balance ledger entry created
- GST/PAN validation working
- Cannot delete if transactions exist
- All actions audit logged
- Transaction safety ensured
```

---

#### Subtask 2.3.6: Create CashCustomerService

```
[PASTE .antigravity RULES FIRST]

TASK: Generate CashCustomerService with deduplication

FILE: app/Services/Customer/CashCustomerService.php

CONTEXT:
- Handle cash customer CRUD operations
- Deduplication: prevent duplicate name+mobile combinations
- Find-or-create pattern for quick customer addition
- No opening balance (all transactions immediate)
- Autocomplete search for quick lookup

REQUIREMENTS:
Create CashCustomerService class with:

DEPENDENCIES (inject in __construct):
- CashCustomerModel
- StateModel (validate state if provided)
- ValidationService
- AuditService

METHODS REQUIRED:

1. public function findOrCreate(string $name, string $mobile, array $additionalData = []): int
   - Trim and clean name and mobile
   - Check if customer exists: CashCustomerModel->findByNameAndMobile($name, $mobile)
   - If exists: return existing customer ID
   - If not exists:
     - Create new customer with provided data
     - Return new customer ID
   - This method enables quick customer creation at billing time

2. public function createCashCustomer(array $data): int
   - Validate required fields: name, mobile
   - Auto-set company_id from session
   - Check duplicate: findByNameAndMobile()
   - If duplicate exists: throw exception "Customer already exists with this name and mobile"
   - Validate state_id if provided
   - Insert cash customer record
   - Audit log create action
   - Return customer ID

3. public function updateCashCustomer(int $id, array $data): bool
   - Validate customer exists and belongs to company
   - Store before data for audit
   - If name or mobile changed: check no duplicate with new combination
   - Update cash customer record
   - Audit log update action
   - Return TRUE

4. public function deleteCashCustomer(int $id): bool
   - Validate customer exists and belongs to company
   - Check if used: CashCustomerModel->isCashCustomerUsedInTransactions($id)
   - If used: throw exception "Cannot delete customer with transactions"
   - Soft delete: set is_deleted = TRUE
   - Audit log delete action
   - Return TRUE

5. public function getCashCustomerById(int $id): ?array
   - Call CashCustomerModel->find($id)
   - Validate belongs to company
   - Return customer data or null

6. public function getActiveCashCustomers(): array
   - Call CashCustomerModel->getActiveCashCustomers()
   - Return array for dropdown

7. public function searchCashCustomers(string $query): array
   - Call CashCustomerModel->searchCashCustomers($query)
   - Return results for autocomplete
   - Format: [{id, name, mobile}, ...]

8. public function mergeDuplicates(int $primaryId, int $secondaryId): bool
   - Validate both customers exist and belong to company
   - Update all invoices: set cash_customer_id = primaryId where cash_customer_id = secondaryId
   - Update all challans: set cash_customer_id = primaryId where cash_customer_id = secondaryId
   - Delete secondary customer (soft delete)
   - Audit log merge action
   - Return TRUE
   - **Use transaction**

9. private function validateCashCustomerData(array $data): void
   - Check required: name, mobile
   - Validate mobile format (10 digits)
   - Validate email format if provided
   - Throw ValidationException if invalid

ERROR HANDLING:
- Throw CashCustomerNotFoundException if not found
- Throw ValidationException for invalid data
- Throw DuplicateCustomerException if duplicate found
- Throw CustomerInUseException if cannot delete
- Log all exceptions

DELIVERABLES:
Complete CashCustomerService.php file

ACCEPTANCE CRITERIA:
- Find-or-create pattern works
- Deduplication prevents duplicates
- Cannot delete if transactions exist
- Merge duplicates function works
- All actions audit logged
- Search works for autocomplete
```

---

#### Subtask 2.3.7: Create AccountController

```
[PASTE .antigravity RULES FIRST]

TASK: Generate AccountController with CRUD endpoints

FILE: app/Controllers/Customers/AccountController.php

CONTEXT:
- Handle HTTP requests for account customer management
- Thin controller, business logic in AccountService
- Permission checks: account.create, account.edit, account.delete, account.view
- JSON API responses for AJAX calls
- State dropdown for address forms

REQUIREMENTS:
Create CodeIgniter 4 Controller extending BaseController:

DEPENDENCIES (inject in __construct):
- AccountService
- StateModel (for state dropdown)
- PermissionService

ROUTES REQUIRED:
- GET /customers/accounts → index() (list all accounts)
- GET /customers/accounts/create → create() (show form)
- POST /customers/accounts → store() (create new)
- GET /customers/accounts/{id} → show() (view details)
- GET /customers/accounts/{id}/edit → edit() (show edit form)
- POST /customers/accounts/{id} → update() (update existing)
- DELETE /customers/accounts/{id} → delete() (soft delete)
- GET /customers/accounts/search → search() (autocomplete API)
- GET /customers/accounts/{id}/ledger → ledger() (account ledger view)

METHODS REQUIRED:

1. public function index()
   - Check permission: account.view
   - Get query params: is_active, search, state_id
   - Load accounts via AccountService
   - If AJAX: return JSON
   - Else: load view with accounts data
   - View: app/Views/customers/accounts/index.php

2. public function create()
   - Check permission: account.create
   - Load states dropdown (StateModel->where('is_active', TRUE)->findAll())
   - Load view: app/Views/customers/accounts/create.php
   - Pass states data

3. public function store()
   - Check permission: account.create
   - Validate CSRF token
   - Get POST data
   - If same_as_billing checked: copy billing address to shipping in data
   - Call AccountService->createAccount($data)
   - Set flash message: "Account created successfully"
   - Redirect to /customers/accounts

4. public function show(int $id)
   - Check permission: account.view
   - Load account via AccountService->getAccountById($id)
   - If not found: 404
   - Load current balance via AccountService->getLedgerBalance($id)
   - Load view: app/Views/customers/accounts/show.php
   - Pass account data and balance

5. public function edit(int $id)
   - Check permission: account.edit
   - Load account via AccountService->getAccountById($id)
   - If not found: 404
   - Load states dropdown
   - Load view: app/Views/customers/accounts/edit.php
   - Pass account and states data

6. public function update(int $id)
   - Check permission: account.edit
   - Validate CSRF token
   - Get POST data
   - If same_as_billing checked: copy billing to shipping
   - Call AccountService->updateAccount($id, $data)
   - Set flash message: "Account updated successfully"
   - Redirect to /customers/accounts

7. public function delete(int $id)
   - Check permission: account.delete
   - Try: AccountService->deleteAccount($id)
   - Catch AccountInUseException: return JSON error with message
   - Set flash message: "Account deleted successfully"
   - Return JSON success

8. public function search()
   - Check permission: account.view
   - Get query param: q
   - Call AccountService->searchAccounts($q)
   - Return JSON results: [{id, account_code, account_name, mobile, current_balance}, ...]

9. public function ledger(int $id)
   - Check permission: account.view
   - Redirect to /reports/ledger/account/{id}
   - Or load ledger view directly

ERROR HANDLING:
- Catch all exceptions
- Return JSON error for AJAX
- Flash messages for web
- Log errors

DELIVERABLES:
Complete AccountController.php file

ACCEPTANCE CRITERIA:
- All CRUD operations working
- Permission checks enforced
- Address copy functionality works (same_as_billing)
- Search autocomplete works
- Cannot delete if transactions exist
- Error handling robust
```

---

#### Subtask 2.3.8: Create CashCustomerController

```
[PASTE .antigravity RULES FIRST]

TASK: Generate CashCustomerController with quick-add feature

FILE: app/Controllers/Customers/CashCustomerController.php

CONTEXT:
- Handle HTTP requests for cash customer management
- Quick-add modal for fast customer creation during billing
- Permission checks: cash_customer.create, cash_customer.edit, cash_customer.delete, cash_customer.view
- JSON API for find-or-create pattern

REQUIREMENTS:
Create CodeIgniter 4 Controller extending BaseController:

DEPENDENCIES (inject in __construct):
- CashCustomerService
- StateModel (for state dropdown)
- PermissionService

ROUTES REQUIRED:
- GET /customers/cash-customers → index() (list all)
- GET /customers/cash-customers/create → create() (show form)
- POST /customers/cash-customers → store() (create new)
- POST /customers/cash-customers/find-or-create → findOrCreate() (API)
- GET /customers/cash-customers/{id} → show() (view details)
- GET /customers/cash-customers/{id}/edit → edit() (show edit form)
- POST /customers/cash-customers/{id} → update() (update existing)
- DELETE /customers/cash-customers/{id} → delete() (soft delete)
- GET /customers/cash-customers/search → search() (autocomplete API)

METHODS REQUIRED:

1. public function index()
   - Check permission: cash_customer.view
   - Get query params: is_active, search
   - Load cash customers via CashCustomerService
   - If AJAX: return JSON
   - Else: load view with customers data
   - View: app/Views/customers/cash_customers/index.php

2. public function create()
   - Check permission: cash_customer.create
   - Load states dropdown
   - Load view: app/Views/customers/cash_customers/create.php
   - Pass states data

3. public function store()
   - Check permission: cash_customer.create
   - Validate CSRF token
   - Get POST data
   - Try: Call CashCustomerService->createCashCustomer($data)
   - Catch DuplicateCustomerException:
     - Set flash error: "Customer with this name and mobile already exists"
     - Redirect back with input
   - Set flash message: "Cash customer created successfully"
   - Redirect to /customers/cash-customers

4. public function findOrCreate()
   - Check permission: cash_customer.create
   - Validate CSRF token
   - Get POST data: name, mobile, (optional: address fields)
   - Call CashCustomerService->findOrCreate($name, $mobile, $additionalData)
   - Return JSON: {success: true, customer_id: id, message: "Customer found/created"}
   - Used during invoice creation for quick customer add

5. public function show(int $id)
   - Check permission: cash_customer.view
   - Load customer via CashCustomerService->getCashCustomerById($id)
   - If not found: 404
   - Load view: app/Views/customers/cash_customers/show.php
   - Pass customer data

6. public function edit(int $id)
   - Check permission: cash_customer.edit
   - Load customer via CashCustomerService->getCashCustomerById($id)
   - If not found: 404
   - Load states dropdown
   - Load view: app/Views/customers/cash_customers/edit.php
   - Pass customer and states data

7. public function update(int $id)
   - Check permission: cash_customer.edit
   - Validate CSRF token
   - Get POST data
   - Try: Call CashCustomerService->updateCashCustomer($id, $data)
   - Catch DuplicateCustomerException: error message
   - Set flash message: "Cash customer updated successfully"
   - Redirect to /customers/cash-customers

8. public function delete(int $id)
   - Check permission: cash_customer.delete
   - Try: CashCustomerService->deleteCashCustomer($id)
   - Catch CustomerInUseException: return JSON error
   - Set flash message: "Cash customer deleted successfully"
   - Return JSON success

9. public function search()
   - Check permission: cash_customer.view
   - Get query param: q
   - Call CashCustomerService->searchCashCustomers($q)
   - Return JSON results: [{id, name, mobile}, ...]

ERROR HANDLING:
- Catch all exceptions
- Return JSON for API endpoints
- Flash messages for web
- Log errors

DELIVERABLES:
Complete CashCustomerController.php file

ACCEPTANCE CRITERIA:
- All CRUD operations working
- Find-or-create API works
- Duplicate prevention working
- Search autocomplete works
- Cannot delete if transactions exist
- Quick-add modal integration ready
```

---

### ADDITIONAL SUBTASKS FOR TASK 2.3 (VIEWS, ROUTES, SIDEBAR)

---

#### Subtask 2.3.9: Create Account Customer Views

```
[PASTE .antigravity RULES FIRST]

TASK: Create view files for Account Customer management

FILES TO CREATE:
1. app/Views/customers/accounts/index.php
2. app/Views/customers/accounts/create.php
3. app/Views/customers/accounts/edit.php
4. app/Views/customers/accounts/show.php

CONTEXT:
- Follow existing view pattern from app/Views/masters/
- Complex forms with billing and shipping address
- "Same as billing" checkbox functionality
- State dropdowns
- Current balance display
- GST and PAN fields

VIEW 1: index.php (List View)
REQUIREMENTS:
- Page title: "Account Customers"
- Breadcrumb: Home > Customers > Accounts
- Action button: "Add New Account"
- Filters:
  - Status dropdown (Active/Inactive/All)
  - State dropdown (filter by billing state)
- DataTable columns:
  - Account Code
  - Account Name
  - Contact Person
  - Mobile
  - Billing City/State
  - Current Balance (₹ formatted, color: green if credit, red if debit)
  - Status
  - Actions (View, Edit, Delete)
- Click on account name: navigate to show page
- Current balance with debit/credit indicator

VIEW 2: create.php (Create Form)
REQUIREMENTS:
- Page title: "Add Account Customer"
- Form sections:

  **Basic Information:**
  - Account Code (auto-generate button, or manual entry)
  - Account Name (required)
  - Business Name (optional)
  - Contact Person
  - Mobile (required)
  - Email
  - GST Number (with validation helper)
  - PAN Number (with validation helper)

  **Billing Address:**
  - Address Line 1 (required)
  - Address Line 2
  - City (required)
  - State (dropdown, required)
  - Pincode (required, 6 digits)

  **Shipping Address:**
  - Checkbox: "Same as Billing Address"
  - If unchecked: show shipping address fields (same structure as billing)
  - If checked: hide shipping fields, copy billing to shipping on submit

  **Financial:**
  - Opening Balance (default 0.00)
  - Opening Balance Type (radio: Debit [default] / Credit)
  - Credit Limit (optional, future use)
  - Payment Terms (text, e.g., "Net 30 days")

  **Notes:**
  - Notes (textarea)
  - Is Active (checkbox, default checked)

- JavaScript:
  - "Same as billing" toggle: show/hide shipping fields
  - Auto-generate account code button
  - GST/PAN format validation
  - Pincode validation (6 digits)
  - Mobile validation (10 digits)

- Submit button
- Cancel button

VIEW 3: edit.php (Edit Form)
REQUIREMENTS:
- Same as create.php but:
  - Page title: "Edit Account Customer"
  - Form pre-filled
  - Account Code read-only (cannot change)
  - Opening Balance read-only (cannot change after creation)
  - Opening Balance Type read-only
  - Current Balance displayed (read-only, informational)

VIEW 4: show.php (Details View)
REQUIREMENTS:
- Page title: "Account Details"
- Display all fields in read-only format
- Sections:
  - Basic Information
  - Billing Address (formatted)
  - Shipping Address (formatted, or "Same as billing")
  - Financial Information
  - Current Balance (highlighted, with debit/credit indicator)
  - Opening Balance
- Action buttons:
  - Edit (if has permission)
  - Delete (if has permission, with confirmation)
  - View Ledger (navigate to ledger report for this account)
  - Back to List

DELIVERABLES:
4 complete view files with address handling

ACCEPTANCE CRITERIA:
- Same as billing checkbox works
- Address fields toggle correctly
- GST/PAN validation works
- Current balance displayed correctly
- All CRUD operations functional
```

---

#### Subtask 2.3.10: Create Cash Customer Views

```
[PASTE .antigravity RULES FIRST]

TASK: Create view files for Cash Customer management

FILES TO CREATE:
1. app/Views/customers/cash_customers/index.php
2. app/Views/customers/cash_customers/create.php
3. app/Views/customers/cash_customers/edit.php
4. app/Views/customers/cash_customers/show.php
5. app/Views/customers/cash_customers/quick_add_modal.php

CONTEXT:
- Simpler than accounts (no financial fields)
- Quick-add modal for use during invoice creation
- Duplicate detection on name+mobile

VIEW 1: index.php (List View)
REQUIREMENTS:
- Page title: "Cash Customers"
- Breadcrumb: Home > Customers > Cash Customers
- Action button: "Add New Cash Customer"
- Filters:
  - Status dropdown
- DataTable columns:
  - Customer Name
  - Mobile
  - Email
  - City/State
  - Status
  - Actions
- Search: name or mobile
- Row click: navigate to show page

VIEW 2: create.php (Create Form)
REQUIREMENTS:
- Page title: "Add Cash Customer"
- Form fields:
  - Customer Name (required)
  - Mobile (required, 10 digits, validation)
  - Email (optional)
  - Address Line 1
  - Address Line 2
  - City
  - State (dropdown)
  - Pincode (6 digits)
  - Notes
  - Is Active (checkbox)
- Duplicate warning: if name+mobile exists, show alert
- Submit button
- Cancel button

VIEW 3: edit.php (Edit Form)
REQUIREMENTS:
- Same as create.php but:
  - Page title: "Edit Cash Customer"
  - Form pre-filled
  - Duplicate detection on name+mobile change

VIEW 4: show.php (Details View)
REQUIREMENTS:
- Page title: "Cash Customer Details"
- Display all fields read-only
- Action buttons: Edit, Delete, Back

VIEW 5: quick_add_modal.php (Modal Component)
REQUIREMENTS:
- Bootstrap modal
- Title: "Quick Add Cash Customer"
- Minimal fields:
  - Customer Name (required)
  - Mobile (required)
- Submit button: "Add Customer"
- JavaScript:
  - AJAX POST to /customers/cash-customers/find-or-create
  - On success: populate customer dropdown in invoice form
  - On error: show error message in modal
- Used during invoice creation for quick customer addition

DELIVERABLES:
5 complete view files including quick-add modal

ACCEPTANCE CRITERIA:
- Quick-add modal works via AJAX
- Duplicate detection shows warning
- All CRUD operations functional
- Mobile/email validation works
```

---

#### Subtask 2.3.11: Add Customer Routes

```
[PASTE .antigravity RULES FIRST]

TASK: Configure routes for Accounts and Cash Customers

FILE: app/Config/Routes.php

CONTEXT:
- RESTful route structure
- Apply AuthFilter and PermissionFilter
- Group routes under /customers prefix
- API endpoints for search and find-or-create

REQUIREMENTS:
Add the following route groups:

ACCOUNT ROUTES:
```php
$routes->group('customers/accounts', ['filter' => 'auth', 'filter' => 'permission:account'], function($routes) {
    $routes->get('/', 'Customers\AccountController::index');
    $routes->get('create', 'Customers\AccountController::create');
    $routes->post('/', 'Customers\AccountController::store');
    $routes->get('search', 'Customers\AccountController::search'); // API
    $routes->get('(:num)', 'Customers\AccountController::show/$1');
    $routes->get('(:num)/edit', 'Customers\AccountController::edit/$1');
    $routes->post('(:num)', 'Customers\AccountController::update/$1');
    $routes->delete('(:num)', 'Customers\AccountController::delete/$1');
    $routes->get('(:num)/ledger', 'Customers\AccountController::ledger/$1');
});
```

CASH CUSTOMER ROUTES:
```php
$routes->group('customers/cash-customers', ['filter' => 'auth', 'filter' => 'permission:cash_customer'], function($routes) {
    $routes->get('/', 'Customers\CashCustomerController::index');
    $routes->get('create', 'Customers\CashCustomerController::create');
    $routes->post('/', 'Customers\CashCustomerController::store');
    $routes->post('find-or-create', 'Customers\CashCustomerController::findOrCreate'); // API
    $routes->get('search', 'Customers\CashCustomerController::search'); // API
    $routes->get('(:num)', 'Customers\CashCustomerController::show/$1');
    $routes->get('(:num)/edit', 'Customers\CashCustomerController::edit/$1');
    $routes->post('(:num)', 'Customers\CashCustomerController::update/$1');
    $routes->delete('(:num)', 'Customers\CashCustomerController::delete/$1');
});
```

DELIVERABLES:
Updated Routes.php with customer routes

ACCEPTANCE CRITERIA:
- All routes working
- Filters applied correctly
- Permission checks enforced
- API endpoints accessible
```

---

#### Subtask 2.3.12: Add Customers to Sidebar

```
[PASTE .antigravity RULES FIRST]

TASK: Add Customers menu items to sidebar navigation

FILE: app/Views/layouts/sidebar.php

CONTEXT:
- Add "Customers" dropdown menu
- Show based on permissions
- Font Awesome icons

REQUIREMENTS:
Add the following menu structure:

```html
<!-- Customers Dropdown -->
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="customersDropdown" role="button" data-bs-toggle="dropdown">
        <i class="fas fa-users"></i> Customers
    </a>
    <ul class="dropdown-menu" aria-labelledby="customersDropdown">
        <?php if (can('account.view')): ?>
        <li>
            <a class="dropdown-item" href="<?= base_url('customers/accounts') ?>">
                <i class="fas fa-building"></i> Account Customers
            </a>
        </li>
        <?php endif; ?>

        <?php if (can('cash_customer.view')): ?>
        <li>
            <a class="dropdown-item" href="<?= base_url('customers/cash-customers') ?>">
                <i class="fas fa-user"></i> Cash Customers
            </a>
        </li>
        <?php endif; ?>
    </ul>
</li>
```

DELIVERABLES:
Updated sidebar with Customers menu

ACCEPTANCE CRITERIA:
- Menu items visible based on permissions
- Icons display correctly
- Navigation works
- Active menu highlighting
```

---

## ✅ TASK-02 COMPLETE

**Total Subtasks Covered:**
- Task 2.2.1-2.2.14: Products, Processes, Categories (14 subtasks)
- Task 2.3.1-2.3.12: Accounts, Cash Customers (12 subtasks)
- **Total: 26 subtasks**

**Files Generated:**
- 9 Migrations
- 5 Models
- 4 Services
- 4 Controllers
- 15 Views
- Routes configuration
- Sidebar navigation

**Key Features:**
✅ Product & Process management
✅ Product categories
✅ Account customers with opening balance
✅ Cash customers with deduplication
✅ GST/PAN validation
✅ Address handling (billing/shipping)
✅ Ledger integration
✅ Image upload for products
✅ Autocomplete search
✅ Quick-add modal for cash customers
✅ Cannot delete if used in transactions

---

**END OF TASK-02 - MASTER DATA MANAGEMENT**

---

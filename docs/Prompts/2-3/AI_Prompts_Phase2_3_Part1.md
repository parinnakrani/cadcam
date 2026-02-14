# AI CODING PROMPTS - PHASE 2 & 3 (CRITICAL FOUNDATION)
## Gold Manufacturing & Billing ERP System

**Generated:** February 10, 2026  
**Coverage:** Tasks 2.2, 2.3, 3.1, 3.2, 3.3, 3.4 (28 Missing Subtasks)  
**Estimated Development Time:** ~100 hours

---

## 📖 HOW TO USE THESE PROMPTS

### Step 1: Copy the prompt for your subtask
### Step 2: Provide your .antigravity coding standards file
### Step 3: Paste both to your AI coding assistant
### Step 4: Review and test the generated code

---

## PHASE 2: MASTER DATA MANAGEMENT

### 🎯 TASK 2.2: PRODUCT & PROCESS MANAGEMENT

---

#### Subtask 2.2.1: Create product_categories Migration

```
[PASTE .antigravity RULES FIRST]

TASK: Generate migration file for product_categories table

FILE: app/Database/Migrations/2026-01-01-000007_create_product_categories_table.php

CONTEXT:
- Organize products by categories (Ring, Bangle, Necklace, Earring, Pendant, Bracelet, Chain, etc.)
- Used for filtering products and reporting
- Multi-tenant system (company_id based, 0 = global for all companies)
- Categories help in product organization and quick selection

REQUIREMENTS:
Using CodeIgniter 4 migration syntax, create migration with:

TABLE STRUCTURE:
CREATE TABLE product_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL DEFAULT 0,
    category_name VARCHAR(100) NOT NULL,
    category_code VARCHAR(50) NOT NULL,
    description TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    is_deleted BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_category_code (company_id, category_code),
    INDEX idx_company_id (company_id),
    INDEX idx_category_name (category_name),
    INDEX idx_active_deleted (is_active, is_deleted)
);

INDEXES:
- PRIMARY KEY (id)
- INDEX (company_id) - for multi-tenant filtering
- UNIQUE (company_id, category_code) - prevent duplicate codes per company
- INDEX (category_name) - for search and ordering
- INDEX (is_active, is_deleted) - for filtering active categories

FOREIGN KEYS:
- company_id REFERENCES companies(id) ON DELETE RESTRICT

METHODS REQUIRED:

1. public function up(): void
   - Use $this->forge->addField() for all columns
   - Add all indexes using $this->forge->addKey()
   - Add unique constraint using $this->forge->addUniqueKey(['company_id', 'category_code'])
   - Add foreign key using $this->forge->addForeignKey()
   - Create table using $this->forge->createTable('product_categories', TRUE)

2. public function down(): void
   - Drop table: $this->forge->dropTable('product_categories', TRUE)

SAMPLE DATA TO SEED (optional, for testing):
- Ring (category_code: RING)
- Bangle (category_code: BANGLE)
- Necklace (category_code: NECKLACE)
- Earring (category_code: EARRING)
- Pendant (category_code: PENDANT)
- Bracelet (category_code: BRACELET)
- Chain (category_code: CHAIN)

BUSINESS RULES:
- category_code must be unique per company (case-insensitive)
- company_id = 0 means global category (available to all companies)
- Cannot delete category if products exist (soft delete only)
- Inactive categories hidden in dropdowns but remain in historical records

DELIVERABLES:
Complete migration file ready to run with: php spark migrate

ACCEPTANCE CRITERIA:
✓ Migration runs without errors
✓ All columns created with correct types and constraints
✓ Foreign key constraint working
✓ Unique constraint on company_id + category_code enforced
✓ Can insert category with company_id = 0 (global)
✓ Can insert category with company_id = 1 (company-specific)
✓ Cannot insert duplicate category_code for same company
✓ Rollback works: php spark migrate:rollback
```

---

#### Subtask 2.2.2: Create products Migration

```
[PASTE .antigravity RULES FIRST]

TASK: Generate migration file for products table

FILE: app/Database/Migrations/2026-01-01-000008_create_products_table.php

CONTEXT:
- Products represent jewelry designs (rings, bangles, necklaces, earrings, pendants, etc.)
- Each product belongs to a category
- Products used in challan line items (multi-select, stored as JSON array)
- Company-specific products (company_id = 0 for global products available to all)
- Image upload support for visual reference
- HSN code for GST compliance

REQUIREMENTS:
Using CodeIgniter 4 migration syntax, create migration with:

TABLE STRUCTURE:
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL DEFAULT 0,
    category_id INT NULL,
    product_code VARCHAR(50) NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    hsn_code VARCHAR(20) NULL COMMENT 'For GST tax calculation',
    image VARCHAR(255) NULL COMMENT 'Product image file path',
    is_active BOOLEAN DEFAULT TRUE,
    is_deleted BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    created_by INT NULL,
    updated_by INT NULL,

    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE RESTRICT,
    FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,

    UNIQUE KEY unique_product_code (company_id, product_code),
    INDEX idx_company_id (company_id),
    INDEX idx_category_id (category_id),
    INDEX idx_product_name (product_name),
    INDEX idx_active_deleted (is_active, is_deleted)
);

COLUMN SPECIFICATIONS:
- id: INT AUTO_INCREMENT PRIMARY KEY
- company_id: INT, DEFAULT 0 (0 = global product)
- category_id: INT NULL (can be null if uncategorized)
- product_code: VARCHAR(50) NOT NULL (e.g., PRD0001, PRD0002)
- product_name: VARCHAR(255) NOT NULL
- description: TEXT NULL (detailed product description)
- hsn_code: VARCHAR(20) NULL (4-8 digit HSN code for GST)
- image: VARCHAR(255) NULL (file path: uploads/products/{company_id}/{filename})
- is_active: BOOLEAN DEFAULT TRUE
- is_deleted: BOOLEAN DEFAULT FALSE (soft delete)
- created_at, updated_at: TIMESTAMP NULL (auto-managed by CI4)
- created_by, updated_by: INT NULL (user IDs)

INDEXES:
- PRIMARY KEY (id)
- UNIQUE (company_id, product_code) - prevent duplicate product codes per company
- INDEX (company_id) - multi-tenant filtering
- INDEX (category_id) - filter by category
- INDEX (product_name) - search and autocomplete
- INDEX (is_active, is_deleted) - filter active products

FOREIGN KEYS:
- company_id → companies(id) ON DELETE RESTRICT
- category_id → product_categories(id) ON DELETE SET NULL (if category deleted, product remains)
- created_by → users(id) ON DELETE SET NULL
- updated_by → users(id) ON DELETE SET NULL

METHODS REQUIRED:

1. public function up(): void
   - Create table with all columns
   - Add all indexes
   - Add unique constraint
   - Add foreign keys with proper cascading

2. public function down(): void
   - Drop foreign keys first (if needed)
   - Drop table

BUSINESS RULES TO ENFORCE:
- product_code unique per company (PRD0001, PRD0002, etc.)
- company_id = 0 means global product (all companies can use)
- Cannot permanently delete if used in any challan (soft delete only)
- Inactive products hidden in dropdowns but visible in historical records
- Image field stores relative path from public directory
- HSN code format: 4 to 8 digits (validation in model)
- category_id nullable (product can be uncategorized)

IMAGE UPLOAD NOTES:
- Upload directory: public/uploads/products/{company_id}/
- Allowed formats: JPG, JPEG, PNG
- Max file size: 5 MB
- Generate unique filename to prevent conflicts

DELIVERABLES:
Complete migration file ready to run with: php spark migrate

ACCEPTANCE CRITERIA:
✓ Migration runs without errors
✓ All columns created with correct data types
✓ Foreign key constraints working properly
✓ Unique constraint on company_id + product_code enforced
✓ Can insert product with category_id NULL
✓ Can insert product with company_id = 0 (global)
✓ Cannot insert duplicate product_code for same company
✓ Image field accepts NULL or file path
✓ Rollback works correctly
```

---

#### Subtask 2.2.3: Create processes Migration

```
[PASTE .antigravity RULES FIRST]

TASK: Generate migration file for processes table

FILE: app/Database/Migrations/2026-01-01-000009_create_processes_table.php

CONTEXT:
- Processes represent manufacturing steps (Rhodium plating, Meena work, Polishing, Stone Setting, Casting, Wax, etc.)
- Each process has a price per unit (per gram, per piece, per job)
- Multiple processes can be applied to one challan line
- Process prices stored at challan creation time as snapshot (historical preservation)
- Process types help categorize and filter processes

REQUIREMENTS:
Using CodeIgniter 4 migration syntax, create migration with:

TABLE STRUCTURE:
CREATE TABLE processes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL DEFAULT 0,
    process_code VARCHAR(50) NOT NULL,
    process_name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    process_type ENUM('Rhodium', 'Meena', 'Polishing', 'Stone Setting', 'Casting', 'Wax', 'Other') DEFAULT 'Other',
    price_per_unit DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    unit_type ENUM('Per Gram', 'Per Piece', 'Per Job') DEFAULT 'Per Gram',
    is_active BOOLEAN DEFAULT TRUE,
    is_deleted BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    created_by INT NULL,
    updated_by INT NULL,

    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,

    UNIQUE KEY unique_process_code (company_id, process_code),
    INDEX idx_company_id (company_id),
    INDEX idx_process_name (process_name),
    INDEX idx_process_type (process_type),
    INDEX idx_active_deleted (is_active, is_deleted)
);

COLUMN SPECIFICATIONS:
- id: INT AUTO_INCREMENT PRIMARY KEY
- company_id: INT DEFAULT 0 (0 = global process)
- process_code: VARCHAR(50) NOT NULL (e.g., PRC0001, PRC0002)
- process_name: VARCHAR(255) NOT NULL (e.g., "Rhodium Plating", "Meena Work")
- description: TEXT NULL
- process_type: ENUM (Rhodium, Meena, Polishing, Stone Setting, Casting, Wax, Other)
- price_per_unit: DECIMAL(10,2) NOT NULL DEFAULT 0.00 (e.g., 50.00 per gram)
- unit_type: ENUM (Per Gram, Per Piece, Per Job)
- is_active: BOOLEAN DEFAULT TRUE
- is_deleted: BOOLEAN DEFAULT FALSE
- created_at, updated_at: TIMESTAMP NULL
- created_by, updated_by: INT NULL

ENUM VALUES:
process_type:
- 'Rhodium' - Rhodium plating process
- 'Meena' - Meena (enamel) work process
- 'Polishing' - Polishing/finishing process
- 'Stone Setting' - Stone setting process
- 'Casting' - Casting process
- 'Wax' - Wax modeling process
- 'Other' - Other custom processes

unit_type:
- 'Per Gram' - Price charged per gram of weight
- 'Per Piece' - Fixed price per piece
- 'Per Job' - Fixed price per job (regardless of weight/quantity)

INDEXES:
- PRIMARY KEY (id)
- UNIQUE (company_id, process_code) - prevent duplicate codes per company
- INDEX (company_id) - multi-tenant filtering
- INDEX (process_name) - search and ordering
- INDEX (process_type) - filter by type
- INDEX (is_active, is_deleted) - filter active processes

FOREIGN KEYS:
- company_id → companies(id) ON DELETE RESTRICT
- created_by → users(id) ON DELETE SET NULL
- updated_by → users(id) ON DELETE SET NULL

METHODS REQUIRED:

1. public function up(): void
   - Create table with all columns including ENUM fields
   - Add all indexes
   - Add unique constraint on company_id + process_code
   - Add foreign keys

2. public function down(): void
   - Drop table

BUSINESS RULES:
- process_code unique per company (PRC0001, PRC0002, etc.)
- company_id = 0 means global process (all companies can use)
- price_per_unit must be >= 0 (can be zero for free processes)
- Cannot delete process if used in any challan (soft delete only)
- Inactive processes hidden in dropdowns but visible in historical records
- When multiple processes selected in challan line: rate = SUM of all process prices
- Process prices snapshot at challan creation time (stored in challan_lines.process_prices JSON)

RATE CALCULATION EXAMPLE:
Challan line with processes:
- Rhodium Plating: ₹50/gram
- Polishing: ₹30/gram
Total rate: ₹80/gram
If weight = 10 grams: Amount = 10 × 80 = ₹800

DELIVERABLES:
Complete migration file ready to run with: php spark migrate

ACCEPTANCE CRITERIA:
✓ Migration runs without errors
✓ All columns created with correct data types
✓ ENUM fields working correctly (process_type, unit_type)
✓ Foreign key constraints working
✓ Unique constraint on company_id + process_code enforced
✓ DECIMAL precision correct (10,2 for price_per_unit)
✓ Can insert process with company_id = 0 (global)
✓ Cannot insert duplicate process_code for same company
✓ ENUM values restricted to defined list
✓ Rollback works correctly
```

---

#### Subtask 2.2.4: Create ProductModel

```
[PASTE .antigravity RULES FIRST]

TASK: Generate ProductModel with CRUD, validation, and business logic

FILE: app/Models/ProductModel.php

CONTEXT:
- Products are jewelry designs used in challans
- Multi-tenant system with automatic company_id filtering
- Soft delete support (cannot permanently delete if used in challans)
- Image upload handling
- Search and autocomplete functionality
- Extends BaseModel for automatic company filtering

REQUIREMENTS:
Create CodeIgniter 4 Model extending BaseModel with:

CLASS STRUCTURE:
```php
<?php

namespace App\Models;

use App\Models\BaseModel;

class ProductModel extends BaseModel
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'company_id', 'category_id', 'product_code', 'product_name',
        'description', 'hsn_code', 'image', 'is_active', 'is_deleted',
        'created_by', 'updated_by'
    ];

    protected $validationRules = [
        'product_code' => 'required|min_length[3]|max_length[50]',
        'product_name' => 'required|min_length[2]|max_length[255]',
        'category_id' => 'permit_empty|integer',
        'hsn_code' => 'permit_empty|min_length[4]|max_length[20]|regex_match[/^[0-9]{4,8}$/]',
        'image' => 'permit_empty|max_length[255]'
    ];

    protected $validationMessages = [
        'product_code' => [
            'required' => 'Product code is required',
            'min_length' => 'Product code must be at least 3 characters',
            'max_length' => 'Product code cannot exceed 50 characters'
        ],
        'product_name' => [
            'required' => 'Product name is required',
            'min_length' => 'Product name must be at least 2 characters'
        ],
        'hsn_code' => [
            'regex_match' => 'HSN code must be 4 to 8 digits'
        ]
    ];

    protected $beforeInsert = ['addCreatedBy', 'addCompanyId'];
    protected $beforeUpdate = ['addUpdatedBy'];
}
```

METHODS REQUIRED:

1. public function getActiveProducts(?int $categoryId = null): array
   Purpose: Get all active products, optionally filtered by category
   Logic:
   - where('is_active', TRUE)
   - where('is_deleted', FALSE)
   - if $categoryId provided: where('category_id', $categoryId)
   - Company filter auto-applied via BaseModel
   - orderBy('product_name', 'ASC')
   - return findAll()

   Example usage:
   $products = $productModel->getActiveProducts(); // All active products
   $rings = $productModel->getActiveProducts(1); // Only rings category

2. public function getProductsByCategory(int $categoryId): array
   Purpose: Get all products in specific category
   Logic:
   - where('category_id', $categoryId)
   - where('is_active', TRUE)
   - where('is_deleted', FALSE)
   - Company filter applied
   - orderBy('product_name', 'ASC')
   - return findAll()

3. public function searchProducts(string $query, int $limit = 10): array
   Purpose: Search products by name or code (for autocomplete)
   Logic:
   - groupStart()
   - like('product_name', $query)
   - orLike('product_code', $query)
   - groupEnd()
   - where('is_active', TRUE)
   - where('is_deleted', FALSE)
   - Company filter applied
   - limit($limit)
   - orderBy('product_name', 'ASC')
   - return findAll()

   Returns: Array of products with id, code, name, category
   Used in: Challan/Invoice forms for product selection

4. public function isUsedInChallans(int $productId): bool
   Purpose: Check if product is used in any challan lines
   Logic:
   - Query challan_lines table
   - Search in JSON column product_ids
   - SQL: SELECT COUNT(*) FROM challan_lines WHERE JSON_CONTAINS(product_ids, ?, '$') AND is_deleted = FALSE
   - Pass: CAST($productId as CHAR)
   - return count > 0

   Business rule: Cannot delete product if used in any challan

5. public function canDelete(int $productId): bool
   Purpose: Check if product can be deleted
   Logic:
   - Call isUsedInChallans($productId)
   - return !isUsedInChallans($productId)

   Returns: FALSE if used in challans, TRUE if safe to delete

6. public function getProductWithCategory(int $id): ?array
   Purpose: Get product with category details
   Logic:
   - select('products.*, product_categories.category_name')
   - join('product_categories', 'product_categories.id = products.category_id', 'left')
   - where('products.id', $id)
   - where('products.is_deleted', FALSE)
   - Company filter applied
   - return first()

7. protected function addCreatedBy(array $data): array
   Purpose: Add created_by user ID before insert
   Logic:
   - if (!isset($data['data']['created_by']))
   - $data['data']['created_by'] = session()->get('user_id')
   - return $data

8. protected function addCompanyId(array $data): array
   Purpose: Add company_id before insert (if not set)
   Logic:
   - if (!isset($data['data']['company_id']))
   - $data['data']['company_id'] = session()->get('company_id') ?? 0
   - return $data

9. protected function addUpdatedBy(array $data): array
   Purpose: Add updated_by user ID before update
   Logic:
   - if (!isset($data['data']['updated_by']))
   - $data['data']['updated_by'] = session()->get('user_id')
   - return $data

RELATIONSHIPS (optional, for future):
- belongsTo: product_categories (category)
- hasMany: challan_lines (via JSON product_ids)

VALIDATION RULES:
- product_code: required, min 3, max 50, unique per company
- product_name: required, min 2, max 255
- category_id: optional, must be integer (if provided)
- hsn_code: optional, 4-8 digits only (regex: /^[0-9]{4,8}$/)
- image: optional, max 255 chars (file path)

BUSINESS RULES:
- Product code must be unique per company (global products have company_id = 0)
- Cannot permanently delete product if used in any challan (soft delete only)
- Inactive products not shown in dropdowns but visible in historical records
- Image field stores relative path: uploads/products/{company_id}/{filename}
- HSN code used for GST tax calculation (4-8 digits)
- Company filter auto-applied via BaseModel (users see only their company's products)

DELIVERABLES:
Complete ProductModel.php file with:
- All properties defined
- All validation rules
- All methods implemented
- Proper PHPDoc comments
- Error handling

ACCEPTANCE CRITERIA:
✓ CRUD operations work correctly
✓ Product code unique per company enforced
✓ Cannot delete product if used in challans
✓ Active products retrieved correctly
✓ Search/autocomplete returns relevant results
✓ Company filter applied automatically (via BaseModel)
✓ Validation rules enforced on insert/update
✓ created_by and updated_by auto-populated
✓ Soft delete working (is_deleted = TRUE)
✓ Can query products by category
```

---

*[Due to length constraints, I'll continue with the remaining 24 subtasks in the next section. Would you like me to continue generating the complete file with all 28 subtasks?]*

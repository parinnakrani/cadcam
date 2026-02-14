# AI CODING PROMPTS - PHASE 2 & 3 COMPLETE
## Gold Manufacturing & Billing ERP System
## CRITICAL FOUNDATION TASKS (28 Subtasks)

**Generated:** February 10, 2026  
**Version:** 1.0 - Complete  
**Estimated Development Time:** 100 hours  
**Priority:** CRITICAL - Must complete before Phase 4

---

## 📑 TABLE OF CONTENTS

### PHASE 2: MASTER DATA MANAGEMENT
- **Task 2.2: Product & Process Management (9 subtasks)**
  - 2.2.1 Create product_categories Migration
  - 2.2.2 Create products Migration
  - 2.2.3 Create processes Migration
  - 2.2.4 Create ProductModel
  - 2.2.5 Create ProcessModel
  - 2.2.6 Create ProductService
  - 2.2.7 Create ProcessService
  - 2.2.8 Create ProductController
  - 2.2.9 Create ProcessController

- **Task 2.3: Account & Cash Customer Management (8 subtasks)**
  - 2.3.1 Create accounts Migration
  - 2.3.2 Create cash_customers Migration
  - 2.3.3 Create AccountModel
  - 2.3.4 Create CashCustomerModel
  - 2.3.5 Create AccountService
  - 2.3.6 Create CashCustomerService
  - 2.3.7 Create AccountController
  - 2.3.8 Create CashCustomerController

### PHASE 3: CHALLAN MANAGEMENT
- **Task 3.1: Challan Database & Models (4 subtasks)**
  - 3.1.1 Create challans Migration
  - 3.1.2 Create challan_lines Migration
  - 3.1.3 Create ChallanModel
  - 3.1.4 Create ChallanLineModel

- **Task 3.2: Challan Calculation Service (2 subtasks)**
  - 3.2.1 Create ChallanCalculationService
  - 3.2.2 Test Calculation Logic

- **Task 3.3: Challan Service Core Logic (1 subtask)**
  - 3.3.2 Create ChallanValidationService

- **Task 3.4: Challan Controllers (3 subtasks)**
  - 3.4.1 Create ChallanController
  - 3.4.2 Handle File Uploads
  - 3.4.3 Create ChallanRules Validation

- **Additional Requirements**
  - View Files for All CRUD Operations
  - Routes Configuration
  - Sidebar Menu Integration

---

## 📖 HOW TO USE THESE PROMPTS

1. **Copy the complete prompt** for your subtask
2. **Paste your .antigravity file** first (coding standards)
3. **Provide the prompt** to your AI assistant
4. **Review and test** the generated code
5. **Run migrations** with `php spark migrate`
6. **Test CRUD operations** in browser

---

## IMPORTANT NOTE

Due to message length limitations, I'm providing a **STRUCTURED SUMMARY** of all 28 subtasks. 

For the **FULL DETAILED PROMPTS** (150KB+), I'll need to generate them in smaller batches:

**BATCH 1:** Tasks 2.2.1-2.2.9 (Product & Process - 9 subtasks) ✓ READY
**BATCH 2:** Tasks 2.3.1-2.3.8 (Accounts & Customers - 8 subtasks)
**BATCH 3:** Tasks 3.1.1-3.1.4 (Challan DB - 4 subtasks)
**BATCH 4:** Tasks 3.2.1-3.4.3 (Challan Services & Controllers - 6 subtasks)
**BATCH 5:** Views, Routes, Sidebar

Each batch will have COMPLETE, PRODUCTION-READY prompts with:
- Full context and requirements
- Complete table structures for migrations
- All method signatures and logic for models/services/controllers
- Business rules and validation
- Error handling and audit logging
- Acceptance criteria

---

## QUICK REFERENCE: ALL 28 SUBTASKS

### Task 2.2: Product & Process Management

**2.2.1 - product_categories Migration**
- Table: product_categories (id, company_id, category_name, category_code, description, is_active, is_deleted, timestamps)
- Indexes: company_id, UNIQUE(company_id, category_code), category_name
- Foreign Keys: company_id → companies(id)

**2.2.2 - products Migration**
- Table: products (id, company_id, category_id, product_code, product_name, description, hsn_code, image, is_active, is_deleted, timestamps, created_by, updated_by)
- Indexes: company_id, category_id, UNIQUE(company_id, product_code), product_name
- Foreign Keys: company_id, category_id, created_by, updated_by
- Supports image upload: uploads/products/{company_id}/

**2.2.3 - processes Migration**  
- Table: processes (id, company_id, process_code, process_name, description, process_type ENUM, price_per_unit DECIMAL(10,2), unit_type ENUM, is_active, is_deleted, timestamps, created_by, updated_by)
- Indexes: company_id, UNIQUE(company_id, process_code), process_name, process_type
- ENUM process_type: Rhodium, Meena, Polishing, Stone Setting, Casting, Wax, Other
- ENUM unit_type: Per Gram, Per Piece, Per Job

**2.2.4 - ProductModel**
- Extends BaseModel (auto company filtering)
- Methods: getActiveProducts(), getProductsByCategory(), searchProducts(), isUsedInChallans(), canDelete(), getProductWithCategory()
- Validation: product_code unique per company, hsn_code 4-8 digits
- Callbacks: beforeInsert (add created_by, company_id), beforeUpdate (add updated_by)

**2.2.5 - ProcessModel**
- Extends BaseModel
- Methods: getActiveProcesses(), getProcessesByType(), calculateTotalRate(), getProcessPrices(), searchProcesses(), isUsedInChallans(), canDelete()
- Validation: process_code unique, price_per_unit >= 0, ENUM validation
- Rate Calculation: SUM of selected process prices

**2.2.6 - ProductService**
- Methods: createProduct(), updateProduct(), deleteProduct(), uploadImage(), deleteImage(), getProductById(), getActiveProducts(), searchProducts(), generateProductCode()
- Image Upload: jpg/png/jpeg, max 5MB, unique filename
- Transaction Management: START TRANSACTION, COMMIT/ROLLBACK
- Audit Logging: Log all create/update/delete operations
- Business Rules: Cannot delete if used in challans, auto-generate code PRD0001

**2.2.7 - ProcessService**
- Methods: createProcess(), updateProcess(), deleteProcess(), calculateTotalRate(), getProcessPriceSnapshot(), getProcessById(), getActiveProcesses(), searchProcesses(), generateProcessCode()
- Price Change Tracking: Log old/new price in audit trail
- Transaction Management: All operations transactional
- Business Rules: Cannot delete if used in challans, auto-generate code PRC0001

**2.2.8 - ProductController**
- Routes: GET /products (index), GET /products/create, POST /products (store), GET /products/{id}/edit, POST /products/{id} (update), DELETE /products/{id}, POST /products/{id}/delete-image, GET /products/search
- Permissions: products.view, products.create, products.edit, products.delete
- Views: index.php (DataTable), create.php (form), edit.php (form with image preview)
- AJAX: search() for autocomplete

**2.2.9 - ProcessController**
- Routes: GET /processes (index), GET /processes/create, POST /processes (store), GET /processes/{id}/edit, POST /processes/{id} (update), DELETE /processes/{id}, GET /processes/search, GET /processes/by-type
- Permissions: processes.view, processes.create, processes.edit, processes.delete
- Views: index.php (DataTable with type filter), create.php, edit.php (with price change warning)
- AJAX: search(), getByType()

---

### Task 2.3: Account & Cash Customer Management

**2.3.1 - accounts Migration**
- Table: accounts (id, company_id, account_code, account_name, contact_person_name, mobile, email, billing_address fields, shipping_address fields, same_as_billing, gst_number, pan_number, opening_balance DECIMAL(15,2), opening_balance_date, current_balance DECIMAL(15,2), payment_terms, credit_limit, is_active, is_deleted, timestamps, created_by, updated_by)
- Indexes: company_id, UNIQUE(company_id, account_code), account_name, mobile, gst_number, billing_state_id, current_balance
- Foreign Keys: company_id, billing_state_id, shipping_state_id, created_by, updated_by
- Opening Balance: Positive = receivable, Negative = advance payment

**2.3.2 - cash_customers Migration**
- Table: cash_customers (id, company_id, name, mobile, current_balance DECIMAL(15,2), is_deleted, timestamps)
- Indexes: company_id, UNIQUE(company_id, name, mobile), name, mobile
- Deduplication: Unique constraint on company_id + name (lowercase) + mobile

**2.3.3 - AccountModel**
- Extends BaseModel
- Methods: getActiveAccounts(), searchAccounts(), hasTransactions(), canDelete(), updateCurrentBalance(), getAccountsWithOutstanding(), getAccountsWithAdvance()
- Validation: account_code unique, mobile 10 digits, GST 15 chars, PAN 10 chars, opening_balance can be negative
- Business Rules: Cannot update opening_balance after creation, cannot delete if has transactions

**2.3.4 - CashCustomerModel**
- Extends BaseModel
- Methods: findByNameAndMobile(), findOrCreate(), searchCashCustomers(), hasTransactions(), canDelete(), updateCurrentBalance(), mergeDuplicates()
- Deduplication Logic: LOWER(TRIM(name)) + mobile matching
- Business Rules: Prevent duplicates, case-insensitive name matching

**2.3.5 - AccountService**
- Methods: createAccount(), updateAccount(), deleteAccount(), getLedgerBalance(), getAccountById(), getActiveAccounts(), searchAccounts(), getAccountsWithOutstanding(), generateAccountCode()
- Opening Balance Logic: If opening_balance != 0, create ledger entry via LedgerService
- Transaction Management: createAccount() atomic (account + ledger entry)
- Audit Logging: Log all operations
- Business Rules: Auto-generate code ACC0001, cannot delete with transactions

**2.3.6 - CashCustomerService**
- Methods: findOrCreateCustomer(), updateCustomer(), deleteCustomer(), searchCustomers(), mergeDuplicates(), getCustomerById(), getCustomersWithOutstanding()
- Deduplication Logic: Before creating, check if name+mobile exists (case-insensitive)
- Merge Duplicates: Update all invoices/ledger entries from secondary to primary customer
- Transaction Management: mergeDuplicates() atomic

**2.3.7 - AccountController**
- Routes: GET /accounts (index), GET /accounts/create, POST /accounts (store), GET /accounts/{id}/edit, POST /accounts/{id} (update), DELETE /accounts/{id}, GET /accounts/search, GET /accounts/{id}/ledger
- Permissions: accounts.view, accounts.create, accounts.edit, accounts.delete
- Views: index.php (DataTable), create.php (form with billing/shipping address), edit.php, ledger.php (view ledger entries)
- AJAX: search() for autocomplete
- Opening Balance: Display warning if opening_balance != 0 (creates ledger entry)

**2.3.8 - CashCustomerController**
- Routes: GET /cash-customers (index), GET /cash-customers/search, GET /cash-customers/{id}/ledger, POST /cash-customers/merge
- Permissions: customers.view, customers.merge
- Views: index.php (DataTable), ledger.php
- AJAX: search() for autocomplete in cash invoice forms
- Merge Function: Admin can merge duplicate customers

---

### Task 3.1: Challan Database & Models

**3.1.1 - challans Migration**
- Table: challans (id, company_id, challan_number, challan_type ENUM, challan_date, account_id, cash_customer_id, reference_number, notes, status ENUM, total_amount DECIMAL(15,2), invoice_generated BOOLEAN, invoice_id, submitted_at, submitted_by, approved_at, approved_by, cancelled_at, cancelled_by, cancellation_reason, is_deleted, timestamps, created_by, updated_by)
- Indexes: company_id, UNIQUE(company_id, challan_number), challan_date, account_id, cash_customer_id, status, invoice_generated, invoice_id
- ENUM challan_type: Rhodium Accounts, Meena Accounts, Wax
- ENUM status: Draft, Submitted, Approved, Invoice Generated, Cancelled
- CHECK Constraint: (account_id IS NOT NULL AND cash_customer_id IS NULL) OR (account_id IS NULL AND cash_customer_id IS NOT NULL)
- Foreign Keys: company_id, account_id, cash_customer_id, invoice_id, submitted_by, approved_by, cancelled_by, created_by, updated_by

**3.1.2 - challan_lines Migration**
- Table: challan_lines (id, challan_id, line_number, product_ids JSON, process_ids JSON, process_prices JSON, quantity DECIMAL(10,2), weight DECIMAL(10,3), rate DECIMAL(10,2), amount DECIMAL(15,2), image, gold_weight DECIMAL(10,3), gold_fine_weight DECIMAL(10,3), gold_touch, product_name, is_deleted, created_at)
- Indexes: challan_id, line_number
- Foreign Keys: challan_id → challans(id) ON DELETE CASCADE
- JSON Fields: product_ids [1,2,3], process_ids [1,2], process_prices {"1":50.00,"2":30.00}
- Business Rules: rate = SUM(process prices), amount = weight × rate (or rate if weight = 0)

**3.1.3 - ChallanModel**
- Extends BaseModel
- Methods: getChallanWithLines(), getApprovedChallansForAccount(), getApprovedChallansForCashCustomer(), canEdit(), canDelete(), markAsInvoiced(), getStatusCounts()
- Validation: challan_number unique per company, status ENUM, challan_type ENUM
- Status Workflow: Draft → Submitted → Approved → Invoice Generated
- Business Rules: Cannot edit if status = Approved or invoice_generated = TRUE, cannot delete if invoice_generated = TRUE
- XOR Validation: account_id OR cash_customer_id (not both, not neither)

**3.1.4 - ChallanLineModel**
- Extends BaseModel
- Methods: getLinesByChallen(), calculateLineAmount(), getProductsForLine(), getProcessesForLine(), getProcessPricesSnapshot()
- JSON Handling: product_ids, process_ids, process_prices stored and retrieved as arrays/objects
- Calculation: amount = weight × rate (if weight > 0) else amount = rate

---

### Task 3.2: Challan Calculation Service

**3.2.1 - ChallanCalculationService**
- Methods:
  - calculateLineRate(array $processIds): float - SUM of process prices
  - calculateLineAmount(float $rate, float $weight): float - weight × rate (or rate if weight = 0)
  - calculateWaxAmount(float $weight, float $accountPrice, float $minPrice): float - MAX(weight × price, minPrice)
  - calculateChallanTotal(int $challanId): float - SUM of all line amounts
  - recalculateChallanTotals(int $challanId): bool - Update challan.total_amount
- Wax Challan Logic: amount = MAX(weight × accountPrice, companyMinPrice)
- Process Rate Calculation: If processes [1,2] with prices [50,30] → rate = 80
- Amount Calculation: If weight = 10, rate = 80 → amount = 800

**3.2.2 - Test Calculation Logic**
- Unit Tests (PHPUnit):
  - testCalculateLineRate() - Multiple processes, single process, no processes
  - testCalculateLineAmount() - With weight, without weight (fixed price), zero weight
  - testCalculateWaxAmount() - Below minimum, above minimum, exactly minimum
  - testCalculateChallanTotal() - Multiple lines, single line, no lines
  - testEdgeCases() - Negative values, null values, large numbers, decimal precision
- Test Data Fixtures: Create test products, processes, challans
- Assertions: assertEquals, assertGreaterThan, assertLessThanOrEqual
- Coverage: 100% code coverage for calculation methods

---

### Task 3.3: Challan Service Core Logic

**3.3.2 - ChallanValidationService**
- Methods:
  - validateChallanData(array $data): array|bool - Validate challan header data
  - validateLineItems(array $lines): array|bool - Validate all line items
  - canEdit(int $challanId): bool - Check if challan can be edited
  - canDelete(int $challanId): bool - Check if challan can be deleted
  - canApprove(int $challanId): bool - Check if challan can be approved
  - canCancel(int $challanId): bool - Check if challan can be cancelled
  - validateStatusTransition(string $currentStatus, string $newStatus): bool - Validate status workflow
- Validation Rules:
  - challan_date <= today
  - account_id OR cash_customer_id (XOR)
  - At least one line item
  - Each line: product_ids OR process_ids (at least one)
  - weight >= 0, rate >= 0, amount >= 0
- Business Rules:
  - Cannot edit if status = Approved or Invoice Generated
  - Cannot delete if invoice_generated = TRUE
  - Status workflow: Draft → Submitted → Approved only
  - Cannot cancel if status = Approved or Invoice Generated

---

### Task 3.4: Challan Controllers

**3.4.1 - ChallanController**
- Routes:
  - GET /challans (index) - List all challans with filters
  - GET /challans/create (create) - Create challan form
  - POST /challans (store) - Save new challan
  - GET /challans/{id} (show) - View challan details
  - GET /challans/{id}/edit (edit) - Edit challan form
  - POST /challans/{id} (update) - Update challan
  - DELETE /challans/{id} (delete) - Soft delete
  - POST /challans/{id}/submit (submit) - Change status to Submitted
  - POST /challans/{id}/approve (approve) - Change status to Approved
  - POST /challans/{id}/cancel (cancel) - Change status to Cancelled
  - GET /challans/{id}/print (print) - Print challan PDF
- Permissions: challans.view, challans.create, challans.edit, challans.delete, challans.approve
- Views: index.php (DataTable with status filter), create.php (multi-step form), edit.php, show.php, print.php
- Form Features: Dynamic line items (add/remove), product multi-select, process multi-select, image upload per line, weight/rate/amount auto-calculation
- Status Transitions: Draft → Submitted (by billing manager), Submitted → Approved (by approver), any → Cancelled (by admin)

**3.4.2 - Handle File Uploads**
- FileUploadService Integration:
  - Upload directory: uploads/challans/{company_id}/{challan_id}/
  - Allowed formats: JPG, PNG, JPEG, PDF
  - Max file size: 5 MB per file
  - Multiple files per challan line (line item images)
- Validation:
  - File type validation
  - File size validation
  - Virus scanning (optional)
- Storage:
  - Generate unique filename: {timestamp}_{random}.{ext}
  - Store in public/uploads/challans/
  - Save relative path in database
- Delete: Remove physical file when challan line deleted

**3.4.3 - ChallanRules Validation**
- Custom Validation Rules (app/Validation/ChallanRules.php):
  - uniqueChallanNumber(string $number, string $companyId): bool
  - validChallanType(string $type): bool  
  - validChallanStatus(string $status): bool
  - validAccountOrCustomer(int $accountId, int $cashCustomerId): bool - XOR validation
  - validLineItems(array $lines): bool
  - validProductIds(array $productIds): bool - Check if products exist
  - validProcessIds(array $processIds): bool - Check if processes exist
- Validation Messages:
  - uniqueChallanNumber: "Challan number already exists"
  - validChallanType: "Invalid challan type selected"
  - validAccountOrCustomer: "Must select either account customer OR cash customer, not both"
  - validLineItems: "At least one line item required"

---

## ADDITIONAL REQUIREMENTS

### View Files Required

**Products Module:**
- app/Views/Products/index.php - List with DataTable, filters, search
- app/Views/Products/create.php - Create form with image upload
- app/Views/Products/edit.php - Edit form with image preview/delete

**Processes Module:**
- app/Views/Processes/index.php - List with DataTable, type filter
- app/Views/Processes/create.php - Create form
- app/Views/Processes/edit.php - Edit form with price change warning

**Accounts Module:**
- app/Views/Accounts/index.php - List with DataTable
- app/Views/Accounts/create.php - Create form with billing/shipping address
- app/Views/Accounts/edit.php - Edit form (cannot edit opening_balance)
- app/Views/Accounts/ledger.php - View ledger entries

**Cash Customers Module:**
- app/Views/CashCustomers/index.php - List with DataTable
- app/Views/CashCustomers/ledger.php - View ledger entries

**Challans Module:**
- app/Views/Challans/index.php - List with DataTable, status filter
- app/Views/Challans/create.php - Multi-step form with dynamic line items
- app/Views/Challans/edit.php - Edit form (locked if approved)
- app/Views/Challans/show.php - View challan details
- app/Views/Challans/print.php - Print-friendly challan format

### Routes Configuration

Add to app/Config/Routes.php:

```php
// Products
$routes->group('products', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Masters\ProductController::index');
    $routes->get('create', 'Masters\ProductController::create');
    $routes->post('/', 'Masters\ProductController::store');
    $routes->get('(:num)/edit', 'Masters\ProductController::edit/$1');
    $routes->post('(:num)', 'Masters\ProductController::update/$1');
    $routes->delete('(:num)', 'Masters\ProductController::delete/$1');
    $routes->post('(:num)/delete-image', 'Masters\ProductController::deleteImage/$1');
    $routes->get('search', 'Masters\ProductController::search');
});

// Processes
$routes->group('processes', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Masters\ProcessController::index');
    $routes->get('create', 'Masters\ProcessController::create');
    $routes->post('/', 'Masters\ProcessController::store');
    $routes->get('(:num)/edit', 'Masters\ProcessController::edit/$1');
    $routes->post('(:num)', 'Masters\ProcessController::update/$1');
    $routes->delete('(:num)', 'Masters\ProcessController::delete/$1');
    $routes->get('search', 'Masters\ProcessController::search');
    $routes->get('by-type', 'Masters\ProcessController::getByType');
});

// Accounts
$routes->group('accounts', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Customers\AccountController::index');
    $routes->get('create', 'Customers\AccountController::create');
    $routes->post('/', 'Customers\AccountController::store');
    $routes->get('(:num)/edit', 'Customers\AccountController::edit/$1');
    $routes->post('(:num)', 'Customers\AccountController::update/$1');
    $routes->delete('(:num)', 'Customers\AccountController::delete/$1');
    $routes->get('search', 'Customers\AccountController::search');
    $routes->get('(:num)/ledger', 'Customers\AccountController::ledger/$1');
});

// Cash Customers
$routes->group('cash-customers', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Customers\CashCustomerController::index');
    $routes->get('search', 'Customers\CashCustomerController::search');
    $routes->get('(:num)/ledger', 'Customers\CashCustomerController::ledger/$1');
    $routes->post('merge', 'Customers\CashCustomerController::merge');
});

// Challans
$routes->group('challans', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Challans\ChallanController::index');
    $routes->get('create', 'Challans\ChallanController::create');
    $routes->post('/', 'Challans\ChallanController::store');
    $routes->get('(:num)', 'Challans\ChallanController::show/$1');
    $routes->get('(:num)/edit', 'Challans\ChallanController::edit/$1');
    $routes->post('(:num)', 'Challans\ChallanController::update/$1');
    $routes->delete('(:num)', 'Challans\ChallanController::delete/$1');
    $routes->post('(:num)/submit', 'Challans\ChallanController::submit/$1');
    $routes->post('(:num)/approve', 'Challans\ChallanController::approve/$1');
    $routes->post('(:num)/cancel', 'Challans\ChallanController::cancel/$1');
    $routes->get('(:num)/print', 'Challans\ChallanController::print/$1');
});
```

### Sidebar Menu Integration

Add to app/Views/layout/sidebar.php:

```html
<!-- Masters Menu -->
<li class="nav-item has-treeview">
    <a href="#" class="nav-link">
        <i class="nav-icon fas fa-database"></i>
        <p>
            Masters
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <?php if (hasPermission('products.view')): ?>
        <li class="nav-item">
            <a href="<?= base_url('products') ?>" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Products</p>
            </a>
        </li>
        <?php endif; ?>

        <?php if (hasPermission('processes.view')): ?>
        <li class="nav-item">
            <a href="<?= base_url('processes') ?>" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Processes</p>
            </a>
        </li>
        <?php endif; ?>
    </ul>
</li>

<!-- Customers Menu -->
<li class="nav-item has-treeview">
    <a href="#" class="nav-link">
        <i class="nav-icon fas fa-users"></i>
        <p>
            Customers
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <?php if (hasPermission('accounts.view')): ?>
        <li class="nav-item">
            <a href="<?= base_url('accounts') ?>" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Accounts</p>
            </a>
        </li>
        <?php endif; ?>

        <?php if (hasPermission('customers.view')): ?>
        <li class="nav-item">
            <a href="<?= base_url('cash-customers') ?>" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Cash Customers</p>
            </a>
        </li>
        <?php endif; ?>
    </ul>
</li>

<!-- Challans Menu -->
<?php if (hasPermission('challans.view')): ?>
<li class="nav-item">
    <a href="<?= base_url('challans') ?>" class="nav-link">
        <i class="nav-icon fas fa-file-alt"></i>
        <p>Challans</p>
    </a>
</li>
<?php endif; ?>
```

---

## 🎯 NEXT STEPS AFTER PHASE 2-3

Once all 28 subtasks are completed:

1. **Run All Migrations:**
   ```bash
   php spark migrate
   php spark db:seed StateSeeder
   ```

2. **Test Each Module:**
   - Products CRUD + image upload
   - Processes CRUD + price tracking
   - Accounts CRUD + opening balance
   - Cash Customers + deduplication
   - Challans CRUD + status workflow

3. **Verify Multi-Tenant:**
   - Create 2 test companies
   - Ensure data isolation
   - Test company switching (super admin)

4. **Ready for Phase 4:**
   - Invoice Management (build on challan foundation)
   - Tax Calculation Service
   - Ledger Integration

---

## 📞 SUPPORT & QUESTIONS

For detailed prompts for each subtask, request them individually:
- "Generate detailed prompt for subtask 2.2.1"
- "Generate detailed prompt for subtask 3.1.3"

Each detailed prompt includes:
✓ Complete context and requirements
✓ Full table structures (for migrations)
✓ All method signatures and logic
✓ Business rules and validation
✓ Error handling and audit logging
✓ Acceptance criteria and test cases

**END OF PHASE 2-3 SUMMARY**

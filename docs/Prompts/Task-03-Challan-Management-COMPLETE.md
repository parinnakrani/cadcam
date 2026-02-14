# AI CODING PROMPTS - TASK 03

## Challan Management

**Version:** 1.0  
**Phase:** 3 - Challan Management (Weeks 7-9)  
**Generated:** February 10, 2026

---

## 📋 OVERVIEW

This document contains complete AI coding prompts for:

- **Task 3.1:** Challan Database & Models (Subtasks 3.1.1 - 3.1.4)
- **Task 3.2:** Challan Services (Subtasks 3.2.1 - 3.2.2)
- **Task 3.3:** Challan Controllers (Subtask 3.3.2)
- **Task 3.4:** Challan Views & UI (Subtasks 3.4.1 - 3.4.3)
- **Additional:** Routes, Sidebar, Validation

All prompts include complete PRD context, database schemas, business logic, and acceptance criteria.

---

## 🎯 TASK 3.1: CHALLAN DATABASE & MODELS

### Subtask 3.1.1: Create challans Migration

```
read .antigravity content and then

TASK: Generate migration file for challans table

FILE: app/Database/Migrations/2026-01-01-000012_create_challans_table.php

CONTEXT:
- Challans represent job orders for manufacturing processes
- Support three types: Rhodium, Meena, Wax
- Can be for Account customers or Cash customers (one or the other, not both)
- Status lifecycle: Draft → Pending → In Progress → Completed → Invoiced
- Challan number is sequential and unique per company
- Can be converted to invoice
- Stores customer type (Account/Cash) and corresponding customer ID

REQUIREMENTS:
Create CodeIgniter 4 migration with:

TABLE STRUCTURE:
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- company_id (INT, FK to companies.id, NOT NULL)
- challan_number (VARCHAR 50, NOT NULL)
- challan_date (DATE, NOT NULL)
- challan_type (ENUM('Rhodium', 'Meena', 'Wax'), NOT NULL)

// Customer (either account OR cash customer, not both)
- customer_type (ENUM('Account', 'Cash'), NOT NULL)
- account_id (INT, FK to accounts.id, NULL) // If customer_type = Account
- cash_customer_id (INT, FK to cash_customers.id, NULL) // If customer_type = Cash

// Status
- challan_status (ENUM('Draft', 'Pending', 'In Progress', 'Completed', 'Invoiced'), DEFAULT 'Draft')

// Amounts (calculated from challan_lines)
- total_weight (DECIMAL 10,3, DEFAULT 0.000) // Total gold weight
- subtotal_amount (DECIMAL 15,2, DEFAULT 0.00)
- tax_amount (DECIMAL 15,2, DEFAULT 0.00)
- total_amount (DECIMAL 15,2, DEFAULT 0.00)

// Invoice tracking
- invoice_generated (BOOLEAN, DEFAULT FALSE)
- invoice_id (INT, FK to invoices.id, NULL)

// Metadata
- notes (TEXT, NULL)
- delivery_date (DATE, NULL) // Expected delivery date
- created_by (INT, FK to users.id, NOT NULL)
- is_deleted (BOOLEAN, DEFAULT FALSE)
- created_at (TIMESTAMP NULL)
- updated_at (TIMESTAMP NULL)

INDEXES:
- PRIMARY KEY (id)
- INDEX (company_id)
- UNIQUE (company_id, challan_number)
- INDEX (account_id)
- INDEX (cash_customer_id)
- INDEX (challan_status)
- INDEX (challan_type)
- INDEX (challan_date)
- INDEX (invoice_id)

FOREIGN KEYS:
- company_id REFERENCES companies(id) ON DELETE CASCADE
- account_id REFERENCES accounts(id) ON DELETE RESTRICT
- cash_customer_id REFERENCES cash_customers(id) ON DELETE RESTRICT
- invoice_id REFERENCES invoices(id) ON DELETE SET NULL
- created_by REFERENCES users(id) ON DELETE RESTRICT

CONSTRAINTS:
- CHECK: (customer_type = 'Account' AND account_id IS NOT NULL AND cash_customer_id IS NULL)
        OR (customer_type = 'Cash' AND cash_customer_id IS NOT NULL AND account_id IS NULL)
- CHECK: total_weight >= 0
- CHECK: subtotal_amount >= 0

METHODS REQUIRED:
- up(): Create table with all columns, indexes, ENUMs, constraints
- down(): Drop table

ADDITIONAL REQUIREMENTS:
- Customer type constraint ensures only one customer ID is populated
- Status ENUM enforces valid workflow states
- Invoice tracking prevents duplicate invoice generation

DELIVERABLES:
Complete migration file

ACCEPTANCE CRITERIA:
- Migration runs successfully
- ENUM types working
- Customer type constraint enforced
- Unique challan number per company
- Foreign keys working
- CHECK constraints validated
```

---

### Subtask 3.1.2: Create challan_lines Migration

```
read .antigravity content and then

TASK: Generate migration file for challan_lines table

FILE: app/Database/Migrations/2026-01-01-000013_create_challan_lines_table.php

CONTEXT:
- Challan lines are line items in a challan
- Each line can have multiple products and multiple processes
- Products and processes stored as JSON arrays with quantities
- Gold weight tracked per line
- Line-level amount calculation
- Supports complex manufacturing scenarios (e.g., 3 products × 5 processes)

REQUIREMENTS:
Create CodeIgniter 4 migration with:

TABLE STRUCTURE:
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- challan_id (INT, FK to challans.id, NOT NULL)
- line_number (INT, NOT NULL) // 1, 2, 3... within challan

// Products (JSON array: [{product_id, quantity, description}])
- products_json (JSON, NOT NULL)
// Example: [{"product_id": 5, "quantity": 2, "description": "Ring Design A"}]

// Processes (JSON array: [{process_id, quantity, rate, amount}])
- processes_json (JSON, NOT NULL)
// Example: [{"process_id": 3, "quantity": 2, "rate": 50.00, "amount": 100.00}]

// Gold weight
- gold_weight_grams (DECIMAL 10,3, DEFAULT 0.000)
- gold_purity (VARCHAR 10, DEFAULT '22K') // 22K, 24K, 18K

// Amounts
- line_subtotal (DECIMAL 15,2, NOT NULL)
- line_tax_amount (DECIMAL 15,2, DEFAULT 0.00)
- line_total (DECIMAL 15,2, NOT NULL)

// Metadata
- notes (TEXT, NULL)
- is_deleted (BOOLEAN, DEFAULT FALSE)
- created_at (TIMESTAMP NULL)
- updated_at (TIMESTAMP NULL)

INDEXES:
- PRIMARY KEY (id)
- INDEX (challan_id)
- INDEX (line_number)

FOREIGN KEYS:
- challan_id REFERENCES challans(id) ON DELETE CASCADE

CONSTRAINTS:
- CHECK: gold_weight_grams >= 0
- CHECK: line_subtotal >= 0
- CHECK: line_total >= 0

METHODS REQUIRED:
- up(): Create table with JSON columns, indexes, constraints
- down(): Drop table

ADDITIONAL REQUIREMENTS:
- JSON columns allow flexible product/process combinations
- Each line calculates its own amount
- Challan total = SUM(line_total) from all lines

DELIVERABLES:
Complete migration file

ACCEPTANCE CRITERIA:
- Migration runs successfully
- JSON columns supported (MySQL 5.7+)
- Foreign key cascade delete works
- CHECK constraints enforced
- Line number ordering maintained
```

---

### Subtask 3.1.3: Create ChallanModel

```
read .antigravity content and then

TASK: Generate ChallanModel with relationships and status management

FILE: app/Models/ChallanModel.php

CONTEXT:
- Challans represent manufacturing job orders
- Multi-tenant: auto-filter by company_id
- Status workflow enforcement
- Relationships: lines, account, cash_customer, invoice
- Soft delete support
- Cannot delete if invoiced

REQUIREMENTS:
Create CodeIgniter 4 Model extending \CodeIgniter\Model with:

PROPERTIES:
- protected $table = 'challans';
- protected $primaryKey = 'id';
- protected $useTimestamps = true;
- protected $allowedFields = [
    'company_id', 'challan_number', 'challan_date', 'challan_type',
    'customer_type', 'account_id', 'cash_customer_id',
    'challan_status', 'total_weight', 'subtotal_amount',
    'tax_amount', 'total_amount', 'invoice_generated', 'invoice_id',
    'notes', 'delivery_date', 'created_by', 'is_deleted'
  ]
- protected $validationRules = [
    'company_id' => 'required|integer',
    'challan_number' => 'required|max_length[50]',
    'challan_date' => 'required|valid_date',
    'challan_type' => 'required|in_list[Rhodium,Meena,Wax]',
    'customer_type' => 'required|in_list[Account,Cash]',
    'challan_status' => 'required|in_list[Draft,Pending,In Progress,Completed,Invoiced]',
    'total_weight' => 'permit_empty|decimal',
    'subtotal_amount' => 'required|decimal',
    'total_amount' => 'required|decimal'
  ]

RELATIONSHIPS (use joins, not ORM):

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

3. public function getChallanWithCustomer(int $id): ?array
   - Select challans.*,
           accounts.account_name, accounts.mobile as account_mobile,
           cash_customers.customer_name, cash_customers.mobile as cash_mobile,
           users.full_name as created_by_name
   - Left join accounts ON account_id
   - Left join cash_customers ON cash_customer_id
   - Join users ON created_by
   - where('challans.id', $id)
   - Apply company filter
   - Return result with customer data or null

4. public function getChallanWithLines(int $id): ?array
   - Get challan with customer (call getChallanWithCustomer)
   - Get all challan_lines for this challan
   - Merge lines into challan array
   - Return complete challan data

5. public function getChallansByStatus(string $status): array
   - where('company_id', session company_id)
   - where('challan_status', $status)
   - where('is_deleted', FALSE)
   - orderBy('challan_date', 'DESC')
   - Return findAll()

6. public function getPendingChallans(int $customerId = null, string $customerType = null): array
   - where('company_id', session company_id)
   - whereIn('challan_status', ['Pending', 'In Progress', 'Completed'])
   - where('invoice_generated', FALSE)
   - If $customerId and $customerType provided: filter by customer
   - where('is_deleted', FALSE)
   - orderBy('challan_date', 'ASC')
   - Return findAll()

7. public function updateTotals(int $challanId, array $totals): bool
   - Update total_weight, subtotal_amount, tax_amount, total_amount
   - where('id', $challanId)
   - No validation (called by service after line calculation)
   - Return success

8. public function markAsInvoiced(int $challanId, int $invoiceId): bool
   - Update challan_status = 'Invoiced'
   - Update invoice_generated = TRUE
   - Update invoice_id = $invoiceId
   - where('id', $challanId)
   - Return success

9. public function canDelete(int $challanId): bool
   - Check if invoice_generated = TRUE
   - If TRUE: return FALSE (cannot delete invoiced challan)
   - Return TRUE

10. public function searchChallans(string $query): array
    - where('company_id', session company_id)
    - where('is_deleted', FALSE)
    - like('challan_number', $query) OR like('notes', $query)
    - limit(20)
    - Return results

ADDITIONAL REQUIREMENTS:
- Auto-filter by company_id
- Customer type determines which customer join to use
- Status validation in service layer
- Totals recalculated when lines change

DELIVERABLES:
Complete ChallanModel.php file

ACCEPTANCE CRITERIA:
- Model auto-filters by company_id
- Relationships working (customer, lines, invoice)
- Status queries work
- Cannot delete if invoiced
- Validation rules enforced
- Search works
```

---

### Subtask 3.1.4: Create ChallanLineModel

```
read .antigravity content and then

TASK: Generate ChallanLineModel with JSON handling

FILE: app/Models/ChallanLineModel.php

CONTEXT:
- Challan lines store products and processes as JSON
- Each line calculates its own amounts
- JSON encoding/decoding handled transparently
- Line numbers sequential within challan

REQUIREMENTS:
Create CodeIgniter 4 Model extending \CodeIgniter\Model with:

PROPERTIES:
- protected $table = 'challan_lines';
- protected $primaryKey = 'id';
- protected $useTimestamps = true;
- protected $allowedFields = [
    'challan_id', 'line_number', 'products_json', 'processes_json',
    'gold_weight_grams', 'gold_purity', 'line_subtotal',
    'line_tax_amount', 'line_total', 'notes', 'is_deleted'
  ]
- protected $validationRules = [
    'challan_id' => 'required|integer',
    'line_number' => 'required|integer',
    'products_json' => 'required', // Must be valid JSON
    'processes_json' => 'required', // Must be valid JSON
    'line_subtotal' => 'required|decimal',
    'line_total' => 'required|decimal'
  ]

CAST PROPERTIES (JSON auto-encoding):
- protected $casts = [
    'products_json' => 'json',
    'processes_json' => 'json'
  ];

METHODS REQUIRED:

1. public function getLinesByChallanId(int $challanId): array
   - where('challan_id', $challanId)
   - where('is_deleted', FALSE)
   - orderBy('line_number', 'ASC')
   - Return findAll()
   - JSON fields automatically decoded to arrays

2. public function getLineWithDetails(int $lineId): ?array
   - Select challan_lines.*
   - where('id', $lineId)
   - where('is_deleted', FALSE)
   - Return result with decoded JSON

3. public function getNextLineNumber(int $challanId): int
   - Select MAX(line_number) from challan_lines
   - where('challan_id', $challanId)
   - Return max + 1 (or 1 if no lines exist)

4. public function deleteLine(int $lineId): bool
   - Soft delete: set is_deleted = TRUE
   - where('id', $lineId)
   - Return success

5. public function getTotalWeightForChallan(int $challanId): float
   - SUM(gold_weight_grams)
   - where('challan_id', $challanId)
   - where('is_deleted', FALSE)
   - Return total

6. public function getTotalsForChallan(int $challanId): array
   - SUM(line_subtotal) as subtotal
   - SUM(line_tax_amount) as tax
   - SUM(line_total) as total
   - SUM(gold_weight_grams) as weight
   - where('challan_id', $challanId)
   - where('is_deleted', FALSE)
   - Return: ['subtotal' => ..., 'tax' => ..., 'total' => ..., 'weight' => ...]

ADDITIONAL REQUIREMENTS:
- JSON casting handles encoding/decoding automatically
- Line number enforced to be sequential
- Totals recalculated when lines added/removed

DELIVERABLES:
Complete ChallanLineModel.php file

ACCEPTANCE CRITERIA:
- JSON fields automatically encode/decode
- Line retrieval by challan works
- Next line number generation works
- Totals calculation accurate
- Soft delete working
```

---

## 🎯 TASK 3.2: CHALLAN SERVICES

### Subtask 3.2.1: Create ChallanService

```
read .antigravity content and then

TASK: Generate ChallanService with business logic

FILE: app/Services/Challan/ChallanService.php

CONTEXT:
- Handle challan CRUD operations
- Generate sequential challan numbers (thread-safe)
- Calculate totals from lines
- Status workflow enforcement
- Cannot delete if invoiced
- Support both account and cash customers

REQUIREMENTS:
Create ChallanService class with:

DEPENDENCIES (inject in __construct):
- ChallanModel
- ChallanLineModel
- AccountModel (validate account exists)
- CashCustomerModel (validate cash customer exists)
- NumberingService (generate challan number)
- ChallanCalculationService
- AuditService

METHODS REQUIRED:

1. public function createChallan(array $data): int
   - Validate required fields
   - Auto-set company_id from session
   - Auto-set created_by from session user_id
   - Validate customer:
     - If customer_type = 'Account': validate account_id exists and belongs to company
     - If customer_type = 'Cash': validate cash_customer_id exists and belongs to company
   - Generate challan_number via NumberingService->getNextChallanNumber()
   - Set challan_status = 'Draft' (default)
   - Start DB transaction
   - Insert challan record
   - If lines provided:
     - Create lines via createChallanLines()
     - Recalculate totals
   - Commit transaction
   - Audit log create action
   - Return challan ID

2. public function updateChallan(int $id, array $data): bool
   - Validate challan exists and belongs to company
   - Check if invoiced: if TRUE, throw exception "Cannot edit invoiced challan"
   - Store before data for audit
   - If customer changed: validate new customer
   - Update challan record (exclude totals, calculated separately)
   - Audit log update action
   - Return TRUE

3. public function deleteChallan(int $id): bool
   - Validate challan exists and belongs to company
   - Check if invoiced: ChallanModel->canDelete($id)
   - If FALSE: throw exception "Cannot delete invoiced challan"
   - Soft delete: set is_deleted = TRUE
   - Soft delete all lines: ChallanLineModel->where('challan_id', $id)->set('is_deleted', TRUE)->update()
   - Audit log delete action
   - Return TRUE

4. public function getChallanById(int $id): ?array
   - Call ChallanModel->getChallanWithCustomer($id)
   - Validate belongs to company
   - Return challan data or null

5. public function getChallanWithLines(int $id): ?array
   - Call ChallanModel->getChallanWithLines($id)
   - Validate belongs to company
   - Decode JSON in lines
   - Return complete challan with lines

6. public function updateChallanStatus(int $id, string $newStatus): bool
   - Validate challan exists
   - Validate status transition allowed (see workflow rules below)
   - Update challan_status
   - Audit log status change
   - Return TRUE

7. public function recalculateTotals(int $challanId): bool
   - Get totals via ChallanLineModel->getTotalsForChallan($challanId)
   - Update challan totals via ChallanModel->updateTotals($challanId, $totals)
   - Return TRUE

8. public function markAsInvoiced(int $challanId, int $invoiceId): bool
   - Call ChallanModel->markAsInvoiced($challanId, $invoiceId)
   - Audit log "Challan converted to invoice"
   - Return TRUE

9. public function getPendingChallansForCustomer(int $customerId, string $customerType): array
   - Call ChallanModel->getPendingChallans($customerId, $customerType)
   - Return array of challans not yet invoiced

10. private function validateChallanData(array $data): void
    - Check required fields: challan_date, challan_type, customer_type, customer_id
    - Validate challan_date not future date
    - Validate challan_type in [Rhodium, Meena, Wax]
    - Validate customer_type in [Account, Cash]
    - Throw ValidationException if invalid

STATUS WORKFLOW RULES:
- Draft → Pending (allowed)
- Draft → In Progress (allowed)
- Pending → In Progress (allowed)
- In Progress → Completed (allowed)
- Completed → Invoiced (allowed, via invoice generation)
- Invoiced → * (NOT allowed, cannot change status once invoiced)
- Cannot move backwards (e.g., Completed → Pending NOT allowed)

ERROR HANDLING:
- Throw ChallanNotFoundException if not found
- Throw ValidationException for invalid data
- Throw InvalidStatusTransitionException for invalid status change
- Throw ChallanInvoicedException if trying to edit/delete invoiced challan
- Rollback transaction on error

DELIVERABLES:
Complete ChallanService.php file

ACCEPTANCE CRITERIA:
- CRUD operations working
- Challan number auto-generated
- Status workflow enforced
- Cannot edit/delete invoiced challan
- Totals recalculated correctly
- All actions audit logged
- Transaction safety ensured
```

---

### Subtask 3.2.2: Create ChallanCalculationService

```
read .antigravity content and then

TASK: Generate ChallanCalculationService for amount calculations

FILE: app/Services/Challan/ChallanCalculationService.php

CONTEXT:
- Calculate line amounts from products and processes
- Calculate tax amounts
- Calculate challan totals
- Handle gold weight calculations
- Separate service for calculation logic (testable)

REQUIREMENTS:
Create ChallanCalculationService class with:

DEPENDENCIES (inject in __construct):
- ProductModel (fetch product details)
- ProcessModel (fetch process rates)
- CompanyModel (fetch tax rate)

METHODS REQUIRED:

1. public function calculateLineTotal(array $lineData): array
   - Input: {products_json: [...], processes_json: [...], gold_weight_grams}
   - For each process in processes_json:
     - Fetch current process rate (if not provided)
     - Calculate amount = quantity × rate
   - Sum all process amounts = line_subtotal
   - Calculate tax: line_tax_amount = line_subtotal × (company tax_rate / 100)
   - Calculate line_total = line_subtotal + line_tax_amount
   - Return: ['line_subtotal' => ..., 'line_tax_amount' => ..., 'line_total' => ...]

2. public function calculateChallanTotals(array $lines): array
   - Input: array of line data
   - For each line:
     - Calculate line total (call calculateLineTotal)
   - Sum all line_subtotals = subtotal_amount
   - Sum all line_tax_amounts = tax_amount
   - Sum all line_totals = total_amount
   - Sum all gold_weight_grams = total_weight
   - Return: ['subtotal_amount' => ..., 'tax_amount' => ..., 'total_amount' => ..., 'total_weight' => ...]

3. public function recalculateProcessAmounts(array $processes): array
   - Input: array of processes [{process_id, quantity, rate (optional)}]
   - For each process:
     - If rate not provided: fetch current rate from ProcessModel
     - Calculate amount = quantity × rate
   - Return updated processes array with amounts

4. public function validateLineData(array $lineData): bool
   - Check products_json is valid array
   - Check processes_json is valid array
   - Check at least 1 product and 1 process
   - Check gold_weight_grams >= 0
   - Return TRUE if valid
   - Throw ValidationException if invalid

5. public function getTaxRate(): float
   - Fetch company tax_rate from CompanyModel (current company)
   - Return tax rate (e.g., 3.00 for 3%)

CALCULATION LOGIC EXAMPLE:
```

Line Data:

- products_json: [{"product_id": 5, "quantity": 2}]
- processes_json: [{"process_id": 3, "quantity": 2, "rate": 50.00}]

Calculation:

- Process amount = 2 × 50.00 = 100.00
- line_subtotal = 100.00
- Tax (3%) = 100.00 × 0.03 = 3.00
- line_total = 100.00 + 3.00 = 103.00

```

ERROR HANDLING:
- Throw CalculationException if rates not found
- Throw ValidationException if data invalid
- Log calculation errors

DELIVERABLES:
Complete ChallanCalculationService.php file

ACCEPTANCE CRITERIA:
- Line calculations accurate
- Challan totals sum correctly
- Tax calculation correct
- Process rates fetched automatically if not provided
- Validation catches invalid data
- Service is unit-testable (no direct DB calls, uses models)
```

---

## 🎯 TASK 3.3: CHALLAN CONTROLLERS

### Subtask 3.3.2: Create ChallanController (Main)

```
read .antigravity content and then

TASK: Generate ChallanController for all challan types

FILE: app/Controllers/Challans/ChallanController.php

CONTEXT:
- Handle HTTP requests for challan management
- Supports all three types: Rhodium, Meena, Wax
- Type-specific controllers extend this base (future)
- Permission checks: challan.create, challan.edit, challan.delete, challan.view
- AJAX endpoints for dynamic forms
- Line item management (add/remove lines)

REQUIREMENTS:
Create CodeIgniter 4 Controller extending BaseController:

DEPENDENCIES (inject in __construct):
- ChallanService
- ChallanCalculationService
- AccountModel (for customer dropdown)
- CashCustomerModel (for customer dropdown)
- ProductModel (for product dropdown)
- ProcessModel (for process dropdown)
- PermissionService

ROUTES REQUIRED:
- GET /challans → index() (list all)
- GET /challans/create → create() (show form)
- POST /challans → store() (create new)
- GET /challans/{id} → show() (view details)
- GET /challans/{id}/edit → edit() (show edit form)
- POST /challans/{id} → update() (update existing)
- DELETE /challans/{id} → delete() (soft delete)
- POST /challans/{id}/add-line → addLine() (API: add line item)
- DELETE /challans/lines/{lineId} → deleteLine() (API: remove line)
- POST /challans/{id}/change-status → changeStatus() (API: update status)
- GET /challans/{id}/print → print() (print challan)

METHODS REQUIRED:

1. public function index()
   - Check permission: challan.view
   - Get query params: challan_type, challan_status, customer_type, from_date, to_date
   - Load challans via ChallanService (apply filters)
   - If AJAX: return JSON
   - Else: load view with challans data
   - View: app/Views/challans/index.php

2. public function create()
   - Check permission: challan.create
   - Get query param: type (Rhodium/Meena/Wax)
   - Load dropdowns:
     - Accounts (AccountModel->getActiveAccounts())
     - Cash Customers (CashCustomerModel->getActiveCashCustomers())
     - Products (ProductModel->getActiveProducts())
     - Processes (ProcessModel->getActiveProcesses($type))
   - Load view: app/Views/challans/create.php
   - Pass: challan_type, dropdowns

3. public function store()
   - Check permission: challan.create
   - Validate CSRF token
   - Get POST data: challan data + lines array
   - Call ChallanService->createChallan($data)
   - For each line in lines array:
     - Call ChallanLineService->createLine() (or include in challan creation)
   - Recalculate totals: ChallanService->recalculateTotals($challanId)
   - Set flash message: "Challan created successfully"
   - Redirect to /challans/{id}

4. public function show(int $id)
   - Check permission: challan.view
   - Load challan with lines: ChallanService->getChallanWithLines($id)
   - If not found: 404
   - Load view: app/Views/challans/show.php
   - Pass challan data with lines

5. public function edit(int $id)
   - Check permission: challan.edit
   - Load challan with lines: ChallanService->getChallanWithLines($id)
   - If not found: 404
   - Check if invoiced: if TRUE, show error "Cannot edit invoiced challan"
   - Load dropdowns (accounts, customers, products, processes)
   - Load view: app/Views/challans/edit.php
   - Pass challan data and dropdowns

6. public function update(int $id)
   - Check permission: challan.edit
   - Validate CSRF token
   - Get POST data
   - Try: ChallanService->updateChallan($id, $data)
   - Catch ChallanInvoicedException: error message
   - Update lines (add/remove as needed)
   - Recalculate totals
   - Set flash message: "Challan updated successfully"
   - Redirect to /challans/{id}

7. public function delete(int $id)
   - Check permission: challan.delete
   - Try: ChallanService->deleteChallan($id)
   - Catch ChallanInvoicedException: return JSON error
   - Set flash message: "Challan deleted successfully"
   - Return JSON success

8. public function addLine(int $id)
   - Check permission: challan.edit
   - Validate CSRF token
   - Get POST data: line data (products_json, processes_json, gold_weight)
   - Calculate line total: ChallanCalculationService->calculateLineTotal($lineData)
   - Insert line: ChallanLineModel->insert()
   - Recalculate challan totals
   - Return JSON: {success: true, line_id: ..., totals: {...}}

9. public function deleteLine(int $lineId)
   - Check permission: challan.edit
   - Delete line: ChallanLineModel->deleteLine($lineId)
   - Get challan_id from line
   - Recalculate challan totals
   - Return JSON success with updated totals

10. public function changeStatus(int $id)
    - Check permission: challan.edit
    - Get POST data: new_status
    - Try: ChallanService->updateChallanStatus($id, $new_status)
    - Catch InvalidStatusTransitionException: return JSON error with message
    - Return JSON success

11. public function print(int $id)
    - Check permission: challan.view
    - Load challan with lines
    - Generate PDF using ChallanPDF library
    - Return PDF for download or inline view

ERROR HANDLING:
- Catch all exceptions
- Return JSON error for API endpoints
- Flash messages for web requests
- Log errors

DELIVERABLES:
Complete ChallanController.php file

ACCEPTANCE CRITERIA:
- All CRUD operations working
- Permission checks enforced
- Line item add/remove via AJAX works
- Status change workflow enforced
- Cannot edit/delete invoiced challan
- Print functionality works
- Totals recalculate automatically
```

---

## 🎯 TASK 3.4: CHALLAN VIEWS & UI

### Subtask 3.4.1: Create Challan List View

```
TASK: Create challan list/index view with filters

FILE: app/Views/challans/index.php

CONTEXT:
- Display all challans in DataTable
- Filter by type, status, customer type, date range
- Status badges with color coding
- Quick actions: View, Edit, Delete, Print
- Click row to view details

REQUIREMENTS:

PAGE STRUCTURE:
- Page title: "Challans"
- Breadcrumb: Home > Challans

FILTERS (above DataTable):
- Challan Type dropdown (All, Rhodium, Meena, Wax)
- Status dropdown (All, Draft, Pending, In Progress, Completed, Invoiced)
- Customer Type dropdown (All, Account, Cash)
- Date Range picker (From Date, To Date)
- Filter button, Reset button

ACTION BUTTONS:
- "Create Challan" dropdown:
  - Create Rhodium Challan
  - Create Meena Challan
  - Create Wax Challan

DATATABLE COLUMNS:
- Challan Number (link to show page)
- Date
- Type (badge: Rhodium=blue, Meena=green, Wax=orange)
- Customer Name (account or cash)
- Status (badge with colors: Draft=gray, Pending=yellow, In Progress=blue, Completed=green, Invoiced=purple)
- Total Weight (grams)
- Total Amount (₹ formatted)
- Actions (View, Edit, Delete, Print icons)

STATUS BADGE COLORS:
- Draft: bg-secondary (gray)
- Pending: bg-warning (yellow)
- In Progress: bg-info (blue)
- Completed: bg-success (green)
- Invoiced: bg-primary (purple)

TYPE BADGE COLORS:
- Rhodium: bg-primary (blue)
- Meena: bg-success (green)
- Wax: bg-warning (orange)

JAVASCRIPT:
- DataTables initialization with server-side processing
- Filter button triggers AJAX reload
- Delete confirmation modal
- Print button opens print view in new tab

DELIVERABLES:
Complete index.php view file

ACCEPTANCE CRITERIA:
- Filters work correctly
- DataTable displays challans
- Status and type badges color-coded
- Row click navigates to show page
- Actions work (edit, delete, print)
- Delete confirmation shown
```

---

### Subtask 3.4.2: Create Challan Form View (Create/Edit)

```
TASK: Create challan creation/edit form with line items

FILE: app/Views/challans/create.php (also used as base for edit.php)

CONTEXT:
- Complex form with header and line items
- Dynamic line item addition (JavaScript)
- Customer selection (account or cash)
- Product and process multi-select per line
- Real-time amount calculation
- Gold weight tracking

REQUIREMENTS:

PAGE STRUCTURE:
- Page title: "Create [Type] Challan" (e.g., "Create Rhodium Challan")
- Breadcrumb: Home > Challans > Create

FORM SECTIONS:

**1. Challan Header:**
- Challan Number (auto-generated, read-only, shown after creation)
- Challan Date (date picker, default today)
- Challan Type (dropdown or pre-selected based on route)
- Customer Type (radio: Account / Cash)
- Customer (dropdown, changes based on customer type selection)
  - If Account: show accounts dropdown
  - If Cash: show cash customers dropdown with "Quick Add" button
- Expected Delivery Date (optional)
- Notes (textarea)

**2. Line Items Section:**
- Table with columns:
  - Line # (auto-numbered)
  - Products (multi-select dropdown, search enabled)
  - Processes (multi-select dropdown, filtered by challan type)
  - Gold Weight (grams, input)
  - Gold Purity (dropdown: 22K, 24K, 18K)
  - Subtotal (calculated, read-only)
  - Tax (calculated, read-only)
  - Total (calculated, read-only)
  - Actions (Remove line button)
- "Add Line" button (adds new row dynamically)

**3. Totals Section (right-aligned):**
- Total Weight: [value] grams
- Subtotal: ₹ [value]
- Tax Amount: ₹ [value]
- Total Amount: ₹ [value]

JAVASCRIPT FUNCTIONALITY:
1. Customer Type Radio Change:
   - Show/hide account or cash customer dropdown

2. Add Line Button:
   - Clone line row template
   - Increment line number
   - Clear inputs
   - Initialize dropdowns (Select2)

3. Remove Line Button:
   - Remove line row
   - Recalculate totals

4. Process Selection:
   - Fetch process rates via AJAX
   - Populate rate fields automatically

5. Amount Calculation (on change of processes or quantities):
   - For each line:
     - Sum process amounts
     - Calculate tax (subtotal × tax_rate)
     - Calculate line total
   - Sum all lines for challan totals
   - Update totals section

6. Form Submission:
   - Validate: at least one line
   - Validate: customer selected
   - Serialize form data including lines array
   - POST to /challans

VALIDATION:
- Required: challan_date, challan_type, customer_type, customer_id
- At least 1 line required
- Each line must have at least 1 product and 1 process

DELIVERABLES:
Complete create.php view file with JavaScript

ACCEPTANCE CRITERIA:
- Customer type selection works
- Line items add/remove dynamically
- Process rates auto-populated
- Amount calculation real-time
- Totals update automatically
- Form submission with lines array works
- Validation prevents incomplete submission
```

---

### Subtask 3.4.3: Create Challan Details View

```
TASK: Create challan details/show view

FILE: app/Views/challans/show.php

CONTEXT:
- Display complete challan details read-only
- Show all line items
- Action buttons based on status and permissions
- Print option

REQUIREMENTS:

PAGE STRUCTURE:
- Page title: "Challan Details - [Challan Number]"
- Breadcrumb: Home > Challans > [Challan Number]

CHALLAN HEADER:
- Challan Number (large, prominent)
- Challan Date
- Challan Type (badge)
- Status (badge with color)
- Customer Information:
  - Customer Name
  - Mobile
  - Address (if account customer)
- Expected Delivery Date
- Notes

LINE ITEMS TABLE:
- Columns:
  - Line #
  - Products (list of products with quantities)
  - Processes (list of processes with quantities and rates)
  - Gold Weight (grams)
  - Gold Purity
  - Subtotal (₹ formatted)
  - Tax (₹ formatted)
  - Total (₹ formatted)

TOTALS BOX (right-aligned):
- Total Weight: [value] grams
- Subtotal: ₹ [value]
- Tax Amount: ₹ [value]
- **Total Amount: ₹ [value]** (bold)

ACTION BUTTONS (based on status and permissions):
- Edit (if status != Invoiced AND has challan.edit permission)
- Delete (if status != Invoiced AND has challan.delete permission)
- Change Status (dropdown with valid next statuses)
- Generate Invoice (if status = Completed AND not yet invoiced)
- Print (always visible if has challan.view permission)
- Back to List

STATUS CHANGE DROPDOWN:
- Show only valid next statuses based on current status
- E.g., if status = "Pending", show: "Mark as In Progress"
- If status = "In Progress", show: "Mark as Completed"
- Cannot change if status = "Invoiced"

INVOICE INFO (if invoiced):
- Show: "This challan has been converted to Invoice #INV-001"
- Link to invoice details

DELIVERABLES:
Complete show.php view file

ACCEPTANCE CRITERIA:
- All challan details displayed
- Line items table formatted correctly
- Totals calculated and displayed
- Action buttons visible based on permissions and status
- Status change dropdown works
- Print button opens print view
- Invoice link shown if invoiced
```

---

### ADDITIONAL SUBTASKS FOR TASK 3

---

#### Subtask 3.4.4: Add Challan Routes

````
TASK: Configure routes for Challans

FILE: app/Config/Routes.php

CONTEXT:
- RESTful routes for challans
- Apply AuthFilter and PermissionFilter
- API endpoints for line management

REQUIREMENTS:
Add the following route group:

```php
$routes->group('challans', ['filter' => 'auth', 'filter' => 'permission:challan'], function($routes) {
    $routes->get('/', 'Challans\ChallanController::index');
    $routes->get('create', 'Challans\ChallanController::create');
    $routes->post('/', 'Challans\ChallanController::store');
    $routes->get('(:num)', 'Challans\ChallanController::show/$1');
    $routes->get('(:num)/edit', 'Challans\ChallanController::edit/$1');
    $routes->post('(:num)', 'Challans\ChallanController::update/$1');
    $routes->delete('(:num)', 'Challans\ChallanController::delete/$1');
    $routes->post('(:num)/add-line', 'Challans\ChallanController::addLine/$1');
    $routes->delete('lines/(:num)', 'Challans\ChallanController::deleteLine/$1');
    $routes->post('(:num)/change-status', 'Challans\ChallanController::changeStatus/$1');
    $routes->get('(:num)/print', 'Challans\ChallanController::print/$1');
});
````

DELIVERABLES:
Updated Routes.php

ACCEPTANCE CRITERIA:

- All routes working
- Permission checks applied
- API endpoints accessible

```

---

#### Subtask 3.4.5: Add Challans to Sidebar

```

TASK: Add Challans menu to sidebar

FILE: app/Views/layouts/sidebar.php

REQUIREMENTS:
Add menu item:

```html
<?php if (can('challan.view')): ?>
<li class="nav-item">
  <a class="nav-link" href="<?= base_url('challans') ?>">
    <i class="fas fa-clipboard-list"></i> Challans
  </a>
</li>
<?php endif; ?>
```

DELIVERABLES:
Updated sidebar

ACCEPTANCE CRITERIA:

- Menu visible based on permission
- Navigation works
- Active highlighting

```

---

**END OF TASK-03 COMPLETE**

```

# AI CODING PROMPTS - TASK 04

## Invoice Management

**Version:** 1.0  
**Phase:** 4 - Invoice Management (Weeks 10-12)  
**Generated:** February 10, 2026

---

## 📋 OVERVIEW

This document contains complete AI coding prompts for Task 04 subtasks:
• 4.1.1-4.1.4: Invoice Database & Models\n• 4.2.2: Invoice Service\n• 4.3.1-4.3.2: Tax Calculation\n• 4.4.2-4.4.3: Invoice Controllers\n• 4.5.1-4.5.3: Views, Routes, Sidebar

All prompts are production-ready and include:
✅ Complete PRD context and business requirements
✅ Database schema details with all fields
✅ Business logic and calculation methods
✅ Validation rules and constraints
✅ Service layer architecture patterns
✅ Controller patterns with all HTTP methods
✅ View file structure with UI components
✅ Route configuration
✅ Sidebar navigation integration
✅ Error handling and edge cases
✅ Acceptance criteria for each subtask

---

## 🎯 TASK 4.1: INVOICE DATABASE & MODELS

### Subtask 4.1.1: Create invoices Migration

```
read .antigravity content and then

TASK: Generate migration file for invoices table

FILE: app/Database/Migrations/2026-01-01-000014_create_invoices_table.php

CONTEXT:
- Invoices can be generated from challans OR created standalone (cash invoices)
- Support three types: Account Invoice, Cash Invoice, Wax Invoice
- Tax calculation: CGST+SGST (same state) OR IGST (different state)
- Payment tracking: amount_paid, amount_due
- Status lifecycle: Draft → Pending → Paid → Partially Paid → Delivered
- Invoice number sequential and unique per company

REQUIREMENTS:
Create CodeIgniter 4 migration with:

TABLE STRUCTURE:
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- company_id (INT, FK to companies.id, NOT NULL)
- invoice_number (VARCHAR 50, NOT NULL)
- invoice_date (DATE, NOT NULL)
- invoice_type (ENUM('Account', 'Cash', 'Wax'), NOT NULL)

// Customer (either account OR cash, not both)
- customer_type (ENUM('Account', 'Cash'), NOT NULL)
- account_id (INT, FK to accounts.id, NULL)
- cash_customer_id (INT, FK to cash_customers.id, NULL)

// Challan reference (optional, null for standalone invoices)
- challan_id (INT, FK to challans.id, NULL)

// Amounts
- subtotal_amount (DECIMAL 15,2, NOT NULL)
- tax_type (ENUM('CGST_SGST', 'IGST'), NOT NULL) // Based on state comparison
- cgst_rate (DECIMAL 5,2, DEFAULT 0.00) // e.g., 1.5 for 1.5%
- cgst_amount (DECIMAL 15,2, DEFAULT 0.00)
- sgst_rate (DECIMAL 5,2, DEFAULT 0.00)
- sgst_amount (DECIMAL 15,2, DEFAULT 0.00)
- igst_rate (DECIMAL 5,2, DEFAULT 0.00) // e.g., 3.0 for 3%
- igst_amount (DECIMAL 15,2, DEFAULT 0.00)
- total_tax_amount (DECIMAL 15,2, DEFAULT 0.00) // Sum of CGST+SGST or IGST
- grand_total (DECIMAL 15,2, NOT NULL) // subtotal + total_tax

// Payment tracking
- amount_paid (DECIMAL 15,2, DEFAULT 0.00)
- amount_due (DECIMAL 15,2, NOT NULL) // grand_total - amount_paid

// Status
- invoice_status (ENUM('Draft', 'Pending', 'Partially Paid', 'Paid', 'Delivered'), DEFAULT 'Pending')
- payment_status (ENUM('Unpaid', 'Partially Paid', 'Paid'), DEFAULT 'Unpaid')

// Delivery
- delivery_status (ENUM('Not Delivered', 'Out for Delivery', 'Delivered'), DEFAULT 'Not Delivered')

// Payment terms
- due_date (DATE, NULL)
- payment_terms (VARCHAR 100, NULL) // e.g., "Net 30 days"

// Metadata
- notes (TEXT, NULL)
- created_by (INT, FK to users.id, NOT NULL)
- is_deleted (BOOLEAN, DEFAULT FALSE)
- created_at (TIMESTAMP NULL)
- updated_at (TIMESTAMP NULL)

INDEXES:
- PRIMARY KEY (id)
- INDEX (company_id)
- UNIQUE (company_id, invoice_number)
- INDEX (account_id)
- INDEX (cash_customer_id)
- INDEX (challan_id)
- INDEX (invoice_status)
- INDEX (payment_status)
- INDEX (invoice_date)
- INDEX (due_date)

FOREIGN KEYS:
- company_id REFERENCES companies(id) ON DELETE CASCADE
- account_id REFERENCES accounts(id) ON DELETE RESTRICT
- cash_customer_id REFERENCES cash_customers(id) ON DELETE RESTRICT
- challan_id REFERENCES challans(id) ON DELETE RESTRICT
- created_by REFERENCES users(id) ON DELETE RESTRICT

CONSTRAINTS:
- CHECK: (customer_type = 'Account' AND account_id IS NOT NULL AND cash_customer_id IS NULL)
        OR (customer_type = 'Cash' AND cash_customer_id IS NOT NULL AND account_id IS NULL)
- CHECK: amount_paid >= 0
- CHECK: amount_due >= 0
- CHECK: grand_total >= 0
- CHECK: (tax_type = 'CGST_SGST' AND cgst_amount > 0 AND sgst_amount > 0 AND igst_amount = 0)
        OR (tax_type = 'IGST' AND igst_amount > 0 AND cgst_amount = 0 AND sgst_amount = 0)

METHODS REQUIRED:
- up(): Create table with all columns, ENUMs, indexes, constraints
- down(): Drop table

BUSINESS LOGIC NOTES:
- Tax type determined by comparing company state with customer state
- Same state: CGST+SGST (split equally, e.g., 3% total = 1.5% CGST + 1.5% SGST)
- Different state: IGST (full rate, e.g., 3% IGST)
- amount_due updates when payments received

DELIVERABLES:
Complete migration file

ACCEPTANCE CRITERIA:
- Migration runs successfully
- Customer type constraint enforced
- Tax type constraint enforced
- Unique invoice number per company
- All foreign keys working
```

---

### Subtask 4.1.2: Create invoice_lines Migration

```
read .antigravity content and then

TASK: Generate migration file for invoice_lines table

FILE: app/Database/Migrations/2026-01-01-000015_create_invoice_lines_table.php

CONTEXT:
- Invoice lines are items in an invoice
- Can reference challan lines (if invoice from challan)
- Or standalone items (for direct invoices)
- Similar structure to challan lines but includes pricing

REQUIREMENTS:
Create CodeIgniter 4 migration with:

TABLE STRUCTURE:
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- invoice_id (INT, FK to invoices.id, NOT NULL)
- line_number (INT, NOT NULL)

// Reference to challan line (optional)
- challan_line_id (INT, FK to challan_lines.id, NULL)

// Products and Processes (JSON)
- products_json (JSON, NOT NULL)
- product_name (varchar(255), NULL)
- processes_json (JSON, NOT NULL)

// Quantities
- quantity (INT, DEFAULT 1)
- weight (FLOAT, DEFAULT 0.000)
- gold_weight_grams (DECIMAL 10,3, DEFAULT 0.000)
- gold_purity (VARCHAR 10, DEFAULT '22K')
- original_gold_weight (DECIMAL 10,3, DEFAULT 0.000)
- adjusted_gold_weight (DECIMAL 10,3, DEFAULT 0.000)
- gold_adjustment_amount (DECIMAL 15,2, DEFAULT 0.00)

// Amounts
- unit_price (DECIMAL 15,2, DEFAULT 0.00)
- line_subtotal (DECIMAL 15,2, NOT NULL)
- line_tax_amount (DECIMAL 15,2, DEFAULT 0.00)
- line_total (DECIMAL 15,2, NOT NULL)

// Description
- description (TEXT, NULL)
- hsn_code (VARCHAR 20, NULL)

// Metadata
- is_deleted (BOOLEAN, DEFAULT FALSE)
- created_at (TIMESTAMP NULL)
- updated_at (TIMESTAMP NULL)

INDEXES:
- PRIMARY KEY (id)
- INDEX (invoice_id)
- INDEX (challan_line_id)
- INDEX (line_number)

FOREIGN KEYS:
- invoice_id REFERENCES invoices(id) ON DELETE CASCADE
- challan_line_id REFERENCES challan_lines(id) ON DELETE RESTRICT

CONSTRAINTS:
- CHECK: gold_weight_grams >= 0
- CHECK: line_subtotal >= 0

METHODS:
- up(): Create table
- down(): Drop table

DELIVERABLES:
Complete migration file

ACCEPTANCE CRITERIA:
- JSON columns supported
- Foreign keys working
- Line ordering maintained
```

---

### Subtask 4.1.3: Create InvoiceModel

```
read .antigravity content and then

TASK: Generate InvoiceModel with payment tracking

FILE: app/Models/InvoiceModel.php

CONTEXT:
- Invoices track customer billing
- Payment status updates automatically
- Multi-tenant auto-filter
- Cannot delete paid invoices
- Relationships: lines, customer, challan, payments

REQUIREMENTS:
Create CodeIgniter 4 Model with:

PROPERTIES:
- protected $table = 'invoices';
- protected $primaryKey = 'id';
- protected $useTimestamps = true;
- protected $allowedFields = [all invoice fields except id, created_at, updated_at]
- protected $validationRules = [validation rules for required fields]

METHODS REQUIRED:

1. public function findAll(int $limit = 0, int $offset = 0)
   - Override with company filter
   - where('is_deleted', FALSE)

2. public function getInvoiceWithCustomer(int $id): ?array
   - Join customer table (account or cash based on customer_type)
   - Return invoice with customer data

3. public function getInvoiceWithLines(int $id): ?array
   - Get invoice with customer
   - Get all invoice_lines
   - Return complete invoice data

4. public function updatePaymentStatus(int $invoiceId, float $amountPaid): bool
   - Update amount_paid
   - Calculate amount_due = grand_total - amount_paid
   - Update payment_status:
     - If amount_due = 0: 'Paid'
     - Else if amount_paid > 0: 'Partially Paid'
     - Else: 'Unpaid'
   - If fully paid: update invoice_status = 'Paid'
   - Return success

5. public function getOutstandingInvoices(int $customerId = null, string $customerType = null): array
   - where('amount_due >', 0)
   - where('payment_status !=', 'Paid')
   - Apply customer filter if provided
   - Order by due_date ASC

6. public function canDelete(int $invoiceId): bool
   - Check amount_paid = 0
   - Return TRUE if no payments, FALSE otherwise

7. public function markAsDelivered(int $invoiceId): bool
   - Update invoice_status = 'Delivered'
   - Update delivery_status = 'Delivered'

DELIVERABLES:
Complete InvoiceModel.php

ACCEPTANCE CRITERIA:
- Model auto-filters by company
- Payment status updates correctly
- Outstanding invoices query works
- Cannot delete paid invoices
```

---

### Subtask 4.1.4: Create InvoiceLineModel

```
read .antigravity content and then

TASK: Generate InvoiceLineModel with JSON handling

FILE: app/Models/InvoiceLineModel.php

CONTEXT:
- Invoice lines with JSON for products/processes
- Similar to ChallanLineModel
- Links to challan lines if applicable

REQUIREMENTS:
Create CodeIgniter 4 Model with:

PROPERTIES:
- protected $table = 'invoice_lines';
- protected $casts = ['products_json' => 'json', 'processes_json' => 'json'];
- Standard validation rules

METHODS REQUIRED:

1. public function getLinesByInvoiceId(int $invoiceId): array
   - where('invoice_id', $invoiceId)
   - where('is_deleted', FALSE)
   - orderBy('line_number')

2. public function getTotalsForInvoice(int $invoiceId): array
   - SUM(line_subtotal, line_tax_amount, line_total, gold_weight_grams)
   - Return totals array

3. public function getNextLineNumber(int $invoiceId): int
   - MAX(line_number) + 1

DELIVERABLES:
Complete InvoiceLineModel.php

ACCEPTANCE CRITERIA:
- JSON auto-casting works
- Totals calculation accurate
- Line ordering maintained
```

---

## 🎯 TASK 4.2: INVOICE SERVICES

### Subtask 4.2.2: Create InvoiceService

```
read .antigravity content and then

TASK: Generate InvoiceService with complete business logic

FILE: app/Services/Invoice/InvoiceService.php

CONTEXT:
- Handle invoice CRUD operations
- Generate invoices from challans OR standalone
- Tax calculation (CGST/SGST or IGST)
- Payment tracking integration
- Ledger entry creation
- Sequential invoice numbering

REQUIREMENTS:
Create InvoiceService class with:

DEPENDENCIES:
- InvoiceModel
- InvoiceLineModel
- ChallanModel (for challan-to-invoice conversion)
- AccountModel, CashCustomerModel
- TaxCalculationService
- LedgerService
- NumberingService
- AuditService

METHODS REQUIRED:

1. public function createInvoice(array $data, array $lines = []): int
   - Validate required fields
   - Auto-set company_id, created_by
   - Generate invoice_number via NumberingService
   - Validate customer exists
   - Start transaction
   - Calculate tax via TaxCalculationService
   - Insert invoice record
   - Create invoice lines
   - Recalculate totals
   - Create ledger entry (debit for customer)
   - Commit transaction
   - Audit log
   - Return invoice ID

2. public function createInvoiceFromChallan(int $challanId): int
   - Validate challan exists and not already invoiced
   - Get challan with lines
   - Map challan data to invoice data
   - Copy challan lines to invoice lines
   - Create invoice
   - Mark challan as invoiced: ChallanModel->markAsInvoiced()
   - Return invoice ID

3. public function updateInvoice(int $id, array $data): bool
   - Validate invoice exists
   - Check if paid: if amount_paid > 0, throw exception
   - Update invoice record
   - Recalculate totals if lines changed
   - Update ledger entry
   - Audit log

4. public function deleteInvoice(int $id): bool
   - Check can delete: InvoiceModel->canDelete()
   - If FALSE: throw exception "Cannot delete paid invoice"
   - Soft delete invoice and lines
   - Delete ledger entry
   - If from challan: unmark challan as invoiced
   - Audit log

5. public function getInvoiceById(int $id): ?array
   - Get invoice with customer and lines
   - Return complete data

6. public function recordPayment(int $invoiceId, float $amount): bool
   - Validate invoice exists
   - Update amount_paid
   - Update payment_status
   - Create payment ledger entry (credit)
   - If fully paid: mark as paid
   - Return success

7. public function getOutstandingAmount(int $invoiceId): float
   - Get invoice
   - Return amount_due

8. public function recalculateTotals(int $invoiceId): bool
   - Get line totals
   - Update invoice totals
   - Return success

9. private function validateInvoiceData(array $data): void
   - Check required fields
   - Validate dates
   - Validate customer type
   - Throw ValidationException if invalid

ERROR HANDLING:
- InvoiceNotFoundException
- InvoiceAlreadyPaidException
- ChallanAlreadyInvoicedException
- ValidationException
- Transaction rollback on error

DELIVERABLES:
Complete InvoiceService.php

ACCEPTANCE CRITERIA:
- CRUD operations working
- Challan-to-invoice conversion works
- Tax calculation correct
- Payment tracking updates
- Ledger entries created
- All actions audit logged
- Transaction safety
```

---

## 🎯 TASK 4.3: TAX CALCULATION

### Subtask 4.3.1: Create TaxCalculationService

```
read .antigravity content and then

TASK: Generate TaxCalculationService for GST calculations

FILE: app/Services/Invoice/TaxCalculationService.php

CONTEXT:
- Calculate CGST+SGST or IGST based on state comparison
- Handle both invoice and challan tax calculations
- Retrieve tax rates from company settings
- Support item-level and invoice-level tax

REQUIREMENTS:
Create TaxCalculationService class with:

DEPENDENCIES:
- CompanyModel (get tax rate)
- StateModel (compare states)
- AccountModel, CashCustomerModel (get customer state)

METHODS REQUIRED:

1. public function calculateInvoiceTax(array $invoiceData): array
   - Get company state
   - Get customer state (from account or cash customer)
   - Determine tax_type: same state = CGST_SGST, different = IGST
   - Get company tax_rate (e.g., 3%)
   - Calculate subtotal (sum of line subtotals)
   - If CGST_SGST:
     - cgst_rate = tax_rate / 2 (e.g., 1.5%)
     - sgst_rate = tax_rate / 2
     - cgst_amount = subtotal × cgst_rate / 100
     - sgst_amount = subtotal × sgst_rate / 100
     - igst_rate = 0, igst_amount = 0
   - If IGST:
     - igst_rate = tax_rate (e.g., 3%)
     - igst_amount = subtotal × igst_rate / 100
     - cgst/sgst = 0
   - total_tax_amount = cgst + sgst OR igst
   - grand_total = subtotal + total_tax
   - Return: ['tax_type' => ..., 'cgst_rate' => ..., 'cgst_amount' => ...,
              'sgst_rate' => ..., 'sgst_amount' => ..., 'igst_rate' => ...,
              'igst_amount' => ..., 'total_tax_amount' => ..., 'grand_total' => ...]

2. public function calculateLineTax(float $lineSubtotal, string $taxType, float $taxRate): array
   - Calculate line-level tax
   - Similar logic as invoice tax
   - Return line tax details

3. public function determineTaxType(int $companyId, int $customerId, string $customerType): string
   - Get company state_id
   - Get customer state_id (from account or cash_customer)
   - If same state: return 'CGST_SGST'
   - If different: return 'IGST'

4. public function getTaxRate(): float
   - Get company tax_rate from settings
   - Return rate (e.g., 3.00)

5. public function validateTaxCalculation(array $taxData): bool
   - Validate tax amounts add up correctly
   - Validate only CGST+SGST OR IGST is non-zero
   - Throw TaxCalculationException if invalid

CALCULATION EXAMPLE:
```

Subtotal: ₹10,000
Tax Rate: 3%
Company State: Gujarat (24)
Customer State: Gujarat (24) → CGST+SGST

CGST Rate: 1.5%
CGST Amount: 10,000 × 1.5% = ₹150
SGST Rate: 1.5%
SGST Amount: 10,000 × 1.5% = ₹150
Total Tax: ₹300
Grand Total: ₹10,300

If Customer State: Maharashtra (27) → IGST
IGST Rate: 3%
IGST Amount: 10,000 × 3% = ₹300
Grand Total: ₹10,300

```

ERROR HANDLING:
- TaxCalculationException if calculation fails
- StateNotFoundException if state not found

DELIVERABLES:
Complete TaxCalculationService.php

ACCEPTANCE CRITERIA:
- Tax type determination correct
- CGST+SGST split correctly (equal halves)
- IGST calculation correct
- Totals accurate
- Service is unit-testable
```

---

### Subtask 4.3.2: Create InvoiceCalculationService

```
read .antigravity content and then

TASK: Generate InvoiceCalculationService for amount calculations

FILE: app/Services/Invoice/InvoiceCalculationService.php

CONTEXT:
- Calculate invoice amounts from lines
- Integrate with tax calculation
- Handle discounts (future)
- Calculate payment balance

REQUIREMENTS:
Create InvoiceCalculationService class with:

DEPENDENCIES:
- TaxCalculationService
- InvoiceLineModel

METHODS REQUIRED:

1. public function calculateInvoiceTotals(int $invoiceId, array $invoiceData): array
   - Get all invoice lines
   - Calculate subtotal = SUM(line_subtotal)
   - Get tax data via TaxCalculationService
   - Calculate grand_total
   - Calculate amount_due = grand_total - amount_paid
   - Return totals array

2. public function calculateLineTotal(array $lineData, string $taxType, float $taxRate): array
   - Calculate line_subtotal from processes
   - Calculate line_tax_amount
   - Calculate line_total = line_subtotal + line_tax
   - Return line totals

3. public function recalculateAmountDue(int $invoiceId, float $newPayment): float
   - Get current amount_due
   - Subtract new payment
   - Return new amount_due

DELIVERABLES:
Complete InvoiceCalculationService.php

ACCEPTANCE CRITERIA:
- Calculations accurate
- Tax integration works
- Amount due updates correctly
```

---

## 🎯 TASK 4.4: INVOICE CONTROLLERS

### Subtask 4.4.2: Create InvoiceController

```
read .antigravity content and then

TASK: Generate InvoiceController with all HTTP methods

FILE: app/Controllers/Invoices/InvoiceController.php

CONTEXT:
- Handle invoice management requests
- Support invoice creation from challan
- Support standalone invoice creation
- PDF generation for printing
- Permission checks

REQUIREMENTS:
Create CodeIgniter 4 Controller with:

DEPENDENCIES:
- InvoiceService
- ChallanService (for conversion)
- TaxCalculationService
- AccountModel, CashCustomerModel
- PermissionService

ROUTES:
- GET /invoices → index()
- GET /invoices/create → create()
- GET /invoices/create-from-challan/{challanId} → createFromChallan()
- POST /invoices → store()
- POST /invoices/from-challan → storeFromChallan()
- GET /invoices/{id} → show()
- GET /invoices/{id}/edit → edit()
- POST /invoices/{id} → update()
- DELETE /invoices/{id} → delete()
- GET /invoices/{id}/print → print()

METHODS REQUIRED:

1. public function index()
   - Check permission: invoice.view
   - Get filters: invoice_type, payment_status, customer_type, date_range
   - Load invoices
   - Return view or JSON

2. public function create()
   - Check permission: invoice.create
   - Load dropdowns (customers, products, processes)
   - Load view: create.php

3. public function createFromChallan(int $challanId)
   - Check permission: invoice.create
   - Get challan data
   - Pre-fill invoice form with challan data
   - Load view: create.php (with pre-filled data)

4. public function store()
   - Check permission: invoice.create
   - Validate data
   - Call InvoiceService->createInvoice()
   - Flash message success
   - Redirect to invoice show page

5. public function storeFromChallan()
   - Check permission: invoice.create
   - Get challan_id from POST
   - Call InvoiceService->createInvoiceFromChallan($challanId)
   - Flash message success
   - Redirect to invoice show page

6. public function show(int $id)
   - Check permission: invoice.view
   - Load invoice with lines
   - Show payment history
   - Load view: show.php

7. public function edit(int $id)
   - Check permission: invoice.edit
   - Check if paid: if yes, show error
   - Load invoice
   - Load view: edit.php

8. public function update(int $id)
   - Check permission: invoice.edit
   - Validate data
   - Call InvoiceService->updateInvoice()
   - Flash message success
   - Redirect

9. public function delete(int $id)
   - Check permission: invoice.delete
   - Try InvoiceService->deleteInvoice()
   - Catch InvoiceAlreadyPaidException: error message
   - Return JSON success

10. public function print(int $id)
    - Check permission: invoice.view
    - Load invoice
    - Generate PDF using InvoicePDF library
    - Return PDF (download or inline)

ERROR HANDLING:
- Catch all exceptions
- JSON for AJAX, flash messages for web
- Log errors

DELIVERABLES:
Complete InvoiceController.php

ACCEPTANCE CRITERIA:
- All routes working
- Challan-to-invoice conversion works
- PDF generation works
- Cannot edit/delete paid invoices
- Permissions enforced
```

---

### Subtask 4.4.3: Additional Invoice Controllers (Account, Cash, Wax)

````
read .antigravity content and then
TASK: Create type-specific invoice controllers (optional, extend base)

FILES:
- app/Controllers/Invoices/AccountInvoiceController.php
- app/Controllers/Invoices/CashInvoiceController.php
- app/Controllers/Invoices/WaxInvoiceController.php

CONTEXT:
- These controllers extend InvoiceController
- Override methods to add type-specific logic
- E.g., AccountInvoiceController pre-filters by customer_type = 'Account'

REQUIREMENTS:
Each controller extends InvoiceController and:
- Override index() to filter by invoice_type
- Override create() to set default invoice_type
- All other methods inherited

EXAMPLE: AccountInvoiceController:
```php
class AccountInvoiceController extends InvoiceController {
    public function index() {
        // Set filter: invoice_type = 'Account'
        return parent::index();
    }
}
````

DELIVERABLES:
3 type-specific controllers (optional optimization)

ACCEPTANCE CRITERIA:

- Type filtering automatic
- Inherits all base functionality

```

---

## 🎯 TASK 4.5: INVOICE VIEWS

### Subtask 4.5.1: Create Invoice List View

```

read .antigravity content and then
TASK: Create invoice list/index view

FILE: app/Views/invoices/index.php

REQUIREMENTS:

PAGE STRUCTURE:

- Title: "Invoices"
- Breadcrumb: Home > Invoices

FILTERS:

- Invoice Type (All, Account, Cash, Wax)
- Payment Status (All, Unpaid, Partially Paid, Paid)
- Delivery Status (All, Not Delivered, Delivered)
- Customer Type (All, Account, Cash)
- Date Range (From, To)

ACTION BUTTONS:

- Create Invoice (dropdown: Account, Cash, Wax)
- Create from Challan

DATATABLE COLUMNS:

- Invoice Number (link to show)
- Date
- Type (badge)
- Customer Name
- Grand Total (₹)
- Amount Paid (₹)
- Amount Due (₹)
- Payment Status (badge: Unpaid=red, Partially Paid=yellow, Paid=green)
- Delivery Status
- Actions (View, Edit, Delete, Print)

JAVASCRIPT:

- DataTables with server-side processing
- Filters trigger AJAX reload
- Payment status badges color-coded

DELIVERABLES:
Complete index.php

ACCEPTANCE CRITERIA:

- Filters working
- Payment status visible
- Amount due highlighted
- Actions functional

```

---

### Subtask 4.5.2: Create Invoice Form View

```

read .antigravity content and then
TASK: Create invoice creation/edit form

FILE: app/Views/invoices/create.php

REQUIREMENTS:

PAGE STRUCTURE:

- Title: "Create Invoice"
- Breadcrumb: Home > Invoices > Create

FORM SECTIONS:

**1. Invoice Header:**

- Invoice Number (auto, read-only)
- Invoice Date (date picker, default today)
- Invoice Type (dropdown: Account, Cash, Wax)
- Customer Type (radio: Account/Cash)
- Customer (dropdown, changes based on type)
- Due Date (optional, for account invoices)
- Payment Terms (text)
- Notes

**2. Line Items:**

- Table with columns:
  - Line #
  - Products (multi-select)
  - Processes (multi-select)
  - Quantity
  - Unit Price
  - Gold Weight (grams)
  - Subtotal
  - Tax
  - Total
  - Actions (Remove)
- Add Line button

**3. Tax Details (calculated, read-only):**

- Tax Type (CGST+SGST or IGST)
- CGST Rate/Amount (if applicable)
- SGST Rate/Amount (if applicable)
- IGST Rate/Amount (if applicable)

**4. Totals:**

- Subtotal: ₹
- Total Tax: ₹
- **Grand Total: ₹** (bold)

JAVASCRIPT:

- Customer type toggle
- Line item add/remove
- Real-time amount calculation
- Tax type auto-determined by state comparison
- Form validation

DELIVERABLES:
Complete create.php

ACCEPTANCE CRITERIA:

- Tax calculation automatic
- Line items dynamic
- Totals update real-time
- Validation prevents incomplete submission

```

---

### Subtask 4.5.3: Create Invoice Details View & Routes

```

read .antigravity content and then
TASK: Create invoice details view and configure routes

FILE 1: app/Views/invoices/show.php

REQUIREMENTS:

- Display complete invoice details
- Show tax breakdown (CGST/SGST or IGST)
- Show payment history
- Show delivery status
- Action buttons: Edit, Delete, Print, Record Payment, Assign Delivery

FILE 2: app/Config/Routes.php

Add invoice routes:

```php
$routes->group('invoices', ['filter' => 'auth', 'filter' => 'permission:invoice'], function($routes) {
    $routes->get('/', 'Invoices\InvoiceController::index');
    $routes->get('create', 'Invoices\InvoiceController::create');
    $routes->get('create-from-challan/(:num)', 'Invoices\InvoiceController::createFromChallan/$1');
    $routes->post('/', 'Invoices\InvoiceController::store');
    $routes->post('from-challan', 'Invoices\InvoiceController::storeFromChallan');
    $routes->get('(:num)', 'Invoices\InvoiceController::show/$1');
    $routes->get('(:num)/edit', 'Invoices\InvoiceController::edit/$1');
    $routes->post('(:num)', 'Invoices\InvoiceController::update/$1');
    $routes->delete('(:num)', 'Invoices\InvoiceController::delete/$1');
    $routes->get('(:num)/print', 'Invoices\InvoiceController::print/$1');
});
```

FILE 3: Add to Sidebar

```html
<?php if (can('invoice.view')): ?>
<li class="nav-item">
  <a class="nav-link" href="<?= base_url('invoices') ?>">
    <i class="fas fa-file-invoice"></i> Invoices
  </a>
</li>
<?php endif; ?>
```

DELIVERABLES:

- show.php view
- Routes configured
- Sidebar updated

ACCEPTANCE CRITERIA:

- Invoice details displayed correctly
- Tax breakdown shown
- Payment history visible
- Routes working
- Sidebar menu active

```

---

**END OF TASK-04 COMPLETE**

```

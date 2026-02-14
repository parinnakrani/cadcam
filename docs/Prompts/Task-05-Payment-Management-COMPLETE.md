# AI CODING PROMPTS - TASK 05
## Payment Management

**Version:** 1.0  
**Phase:** 5 - Payment Management (Weeks 13-14)  
**Generated:** February 10, 2026

---

## 📋 SUBTASKS COVERED
- 5.1.1-5.1.3: Payment Database, Models, Services
- 5.2.2: Gold Adjustment Logic
- 5.3.2: Payment Controllers
- 5.4.1-5.4.2: Payment Views, Routes & Sidebar

---

## 🎯 TASK 5.1: PAYMENT DATABASE & MODELS

### Subtask 5.1.1: Create payments Migration

```
[PASTE .antigravity RULES FIRST]

TASK: Generate migration file for payments table

FILE: app/Database/Migrations/2026-01-01-000016_create_payments_table.php

CONTEXT:
- Payments track invoice payment collection
- Support multiple payment modes (Cash, Cheque, Bank Transfer, UPI, etc.)
- Gold adjustment feature: adjust amount based on gold rate change
- Payment can be partial or full
- Link to invoice for payment tracking

TABLE STRUCTURE:
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- company_id (INT, FK to companies.id, NOT NULL)
- payment_number (VARCHAR 50, NOT NULL) // Auto-generated PAY-0001
- payment_date (DATE, NOT NULL)
- invoice_id (INT, FK to invoices.id, NOT NULL)
- customer_type (ENUM('Account', 'Cash'), NOT NULL)
- account_id (INT, FK to accounts.id, NULL)
- cash_customer_id (INT, FK to cash_customers.id, NULL)

// Payment Amount
- original_invoice_amount (DECIMAL 15,2, NOT NULL) // Invoice amount at payment time
- gold_adjustment_applied (BOOLEAN, DEFAULT FALSE)
- gold_weight_adjusted (DECIMAL 10,3, DEFAULT 0.000) // Gold weight for adjustment
- old_gold_rate (DECIMAL 10,2, DEFAULT 0.00) // Rate at invoice time
- new_gold_rate (DECIMAL 10,2, DEFAULT 0.00) // Rate at payment time
- adjustment_amount (DECIMAL 15,2, DEFAULT 0.00) // Calculated adjustment
- final_payable_amount (DECIMAL 15,2, NOT NULL) // After adjustment
- amount_paid (DECIMAL 15,2, NOT NULL) // Actual payment received

// Payment Details
- payment_mode (ENUM('Cash', 'Cheque', 'Bank Transfer', 'UPI', 'Card', 'Other'), NOT NULL)
- transaction_reference (VARCHAR 100, NULL) // Cheque no, UPI ref, etc.
- bank_name (VARCHAR 100, NULL)
- transaction_date (DATE, NULL)

// Metadata
- notes (TEXT, NULL)
- created_by (INT, FK to users.id, NOT NULL)
- is_deleted (BOOLEAN, DEFAULT FALSE)
- created_at (TIMESTAMP NULL)
- updated_at (TIMESTAMP NULL)

INDEXES:
- PRIMARY KEY (id)
- INDEX (company_id)
- UNIQUE (company_id, payment_number)
- INDEX (invoice_id)
- INDEX (account_id)
- INDEX (cash_customer_id)
- INDEX (payment_date)

FOREIGN KEYS:
- company_id REFERENCES companies(id) ON DELETE CASCADE
- invoice_id REFERENCES invoices(id) ON DELETE RESTRICT
- account_id REFERENCES accounts(id) ON DELETE RESTRICT
- cash_customer_id REFERENCES cash_customers(id) ON DELETE RESTRICT
- created_by REFERENCES users(id) ON DELETE RESTRICT

CONSTRAINTS:
- CHECK: amount_paid > 0
- CHECK: final_payable_amount > 0

DELIVERABLES: Complete migration file

ACCEPTANCE CRITERIA:
- Migration runs successfully
- Payment numbering unique per company
- Gold adjustment fields captured
- Foreign keys working
```

---

### Subtask 5.1.2: Create PaymentModel

```
[PASTE .antigravity RULES FIRST]

TASK: Generate PaymentModel with validation

FILE: app/Models/PaymentModel.php

REQUIREMENTS:
Standard CodeIgniter 4 Model with:

PROPERTIES:
- protected $table = 'payments';
- protected $allowedFields = [all payment fields]
- protected $validationRules = [
    'payment_date' => 'required|valid_date',
    'invoice_id' => 'required|integer',
    'amount_paid' => 'required|decimal|greater_than[0]',
    'payment_mode' => 'required|in_list[Cash,Cheque,Bank Transfer,UPI,Card,Other]'
  ]

METHODS:

1. public function findAll(int $limit = 0, int $offset = 0)
   - Apply company filter
   - where('is_deleted', FALSE)

2. public function getPaymentsByInvoice(int $invoiceId): array
   - where('invoice_id', $invoiceId)
   - where('is_deleted', FALSE)
   - orderBy('payment_date', 'ASC')

3. public function getTotalPaidForInvoice(int $invoiceId): float
   - SUM(amount_paid)
   - where('invoice_id', $invoiceId)

4. public function getPaymentsByCustomer(int $customerId, string $customerType, $fromDate = null, $toDate = null): array
   - Filter by customer
   - Optional date range
   - Order by payment_date DESC

DELIVERABLES: Complete PaymentModel.php

ACCEPTANCE CRITERIA:
- Model auto-filters by company
- Payment mode validation works
- Sum calculations accurate
```

---

### Subtask 5.1.3: Create PaymentService

```
[PASTE .antigravity RULES FIRST]

TASK: Generate PaymentService with gold adjustment integration

FILE: app/Services/Payment/PaymentService.php

DEPENDENCIES:
- PaymentModel
- InvoiceModel
- GoldAdjustmentService
- LedgerService
- NumberingService
- AuditService

METHODS:

1. public function createPayment(array $data): int
   - Validate invoice exists and has outstanding amount
   - Auto-set company_id, created_by
   - Generate payment_number via NumberingService
   - If gold_adjustment_applied = TRUE:
     - Call GoldAdjustmentService->calculateAdjustment()
     - Update adjustment fields
   - Else:
     - final_payable_amount = original_invoice_amount
   - Validate amount_paid <= final_payable_amount
   - Start transaction
   - Insert payment record
   - Update invoice payment status: InvoiceModel->updatePaymentStatus()
   - Create payment ledger entry (credit customer account)
   - Commit transaction
   - Audit log
   - Return payment ID

2. public function getPaymentById(int $id): ?array
   - Get payment with invoice and customer data
   - Return complete payment details

3. public function getPaymentsByInvoice(int $invoiceId): array
   - Call PaymentModel->getPaymentsByInvoice()
   - Return payment history

4. public function deletePayment(int $id): bool
   - Validate payment exists
   - Check if only payment (cannot delete if multiple partial payments)
   - Start transaction
   - Soft delete payment
   - Update invoice payment status (subtract amount_paid)
   - Delete ledger entry
   - Commit transaction
   - Audit log
   - Return TRUE

5. public function getTotalPaidForInvoice(int $invoiceId): float
   - Call PaymentModel->getTotalPaidForInvoice()

ERROR HANDLING:
- PaymentNotFoundException
- InvoiceFullyPaidException (if amount_due = 0)
- InvalidPaymentAmountException (if amount > amount_due)
- Transaction rollback on error

DELIVERABLES: Complete PaymentService.php

ACCEPTANCE CRITERIA:
- Payment creation working
- Gold adjustment integration
- Invoice status updates
- Ledger entries created
- Cannot overpay invoice
```

---

## 🎯 TASK 5.2: GOLD ADJUSTMENT

### Subtask 5.2.2: Create GoldAdjustmentService

```
[PASTE .antigravity RULES FIRST]

TASK: Generate GoldAdjustmentService for gold rate adjustment calculations

FILE: app/Services/Payment/GoldAdjustmentService.php

CONTEXT:
- When customer pays after gold rate change, adjust invoice amount
- If gold rate increased: customer pays more
- If gold rate decreased: customer pays less
- Calculation: adjustment = gold_weight × (new_rate - old_rate)

DEPENDENCIES:
- GoldRateService (get current rate)
- InvoiceModel (get invoice gold weight)

METHODS:

1. public function calculateAdjustment(int $invoiceId, float $currentGoldRate): array
   - Get invoice data (must include total gold weight)
   - Get invoice gold rate (rate at invoice creation date)
   - Calculate rate_difference = currentGoldRate - invoiceGoldRate
   - Calculate adjustment_amount = gold_weight × rate_difference
   - Calculate final_payable = invoice_grand_total + adjustment_amount
   - Return: [
       'old_gold_rate' => ...,
       'new_gold_rate' => ...,
       'gold_weight_adjusted' => ...,
       'adjustment_amount' => ...,
       'final_payable_amount' => ...
     ]

2. public function getCurrentGoldRate(string $metalType = '22K'): float
   - Call GoldRateService->getLatestRate($metalType)
   - Return current rate

3. public function getInvoiceGoldRate(int $invoiceId): float
   - Get invoice_date from invoice
   - Call GoldRateService->getRateByDate($invoice_date, $metalType)
   - Return rate at invoice time

4. public function validateAdjustmentData(array $adjustmentData): bool
   - Check gold_weight > 0
   - Check rates are valid
   - Throw ValidationException if invalid

CALCULATION EXAMPLE:
```
Invoice Details:
- Invoice Date: 2026-01-01
- Gold Weight: 100 grams
- Gold Rate on 01-01: ₹6,000/gram
- Invoice Amount: ₹10,000 (including tax)

Payment Date: 2026-02-01
- Current Gold Rate: ₹6,200/gram

Calculation:
- Rate Difference: 6,200 - 6,000 = ₹200/gram
- Adjustment: 100 × 200 = ₹20,000
- Final Payable: 10,000 + 20,000 = ₹30,000

If rate decreased to ₹5,800:
- Rate Difference: 5,800 - 6,000 = -₹200/gram
- Adjustment: 100 × (-200) = -₹20,000
- Final Payable: 10,000 - 20,000 = CANNOT BE NEGATIVE (minimum = 0)
```

BUSINESS RULES:
- Adjustment can be positive (rate increased) or negative (rate decreased)
- Final payable amount cannot be negative (minimum = 0)
- Gold adjustment optional (user decides whether to apply)
- Adjustment recorded in payment record for audit

ERROR HANDLING:
- GoldRateNotFoundException if rate not available
- InvalidAdjustmentException if calculation fails

DELIVERABLES: Complete GoldAdjustmentService.php

ACCEPTANCE CRITERIA:
- Adjustment calculation accurate
- Handles rate increase and decrease
- Final amount never negative
- Service is unit-testable
```

---

## 🎯 TASK 5.3: PAYMENT CONTROLLERS

### Subtask 5.3.2: Create PaymentController

```
[PASTE .antigravity RULES FIRST]

TASK: Generate PaymentController

FILE: app/Controllers/Payments/PaymentController.php

DEPENDENCIES:
- PaymentService
- GoldAdjustmentService
- InvoiceModel (to select invoice for payment)
- PermissionService

ROUTES:
- GET /payments → index()
- GET /payments/create → create()
- POST /payments → store()
- GET /payments/{id} → show()
- DELETE /payments/{id} → delete()
- POST /payments/calculate-adjustment → calculateAdjustment() [API]

METHODS:

1. public function index()
   - Check permission: payment.view
   - Get filters: date_range, customer_type, payment_mode
   - Load payments
   - View: app/Views/payments/index.php

2. public function create()
   - Check permission: payment.create
   - Get query param: invoice_id (pre-select invoice)
   - Load outstanding invoices dropdown
   - Get current gold rate for adjustment preview
   - View: app/Views/payments/create.php

3. public function store()
   - Check permission: payment.create
   - Validate CSRF
   - Get POST data
   - Call PaymentService->createPayment($data)
   - Flash message: "Payment recorded successfully"
   - Redirect to /payments/{id}

4. public function show(int $id)
   - Check permission: payment.view
   - Load payment with invoice and customer data
   - View: app/Views/payments/show.php

5. public function delete(int $id)
   - Check permission: payment.delete
   - Try: PaymentService->deletePayment($id)
   - Catch exceptions: return JSON error
   - Flash message: "Payment deleted"
   - Return JSON success

6. public function calculateAdjustment()
   - Check permission: payment.create
   - Get POST data: invoice_id, current_gold_rate
   - Call GoldAdjustmentService->calculateAdjustment()
   - Return JSON: adjustment details for UI display

ERROR HANDLING:
- Catch all exceptions
- JSON for AJAX, flash for web

DELIVERABLES: Complete PaymentController.php

ACCEPTANCE CRITERIA:
- Payment recording works
- Gold adjustment calculation API works
- Cannot overpay invoice
- Permissions enforced
```

---

## 🎯 TASK 5.4: PAYMENT VIEWS & ROUTES

### Subtask 5.4.1: Create Payment Views

```
TASK: Create payment views

FILES:
1. app/Views/payments/index.php
2. app/Views/payments/create.php
3. app/Views/payments/show.php

VIEW 1: index.php (Payment List)
- Title: "Payments"
- Filters: Date Range, Customer Type, Payment Mode
- DataTable columns:
  - Payment Number
  - Date
  - Invoice Number (link)
  - Customer Name
  - Payment Mode
  - Amount Paid (₹)
  - Gold Adjustment Applied (Yes/No badge)
  - Actions (View, Delete)

VIEW 2: create.php (Record Payment Form)
- Title: "Record Payment"
- Form fields:
  - Payment Date (default today)
  - Invoice (dropdown: outstanding invoices with amount due)
  - Payment Mode (dropdown)
  - Transaction Reference (for non-cash modes)
  - Bank Name (if applicable)

  **Gold Adjustment Section:**
  - Checkbox: "Apply Gold Adjustment"
  - If checked: show gold adjustment details
    - Invoice Gold Weight (read-only, from invoice)
    - Old Gold Rate (at invoice time, read-only)
    - New Gold Rate (current rate, editable)
    - Calculate Adjustment button (AJAX)
    - Adjustment Amount (calculated, display)
    - Final Payable Amount (calculated, display)

  - Amount Paid (input, validate <= final_payable)
  - Notes

- JavaScript:
  - Invoice selection: load invoice details, gold weight, amount due
  - Gold adjustment checkbox: show/hide adjustment section
  - Calculate Adjustment button: AJAX call to calculate
  - Validate amount_paid <= final_payable_amount

VIEW 3: show.php (Payment Details)
- Display payment information
- Show invoice details
- If gold adjustment applied: show adjustment breakdown
- Action buttons: Delete (if permitted)

DELIVERABLES: 3 complete payment view files

ACCEPTANCE CRITERIA:
- Invoice dropdown shows outstanding invoices
- Gold adjustment calculation works via AJAX
- Amount validation prevents overpayment
- Form submission successful
```

---

### Subtask 5.4.2: Add Payment Routes & Sidebar

```
TASK: Configure payment routes and sidebar

FILE 1: app/Config/Routes.php

Add payment routes:
```php
$routes->group('payments', ['filter' => 'auth', 'filter' => 'permission:payment'], function($routes) {
    $routes->get('/', 'Payments\PaymentController::index');
    $routes->get('create', 'Payments\PaymentController::create');
    $routes->post('/', 'Payments\PaymentController::store');
    $routes->get('(:num)', 'Payments\PaymentController::show/$1');
    $routes->delete('(:num)', 'Payments\PaymentController::delete/$1');
    $routes->post('calculate-adjustment', 'Payments\PaymentController::calculateAdjustment');
});
```

FILE 2: app/Views/layouts/sidebar.php

Add to sidebar:
```html
<?php if (can('payment.view')): ?>
<li class="nav-item">
    <a class="nav-link" href="<?= base_url('payments') ?>">
        <i class="fas fa-money-bill-wave"></i> Payments
    </a>
</li>
<?php endif; ?>
```

DELIVERABLES:
- Routes configured
- Sidebar updated

ACCEPTANCE CRITERIA:
- All routes working
- Sidebar menu visible based on permission
```

---

**END OF TASK-05 COMPLETE**

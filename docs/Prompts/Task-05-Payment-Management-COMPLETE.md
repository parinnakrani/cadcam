# AI CODING PROMPTS - TASK 05

## Payment Management

**Version:** 1.1 (Revised)
**Phase:** 5 - Payment Management (Weeks 13-14)
**Generated:** February 14, 2026

---

## 📋 SUBTASKS COVERED

- 5.1.1-5.1.3: Payment Database, Models, Services
- 5.3.2: Payment Controllers
- 5.4.1-5.4.2: Payment Views, Routes & Sidebar

---

## 🎯 TASK 5.1: PAYMENT DATABASE & MODELS

### Subtask 5.1.1: Create payments Migration

```
Read .antigravity content and then

TASK: Generate migration file for payments table

FILE: app/Database/Migrations/2026-01-01-000016_create_payments_table.php

CONTEXT:
- Payments track invoice payment collection
- Support multiple payment modes (Cash, Cheque, Bank Transfer, UPI, etc.)
- Payment can be partial or full
- Link to invoice for payment tracking

TABLE STRUCTURE:
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- company_id (INT, FK to companies.id, NOT NULL)
- payment_number (VARCHAR 50, NOT NULL) // Auto-generated PAY-0001
- invoice_id (INT, FK to invoices.id, NOT NULL)
- customer_type (ENUM('Account', 'Cash'), NOT NULL)
- account_id (INT, FK to accounts.id, NULL)
- cash_customer_id (INT, FK to cash_customers.id, NULL)
- payment_date (DATE, NOT NULL)
- payment_amount (DECIMAL 15,2, NOT NULL)
- payment_mode (ENUM('Cash', 'Cheque', 'Bank Transfer', 'UPI', 'Card', 'Other'), NOT NULL)

// Instrument Details
- cheque_number (VARCHAR 50, NULL)
- cheque_date (DATE, NULL)
- bank_name (VARCHAR 100, NULL)
- transaction_reference (VARCHAR 100, NULL) // UPI ref, etc.

// Metadata
- notes (TEXT, NULL)
- received_by (INT, FK to users.id, NOT NULL)
- created_at (TIMESTAMP NULL)
- updated_at (TIMESTAMP NULL)
- is_deleted (BOOLEAN, DEFAULT FALSE)

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
- received_by REFERENCES users(id) ON DELETE RESTRICT

CONSTRAINTS:
- CHECK: payment_amount > 0

DELIVERABLES: Complete migration file

ACCEPTANCE CRITERIA:
- Migration runs successfully
- Payment numbering unique per company
- Foreign keys working
```

---

### Subtask 5.1.2: Create PaymentModel

```
Read .antigravity content and then

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
    'payment_amount' => 'required|decimal|greater_than[0]',
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
   - SUM(payment_amount)
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
Read .antigravity content and then

TASK: Generate PaymentService

FILE: app/Services/Payment/PaymentService.php

DEPENDENCIES:
- PaymentModel
- InvoiceModel
- LedgerService
- NumberingService
- AuditService

METHODS:

1. public function createPayment(array $data): int
   - Validate invoice exists and has outstanding amount
   - Auto-set company_id, received_by (from current user)
   - Generate payment_number via NumberingService
   - Validate payment_amount <= amount_due
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
   - Check if only payment (usually last payment first, but flexibility permitted if needed)
   - Start transaction
   - Soft delete payment
   - Update invoice payment status (subtract payment_amount)
   - Delete related ledger entry
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
- Invoice status updates
- Ledger entries created
- Cannot overpay invoice
```

---

## 🎯 TASK 5.3: PAYMENT CONTROLLERS

### Subtask 5.3.2: Create PaymentController

```
Read .antigravity content and then

TASK: Generate PaymentController

FILE: app/Controllers/Payments/PaymentController.php

DEPENDENCIES:
- PaymentService
- InvoiceModel (to select invoice for payment)
- PermissionService

ROUTES:
- GET /payments → index()
- GET /payments/create → create()
- POST /payments → store()
- GET /payments/{id} → show()
- DELETE /payments/{id} → delete()

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

ERROR HANDLING:
- Catch all exceptions
- JSON for AJAX, flash for web

DELIVERABLES: Complete PaymentController.php

ACCEPTANCE CRITERIA:
- Payment recording works
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
  - Actions (View, Delete)

VIEW 2: create.php (Record Payment Form)
- Title: "Record Payment"
- Form fields:
  - Payment Date (default today)
  - Invoice (dropdown: outstanding invoices with amount due)
  - Payment Mode (dropdown)
  - Payment Amount (input, validate <= amount_due)

  **Conditional Fields (based on Mode):**
  - If Cheque: Cheque Number, Cheque Date, Bank Name
  - If Bank Transfer/UPI: Transaction Reference

  - Notes

- JavaScript:
  - Invoice selection: load invoice details, amount due
  - Payment Mode change: show/hide specific fields (Cheque/Bank details)
  - Validate payment_amount <= amount_due

VIEW 3: show.php (Payment Details)
- Display payment information
- Show invoice details
- Show Payment Mode specific details (e.g. Cheque No if Cheque)
- Action buttons: Delete (if permitted)

DELIVERABLES: 3 complete payment view files

ACCEPTANCE CRITERIA:
- Invoice dropdown shows outstanding invoices
- Amount validation prevents overpayment
- Mode specific fields toggle correctly
- Form submission successful
```

---

### Subtask 5.4.2: Add Payment Routes & Sidebar

````
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
});
````

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
```

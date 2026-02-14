# AI CODING PROMPTS - TASK 07
## Ledger Management

**Version:** 1.0  
**Phase:** 7 - Ledger & Financial (Weeks 17-18)  
**Generated:** February 10, 2026

---

## SUBTASKS: 7.1.1-7.1.4 (Ledger Entry System), 7.2.1-7.2.2 (Balance Calculation)

---

## 🎯 TASK 7.1: LEDGER ENTRY SYSTEM

### Subtask 7.1.1: Create ledger_entries Migration

```
[PASTE .antigravity RULES FIRST]

FILE: app/Database/Migrations/2026-01-01-000017_create_ledger_entries_table.php

CONTEXT:
- Double-entry bookkeeping system
- Every financial transaction creates ledger entry
- Debit = customer owes us (invoice, opening balance debit)
- Credit = we owe customer OR customer paid us (payment, opening balance credit)

TABLE STRUCTURE:
- id, company_id
- entry_date (DATE, NOT NULL)
- entry_type (ENUM('Opening Balance', 'Invoice', 'Payment', 'Adjustment'), NOT NULL)
- customer_type (ENUM('Account', 'Cash'))
- account_id (INT, FK to accounts.id, NULL)
- cash_customer_id (INT, FK to cash_customers.id, NULL)

// Reference to source transaction
- reference_type (ENUM('Invoice', 'Payment', 'Opening Balance'), NOT NULL)
- reference_id (INT, NOT NULL) // invoice_id or payment_id
- reference_number (VARCHAR 50) // INV-001, PAY-001

// Amounts
- debit_amount (DECIMAL 15,2, DEFAULT 0.00)
- credit_amount (DECIMAL 15,2, DEFAULT 0.00)
- balance (DECIMAL 15,2, DEFAULT 0.00) // Running balance after this entry

// Description
- description (VARCHAR 255, NOT NULL)
- notes (TEXT, NULL)

// Metadata
- created_by, is_deleted, created_at, updated_at

INDEXES: company_id, account_id, cash_customer_id, entry_date, entry_type

CONSTRAINTS:
- CHECK: (debit_amount > 0 AND credit_amount = 0) OR (credit_amount > 0 AND debit_amount = 0)
  // Either debit OR credit, not both

DELIVERABLES: Complete migration

ACCEPTANCE CRITERIA: Migration runs, constraint enforces debit XOR credit
```

---

### Subtask 7.1.2: Create LedgerEntryModel

```
[PASTE .antigravity RULES FIRST]

FILE: app/Models/LedgerEntryModel.php

METHODS:
1. findAll() - with company filter
2. getLedgerForAccount(int $accountId, $fromDate, $toDate): array
3. getLedgerForCashCustomer(int $cashCustomerId, $fromDate, $toDate): array
4. getOpeningBalance(int $customerId, string $customerType, $beforeDate): float
   - SUM(debit - credit) before date
5. getCurrentBalance(int $customerId, string $customerType): float
   - SUM(debit - credit) all time

DELIVERABLES: Complete LedgerEntryModel.php

ACCEPTANCE CRITERIA: Ledger queries working, balance calculations accurate
```

---

### Subtask 7.1.3: Create LedgerService

```
[PASTE .antigravity RULES FIRST]

FILE: app/Services/Ledger/LedgerService.php

CONTEXT:
- Central service to create ledger entries
- Called by InvoiceService, PaymentService, AccountService
- Maintains running balance

DEPENDENCIES: LedgerEntryModel, AccountModel, CashCustomerModel

METHODS:

1. public function createInvoiceLedgerEntry(int $invoiceId, array $invoiceData): int
   - entry_type = 'Invoice'
   - reference_type = 'Invoice', reference_id = invoiceId
   - debit_amount = invoice grand_total (customer owes us)
   - credit_amount = 0
   - Calculate new balance
   - Insert ledger entry
   - Update account/customer current_balance
   - Return entry ID

2. public function createPaymentLedgerEntry(int $paymentId, array $paymentData): int
   - entry_type = 'Payment'
   - reference_type = 'Payment', reference_id = paymentId
   - debit_amount = 0
   - credit_amount = payment amount_paid (customer paid us)
   - Calculate new balance
   - Insert ledger entry
   - Update account/customer current_balance

3. public function createOpeningBalanceLedgerEntry(int $customerId, string $customerType, float $amount, string $type): int
   - entry_type = 'Opening Balance'
   - If type = 'Debit': debit_amount = amount (customer owes)
   - If type = 'Credit': credit_amount = amount (we owe customer)
   - Insert ledger entry

4. public function getAccountBalance(int $accountId): float
   - Call LedgerEntryModel->getCurrentBalance()

5. public function calculateRunningBalance(int $customerId, string $customerType, $upToDate = null): float
   - Get all ledger entries
   - Calculate: SUM(debit) - SUM(credit)
   - Positive = customer owes us
   - Negative = we owe customer

BUSINESS RULE:
- Running Balance = Previous Balance + Debit - Credit
- Example:
  - Opening Balance: ₹1000 Dr (customer owes ₹1000)
  - Invoice ₹500: Balance = 1000 + 500 = ₹1500 Dr
  - Payment ₹300: Balance = 1500 - 300 = ₹1200 Dr
  - Payment ₹1200: Balance = 1200 - 1200 = ₹0

DELIVERABLES: Complete LedgerService.php

ACCEPTANCE CRITERIA: Ledger entries created correctly, balance calculations accurate
```

---

### Subtask 7.1.4: Create LedgerController (View Only)

```
[PASTE .antigravity RULES FIRST]

FILE: app/Controllers/Ledgers/LedgerController.php

ROUTES:
- GET /ledgers/accounts → accountsLedger()
- GET /ledgers/cash-customers → cashCustomersLedger()
- GET /ledgers/account/{id} → accountLedger()
- GET /ledgers/cash-customer/{id} → cashCustomerLedger()

METHODS:
1. accountsLedger() - list all accounts with current balance
2. cashCustomersLedger() - list all cash customers with current balance
3. accountLedger($id) - detailed ledger for account (with date filter)
4. cashCustomerLedger($id) - detailed ledger for cash customer

VIEWS:
- app/Views/ledgers/accounts_list.php
- app/Views/ledgers/cash_customers_list.php
- app/Views/ledgers/account_ledger.php
- app/Views/ledgers/cash_customer_ledger.php

DELIVERABLES: Controller, 4 views, routes

ACCEPTANCE CRITERIA: Ledger views display correctly, balances accurate
```

---

## 🎯 TASK 7.2: BALANCE CALCULATION

### Subtask 7.2.1: Balance Calculation Methods

```
[PASTE .antigravity RULES FIRST]

TASK: Add balance calculation methods to LedgerService

Methods already defined in 7.1.3, ensure:
- getAccountBalance(int $accountId): float
- getCashCustomerBalance(int $customerId): float
- calculateRunningBalance() - with date range support

ADDITIONAL METHOD:
public function recalculateAllBalances(): void
   - For all accounts:
     - Calculate balance from ledger entries
     - Update accounts.current_balance
   - For all cash customers:
     - Calculate balance from ledger entries
     - Update cash_customers.current_balance (if column exists)
   - Use for data migration or balance correction

DELIVERABLES: Balance calculation methods

ACCEPTANCE CRITERIA: Balance calculations match ledger totals
```

---

### Subtask 7.2.2: Add Ledger to Sidebar & Routes

```
FILE 1: app/Config/Routes.php

```php
$routes->group('ledgers', ['filter' => 'auth', 'filter' => 'permission:ledger'], function($routes) {
    $routes->get('accounts', 'Ledgers\LedgerController::accountsLedger');
    $routes->get('cash-customers', 'Ledgers\LedgerController::cashCustomersLedger');
    $routes->get('account/(:num)', 'Ledgers\LedgerController::accountLedger/$1');
    $routes->get('cash-customer/(:num)', 'Ledgers\LedgerController::cashCustomerLedger/$1');
});
```

FILE 2: Sidebar

```html
<?php if (can('ledger.view')): ?>
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="ledgerDropdown" role="button" data-bs-toggle="dropdown">
        <i class="fas fa-book"></i> Ledger
    </a>
    <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="<?= base_url('ledgers/accounts') ?>">Accounts Ledger</a></li>
        <li><a class="dropdown-item" href="<?= base_url('ledgers/cash-customers') ?>">Cash Customers Ledger</a></li>
    </ul>
</li>
<?php endif; ?>
```

DELIVERABLES: Routes and sidebar

ACCEPTANCE CRITERIA: Ledger menu accessible, routes working
```

---

**END OF TASK-07 COMPLETE**

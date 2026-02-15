# AI CODING PROMPTS - TASK 07

## Ledger Management & Financial Reports

**Version:** 2.0
**Phase:** 7 - Ledger & Financial (Weeks 17-18)
**Generated:** February 15, 2026

---

## SUBTASKS: 7.1.1-7.1.4 (Ledger Core), 7.2.1-7.2.2 (Balance Logic), 7.3.1-7.3.3 (Advanced Reports), 7.4.1-7.4.2 (Dashboard & Reminders)

---

## 🎯 TASK 7.1: LEDGER ENTRY SYSTEM

### Subtask 7.1.1: Verify/Update ledger_entries Migration

```
read .antigravity content and then

FILE: app/Database/Migrations/2026-01-01-000017_create_ledger_entries_table.php

CONTEXT:
- The table `ledger_entries` might already exist.
- We need to ensure it matches the required structure for the reporting module.
- Double-entry bookkeeping system:
  - Debit = customer owes us (invoice, opening balance debit).
  - Credit = we owe customer OR customer paid us (payment, opening balance credit).

TABLE STRUCTURE (Must match existing schema):
- id (INT UNSIGNED, AI, PK)
- company_id (INT UNSIGNED)
- account_id (INT UNSIGNED, NULL)
- cash_customer_id (INT UNSIGNED, NULL)
- entry_date (DATE, NOT NULL)
- reference_type (ENUM('opening_balance','invoice','payment','gold_adjustment'), NOT NULL)
- reference_id (INT UNSIGNED, NULL)
- reference_number (VARCHAR 100, NULL)
- description (TEXT, NULL)
- debit_amount (DECIMAL 15,2, DEFAULT 0.00)
- credit_amount (DECIMAL 15,2, DEFAULT 0.00)
- balance_after (DECIMAL 15,2, DEFAULT 0.00) // Running balance
- created_at (TIMESTAMP)

INDEXES: company_id, account_id, cash_customer_id, entry_date, reference_type

BUSINESS RULES:
- If table exists, ensure columns match above.
- One of account_id or cash_customer_id must be set.
- (debit_amount > 0 AND credit_amount = 0) OR (credit_amount > 0 AND debit_amount = 0).

DELIVERABLES: Migration file (create or alter if needed).
```

---

### Subtask 7.1.2: Create LedgerEntryModel

```
read .antigravity content and then

FILE: app/Models/LedgerEntryModel.php

METHODS:
1. findAll() - with company filter scope.
2. getLedgerForAccount(int $accountId, $fromDate, $toDate): array
   - Returns entries sorted by entry_date ASC, id ASC.
3. getLedgerForCashCustomer(int $cashCustomerId, $fromDate, $toDate): array
4. getOpeningBalance(int $customerId, string $customerType, $beforeDate): float
   - Calculate SUM(debit_amount) - SUM(credit_amount) for entries < $beforeDate.
   - customerType is 'Account' or 'Cash'.
5. getCurrentBalance(int $customerId, string $customerType): float
   - SUM(debit_amount) - SUM(credit_amount) for all entries.

DELIVERABLES: Complete LedgerEntryModel.php

ACCEPTANCE CRITERIA:
- Correctly handles both Accounts and Cash Customers.
- Balances calculated correctly via SUM query.
```

---

### Subtask 7.1.3: Create LedgerService

```
read .antigravity content and then

FILE: app/Services/Ledger/LedgerService.php

CONTEXT:
- Central service to create ledger entries.
- Called by InvoiceService, PaymentService, AccountService.

DEPENDENCIES: LedgerEntryModel, AccountModel, CashCustomerModel

METHODS:

1. public function createInvoiceLedgerEntry(int $invoiceId, array $invoiceData): int
   - reference_type = 'invoice'
   - debit_amount = invoice grand_total (customer owes us)
   - credit_amount = 0
   - Calculate new balance and insert entry.
   - Update account/cash_customer `current_balance`.

2. public function createPaymentLedgerEntry(int $paymentId, array $paymentData): int
   - reference_type = 'payment'
   - credit_amount = payment_amount (customer paid us)
   - debit_amount = 0
   - Calculate new balance and insert entry.
   - Update account/cash_customer `current_balance`.

3. public function createOpeningBalanceLedgerEntry(int $customerId, string $customerType, float $amount, string $type): int
   - reference_type = 'opening_balance'
   - If type = 'Debit': debit_amount = amount.
   - If type = 'Credit': credit_amount = amount.
   - Insert entry.

4. public function recalculateRunningBalance(int $customerId, string $customerType): void
   - Fetch all entries ordered by date.
   - Iterate and update `balance_after` for each row sequentially.
   - Update final balance in parent table (accounts/cash_customers).
   - *Use this method when inserting backdated entries or modifying entries.*

DELIVERABLES: Complete LedgerService.php
```

---

### Subtask 7.1.4: Create LedgerController (Basic Views)

```
read .antigravity content and then

FILE: app/Controllers/Ledgers/LedgerController.php

ROUTES:
- GET /ledgers/accounts → accountsLedger()
- GET /ledgers/cash-customers → cashCustomersLedger()
- GET /ledgers/account/{id} → accountLedger()
- GET /ledgers/cash-customer/{id} → cashCustomerLedger()

VIEWS:
- app/Views/ledgers/accounts_list.php (List of accounts with current balance)
- app/Views/ledgers/cash_customers_list.php (List of cash customers with balance)
- app/Views/ledgers/account_ledger.php (Detailed date-wise ledger)
- app/Views/ledgers/cash_customer_ledger.php

DELIVERABLES: Controller, 4 views, routes.
```

---

## 🎯 TASK 7.2: BALANCE CALCULATION & SYNC

### Subtask 7.2.1: Balance Sync Command

```
read .antigravity content and then

TASK: Create a Spark command to recalculate all balances.

FILE: app/Commands/RecalculateBalances.php

LOGIC:
- Iterate all Accounts.
- Call LedgerService::recalculateRunningBalance().
- Iterate all Cash Customers.
- Call LedgerService::recalculateRunningBalance().
- Output progress bar.

DELIVERABLES: Spark command.
```

---

## 🎯 TASK 7.3: ADVANCED REPORTING

### Subtask 7.3.1: Monthly Receivable Summary Report

```
read .antigravity content and then

CONTEXT:
- Generate month-wise receivable summary for all customers.

FILE: app/Controllers/Reports/ReceivableReportController.php
VIEW: app/Views/reports/monthly_receivable.php

INPUTS:
- Date Range (Start Month to End Month).

LOGIC:
- Fetch all customers (Account + Cash).
- For each customer:
  - Calculate Opening Balance (before start date).
  - For each month in range:
    - Sum Debits (Invoices).
    - Sum Credits (Payments).
    - Calculate Month Closing Balance.
  - Final Closing Balance.

REPORT COLUMNS:
- Customer Name
- Mobile
- Opening Balance
- Month 1 (Dr / Cr / Bal)
- Month 2 (Dr / Cr / Bal)
- ...
- Closing Balance
- Aging (0-30, 31-60, 61-90, 90+ days) based on *current* date or report end date.

DELIVERABLES: Controller method, View with table.
```

### Subtask 7.3.2: Ledger Export (Excel/PDF)

```
read .antigravity content and then

CONTEXT:
- Add export buttons to `account_ledger.php` and `cash_customer_ledger.php`.

REQUIREMENTS:
1. Export to Excel (using PhpSpreadsheet or simple CSV).
2. Export to PDF (using Dompdf or TCPDF).
3. Options:
   - Include/Exclude Opening Balance.
   - Summary Only vs Detailed.

IMPLEMENTATION:
- Add `exportLedger($id, $type, $format)` method in LedgerController.
- Generate file download.

DELIVERABLES: Export functionality.
```

### Subtask 7.3.3: Payment Collection Summary

```
read .antigravity content and then

CONTEXT:
- Report to show all payments received in a date range.

FILE: app/Controllers/Reports/PaymentReportController.php
VIEW: app/Views/reports/payment_collection.php

COLUMNS:
- Date
- Customer Name
- Invoice Ref
- Amount
- Mode (Cash/UPI/etc)
- Received By

SUMMARY SECTION:
- Total Collected.
- Breakdown by Payment Mode (Cash: X, UPI: Y, etc).

DELIVERABLES: Controller, View.
```

---

## 🎯 TASK 7.4: DASHBOARD & ANALYTICS

### Subtask 7.4.1: Dashboard Widgets

```
read .antigravity content and then

FILE: app/Controllers/Dashboard/DashboardController.php (Update existing or create new)
VIEW: app/Views/dashboard/index.php (Update)

WIDGETS:
1. **Today's Summary**:
   - Invoices created (Count & Total).
   - Payments received (Count & Total).
   - Pending Deliveries (Count).
2. **Outstanding Summary**:
   - Total Receivables (Sum of current_balance > 0).
   - Count of Unpaid Invoices.
3. **Top Customers**:
   - Limit 10, ordered by High Receivable Balance.
4. **Challan Status**:
   - Pie Chart (Draft vs Approved vs Invoiced).

DELIVERABLES: Updated Dashboard with real-time specific widgets.
```

### Subtask 7.4.2: Payment Reminders

```
read .antigravity content and then

CONTEXT:
- List outstanding invoices and allow sending reminders.

FILE: app/Controllers/Ledgers/ReminderController.php
VIEW: app/Views/reports/outstanding_invoices.php

FEATURES:
1. List all invoices with `amount_due > 0`.
2. Filter by Customer, Date.
3. Show "Days Overdue".
4. **Action**: "Send Reminder" button.
   - For now, log the reminder in `audit_logs` or set a flash message "Reminder sent to [Mobile/Email]".
   - (Real SMS/Email integration can be a service stub for now).

DELIVERABLES: Outstanding invoice list with "Send Reminder" action.
```

---

**END OF TASK-07 COMPLETE**

# AI CODING PROMPTS - TASK 08

## Reports Module

**Version:** 1.0  
**Phase:** 8 - Reporting & Analytics (Week 19)  
**Generated:** February 10, 2026

---

## SUBTASKS: 8.1.2-8.1.3 (Ledger Reports), 8.2.1-8.2.4 (Outstanding & Receivables), 8.3.1-8.3.3 (Dashboard & Exports)

---

## 🎯 TASK 8.1: LEDGER REPORTS

### Subtask 8.1.2: Create LedgerReportService

```
read .antigravity content and then

FILE: app/Services/Report/LedgerReportService.php

DEPENDENCIES: LedgerEntryModel, AccountModel, CashCustomerModel

METHODS:

1. public function generateAccountLedger(int $accountId, $fromDate, $toDate): array
   - Get opening balance (before fromDate)
   - Get all ledger entries in date range
   - Calculate running balance for each entry
   - Calculate closing balance
   - Return: [
       'opening_balance' => ...,
       'entries' => [...], // Each entry with running balance
       'closing_balance' => ...,
       'total_debit' => ...,
       'total_credit' => ...
     ]

2. public function generateCashCustomerLedger(int $customerId, $fromDate, $toDate): array
   - Same logic as account ledger
   - Opening balance usually 0 for cash customers

3. public function generateConsolidatedLedger($fromDate, $toDate, $customerType = null): array
   - Generate ledger for all customers
   - Grouped by customer
   - Return array of customer ledgers

DELIVERABLES: Complete LedgerReportService.php

ACCEPTANCE CRITERIA: Ledger generation accurate, running balance correct
```

---

### Subtask 8.1.3: Create LedgerReportController

```
read .antigravity content and then

FILE: app/Controllers/Reports/LedgerReportController.php

ROUTES:
- GET /reports/ledger/account/{id} → accountLedger()
- GET /reports/ledger/cash-customer/{id} → cashCustomerLedger()
- GET /reports/ledger/consolidated → consolidatedLedger()
- GET /reports/ledger/export/{type}/{id} → exportLedger() [PDF/Excel]

METHODS:
1. accountLedger($id) - show account ledger with date filter
2. cashCustomerLedger($id) - show cash customer ledger
3. consolidatedLedger() - show all customers ledger summary
4. exportLedger($type, $id) - export as PDF or Excel

VIEWS:
- app/Views/reports/ledger/account_ledger.php
- app/Views/reports/ledger/cash_customer_ledger.php
- app/Views/reports/ledger/consolidated_ledger.php

DELIVERABLES: Controller, 3 views, export functionality

ACCEPTANCE CRITERIA: Reports display correctly, export works
```

---

## 🎯 TASK 8.2: OUTSTANDING & RECEIVABLES

### Subtask 8.2.1: Create OutstandingReportService

```
read .antigravity content and then

FILE: app/Services/Report/OutstandingReportService.php

DEPENDENCIES: InvoiceModel

METHODS:

1. public function getOutstandingInvoices($filters = []): array
   - Query all invoices where amount_due > 0
   - Apply filters: customer, date range, overdue only
   - Calculate days_overdue = today - due_date
   - Order by due_date ASC
   - Return: [
       ['invoice' => ..., 'amount_due' => ..., 'days_overdue' => ...],
       ...
     ]

2. public function getTotalOutstanding(): float
   - SUM(amount_due) from all invoices
   - Return total

3. public function getOverdueTotal(): float
   - SUM(amount_due) where due_date < today

4. public function getAgingReport(): array
   - Group outstanding by aging buckets:
     - 0-30 days
     - 31-60 days
     - 61-90 days
     - 90+ days
   - Return: [
       '0-30' => ['count' => ..., 'amount' => ...],
       '31-60' => [...],
       ...
     ]

DELIVERABLES: Complete OutstandingReportService.php

ACCEPTANCE CRITERIA: Outstanding calculations accurate, aging report works
```

---

### Subtask 8.2.2: Create ReceivableReportService

```
read .antigravity content and then

FILE: app/Services/Report/ReceivableReportService.php

METHODS:

1. public function generateMonthlyReceivableSummary($fromDate, $toDate): array
   - For each customer (account + cash):
     - Calculate opening balance (before fromDate)
     - For each month in range:
       - Month debits = SUM(invoices in month)
       - Month credits = SUM(payments in month)
       - Month closing = opening + debits - credits
     - Calculate final closing balance
   - Return: [
       ['customer' => ..., 'opening' => ..., 'months' => [...], 'closing' => ...],
       ...
     ]

2. public function getTopDebtors(int $limit = 10): array
   - Get customers with highest outstanding balance
   - Order by balance DESC
   - Return top N

DELIVERABLES: Complete ReceivableReportService.php

ACCEPTANCE CRITERIA: Monthly summary accurate
```

---

### Subtask 8.2.3: Create OutstandingReportController

```
read .antigravity content and then

FILE: app/Controllers/Reports/OutstandingReportController.php

ROUTES:
- GET /reports/outstanding → index()
- GET /reports/outstanding/aging → agingReport()
- GET /reports/outstanding/export → exportOutstanding()

VIEWS:
- app/Views/reports/outstanding/index.php - list outstanding invoices
- app/Views/reports/outstanding/aging.php - aging report

DELIVERABLES: Controller, 2 views

ACCEPTANCE CRITERIA: Outstanding reports display correctly
```

---

### Subtask 8.2.4: Create ReceivableReportController

```
read .antigravity content and then

FILE: app/Controllers/Reports/ReceivableReportController.php

ROUTES:
- GET /reports/receivables → index()
- GET /reports/receivables/monthly → monthlySummary()
- GET /reports/receivables/top-debtors → topDebtors()

VIEWS:
- app/Views/reports/receivables/index.php
- app/Views/reports/receivables/monthly_summary.php
- app/Views/reports/receivables/top_debtors.php

DELIVERABLES: Controller, 3 views

ACCEPTANCE CRITERIA: Receivable reports accurate
```

---

## 🎯 TASK 8.3: DASHBOARD & EXPORTS

### Subtask 8.3.1: Create DashboardService

```
read .antigravity content and then

FILE: app/Services/Report/DashboardService.php

DEPENDENCIES: InvoiceModel, PaymentModel, OutstandingReportService

METHODS:

1. public function getTodaySummary(): array
   - Count invoices created today
   - SUM invoice grand_total today
   - Count payments received today
   - SUM payment amounts today
   - Return: ['invoices_count', 'invoices_total', 'payments_count', 'payments_total']

2. public function getOutstandingSummary(): array
   - Call OutstandingReportService->getTotalOutstanding()
   - Count outstanding invoices
   - Count overdue invoices
   - Return: ['total_outstanding', 'outstanding_count', 'overdue_count']

3. public function getTopCustomers(int $limit = 10): array
   - Query customers with highest outstanding balance
   - Order by balance DESC
   - Return top N

4. public function getPaymentCollectionTrend(int $days = 30): array
   - For last N days:
     - SUM payment amounts per day
   - Return: [['date' => ..., 'amount' => ...], ...]

5. public function getInvoiceTrend(int $days = 30): array
   - For last N days:
     - Count invoices per day
     - SUM invoice amounts per day
   - Return chart data

DELIVERABLES: Complete DashboardService.php

ACCEPTANCE CRITERIA: Dashboard KPIs accurate
```

---

### Subtask 8.3.2: Create DashboardController

```
read .antigravity content and then

FILE: app/Controllers/DashboardController.php

ROUTES:
- GET / → index() (dashboard home)
- GET /dashboard → index()

METHODS:
1. index() - load dashboard with all KPIs and charts

VIEW: app/Views/dashboard/index.php

Dashboard components:
- Today's Summary (invoices, payments)
- Outstanding Summary (total, count, overdue)
- Top 10 Customers by Outstanding
- Payment Collection Trend Chart (last 30 days)
- Invoice Trend Chart (last 30 days)
- Recent Invoices (last 10)
- Recent Payments (last 10)

DELIVERABLES: Controller, dashboard view

ACCEPTANCE CRITERIA: Dashboard displays all KPIs, charts render
```

---

### Subtask 8.3.3: Create Report Export Library

```
read .antigravity content and then

FILE 1: app/Libraries/PDF/ReportPDF.php

Use TCPDF or mPDF library

METHODS:
1. generateLedgerPDF(array $ledgerData): string
   - Generate PDF from ledger data
   - Return file path

2. generateInvoicePDF(array $invoiceData): string
   - Generate invoice PDF

3. generateReportPDF(string $reportTitle, array $data, array $columns): string
   - Generic report PDF generator

FILE 2: app/Libraries/Excel/ReportExcel.php

Use PhpSpreadsheet library

METHODS:
1. generateLedgerExcel(array $ledgerData): string
   - Generate Excel file
   - Return file path

2. generateReportExcel(string $reportTitle, array $data, array $columns): string
   - Generic report Excel generator

DELIVERABLES: PDF and Excel export libraries

ACCEPTANCE CRITERIA: Export to PDF/Excel works
```

---

### Add Reports Routes & Sidebar

````
FILE 1: app/Config/Routes.php

```php
$routes->group('reports', ['filter' => 'auth', 'filter' => 'permission:report'], function($routes) {
    // Ledger Reports
    $routes->get('ledger/account/(:num)', 'Reports\LedgerReportController::accountLedger/$1');
    $routes->get('ledger/cash-customer/(:num)', 'Reports\LedgerReportController::cashCustomerLedger/$1');
    $routes->get('ledger/consolidated', 'Reports\LedgerReportController::consolidatedLedger');

    // Outstanding Reports
    $routes->get('outstanding', 'Reports\OutstandingReportController::index');
    $routes->get('outstanding/aging', 'Reports\OutstandingReportController::agingReport');

    // Receivable Reports
    $routes->get('receivables', 'Reports\ReceivableReportController::index');
    $routes->get('receivables/monthly', 'Reports\ReceivableReportController::monthlySummary');
});
````

FILE 2: Sidebar

```html
<?php if (can('report.view')): ?>
<li class="nav-item dropdown">
  <a
    class="nav-link dropdown-toggle"
    href="#"
    id="reportsDropdown"
    role="button"
    data-bs-toggle="dropdown"
  >
    <i class="fas fa-chart-bar"></i> Reports
  </a>
  <ul class="dropdown-menu">
    <li>
      <a
        class="dropdown-item"
        href="<?= base_url('reports/ledger/consolidated') ?>"
        >Ledger Reports</a
      >
    </li>
    <li>
      <a class="dropdown-item" href="<?= base_url('reports/outstanding') ?>"
        >Outstanding Invoices</a
      >
    </li>
    <li>
      <a class="dropdown-item" href="<?= base_url('reports/receivables') ?>"
        >Receivables</a
      >
    </li>
    <li><hr class="dropdown-divider" /></li>
    <li>
      <a
        class="dropdown-item"
        href="<?= base_url('reports/outstanding/aging') ?>"
        >Aging Report</a
      >
    </li>
  </ul>
</li>
<?php endif; ?>
```

DELIVERABLES: Routes and sidebar

```

---

**END OF TASK-08 COMPLETE**
```

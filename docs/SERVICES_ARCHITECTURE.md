# SERVICES ARCHITECTURE
## Gold Manufacturing & Billing ERP System

---

## SERVICES OVERVIEW

### Design Principles
1. **Single Responsibility:** Each service handles one domain
2. **Business Logic in Services:** Controllers are thin, services are fat
3. **Transaction Management:** Services manage DB transactions
4. **Validation:** Services validate before processing
5. **Audit Logging:** Services call AuditService after critical operations
6. **Reusability:** Services can call other services
7. **Testability:** Services are unit-testable

---

## SERVICE CATALOG

### 1. AuthService
**Location:** `app/Services/Auth/AuthService.php`  
**Responsibility:** User authentication and session management

**Public Methods:**
- `login(string $username, string $password): array|false`
  - Validate credentials
  - Check failed login attempts
  - Create session
  - Reset failed attempts on success
  - Return user data or false

- `logout(): bool`
  - Destroy session
  - Audit log logout
  - Return success

- `getCurrentUser(): ?User`
  - Get logged-in user from session
  - Return User entity or null

- `validatePassword(string $password, string $hash): bool`
  - Verify password against hash
  - Use password_verify()

- `handleFailedLogin(int $userId): void`
  - Increment failed_login_attempts
  - Lock account after 5 attempts

- `resetFailedAttempts(int $userId): void`
  - Reset failed_login_attempts to 0

**Dependencies:**
- UserModel
- AuditService

---

### 2. PermissionService
**Location:** `app/Services/Auth/PermissionService.php`  
**Responsibility:** Permission management and authorization

**Public Methods:**
- `getUserPermissions(int $userId): array`
  - Get all permissions from user's roles
  - Merge permissions (additive)
  - Cache in session
  - Return array of permission codes

- `hasPermission(int $userId, string $permission): bool`
  - Check if user has specific permission
  - Super admin always true

- `can(string $permission): bool`
  - Check if current user has permission
  - Shortcut for hasPermission(getCurrentUser()->id, $permission)

- `cachePermissions(int $userId, array $permissions): void`
  - Store permissions in session cache

- `clearPermissionCache(int $userId): void`
  - Clear cached permissions (after role change)

**Dependencies:**
- UserModel
- RoleModel
- Session library

---

### 3. CompanyService
**Location:** `app/Services/Company/CompanyService.php`  
**Responsibility:** Company management and configuration

**Public Methods:**
- `createCompany(array $data): int`
  - Validate company data
  - Check unique company name
  - Validate GST, PAN
  - Create company record
  - Initialize settings
  - Return company ID

- `updateCompany(int $id, array $data): bool`
  - Validate update data
  - Update company record
  - Audit log changes
  - Return success

- `getCompanyById(int $id): ?Company`
  - Fetch company entity
  - Return Company or null

- `validateGSTNumber(string $gst): bool`
  - Regex validation: 15 chars, format check
  - Return valid or not

- `validatePANNumber(string $pan): bool`
  - Regex validation: 10 chars, format check
  - Return valid or not

- `getCompanySettings(int $companyId): array`
  - Get all settings as key-value array

**Dependencies:**
- CompanyModel
- CompanySettingModel
- AuditService

---

### 4. UserService
**Location:** `app/Services/User/UserService.php`  
**Responsibility:** User account management

**Public Methods:**
- `createUser(array $data): int`
  - Validate user data
  - Check unique username, email
  - Hash password
  - Create user record
  - Return user ID

- `updateUser(int $id, array $data): bool`
  - Validate update data
  - Check username/email uniqueness (exclude current)
  - Hash password if changed
  - Update user record
  - Clear permission cache if roles changed
  - Audit log
  - Return success

- `deleteUser(int $id): bool`
  - Check if user has transactions
  - Soft delete if allowed
  - Audit log
  - Return success or throw exception

- `assignRoles(int $userId, array $roleIds): bool`
  - Delete existing role assignments
  - Insert new role assignments
  - Clear permission cache
  - Audit log
  - Wrap in transaction
  - Return success

- `getUserRoles(int $userId): array`
  - Fetch user's assigned roles
  - Return array of Role entities

- `validatePasswordComplexity(string $password): bool`
  - Check min 8 chars
  - Check has uppercase, lowercase, number, special char
  - Return valid or not

**Dependencies:**
- UserModel
- UserRoleModel
- PermissionService
- AuditService

---

### 5. RoleService
**Location:** `app/Services/User/RoleService.php`  
**Responsibility:** Role and permission management

**Public Methods:**
- `createRole(array $data): int`
  - Validate role data
  - Validate permissions array
  - Create role record
  - Return role ID

- `updateRole(int $id, array $data): bool`
  - Check if system role (cannot edit)
  - Update role record
  - Clear permission cache for all users with this role
  - Audit log
  - Return success

- `deleteRole(int $id): bool`
  - Check if system role (cannot delete)
  - Check if role assigned to users
  - Soft delete if allowed
  - Audit log
  - Return success or throw exception

- `getRolePermissions(int $roleId): array`
  - Fetch role's permissions JSON
  - Return array of permission codes

- `updatePermissions(int $roleId, array $permissions): bool`
  - Update role's permissions JSON
  - Clear permission cache for affected users
  - Audit log
  - Return success

**Dependencies:**
- RoleModel
- UserRoleModel
- PermissionService
- AuditService

---

### 6. GoldRateService
**Location:** `app/Services/Master/GoldRateService.php`  
**Responsibility:** Gold rate management

**Public Methods:**
- `createRate(array $data): int`
  - Validate rate data
  - Check rate within range (1000-100000)
  - Check date not future
  - Upsert rate (update if exists for date+metal)
  - Audit log
  - Clear rate cache
  - Return rate ID

- `updateRate(int $id, array $data): bool`
  - Validate update data
  - Update rate record
  - Clear rate cache
  - Audit log
  - Return success

- `getLatestRate(string $metalType = '22K'): ?float`
  - Check cache first
  - Query latest rate for metal type
  - Cache result (1 hour)
  - Return rate or null

- `getRateByDate(Date $date, string $metalType = '22K'): ?float`
  - Query rate for specific date and metal
  - Return rate or null

- `checkIfTodayRateEntered(): bool`
  - Check if rate exists for today
  - Return true/false

- `sendRateAlert(): void`
  - If rate not entered by 10 AM
  - Send notification to admin
  - Log alert sent

**Dependencies:**
- GoldRateModel
- Cache library
- AuditService

---

### 7. ProductService
**Location:** `app/Services/Master/ProductService.php`  
**Responsibility:** Product catalog management

**Public Methods:**
- `createProduct(array $data): int`
  - Validate product data
  - Check unique product code
  - Handle image upload
  - Create product record
  - Return product ID

- `updateProduct(int $id, array $data): bool`
  - Validate update data
  - Handle image upload/replace
  - Update product record
  - Audit log
  - Return success

- `deleteProduct(int $id): bool`
  - Check if product used in challans/invoices
  - Soft delete if allowed
  - Delete image file
  - Audit log
  - Return success or throw exception

- `getActiveProducts(int $categoryId = null): array`
  - Fetch active products
  - Filter by category if provided
  - Return array of Product entities

**Dependencies:**
- ProductModel
- FileUploadService
- AuditService

---

### 8. ProcessService
**Location:** `app/Services/Master/ProcessService.php`  
**Responsibility:** Manufacturing process management

**Public Methods:**
- `createProcess(array $data): int`
  - Validate process data
  - Check unique process code
  - Create process record
  - Return process ID

- `updateProcess(int $id, array $data): bool`
  - Validate update data
  - Update process record
  - Audit log (price change is critical)
  - Return success

- `deleteProcess(int $id): bool`
  - Check if process used in challans/invoices
  - Soft delete if allowed
  - Audit log
  - Return success or throw exception

- `calculateRateFromProcesses(array $processIds): float`
  - Fetch process prices
  - SUM all prices
  - Return total rate

- `getActiveProcesses(): array`
  - Fetch active processes
  - Return array of Process entities

**Dependencies:**
- ProcessModel
- AuditService

---

### 9. AccountService
**Location:** `app/Services/Customer/AccountService.php`  
**Responsibility:** Account customer management

**Public Methods:**
- `createAccount(array $data): int`
  - Validate account data
  - Generate account code if not provided
  - Create account record
  - Create opening balance ledger entry
  - Wrap in transaction
  - Return account ID

- `updateAccount(int $id, array $data): bool`
  - Validate update data
  - Cannot change opening balance (immutable)
  - Update account record
  - Audit log
  - Return success

- `deleteAccount(int $id): bool`
  - Check if account has transactions
  - Soft delete if allowed
  - Audit log
  - Return success or throw exception

- `getLedgerBalance(int $accountId): float`
  - Call LedgerService.getLastBalance()
  - Return current balance

- `generateAccountCode(int $companyId): string`
  - Generate unique code: ACC-{sequence}
  - Check uniqueness
  - Return code

**Dependencies:**
- AccountModel
- LedgerService
- AuditService

---

### 10. CashCustomerService
**Location:** `app/Services/Customer/CashCustomerService.php`  
**Responsibility:** Cash customer management and deduplication

**Public Methods:**
- `findOrCreate(string $name, string $mobile): int`
  - Normalize name (trim, lower, collapse spaces)
  - Check if customer exists (name + mobile + company_id)
  - If exists: Return customer ID
  - If not: Create new customer, return ID
  - Idempotent operation

- `search(string $query): array`
  - Search by name or mobile
  - Return array of matching customers (for autocomplete)

- `mergeDuplicates(int $primaryId, int $secondaryId): bool`
  - Update all invoices/challans from secondary to primary
  - Soft delete secondary customer
  - Audit log
  - Wrap in transaction
  - Return success

- `getCustomerHistory(int $customerId): array`
  - Get last 10 invoices for customer
  - Return invoice summaries

**Dependencies:**
- CashCustomerModel
- AuditService

---

### 11. ChallanService
**Location:** `app/Services/Challan/ChallanService.php`  
**Responsibility:** Challan lifecycle management

**Public Methods:**
- `createChallan(array $data): int`
  - Validate challan data
  - Call NumberingService.getNextChallanNumber()
  - Create challan header
  - Create challan lines
  - Call ChallanCalculationService.calculateChallanTotal()
  - Update challan.total_amount
  - Audit log
  - Wrap in transaction
  - Return challan ID

- `updateChallan(int $id, array $data): bool`
  - Call ChallanValidationService.canEdit()
  - If not editable: Throw exception
  - Update challan header
  - Update/delete/add challan lines
  - Recalculate total
  - Audit log (before/after data)
  - Wrap in transaction
  - Return success

- `deleteChallan(int $id): bool`
  - Call ChallanValidationService.canDelete()
  - If not deletable: Throw exception
  - Soft delete challan
  - Soft delete challan lines
  - Audit log
  - Wrap in transaction
  - Return success

- `submitForApproval(int $id): bool`
  - Check status = Draft
  - Update status to Submitted
  - Audit log
  - Return success

- `approveChallan(int $id): bool`
  - Check permission
  - Check status = Submitted
  - Update status to Approved
  - Audit log
  - Return success

- `cancelChallan(int $id): bool`
  - Check status (only Draft/Submitted)
  - Update status to Cancelled
  - Audit log
  - Return success

**Dependencies:**
- ChallanModel
- ChallanLineModel
- ChallanCalculationService
- ChallanValidationService
- NumberingService
- AuditService

---

### 12. ChallanCalculationService
**Location:** `app/Services/Challan/ChallanCalculationService.php`  
**Responsibility:** Challan amount calculations

**Public Methods:**
- `calculateLineRate(array $processIds): float`
  - Fetch process prices
  - SUM all prices
  - Return total rate

- `calculateLineAmount(float $rate, float $weight): float`
  - IF weight > 0: amount = weight × rate
  - ELSE: amount = rate (fixed price)
  - Round to 2 decimals
  - Return amount

- `calculateWaxAmount(float $weight, float $accountPrice, float $minPrice): float`
  - calculated = weight × accountPrice
  - IF calculated < minPrice: Return minPrice
  - ELSE: Return calculated

- `calculateChallanTotal(int $challanId): float`
  - Fetch all challan lines
  - SUM all line amounts
  - Return total

**Dependencies:**
- ProcessModel
- ChallanLineModel

---

### 13. ChallanValidationService
**Location:** `app/Services/Challan/ChallanValidationService.php`  
**Responsibility:** Challan business rule validation

**Public Methods:**
- `validateChallanData(array $data): array|bool`
  - Validate all challan fields
  - Validate account_id OR cash_customer_id (XOR)
  - Validate line items (at least 1)
  - Validate each line (products OR processes)
  - Return validation errors array or true

- `canEdit(int $challanId): bool`
  - Fetch challan
  - Check invoice_generated = false
  - Check status != Cancelled
  - Return true/false

- `canDelete(int $challanId): bool`
  - Fetch challan
  - Check invoice_generated = false
  - Return true/false

- `canApprove(int $challanId): bool`
  - Fetch challan
  - Check status = Submitted
  - Check current user has challan.approve permission
  - Return true/false

**Dependencies:**
- ChallanModel
- PermissionService

---

### 14. InvoiceService
**Location:** `app/Services/Invoice/InvoiceService.php`  
**Responsibility:** Invoice lifecycle management (CRITICAL)

**Public Methods:**
- `createInvoiceFromChallans(array $challanIds, array $invoiceData): int`
  - **Start Transaction**
  - Validate challans (approved, not invoiced)
  - Call NumberingService.getNextInvoiceNumber()
  - Create invoice header
  - For each challan:
    - Copy lines from challan to invoice_lines
    - Link source_challan_id, source_challan_line_id
  - Call InvoiceCalculationService.calculateInvoiceTotals()
  - Update invoice totals
  - Mark challans as invoice_generated = true
  - Update challan.invoice_id
  - Call LedgerService.createInvoiceEntry() (DEBIT)
  - Audit log
  - **Commit Transaction**
  - Return invoice ID
  - **On Error: Rollback**

- `createCashInvoice(array $data): int`
  - **Start Transaction**
  - Call CashCustomerService.findOrCreate()
  - Call NumberingService.getNextInvoiceNumber()
  - Create invoice header
  - Create invoice lines from data
  - Call InvoiceCalculationService.calculateInvoiceTotals()
  - Update invoice totals
  - Call LedgerService.createInvoiceEntry() (DEBIT)
  - Audit log
  - **Commit Transaction**
  - Return invoice ID
  - **On Error: Rollback**

- `updateInvoice(int $id, array $data): bool`
  - Call InvoiceValidationService.canEdit()
  - If not editable: Throw exception
  - **Start Transaction**
  - Update invoice header
  - Update invoice lines
  - Recalculate totals
  - Update ledger entry
  - Audit log
  - **Commit Transaction**
  - Return success
  - **On Error: Rollback**

- `deleteInvoice(int $id): bool`
  - Call InvoiceValidationService.canDelete()
  - If not deletable: Throw exception
  - **Start Transaction**
  - Revert challan.invoice_generated = false
  - Clear challan.invoice_id
  - Delete ledger entry (if no payments)
  - Soft delete invoice
  - Soft delete invoice lines
  - Audit log
  - **Commit Transaction**
  - Return success
  - **On Error: Rollback**

- `postInvoice(int $id): bool`
  - Check status = Draft
  - Update status to Posted
  - Audit log
  - Return success

**Dependencies:**
- InvoiceModel
- InvoiceLineModel
- ChallanModel
- InvoiceCalculationService
- InvoiceValidationService
- LedgerService
- NumberingService
- AuditService

---

### 15. InvoiceCalculationService
**Location:** `app/Services/Invoice/InvoiceCalculationService.php`  
**Responsibility:** Invoice amount calculations

**Public Methods:**
- `calculateLineAmount(float $rate, float $weight): float`
  - IF weight > 0: amount = weight × rate
  - ELSE: amount = rate
  - Round to 2 decimals
  - Return amount

- `calculateInvoiceSubtotal(array $lines): float`
  - SUM all line amounts
  - Return subtotal

- `calculateInvoiceTotals(int $invoiceId): array`
  - Fetch invoice and lines
  - Fetch company state, customer state
  - Calculate subtotal
  - Call TaxCalculationService.calculateInvoiceTax()
  - Return: ['subtotal', 'tax_amount', 'cgst', 'sgst', 'igst', 'grand_total']

**Dependencies:**
- InvoiceLineModel
- TaxCalculationService

---

### 16. TaxCalculationService
**Location:** `app/Services/Invoice/TaxCalculationService.php`  
**Responsibility:** Tax calculations (GST CGST/SGST/IGST)

**Public Methods:**
- `calculateTaxFromInclusive(float $amount, float $taxRate): array`
  - tax_amount = amount × taxRate / (100 + taxRate)
  - subtotal = amount - tax_amount
  - Round both to 2 decimals
  - Return: ['subtotal' => float, 'tax_amount' => float]

- `calculateGST(float $taxAmount, string $companyState, string $customerState): array`
  - IF companyState == customerState:
    - cgst = taxAmount / 2
    - sgst = taxAmount / 2
    - igst = 0
  - ELSE:
    - cgst = 0
    - sgst = 0
    - igst = taxAmount
  - Round all to 2 decimals
  - Return: ['cgst' => float, 'sgst' => float, 'igst' => float]

- `calculateInvoiceTax(array $lines, float $taxRate, string $companyState, string $customerState): array`
  - For each line:
    - Calculate tax from inclusive amount
    - Aggregate subtotal, tax
  - Call calculateGST()
  - Return: ['subtotal', 'tax_amount', 'cgst', 'sgst', 'igst']

**Dependencies:**
- None (pure calculation logic)

---

### 17. InvoiceValidationService
**Location:** `app/Services/Invoice/InvoiceValidationService.php`  
**Responsibility:** Invoice business rule validation

**Public Methods:**
- `validateInvoiceData(array $data): array|bool`
  - Validate invoice fields
  - Validate line items
  - Return errors or true

- `validateChallanSelection(array $challanIds): bool`
  - Check all challans approved
  - Check all challans not invoiced
  - Check all challans belong to same account
  - Return true or throw exception

- `canEdit(int $invoiceId): bool`
  - Fetch invoice
  - Check total_paid = 0
  - Return true/false

- `canDelete(int $invoiceId): bool`
  - Fetch invoice
  - Check total_paid = 0
  - Return true/false

**Dependencies:**
- InvoiceModel
- ChallanModel

---

### 18. PaymentService
**Location:** `app/Services/Payment/PaymentService.php`  
**Responsibility:** Payment processing (CRITICAL)

**Public Methods:**
- `recordPayment(int $invoiceId, array $paymentData, array $goldAdjustment = null): int`
  - **Start Transaction**
  - Validate payment amount <= invoice.amount_due
  - IF gold adjustment provided:
    - Call GoldAdjustmentService.applyGoldAdjustment()
    - Invoice totals updated, adjustment ledger entry created
  - Create payment record
  - Update invoice.total_paid += payment_amount
  - Update invoice.amount_due = grand_total - total_paid
  - Update invoice.payment_status
  - Update invoice.invoice_status
  - Call LedgerService.createPaymentEntry() (CREDIT)
  - Audit log
  - **Commit Transaction**
  - Return payment ID
  - **On Error: Rollback**

- `deletePayment(int $id): bool`
  - Check permission (admin only)
  - Check invoice not delivered
  - **Start Transaction**
  - Fetch payment
  - Reverse invoice.total_paid
  - Reverse invoice.amount_due
  - Update invoice statuses
  - Delete ledger entry
  - Soft delete payment
  - Audit log
  - **Commit Transaction**
  - Return success
  - **On Error: Rollback**

- `getPaymentHistory(int $invoiceId): array`
  - Fetch all payments for invoice
  - Return array of Payment entities

**Dependencies:**
- PaymentModel
- InvoiceModel
- PaymentValidationService
- GoldAdjustmentService
- LedgerService
- AuditService

---

### 19. GoldAdjustmentService
**Location:** `app/Services/Payment/GoldAdjustmentService.php`  
**Responsibility:** Gold weight adjustment calculations and application (CRITICAL)

**Public Methods:**
- `calculateAdjustment(int $invoiceId, array $lineAdjustments, float $goldRate): array`
  - For each line:
    - gold_difference = new_weight - original_weight
    - adjustment_amount = gold_difference × gold_rate
    - adjusted_line_amount = original_amount + adjustment_amount
  - Aggregate:
    - total_adjustment_amount = SUM(adjustment_amounts)
    - adjusted_grand_total = original_grand_total + total_adjustment_amount
  - Return: ['line_adjustments' => [...], 'total_adjustment' => float, 'adjusted_grand_total' => float]

- `applyGoldAdjustment(int $invoiceId, array $lineAdjustments, float $goldRate): bool`
  - **Start Transaction**
  - Check invoice.gold_adjustment_applied = false
  - Call calculateAdjustment()
  - For each line:
    - Update invoice_line.adjusted_gold_weight
    - Update invoice_line.gold_adjustment_amount
    - Update invoice_line.amount (adjusted)
  - Recalculate invoice totals (with TaxCalculationService)
  - Update invoice.grand_total
  - Update invoice.gold_adjustment_applied = true
  - Update invoice.gold_adjustment_amount
  - Update invoice.gold_adjustment_date
  - Update invoice.gold_rate_used
  - Call LedgerService.createAdjustmentEntry() (DEBIT or CREDIT)
  - Audit log
  - **Commit Transaction**
  - Return success
  - **On Error: Rollback**

- `validateAdjustment(int $invoiceId, array $lineAdjustments): bool`
  - Check invoice not already adjusted
  - Check gold rate available
  - Validate new weights >= 0
  - Return true or throw exception

**Dependencies:**
- InvoiceModel
- InvoiceLineModel
- GoldRateService
- TaxCalculationService
- LedgerService
- AuditService

---

### 20. PaymentValidationService
**Location:** `app/Services/Payment/PaymentValidationService.php`  
**Responsibility:** Payment validation

**Public Methods:**
- `validatePaymentData(array $data): array|bool`
  - Validate payment amount
  - Validate payment mode
  - Validate mode-specific fields (cheque number, etc.)
  - Return errors or true

- `validatePaymentAmount(int $invoiceId, float $amount): bool`
  - Fetch invoice.amount_due
  - Check amount <= amount_due
  - Check amount > 0
  - Return true or throw exception

**Dependencies:**
- InvoiceModel

---

### 21. LedgerService
**Location:** `app/Services/Ledger/LedgerService.php`  
**Responsibility:** Ledger entry management (APPEND-ONLY)

**Public Methods:**
- `createEntry(array $data): int`
  - Validate entry data
  - Calculate balance_after
  - Insert ledger entry (APPEND ONLY)
  - Return entry ID
  - **Never update or delete**

- `getLastBalance(int $accountId = null, int $cashCustomerId = null): float`
  - Query last ledger entry for customer
  - Return balance_after
  - If no entries: Return 0

- `createOpeningBalanceEntry(int $accountId, float $amount, Date $date): int`
  - Create entry:
    - reference_type = 'opening_balance'
    - debit or credit based on amount sign
    - balance_after = amount
  - Return entry ID

- `createInvoiceEntry(int $invoiceId): int`
  - Fetch invoice
  - Get last balance
  - Create entry:
    - reference_type = 'invoice'
    - reference_id = invoice_id
    - debit_amount = invoice.grand_total
    - balance_after = last_balance + debit_amount
  - Return entry ID

- `createPaymentEntry(int $paymentId): int`
  - Fetch payment
  - Get last balance
  - Create entry:
    - reference_type = 'payment'
    - reference_id = payment_id
    - credit_amount = payment.payment_amount
    - balance_after = last_balance - credit_amount
  - Return entry ID

- `createAdjustmentEntry(int $invoiceId, float $amount, string $description): int`
  - Get last balance
  - Create entry:
    - reference_type = 'gold_adjustment'
    - reference_id = invoice_id
    - debit_amount or credit_amount (based on sign)
    - balance_after = last_balance +/- amount
  - Return entry ID

**Dependencies:**
- LedgerEntryModel
- InvoiceModel
- PaymentModel

---

### 22. DeliveryService
**Location:** `app/Services/Delivery/DeliveryService.php`  
**Responsibility:** Delivery management

**Public Methods:**
- `assignDelivery(int $invoiceId, int $userId, Date $expectedDate): int`
  - Validate invoice paid
  - Create delivery record
  - Assign to user
  - Return delivery ID

- `markDelivered(int $deliveryId, string $proofPhoto): bool`
  - Upload proof photo
  - Update delivery.delivery_status = Delivered
  - Update delivery.actual_delivery_date
  - Update delivery.delivered_timestamp
  - Update invoice.invoice_status = Delivered
  - Audit log
  - Return success

- `markFailed(int $deliveryId, string $reason): bool`
  - Update delivery.delivery_status = Failed
  - Add failure reason to notes
  - Audit log
  - Return success

- `getMyDeliveries(int $userId): array`
  - Fetch deliveries assigned to user
  - Filter by status (not delivered)
  - Return array

**Dependencies:**
- DeliveryModel
- InvoiceModel
- FileUploadService
- AuditService

---

### 23. LedgerReportService
**Location:** `app/Services/Report/LedgerReportService.php`  
**Responsibility:** Ledger report generation

**Public Methods:**
- `generateAccountLedger(int $accountId, Date $fromDate, Date $toDate): array`
  - Calculate opening balance (before fromDate)
  - Fetch all ledger entries in date range
  - Calculate running balance for each entry
  - Calculate closing balance
  - Return: ['opening_balance', 'entries' => [...], 'closing_balance']

- `generateCashCustomerLedger(int $cashCustomerId, Date $fromDate, Date $toDate): array`
  - Same logic as account ledger
  - Opening balance always 0 for cash customers

- `exportLedgerToPDF(array $data): string`
  - Generate PDF using InvoicePDF library
  - Return file path

- `exportLedgerToExcel(array $data): string`
  - Generate Excel using PhpSpreadsheet
  - Return file path

**Dependencies:**
- LedgerEntryModel
- AccountModel
- CashCustomerModel
- PDF/Excel libraries

---

### 24. ReceivableReportService
**Location:** `app/Services/Report/ReceivableReportService.php`  
**Responsibility:** Receivable summary reports

**Public Methods:**
- `generateMonthlyReceivableSummary(Date $fromDate, Date $toDate): array`
  - For each customer (account + cash):
    - Calculate opening balance
    - For each month in range:
      - Calculate month debits (SUM invoices)
      - Calculate month credits (SUM payments)
      - Calculate month closing balance
    - Calculate final closing balance
  - Return: [['customer' => ..., 'opening' => ..., 'months' => [...], 'closing' => ...], ...]
  - **Heavy query - optimize with indexes**

**Dependencies:**
- LedgerEntryModel
- AccountModel
- CashCustomerModel

---

### 25. OutstandingReportService
**Location:** `app/Services/Report/OutstandingReportService.php`  
**Responsibility:** Outstanding invoice reports

**Public Methods:**
- `getOutstandingInvoices(array $filters = []): array`
  - Query all invoices where amount_due > 0
  - Apply filters: customer, date range
  - Calculate days overdue (today - due_date)
  - Order by due_date ASC
  - Return: [['invoice' => ..., 'days_overdue' => ...], ...]

- `getTotalOutstanding(): float`
  - SUM all invoices.amount_due
  - Return total

- `getOverdueTotal(): float`
  - SUM amount_due where due_date < today
  - Return total

**Dependencies:**
- InvoiceModel

---

### 26. DashboardService
**Location:** `app/Services/Report/DashboardService.php`  
**Responsibility:** Dashboard KPI generation

**Public Methods:**
- `getTodaySummary(): array`
  - Count invoices created today
  - SUM invoice grand_total today
  - Count payments received today
  - SUM payment amounts today
  - Return: ['invoices_count', 'invoices_total', 'payments_count', 'payments_total']

- `getOutstandingSummary(): array`
  - Call OutstandingReportService.getTotalOutstanding()
  - Count outstanding invoices
  - Count overdue invoices
  - Return: ['total_outstanding', 'outstanding_count', 'overdue_count']

- `getTopCustomers(int $limit = 10): array`
  - Query customers with highest outstanding balance
  - Order by balance DESC
  - Limit to $limit
  - Return: [['customer' => ..., 'balance' => ...], ...]

- `getPaymentCollectionTrend(int $days = 30): array`
  - For last $days days:
    - SUM payment amounts per day
  - Return: [['date' => ..., 'amount' => ...], ...]

**Dependencies:**
- InvoiceModel
- PaymentModel
- OutstandingReportService

---

### 27. AuditService
**Location:** `app/Services/Audit/AuditService.php`  
**Responsibility:** Audit logging

**Public Methods:**
- `log(string $module, string $actionType, string $recordType, int $recordId, array $beforeData = null, array $afterData = null): int`
  - Capture current user ID
  - Capture IP address
  - Capture user agent
  - JSON encode before/after data
  - Insert audit log entry
  - Return log ID

- `logCreate(string $module, string $recordType, int $recordId, array $data): int`
  - Call log() with action_type = 'create'

- `logUpdate(string $module, string $recordType, int $recordId, array $beforeData, array $afterData): int`
  - Call log() with action_type = 'update'

- `logDelete(string $module, string $recordType, int $recordId, array $beforeData): int`
  - Call log() with action_type = 'delete'

- `logView(string $module, string $recordType, int $recordId): int`
  - Call log() with action_type = 'view'
  - Optional: Only log sensitive views

- `getAuditTrail(string $recordType, int $recordId): array`
  - Fetch all audit logs for record
  - Order by created_at DESC
  - Return array

**Dependencies:**
- AuditLogModel
- Request library (for IP, user agent)

---

### 28. NumberingService
**Location:** `app/Services/Common/NumberingService.php`  
**Responsibility:** Sequential numbering for invoices and challans (CONCURRENCY-SAFE)

**Public Methods:**
- `getNextChallanNumber(int $companyId): string`
  - **Start Transaction with Row Lock**
  - SELECT last_challan_number FROM companies WHERE id = $companyId FOR UPDATE
  - Increment last_challan_number
  - UPDATE companies SET last_challan_number = new_value
  - Get challan_prefix from company
  - Generate: {prefix}{padded_number} (e.g., "CH-0001")
  - **Commit Transaction**
  - Return challan number
  - **On Error: Rollback**

- `getNextInvoiceNumber(int $companyId): string`
  - **Start Transaction with Row Lock**
  - SELECT last_invoice_number FROM companies WHERE id = $companyId FOR UPDATE
  - Increment last_invoice_number
  - UPDATE companies SET last_invoice_number = new_value
  - Get invoice_prefix from company
  - Generate: {prefix}{padded_number} (e.g., "INV-0001")
  - **Commit Transaction**
  - Return invoice number
  - **On Error: Rollback**

**Dependencies:**
- CompanyModel
- Database transaction support

**Critical Note:**
- Uses FOR UPDATE row lock to prevent race conditions
- Ensures sequential, gap-free numbering
- Required for GST compliance

---

### 29. ValidationService
**Location:** `app/Services/Common/ValidationService.php`  
**Responsibility:** Reusable validation logic

**Public Methods:**
- `validateGST(string $gst): bool`
  - Regex: 15 chars, format check
  - Return true/false

- `validatePAN(string $pan): bool`
  - Regex: 10 chars, format check
  - Return true/false

- `validateMobile(string $mobile): bool`
  - Regex: 10 digits
  - Return true/false

- `validateEmail(string $email): bool`
  - Standard email validation
  - Return true/false

- `validateDate(string $date): bool`
  - Check valid date format
  - Check not future date (if applicable)
  - Return true/false

- `validateAmount(float $amount): bool`
  - Check > 0
  - Check <= max value
  - Return true/false

**Dependencies:**
- None (pure validation logic)

---

### 30. FileUploadService
**Location:** `app/Services/Common/FileUploadService.php`  
**Responsibility:** File upload handling

**Public Methods:**
- `uploadImage(UploadedFile $file, string $destination): string`
  - Validate file type (JPG, PNG)
  - Validate file size (< 10 MB)
  - Generate random filename
  - Move file to $destination
  - Return file path

- `deleteFile(string $filePath): bool`
  - Delete file from disk
  - Return success

- `validateFileType(UploadedFile $file, array $allowedTypes): bool`
  - Check MIME type
  - Return true/false

- `validateFileSize(UploadedFile $file, int $maxSizeBytes): bool`
  - Check file size
  - Return true/false

**Dependencies:**
- CodeIgniter File library

---

## SERVICE INTERACTION FLOW

### Example: Create Invoice from Challans with Payment and Gold Adjustment

1. **InvoiceController.store()**
   - Receives request data
   - Calls InvoiceService.createInvoiceFromChallans()

2. **InvoiceService.createInvoiceFromChallans()**
   - Calls NumberingService.getNextInvoiceNumber()
   - Calls InvoiceCalculationService.calculateInvoiceTotals()
   - Calls TaxCalculationService (via InvoiceCalculationService)
   - Calls LedgerService.createInvoiceEntry()
   - Calls AuditService.logCreate()
   - Returns invoice ID

3. **PaymentController.store()**
   - Receives payment data + gold adjustment data
   - Calls PaymentService.recordPayment()

4. **PaymentService.recordPayment()**
   - Calls PaymentValidationService.validatePaymentData()
   - If gold adjustment:
     - Calls GoldAdjustmentService.applyGoldAdjustment()
   - Calls LedgerService.createPaymentEntry()
   - Calls AuditService.logCreate()
   - Returns payment ID

5. **GoldAdjustmentService.applyGoldAdjustment()**
   - Calls GoldRateService.getLatestRate()
   - Calls TaxCalculationService (to recalculate tax on adjusted amounts)
   - Calls LedgerService.createAdjustmentEntry()
   - Calls AuditService.logUpdate()
   - Returns success

**Result:**
- Invoice created with sequential number
- Ledger entry created (debit)
- Payment recorded
- Gold adjustment applied (if provided)
- Adjustment ledger entry created
- Payment ledger entry created (credit)
- All operations transactional (rollback on any failure)
- Complete audit trail

---

## TRANSACTION BOUNDARIES

### Critical Operations (Must Use Transactions)

1. **Create Invoice from Challans**
   - Create invoice
   - Copy lines
   - Mark challans as invoiced
   - Create ledger entry
   - **Single transaction**

2. **Record Payment with Gold Adjustment**
   - Apply gold adjustment (update invoice amounts, create adjustment ledger entry)
   - Record payment
   - Create payment ledger entry
   - Update invoice balances
   - **Single transaction**

3. **Delete Invoice**
   - Revert challan flags
   - Delete invoice and lines
   - Delete ledger entry
   - **Single transaction**

4. **Create Account with Opening Balance**
   - Create account
   - Create opening balance ledger entry
   - **Single transaction**

### Transaction Best Practices

- **Start transaction at service method level (not controller)**
- **Commit on success, rollback on any exception**
- **Use try-catch blocks**
- **Log errors before rollback**
- **Never nest transactions (CI4 doesn't support nested)**

---

## ERROR HANDLING STRATEGY

### Service Method Structure (Template)

```php
public function criticalOperation(array $data): int
{
    // 1. Validation
    $this->validate($data);

    // 2. Start Transaction
    $this->db->transStart();

    try {
        // 3. Business Logic
        $result = $this->performOperation($data);

        // 4. Audit Log
        $this->auditService->log(...);

        // 5. Commit Transaction
        $this->db->transComplete();

        // 6. Check Transaction Status
        if ($this->db->transStatus() === false) {
            throw new DatabaseException('Transaction failed');
        }

        // 7. Return Result
        return $result;

    } catch (Exception $e) {
        // 8. Rollback on Error
        $this->db->transRollback();

        // 9. Log Error
        log_message('error', $e->getMessage());

        // 10. Throw Custom Exception
        throw new ServiceException($e->getMessage());
    }
}
```

### Exception Types

- **ValidationException:** Validation failed
- **AuthorizationException:** Permission denied
- **BusinessRuleException:** Business rule violated
- **DatabaseException:** Database error
- **ServiceException:** General service error

---

## CACHING STRATEGY

### What to Cache

1. **Gold Rate (Latest)**
   - Cache key: `gold_rate_{company_id}_{metal_type}`
   - TTL: 1 hour
   - Invalidate on new rate entry

2. **User Permissions**
   - Cache key: `permissions_{user_id}`
   - TTL: Session lifetime
   - Invalidate on role change

3. **Company Settings**
   - Cache key: `company_settings_{company_id}`
   - TTL: 1 hour
   - Invalidate on settings update

4. **Active Products/Processes**
   - Cache key: `active_products_{company_id}`
   - TTL: 30 minutes
   - Invalidate on product create/update/delete

### Caching Implementation

- Use CodeIgniter Cache library
- Redis preferred (fallback to File cache)
- Cache service methods responsible for cache management
- Cache invalidation in update/delete methods

---

## TESTING STRATEGY

### Unit Tests (Service Layer)

- Test each public service method
- Mock dependencies
- Test success cases
- Test validation failures
- Test exception handling

### Integration Tests

- Test service interactions
- Test transaction rollback
- Test ledger balance accuracy
- Use test database

### Critical Tests

1. **Tax Calculation Tests**
   - Test tax-inclusive calculation
   - Test CGST/SGST vs IGST
   - Test rounding precision

2. **Gold Adjustment Tests**
   - Test positive adjustment
   - Test negative adjustment
   - Test multi-line adjustment
   - Test ledger entry creation

3. **Payment Flow Tests**
   - Test partial payment
   - Test full payment
   - Test payment with gold adjustment
   - Test invoice balance update

4. **Ledger Balance Tests**
   - Test opening balance
   - Test invoice debit
   - Test payment credit
   - Test running balance calculation
   - Test ledger vs invoice balance matching

5. **Concurrency Tests**
   - Test simultaneous invoice numbering
   - Test simultaneous challan numbering
   - Test race conditions

---

## PERFORMANCE CONSIDERATIONS

### Query Optimization

- **Use eager loading (with, join) to avoid N+1 queries**
- **Add indexes on frequently queried columns**
- **Paginate large result sets**
- **Use query result caching for heavy reports**

### Heavy Operations

1. **Monthly Receivable Summary**
   - Heavy aggregation query
   - Consider background job processing
   - Cache result for 1 hour

2. **Ledger Report**
   - Can be heavy for high-volume accounts
   - Paginate results
   - Add date range filter

### Background Jobs

- **Async report generation**
- **Email/SMS notifications**
- **Audit log batch writes**

---

## SUMMARY

**Total Services:** 30  
**Critical Services:** 10 (Invoice, Payment, Gold Adjustment, Ledger, Tax Calculation, Numbering)  
**Transaction-Safe Services:** 8  
**Append-Only Services:** 1 (LedgerService)  

**Service Layers:**
1. **Auth Layer:** AuthService, PermissionService
2. **Domain Services:** Company, User, Role, GoldRate, Product, Process, Account, CashCustomer
3. **Core Business Services:** Challan, Invoice, Payment, Delivery
4. **Calculation Services:** ChallanCalculation, InvoiceCalculation, TaxCalculation, GoldAdjustment
5. **Ledger Services:** LedgerService
6. **Report Services:** LedgerReport, Receivable, Outstanding, Dashboard
7. **Support Services:** Audit, Numbering, Validation, FileUpload

**All services follow:**
- Single Responsibility Principle
- Transaction safety for financial operations
- Comprehensive error handling
- Audit logging for critical operations
- Validation before processing
- Clear dependencies and testability


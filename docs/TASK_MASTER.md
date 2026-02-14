# DEVELOPMENT TASK MASTER FILE
## Gold Manufacturing & Billing ERP System

**Project Duration:** 20 weeks (Phase 1)  
**Team Size:** 3-4 developers  
**Methodology:** Agile (2-week sprints)

---

## TASK BREAKDOWN BY PHASE

### PHASE 1: Core Setup & Foundation (Weeks 1-4)

#### Task 1.1: Project Setup & Configuration
**Priority:** Critical  
**Estimated Time:** 8 hours  
**Assigned To:** Tech Lead

**Subtasks:**
- 1.1.1 Initialize CodeIgniter 4 project (2 hours)
- 1.1.2 Configure database connection (.env setup) (1 hour)
- 1.1.3 Setup Git repository and branching strategy (1 hour)
- 1.1.4 Configure development environment (Docker/XAMPP) (2 hours)
- 1.1.5 Setup PHPUnit for testing (1 hour)
- 1.1.6 Configure CI/CD pipeline basics (1 hour)

**Dependencies:** None  
**Acceptance Criteria:**
- CI4 project running on localhost
- Database connection successful
- Git repository initialized with .gitignore
- PHPUnit configured and sample test passing

---

#### Task 1.2: Database Migration - Core Tables
**Priority:** Critical  
**Estimated Time:** 16 hours  
**Assigned To:** Backend Developer

**Subtasks:**
- 1.2.1 Create migration: companies table (2 hours)
  - File: `app/Database/Migrations/2026-01-01-000001_create_companies_table.php`
  - Include all columns from schema
  - Add indexes and constraints
  - Test up() and down() methods

- 1.2.2 Create migration: states table (1 hour)
  - File: `app/Database/Migrations/2026-01-01-000002_create_states_table.php`
  - Master data table
  - Global scope (company_id = 0)

- 1.2.3 Create migration: roles table (2 hours)
  - File: `app/Database/Migrations/2026-01-01-000003_create_roles_table.php`
  - JSON permissions column
  - System role flag

- 1.2.4 Create migration: users table (2 hours)
  - File: `app/Database/Migrations/2026-01-01-000004_create_users_table.php`
  - Foreign key to companies
  - Authentication fields
  - Failed login tracking

- 1.2.5 Create migration: user_roles table (1 hour)
  - File: `app/Database/Migrations/2026-01-01-000005_create_user_roles_table.php`
  - Many-to-many relationship
  - Unique constraint on user_id + role_id

- 1.2.6 Test all migrations (2 hours)
  - Run php spark migrate
  - Verify table structure
  - Test rollback (php spark migrate:rollback)
  - Verify foreign keys working

- 1.2.7 Create StateSeeder (2 hours)
  - File: `app/Database/Seeds/StateSeeder.php`
  - Insert all Indian states
  - Test seeding

- 1.2.8 Create RoleSeeder (2 hours)
  - File: `app/Database/Seeds/RoleSeeder.php`
  - Insert predefined roles (Super Admin, Company Admin, etc.)
  - Define permissions array for each role
  - Test seeding

- 1.2.9 Create SuperAdminSeeder (2 hours)
  - File: `app/Database/Seeds/SuperAdminSeeder.php`
  - Create default super admin user
  - Hash password
  - Assign Super Admin role
  - Test login

**Dependencies:**
- Depends on: 1.1 (Project Setup)

**Acceptance Criteria:**
- All 5 tables created successfully
- Foreign keys working
- Seeders populate data correctly
- Can create test user and role
- Super admin can login

---

#### Task 1.3: Authentication System
**Priority:** Critical  
**Estimated Time:** 24 hours  
**Assigned To:** Backend Developer

**Subtasks:**
- 1.3.1 Create UserModel (3 hours)
  - File: `app/Models/UserModel.php`
  - Auto-filter by company_id
  - Validation rules
  - Password hashing in beforeInsert/beforeUpdate
  - Soft delete support

- 1.3.2 Create RoleModel (2 hours)
  - File: `app/Models/RoleModel.php`
  - JSON permissions handling
  - Methods: getPermissions(), hasPermission()

- 1.3.3 Create AuthService (6 hours)
  - File: `app/Services/Auth/AuthService.php`
  - Methods:
    - `login(string $username, string $password): array|false`
    - `logout(): bool`
    - `getCurrentUser(): ?User`
    - `validatePassword(string $password, string $hash): bool`
    - `handleFailedLogin(int $userId): void`
    - `resetFailedAttempts(int $userId): void`
  - Implement login rate limiting (5 attempts)
  - Session management
  - Remember me functionality

- 1.3.4 Create PermissionService (4 hours)
  - File: `app/Services/Auth/PermissionService.php`
  - Methods:
    - `getUserPermissions(int $userId): array`
    - `hasPermission(int $userId, string $permission): bool`
    - `can(string $permission): bool` (current user)
  - Cache permissions in session
  - Handle multiple roles (merge permissions)

- 1.3.5 Create LoginController (3 hours)
  - File: `app/Controllers/Auth/LoginController.php`
  - Routes:
    - GET /login → showLoginForm()
    - POST /login → authenticate()
  - Validate credentials
  - Set session
  - Redirect to dashboard

- 1.3.6 Create LogoutController (1 hour)
  - File: `app/Controllers/Auth/LogoutController.php`
  - Route: POST /logout → logout()
  - Clear session
  - Audit log logout action
  - Redirect to login

- 1.3.7 Create AuthFilter (3 hours)
  - File: `app/Filters/AuthFilter.php`
  - Check if user logged in
  - Redirect to login if not authenticated
  - Apply to all routes except login/public

- 1.3.8 Create PermissionFilter (2 hours)
  - File: `app/Filters/PermissionFilter.php`
  - Check user has required permission
  - Return 403 if unauthorized
  - Log unauthorized access attempts

**Dependencies:**
- Depends on: 1.2 (Database migrations, seeders)

**Acceptance Criteria:**
- User can login with valid credentials
- Failed login attempts tracked and locked after 5 attempts
- User can logout
- Session persists across requests
- AuthFilter blocks unauthenticated access
- PermissionFilter blocks unauthorized access

---

#### Task 1.4: Multi-Tenant Setup (Company Filter)
**Priority:** Critical  
**Estimated Time:** 12 hours  
**Assigned To:** Backend Developer

**Subtasks:**
- 1.4.1 Create CompanyModel (3 hours)
  - File: `app/Models/CompanyModel.php`
  - CRUD methods
  - Validation rules
  - Soft delete
  - Methods:
    - `getActiveCompanies()`
    - `incrementInvoiceNumber(int $companyId): int`
    - `incrementChallanNumber(int $companyId): int`

- 1.4.2 Create CompanyService (4 hours)
  - File: `app/Services/Company/CompanyService.php`
  - Methods:
    - `createCompany(array $data): int`
    - `updateCompany(int $id, array $data): bool`
    - `getCompanyById(int $id): ?Company`
    - `validateGSTNumber(string $gst): bool`
    - `validatePANNumber(string $pan): bool`

- 1.4.3 Create CompanyFilter (3 hours)
  - File: `app/Filters/CompanyFilter.php`
  - Inject company_id into all DB queries
  - Use BaseModel method to auto-filter
  - Exception for Super Admin (can access all companies)

- 1.4.4 Modify BaseModel (2 hours)
  - Override find(), findAll() methods
  - Auto-add where('company_id', session company_id)
  - Global scope for multi-tenancy

**Dependencies:**
- Depends on: 1.2, 1.3

**Acceptance Criteria:**
- Company can be created
- All queries automatically filter by company_id
- Super admin can switch company context
- Users see only their company data

---

#### Task 1.5: User & Role Management
**Priority:** High  
**Estimated Time:** 16 hours  
**Assigned To:** Backend Developer

**Subtasks:**
- 1.5.1 Create UserService (4 hours)
  - File: `app/Services/User/UserService.php`
  - Methods:
    - `createUser(array $data): int`
    - `updateUser(int $id, array $data): bool`
    - `deleteUser(int $id): bool` (soft delete)
    - `assignRoles(int $userId, array $roleIds): bool`
    - `getUserRoles(int $userId): array`

- 1.5.2 Create RoleService (3 hours)
  - File: `app/Services/User/RoleService.php`
  - Methods:
    - `createRole(array $data): int`
    - `updateRole(int $id, array $data): bool`
    - `deleteRole(int $id): bool`
    - `getRolePermissions(int $roleId): array`
    - `updatePermissions(int $roleId, array $permissions): bool`

- 1.5.3 Create UserController (4 hours)
  - File: `app/Controllers/Users/UserController.php`
  - Routes:
    - GET /users → index() (list)
    - GET /users/create → create() (form)
    - POST /users → store()
    - GET /users/{id}/edit → edit()
    - POST /users/{id} → update()
    - DELETE /users/{id} → delete()

- 1.5.4 Create RoleController (3 hours)
  - File: `app/Controllers/Users/RoleController.php`
  - Similar CRUD routes as UserController
  - Permission management UI

- 1.5.5 Create user validation rules (2 hours)
  - File: `app/Validation/UserRules.php`
  - Custom rules:
    - Unique username
    - Unique email
    - Password complexity
    - Mobile number format

**Dependencies:**
- Depends on: 1.2, 1.3, 1.4

**Acceptance Criteria:**
- Company admin can create users
- Users can be assigned multiple roles
- Permissions are additive (union of all role permissions)
- Cannot delete user with transactions
- Password complexity enforced

---

### PHASE 2: Master Data Management (Weeks 5-6)

#### Task 2.1: Gold Rate Management
**Priority:** High  
**Estimated Time:** 10 hours  
**Assigned To:** Backend Developer

**Subtasks:**
- 2.1.1 Create gold_rates migration (2 hours)
  - File: `app/Database/Migrations/2026-01-01-000006_create_gold_rates_table.php`
  - Unique constraint: company_id + rate_date + metal_type

- 2.1.2 Create GoldRateModel (2 hours)
  - File: `app/Models/GoldRateModel.php`
  - Methods:
    - `getLatestRate(int $companyId, string $metalType): ?float`
    - `getRateByDate(int $companyId, Date $date, string $metalType): ?float`
    - `getRateHistory(int $companyId, Date $fromDate, Date $toDate): array`

- 2.1.3 Create GoldRateService (3 hours)
  - File: `app/Services/Master/GoldRateService.php`
  - Methods:
    - `createRate(array $data): int`
    - `updateRate(int $id, array $data): bool`
    - `getLatestRate(string $metalType = '22K'): ?float`
    - `checkIfTodayRateEntered(): bool`
    - `sendRateAlert(): void` (if rate not entered)

- 2.1.4 Create GoldRateController (3 hours)
  - File: `app/Controllers/Masters/GoldRateController.php`
  - CRUD routes
  - Today's rate entry form
  - Rate history view

**Dependencies:**
- Depends on: Phase 1

**Acceptance Criteria:**
- Gold rate can be entered daily
- Multiple rates per day (updates override)
- Latest rate retrieved correctly
- Alert shown if today's rate missing

---

#### Task 2.2: Product & Process Management
**Priority:** High  
**Estimated Time:** 18 hours  
**Assigned To:** Backend Developer

**Subtasks:**
- 2.2.1 Create product_categories migration (1 hour)
  - File: `app/Database/Migrations/2026-01-01-000007_create_product_categories_table.php`

- 2.2.2 Create products migration (2 hours)
  - File: `app/Database/Migrations/2026-01-01-000008_create_products_table.php`
  - Foreign key to categories

- 2.2.3 Create processes migration (2 hours)
  - File: `app/Database/Migrations/2026-01-01-000009_create_processes_table.php`
  - Price per unit column

- 2.2.4 Create ProductModel (2 hours)
  - File: `app/Models/ProductModel.php`
  - Soft delete
  - Image upload handling

- 2.2.5 Create ProcessModel (2 hours)
  - File: `app/Models/ProcessModel.php`
  - Price history tracking

- 2.2.6 Create ProductService (3 hours)
  - File: `app/Services/Master/ProductService.php`
  - CRUD methods
  - Image upload/delete
  - Cannot delete if used in challan

- 2.2.7 Create ProcessService (3 hours)
  - File: `app/Services/Master/ProcessService.php`
  - CRUD methods
  - Calculate rate from multiple processes

- 2.2.8 Create ProductController (2 hours)
  - File: `app/Controllers/Masters/ProductController.php`

- 2.2.9 Create ProcessController (2 hours)
  - File: `app/Controllers/Masters/ProcessController.php`

**Dependencies:**
- Depends on: Phase 1

**Acceptance Criteria:**
- Products and processes can be created
- Products organized by categories
- Process prices stored per unit
- Cannot delete if used in transactions
- Inactive items hidden in dropdowns

---

#### Task 2.3: Account & Cash Customer Management
**Priority:** High  
**Estimated Time:** 20 hours  
**Assigned To:** Backend Developer

**Subtasks:**
- 2.3.1 Create accounts migration (3 hours)
  - File: `app/Database/Migrations/2026-01-01-000010_create_accounts_table.php`
  - Opening balance
  - Billing and shipping address
  - GST, PAN fields

- 2.3.2 Create cash_customers migration (2 hours)
  - File: `app/Database/Migrations/2026-01-01-000011_create_cash_customers_table.php`
  - Unique: company_id + name + mobile

- 2.3.3 Create AccountModel (3 hours)
  - File: `app/Models/AccountModel.php`
  - Auto-generate account code
  - Opening balance ledger entry on creation

- 2.3.4 Create CashCustomerModel (2 hours)
  - File: `app/Models/CashCustomerModel.php`
  - Deduplication logic

- 2.3.5 Create AccountService (4 hours)
  - File: `app/Services/Customer/AccountService.php`
  - Methods:
    - `createAccount(array $data): int`
    - `updateAccount(int $id, array $data): bool`
    - `getLedgerBalance(int $accountId): float`
    - `createOpeningBalanceLedgerEntry(int $accountId): void`

- 2.3.6 Create CashCustomerService (3 hours)
  - File: `app/Services/Customer/CashCustomerService.php`
  - Methods:
    - `findOrCreate(string $name, string $mobile): int`
    - `search(string $query): array` (autocomplete)
    - `mergeDuplicates(int $primaryId, int $secondaryId): bool`

- 2.3.7 Create AccountController (2 hours)
  - File: `app/Controllers/Customers/AccountController.php`

- 2.3.8 Create CashCustomerController (1 hour)
  - File: `app/Controllers/Customers/CashCustomerController.php`

**Dependencies:**
- Depends on: Phase 1, Task 2.1

**Acceptance Criteria:**
- Account customers can be created with opening balance
- Opening balance creates ledger entry
- Cash customers deduplicated on name+mobile
- Cash customer autocomplete works
- Cannot delete account with transactions

---

### PHASE 3: Challan Management (Weeks 7-9)

#### Task 3.1: Challan Database & Models
**Priority:** Critical  
**Estimated Time:** 12 hours  
**Assigned To:** Backend Developer

**Subtasks:**
- 3.1.1 Create challans migration (3 hours)
  - File: `app/Database/Migrations/2026-01-01-000012_create_challans_table.php`
  - Status enum
  - Invoice generated flag
  - Foreign keys to accounts and cash_customers

- 3.1.2 Create challan_lines migration (3 hours)
  - File: `app/Database/Migrations/2026-01-01-000013_create_challan_lines_table.php`
  - JSON columns for product_ids, process_ids
  - Gold weight fields

- 3.1.3 Create ChallanModel (3 hours)
  - File: `app/Models/ChallanModel.php`
  - Relationships: lines, account, customer
  - Status validation

- 3.1.4 Create ChallanLineModel (3 hours)
  - File: `app/Models/ChallanLineModel.php`
  - JSON handling for products and processes

**Dependencies:**
- Depends on: Phase 1, Phase 2

**Acceptance Criteria:**
- Challan tables created
- Relationships working
- JSON columns storing arrays correctly

---

#### Task 3.2: Challan Calculation Service
**Priority:** Critical  
**Estimated Time:** 10 hours  
**Assigned To:** Backend Developer

**Subtasks:**
- 3.2.1 Create ChallanCalculationService (6 hours)
  - File: `app/Services/Challan/ChallanCalculationService.php`
  - Methods:
    - `calculateLineRate(array $processIds): float`
    - `calculateLineAmount(float $rate, float $weight): float`
    - `calculateWaxAmount(float $weight, float $accountPrice, float $minPrice): float`
    - `calculateChallanTotal(int $challanId): float`

- 3.2.2 Test calculation logic (4 hours)
  - Unit tests for each calculation method
  - Edge cases: zero weight, zero rate, multiple processes
  - Wax minimum price logic

**Dependencies:**
- Depends on: 3.1

**Acceptance Criteria:**
- Rate calculation: SUM of process prices
- Amount calculation: weight × rate (or rate if weight = 0)
- Wax amount: MAX(weight × price, minPrice)
- All calculations accurate to 2 decimal places

---

#### Task 3.3: Challan Service (Core Logic)
**Priority:** Critical  
**Estimated Time:** 20 hours  
**Assigned To:** Backend Developer

**Subtasks:**
- 3.3.1 Create ChallanService (12 hours)
  - File: `app/Services/Challan/ChallanService.php`
  - Methods:
    - `createChallan(array $data): int`
      - Generate challan number (concurrency-safe)
      - Create header record
      - Create line items
      - Calculate totals
      - Wrap in transaction
    - `updateChallan(int $id, array $data): bool`
      - Check if editable (status, invoice_generated)
      - Update header and lines
      - Recalculate totals
      - Audit log
    - `deleteChallan(int $id): bool`
      - Check if deletable
      - Soft delete
      - Audit log
    - `submitForApproval(int $id): bool`
      - Change status to Submitted
    - `approveChallan(int $id): bool`
      - Check permission
      - Change status to Approved
    - `cancelChallan(int $id): bool`
      - Check if cancellable
      - Change status to Cancelled

- 3.3.2 Create ChallanValidationService (4 hours)
  - File: `app/Services/Challan/ChallanValidationService.php`
  - Methods:
    - `validateChallanData(array $data): array|bool`
    - `canEdit(int $challanId): bool`
    - `canDelete(int $challanId): bool`
    - `canApprove(int $challanId): bool`

- 3.3.3 Create NumberingService (4 hours)
  - File: `app/Services/Common/NumberingService.php`
  - Methods:
    - `getNextChallanNumber(int $companyId): string`
    - `getNextInvoiceNumber(int $companyId): string`
  - Use DB transaction with row lock
  - Format: {prefix}{padded_number}

**Dependencies:**
- Depends on: 3.1, 3.2

**Acceptance Criteria:**
- Challan can be created with multiple lines
- Challan number sequential and gap-free
- Totals calculated correctly
- Status workflow enforced
- Cannot edit after invoice generated
- All operations transactional

---

#### Task 3.4: Challan Controllers
**Priority:** High  
**Estimated Time:** 12 hours  
**Assigned To:** Backend Developer

**Subtasks:**
- 3.4.1 Create ChallanController (8 hours)
  - File: `app/Controllers/Challans/ChallanController.php`
  - Routes:
    - GET /challans → index()
    - GET /challans/create → create()
    - POST /challans → store()
    - GET /challans/{id} → show()
    - GET /challans/{id}/edit → edit()
    - POST /challans/{id} → update()
    - DELETE /challans/{id} → delete()
    - POST /challans/{id}/submit → submit()
    - POST /challans/{id}/approve → approve()
    - POST /challans/{id}/cancel → cancel()
    - GET /challans/{id}/print → print()

- 3.4.2 Handle file uploads (images) (2 hours)
  - Use FileUploadService
  - Store in public/uploads/challans/
  - Validate file type and size

- 3.4.3 Create ChallanRules validation (2 hours)
  - File: `app/Validation/ChallanRules.php`
  - Custom validation rules

**Dependencies:**
- Depends on: 3.3

**Acceptance Criteria:**
- All CRUD operations working
- Status transitions working
- File uploads working
- Validation enforced
- Permission checks working

---

### PHASE 4: Invoice Management (Weeks 10-12)

#### Task 4.1: Invoice Database & Models
**Priority:** Critical  
**Estimated Time:** 12 hours  
**Assigned To:** Backend Developer

**Subtasks:**
- 4.1.1 Create invoices migration (4 hours)
  - File: `app/Database/Migrations/2026-01-01-000014_create_invoices_table.php`
  - Tax fields (CGST, SGST, IGST)
  - Payment tracking fields
  - Gold adjustment fields

- 4.1.2 Create invoice_lines migration (3 hours)
  - File: `app/Database/Migrations/2026-01-01-000015_create_invoice_lines_table.php`
  - Link to challan lines
  - Gold adjustment per line

- 4.1.3 Create InvoiceModel (3 hours)
  - File: `app/Models/InvoiceModel.php`
  - Relationships: lines, account, customer, payments

- 4.1.4 Create InvoiceLineModel (2 hours)
  - File: `app/Models/InvoiceLineModel.php`

**Dependencies:**
- Depends on: Phase 3

**Acceptance Criteria:**
- Invoice tables created
- Relationships working
- Foreign keys to challans

---

#### Task 4.2: Tax Calculation Service
**Priority:** Critical  
**Estimated Time:** 12 hours  
**Assigned To:** Backend Developer

**Subtasks:**
- 4.2.1 Create TaxCalculationService (8 hours)
  - File: `app/Services/Invoice/TaxCalculationService.php`
  - Methods:
    - `calculateTaxFromInclusive(float $amount, float $taxRate): array`
      - Returns: ['subtotal', 'tax_amount']
      - Formula: tax = amount × rate / (100 + rate)
    - `calculateGST(float $taxAmount, string $companyState, string $customerState): array`
      - Returns: ['cgst', 'sgst', 'igst']
      - Same state: CGST = SGST = tax/2, IGST = 0
      - Different state: IGST = tax, CGST = SGST = 0
    - `calculateInvoiceTax(array $lines, float $taxRate, string $companyState, string $customerState): array`
      - Aggregate for all lines

- 4.2.2 Test tax calculation (4 hours)
  - Unit tests for all scenarios
  - Intra-state vs inter-state
  - Multiple line items
  - Rounding precision

**Dependencies:**
- Depends on: 4.1

**Acceptance Criteria:**
- Tax-inclusive calculation correct
- CGST/SGST split correctly
- IGST applied for inter-state
- Rounding to 2 decimals

---

#### Task 4.3: Invoice Calculation Service
**Priority:** Critical  
**Estimated Time:** 10 hours  
**Assigned To:** Backend Developer

**Subtasks:**
- 4.3.1 Create InvoiceCalculationService (6 hours)
  - File: `app/Services/Invoice/InvoiceCalculationService.php`
  - Methods:
    - `calculateLineAmount(float $rate, float $weight): float`
    - `calculateInvoiceSubtotal(array $lines): float`
    - `calculateInvoiceTotals(int $invoiceId): array`
      - Returns: ['subtotal', 'tax_amount', 'cgst', 'sgst', 'igst', 'grand_total']

- 4.3.2 Test calculation logic (4 hours)
  - Unit tests

**Dependencies:**
- Depends on: 4.2

**Acceptance Criteria:**
- All calculations accurate
- Integration with TaxCalculationService

---

#### Task 4.4: Invoice Service (Core Logic)
**Priority:** Critical  
**Estimated Time:** 24 hours  
**Assigned To:** Backend Developer

**Subtasks:**
- 4.4.1 Create InvoiceService (16 hours)
  - File: `app/Services/Invoice/InvoiceService.php`
  - Methods:
    - `createInvoiceFromChallans(array $challanIds, array $invoiceData): int`
      - Validate challans (approved, not invoiced)
      - Copy lines from challans
      - Calculate tax and totals
      - Generate invoice number (concurrency-safe)
      - Mark challans as invoiced
      - Create ledger entry (debit)
      - Wrap in transaction
    - `createCashInvoice(array $data): int`
      - Manual line entry
      - Find or create cash customer
      - Calculate tax and totals
      - Generate invoice number
      - Create ledger entry
      - Wrap in transaction
    - `updateInvoice(int $id, array $data): bool`
      - Check if editable (no payments)
      - Update and recalculate
    - `deleteInvoice(int $id): bool`
      - Check if deletable
      - Revert challan invoice_generated flags
      - Delete ledger entry
      - Soft delete invoice
      - Wrap in transaction
    - `postInvoice(int $id): bool`
      - Change status to Posted

- 4.4.2 Create InvoiceValidationService (4 hours)
  - File: `app/Services/Invoice/InvoiceValidationService.php`
  - Validate invoice data
  - Check challan availability
  - Check if editable/deletable

- 4.4.3 Integrate with LedgerService (4 hours)
  - Create ledger entry on invoice creation
  - Debit entry (increases customer balance)

**Dependencies:**
- Depends on: 4.1, 4.2, 4.3

**Acceptance Criteria:**
- Invoice created from challans
- Cash invoice created manually
- Invoice number sequential
- Ledger entry created
- Challans marked as invoiced
- All operations transactional
- Cannot edit after payment

---

#### Task 4.5: Invoice Controllers
**Priority:** High  
**Estimated Time:** 12 hours  
**Assigned To:** Backend Developer

**Subtasks:**
- 4.5.1 Create InvoiceController (8 hours)
  - File: `app/Controllers/Invoices/InvoiceController.php`
  - CRUD routes
  - Challan selection UI
  - Print/export functionality

- 4.5.2 Create invoice validation rules (2 hours)
  - File: `app/Validation/InvoiceRules.php`

- 4.5.3 Test invoice workflow (2 hours)
  - End-to-end: Challan → Approve → Invoice → Posted

**Dependencies:**
- Depends on: 4.4

**Acceptance Criteria:**
- Invoice can be created from multiple challans
- Cash invoice can be created manually
- Print PDF working
- Status workflow enforced

---

### PHASE 5: Payment & Gold Adjustment (Weeks 13-14)

#### Task 5.1: Payment Database & Models
**Priority:** Critical  
**Estimated Time:** 8 hours  
**Assigned To:** Backend Developer

**Subtasks:**
- 5.1.1 Create payments migration (3 hours)
  - File: `app/Database/Migrations/2026-01-01-000016_create_payments_table.php`
  - Payment mode enum
  - Cheque/bank details

- 5.1.2 Create PaymentModel (3 hours)
  - File: `app/Models/PaymentModel.php`
  - Relationships: invoice

- 5.1.3 Test payment queries (2 hours)

**Dependencies:**
- Depends on: Phase 4

**Acceptance Criteria:**
- Payment table created
- Relationships working

---

#### Task 5.2: Gold Adjustment Service
**Priority:** Critical  
**Estimated Time:** 16 hours  
**Assigned To:** Backend Developer

**Subtasks:**
- 5.2.1 Create GoldAdjustmentService (12 hours)
  - File: `app/Services/Payment/GoldAdjustmentService.php`
  - Methods:
    - `calculateAdjustment(int $invoiceId, array $lineAdjustments, float $goldRate): array`
      - For each line:
        - gold_difference = new_weight - original_weight
        - adjustment_amount = gold_difference × gold_rate
        - adjusted_line_amount = original_amount + adjustment_amount
      - Aggregate:
        - Total adjustment amount
        - Adjusted grand total
      - Returns: ['line_adjustments' => [], 'total_adjustment' => float, 'adjusted_grand_total' => float]
    - `applyGoldAdjustment(int $invoiceId, array $lineAdjustments, float $goldRate): bool`
      - Update invoice line weights and amounts
      - Recalculate invoice totals
      - Update invoice.gold_adjustment_applied = true
      - Update invoice.gold_adjustment_amount
      - Update invoice.gold_rate_used
      - Create ledger entry for adjustment (debit or credit)
      - Wrap in transaction
    - `validateAdjustment(int $invoiceId, array $lineAdjustments): bool`
      - Check invoice not already adjusted
      - Check gold rate available
      - Validate new weights

- 5.2.2 Test gold adjustment calculations (4 hours)
  - Positive adjustment (customer owes more)
  - Negative adjustment (customer owes less)
  - Multiple lines
  - Edge cases

**Dependencies:**
- Depends on: 5.1, Task 2.1 (Gold Rate)

**Acceptance Criteria:**
- Gold adjustment calculated correctly
- Invoice amounts updated
- Ledger entry created
- Can only apply once per invoice
- Cannot apply without gold rate

---

#### Task 5.3: Payment Service
**Priority:** Critical  
**Estimated Time:** 16 hours  
**Assigned To:** Backend Developer

**Subtasks:**
- 5.3.1 Create PaymentService (12 hours)
  - File: `app/Services/Payment/PaymentService.php`
  - Methods:
    - `recordPayment(int $invoiceId, array $paymentData, array $goldAdjustment = null): int`
      - Validate payment amount <= invoice.amount_due
      - If gold adjustment provided:
        - Call GoldAdjustmentService.applyGoldAdjustment()
        - Update invoice totals
        - Create adjustment ledger entry
      - Create payment record
      - Update invoice.total_paid
      - Update invoice.amount_due
      - Update invoice payment_status (Pending/Partial Paid/Paid)
      - Update invoice.invoice_status (Partially Paid/Paid)
      - Create payment ledger entry (credit)
      - Audit log
      - Wrap entire operation in transaction
    - `deletePayment(int $id): bool`
      - Check if allowed (admin only, before delivery)
      - Reverse payment amounts
      - Delete ledger entry
      - Soft delete payment
      - Wrap in transaction
    - `getPaymentHistory(int $invoiceId): array`

- 5.3.2 Create PaymentValidationService (4 hours)
  - File: `app/Services/Payment/PaymentValidationService.php`
  - Validate payment data
  - Check payment amount
  - Validate cheque/bank details

**Dependencies:**
- Depends on: 5.2

**Acceptance Criteria:**
- Payment recorded correctly
- Invoice balances updated
- Ledger entries created (payment + adjustment)
- Gold adjustment applied atomically with payment
- All operations transactional
- Cannot exceed amount due

---

#### Task 5.4: Payment Controller
**Priority:** High  
**Estimated Time:** 10 hours  
**Assigned To:** Backend Developer

**Subtasks:**
- 5.4.1 Create PaymentController (6 hours)
  - File: `app/Controllers/Payments/PaymentController.php`
  - Routes:
    - GET /invoices/{id}/payment → show payment form
    - POST /invoices/{id}/payment → record payment
    - GET /payments/{id} → view payment details
    - DELETE /payments/{id} → delete payment (admin)

- 5.4.2 Create payment UI with gold adjustment (4 hours)
  - Show invoice details
  - Show line items with original gold weights
  - Input fields for new gold weights
  - Calculate and display adjustment preview
  - Confirm and record payment

**Dependencies:**
- Depends on: 5.3

**Acceptance Criteria:**
- Payment form shows invoice details
- Gold adjustment optional
- Adjustment preview shown before confirmation
- Payment recorded with adjustment

---

### PHASE 6: Ledger Management (Week 15)

#### Task 6.1: Ledger Database & Model
**Priority:** Critical  
**Estimated Time:** 8 hours  
**Assigned To:** Backend Developer

**Subtasks:**
- 6.1.1 Create ledger_entries migration (3 hours)
  - File: `app/Database/Migrations/2026-01-01-000017_create_ledger_entries_table.php`
  - Append-only (no updates/deletes)
  - Balance_after column

- 6.1.2 Create LedgerEntryModel (3 hours)
  - File: `app/Models/LedgerEntryModel.php`
  - Read-only (no insert/update via model)
  - Query methods for reports

- 6.1.3 Test ledger queries (2 hours)

**Dependencies:**
- Depends on: Phase 5

**Acceptance Criteria:**
- Ledger table created
- Append-only enforced

---

#### Task 6.2: Ledger Service
**Priority:** Critical  
**Estimated Time:** 16 hours  
**Assigned To:** Backend Developer

**Subtasks:**
- 6.2.1 Create LedgerService (12 hours)
  - File: `app/Services/Ledger/LedgerService.php`
  - Methods:
    - `createEntry(array $data): int`
      - Calculate balance_after
      - Insert entry (append-only)
      - Never update or delete
    - `getLastBalance(int $accountId = null, int $cashCustomerId = null): float`
    - `createOpeningBalanceEntry(int $accountId, float $amount, Date $date): int`
    - `createInvoiceEntry(int $invoiceId): int`
      - Debit entry
    - `createPaymentEntry(int $paymentId): int`
      - Credit entry
    - `createAdjustmentEntry(int $invoiceId, float $amount, string $description): int`
      - Debit or credit based on amount sign

- 6.2.2 Integrate ledger creation in services (4 hours)
  - InvoiceService: Call LedgerService.createInvoiceEntry()
  - PaymentService: Call LedgerService.createPaymentEntry()
  - GoldAdjustmentService: Call LedgerService.createAdjustmentEntry()
  - AccountService: Call LedgerService.createOpeningBalanceEntry()

**Dependencies:**
- Depends on: 6.1

**Acceptance Criteria:**
- Ledger entries created for all transactions
- Balance calculated correctly
- Entries immutable
- Sequential by date

---

### PHASE 7: Delivery Management (Week 16)

#### Task 7.1: Delivery Database, Model, Service
**Priority:** High  
**Estimated Time:** 12 hours  
**Assigned To:** Backend Developer

**Subtasks:**
- 7.1.1 Create deliveries migration (3 hours)
  - File: `app/Database/Migrations/2026-01-01-000018_create_deliveries_table.php`

- 7.1.2 Create DeliveryModel (2 hours)
  - File: `app/Models/DeliveryModel.php`

- 7.1.3 Create DeliveryService (5 hours)
  - File: `app/Services/Delivery/DeliveryService.php`
  - Methods:
    - `assignDelivery(int $invoiceId, int $userId, Date $expectedDate): int`
    - `markDelivered(int $deliveryId, string $proofPhoto): bool`
      - Upload proof photo
      - Update delivery status
      - Update invoice status to Delivered
      - Audit log
    - `markFailed(int $deliveryId, string $reason): bool`
    - `getMyDeliveries(int $userId): array`

- 7.1.4 Test delivery workflow (2 hours)

**Dependencies:**
- Depends on: Phase 5

**Acceptance Criteria:**
- Delivery can be assigned
- Delivery marked as delivered with proof
- Invoice status updated
- Delivery user sees only assigned deliveries

---

#### Task 7.2: Delivery Controller
**Priority:** High  
**Estimated Time:** 8 hours  
**Assigned To:** Backend Developer

**Subtasks:**
- 7.2.1 Create DeliveryController (6 hours)
  - File: `app/Controllers/Deliveries/DeliveryController.php`
  - Routes:
    - GET /deliveries → index (my deliveries if delivery user)
    - POST /invoices/{id}/assign-delivery → assign
    - POST /deliveries/{id}/mark-delivered → mark delivered
    - POST /deliveries/{id}/mark-failed → mark failed

- 7.2.2 File upload for proof photo (2 hours)

**Dependencies:**
- Depends on: 7.1

**Acceptance Criteria:**
- Delivery assignment working
- Proof photo upload working
- Permissions enforced

---

### PHASE 8: Reporting (Weeks 17-18)

#### Task 8.1: Ledger Reports
**Priority:** High  
**Estimated Time:** 16 hours  
**Assigned To:** Backend Developer

**Subtasks:**
- 8.1.1 Create LedgerReportService (8 hours)
  - File: `app/Services/Report/LedgerReportService.php`
  - Methods:
    - `generateAccountLedger(int $accountId, Date $fromDate, Date $toDate): array`
      - Calculate opening balance
      - Get all ledger entries in date range
      - Calculate running balance
      - Return: ['opening_balance', 'entries' => [], 'closing_balance']
    - `generateCashCustomerLedger(int $cashCustomerId, Date $fromDate, Date $toDate): array`
      - Same logic as account ledger
    - `exportLedgerToPDF(array $data): string`
    - `exportLedgerToExcel(array $data): string`

- 8.1.2 Create LedgerReportController (4 hours)
  - File: `app/Controllers/Reports/LedgerReportController.php`
  - Routes:
    - GET /reports/ledger/account/{id} → account ledger
    - GET /reports/ledger/cash/{id} → cash customer ledger
    - POST /reports/ledger/export → export

- 8.1.3 Test ledger reports (4 hours)
  - Test balance calculations
  - Test date range filtering
  - Test export formats

**Dependencies:**
- Depends on: Phase 6

**Acceptance Criteria:**
- Ledger report accurate
- Opening/closing balance correct
- Running balance calculated correctly
- Export to PDF/Excel working

---

#### Task 8.2: Receivable & Outstanding Reports
**Priority:** High  
**Estimated Time:** 16 hours  
**Assigned To:** Backend Developer

**Subtasks:**
- 8.2.1 Create ReceivableReportService (6 hours)
  - File: `app/Services/Report/ReceivableReportService.php`
  - Methods:
    - `generateMonthlyReceivableSummary(Date $fromDate, Date $toDate): array`
      - For each customer (account + cash):
        - Opening balance
        - Month-wise debits, credits, closing balance
      - Heavy query - optimize with indexes

- 8.2.2 Create OutstandingReportService (4 hours)
  - File: `app/Services/Report/OutstandingReportService.php`
  - Methods:
    - `getOutstandingInvoices(filters): array`
      - List all invoices where amount_due > 0
      - Calculate days overdue
      - Filter by customer, date range

- 8.2.3 Create report controllers (4 hours)
  - ReceivableReportController
  - OutstandingReportController

- 8.2.4 Test reports (2 hours)

**Dependencies:**
- Depends on: Phase 6

**Acceptance Criteria:**
- Monthly receivable summary accurate
- Outstanding invoices listed correctly
- Overdue days calculated
- Export working

---

#### Task 8.3: Dashboard
**Priority:** High  
**Estimated Time:** 12 hours  
**Assigned To:** Backend Developer

**Subtasks:**
- 8.3.1 Create DashboardService (6 hours)
  - File: `app/Services/Report/DashboardService.php`
  - Methods:
    - `getTodaySummary(): array` (invoices, payments)
    - `getOutstandingSummary(): array` (total receivables)
    - `getTopCustomers(int $limit = 10): array`
    - `getPaymentCollectionTrend(int $days = 30): array`

- 8.3.2 Create DashboardController (4 hours)
  - File: `app/Controllers/Reports/DashboardController.php`
  - Display widgets and charts

- 8.3.3 Test dashboard (2 hours)

**Dependencies:**
- Depends on: Phase 6

**Acceptance Criteria:**
- Dashboard shows real-time KPIs
- Charts displaying correctly
- Performance acceptable (<2 seconds load)

---

### PHASE 9: Audit & Settings (Week 19)

#### Task 9.1: Audit Logging
**Priority:** High  
**Estimated Time:** 12 hours  
**Assigned To:** Backend Developer

**Subtasks:**
- 9.1.1 Create audit_logs migration (2 hours)
  - File: `app/Database/Migrations/2026-01-01-000019_create_audit_logs_table.php`

- 9.1.2 Create AuditLogModel (2 hours)
  - File: `app/Models/AuditLogModel.php`

- 9.1.3 Create AuditService (6 hours)
  - File: `app/Services/Audit/AuditService.php`
  - Methods:
    - `log(string $module, string $action, array $beforeData, array $afterData): int`
    - Capture IP, user agent
    - Store JSON snapshots

- 9.1.4 Integrate audit logging (2 hours)
  - Call AuditService in all critical operations
  - Invoice create/edit/delete
  - Payment create/delete
  - Gold adjustment
  - Settings changes

**Dependencies:**
- Depends on: All previous phases

**Acceptance Criteria:**
- All critical actions logged
- Audit logs immutable
- Admin can view audit trail

---

#### Task 9.2: Company Settings
**Priority:** Medium  
**Estimated Time:** 8 hours  
**Assigned To:** Backend Developer

**Subtasks:**
- 9.2.1 Create company_settings migration (2 hours)
  - File: `app/Database/Migrations/2026-01-01-000020_create_company_settings_table.php`

- 9.2.2 Create CompanySettingModel (2 hours)
  - File: `app/Models/CompanySettingModel.php`

- 9.2.3 Create settings management (4 hours)
  - CompanySettingsController
  - UI for editing settings
  - Validation

**Dependencies:**
- Depends on: Phase 1

**Acceptance Criteria:**
- Company admin can edit settings
- Settings saved per company
- Changes affect future transactions

---

### PHASE 10: Testing & Optimization (Week 20)

#### Task 10.1: Unit Testing
**Priority:** High  
**Estimated Time:** 20 hours  
**Assigned To:** QA Engineer + Developers

**Subtasks:**
- 10.1.1 Test InvoiceService (4 hours)
- 10.1.2 Test PaymentService (4 hours)
- 10.1.3 Test GoldAdjustmentService (4 hours)
- 10.1.4 Test TaxCalculationService (3 hours)
- 10.1.5 Test LedgerService (3 hours)
- 10.1.6 Test ChallanService (2 hours)

**Dependencies:**
- Depends on: All previous phases

**Acceptance Criteria:**
- 80%+ code coverage
- All critical services tested
- Edge cases covered

---

#### Task 10.2: Integration Testing
**Priority:** High  
**Estimated Time:** 16 hours  
**Assigned To:** QA Engineer

**Subtasks:**
- 10.2.1 Test challan to invoice flow (4 hours)
- 10.2.2 Test payment with gold adjustment (4 hours)
- 10.2.3 Test ledger balance accuracy (4 hours)
- 10.2.4 Test delivery flow (2 hours)
- 10.2.5 Test multi-tenant isolation (2 hours)

**Dependencies:**
- Depends on: 10.1

**Acceptance Criteria:**
- All workflows tested end-to-end
- No data leakage between companies
- Ledger balances match invoice/payment totals

---

#### Task 10.3: Performance Optimization
**Priority:** Medium  
**Estimated Time:** 12 hours  
**Assigned To:** Tech Lead

**Subtasks:**
- 10.3.1 Optimize database queries (4 hours)
  - Add missing indexes
  - Optimize N+1 queries
- 10.3.2 Implement caching (4 hours)
  - Cache gold rates
  - Cache permissions
- 10.3.3 Load testing (4 hours)
  - Test with 10,000 invoices
  - Test concurrent challan numbering

**Dependencies:**
- Depends on: 10.2

**Acceptance Criteria:**
- All pages load <2 seconds
- Concurrent operations safe
- Reports generate <5 seconds

---

#### Task 10.4: Security Audit
**Priority:** High  
**Estimated Time:** 8 hours  
**Assigned To:** Security Specialist

**Subtasks:**
- 10.4.1 SQL injection testing (2 hours)
- 10.4.2 XSS testing (2 hours)
- 10.4.3 CSRF testing (2 hours)
- 10.4.4 Permission bypass testing (2 hours)

**Dependencies:**
- Depends on: All phases

**Acceptance Criteria:**
- No security vulnerabilities
- All inputs sanitized
- Permissions enforced

---

#### Task 10.5: Documentation
**Priority:** High  
**Estimated Time:** 12 hours  
**Assigned To:** Tech Writer + Developers

**Subtasks:**
- 10.5.1 User manual (4 hours)
- 10.5.2 Admin manual (4 hours)
- 10.5.3 API documentation (2 hours)
- 10.5.4 Deployment guide (2 hours)

**Dependencies:**
- Depends on: All phases

**Acceptance Criteria:**
- Complete user documentation
- Deployment instructions clear
- API endpoints documented

---

## TASK SUMMARY

**Total Tasks:** 55  
**Total Subtasks:** 200+  
**Total Estimated Hours:** 480+ hours (12 weeks @ 40 hours/week per developer)

## DEPENDENCIES OVERVIEW

```
Phase 1 (Foundation) → Phase 2 (Masters) → Phase 3 (Challans) → Phase 4 (Invoices) → Phase 5 (Payments) → Phase 6 (Ledger) → Phase 7 (Delivery) → Phase 8 (Reports) → Phase 9 (Audit) → Phase 10 (Testing)
```

**Critical Path:**
1.1 → 1.2 → 1.3 → 1.4 → 2.3 → 3.1 → 3.2 → 3.3 → 4.1 → 4.2 → 4.3 → 4.4 → 5.1 → 5.2 → 5.3 → 6.1 → 6.2 → 8.1 → 10.1 → 10.2

**Parallel Development Possible:**
- Phase 2 tasks can run in parallel (Gold Rate, Products, Accounts)
- Phase 7 (Delivery) can start after Phase 5 (Payments) without waiting for Phase 6
- Phase 9 (Audit) can run in parallel with Phase 8 (Reports)


# AI CODING PROMPTS FOR ALL TASKS

## Gold Manufacturing & Billing ERP System

### Complete Prompt Library for AI-Assisted Development

**Version:** 1.0  
**Generated:** February 8, 2026  
**How to Use:** Copy the prompt for your task, provide the .antigravity file to AI, and get production-ready code

---

## 📖 HOW TO USE THIS FILE

### Step 1: Find Your Task

Navigate to the phase and task number you're working on (e.g., Task 4.4.1)

### Step 2: Copy the Complete Prompt

Copy the entire prompt block including:

- Context section
- Requirements
- Dependencies
- Acceptance criteria
- Table schema (if applicable)

### Step 3: Provide to AI

```
[Paste .antigravity file content]

[Paste task prompt from this file]
```

### Step 4: Validate Output

Use the checklist from .antigravity to validate the generated code

---

## PHASE 1: CORE SETUP & FOUNDATION

### 🎯 TASK 1.1: PROJECT SETUP & CONFIGURATION

#### Subtask 1.1.1: Initialize CodeIgniter 4 Project

```
TASK: Initialize CodeIgniter 4 project structure

CONTEXT:
- Starting fresh project for Gold Manufacturing & Billing ERP
- Multi-tenant SaaS application
- PHP 8.1+, CodeIgniter 4.5+

REQUIREMENTS:
1. Install CodeIgniter 4 via Composer:
   composer create-project codeigniter4/appstarter gold-erp
2. Verify installation successful
3. Configure base URL in app/Config/App.php
4. Enable development mode
5. Test localhost access

DELIVERABLES:
- Working CI4 installation
- Development environment configured
- Welcome page accessible on localhost

ACCEPTANCE CRITERIA:
- CI4 project runs on localhost
- No errors on homepage
- Development mode enabled
```

#### Subtask 1.1.2: Configure Database Connection

```
TASK: Setup database connection in .env file

CONTEXT:
- MySQL 8.0+ database
- Multi-tenant database design
- All tables will use company_id for isolation

REQUIREMENTS:
1. Copy env file to .env
2. Configure database credentials:
   database.default.hostname = localhost
   database.default.database = cadcam_invoice
   database.default.username = root
   database.default.password =
   database.default.DBDriver = MySQLi
   database.default.DBPrefix =
   database.default.port = 3306
3. Test connection using: php spark db:table
4. Enable query logging in development

DELIVERABLES:
- .env file configured
- Database connection working
- Query logging enabled

ACCEPTANCE CRITERIA:
- Database connection successful
- No connection errors
- Can run migrations
```

---

### 🎯 TASK 1.2: DATABASE MIGRATION - CORE TABLES

#### Subtask 1.2.1: Create Migration - Companies Table

```
[PASTE .antigravity RULES FIRST]

TASK: Generate migration file for companies table

FILE: app/Database/Migrations/2026-01-01-000001_create_companies_table.php

CONTEXT:
- Multi-tenant ERP system
- Each company is isolated tenant
- Companies have their own invoice/challan numbering

REQUIREMENTS:
Using CodeIgniter 4 migration syntax, create migration with:

TABLE STRUCTURE:
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- company_name (VARCHAR 255, NOT NULL)
- company_code (VARCHAR 50, UNIQUE, NOT NULL)
- gst_number (VARCHAR 15, UNIQUE, NOT NULL)
- pan_number (VARCHAR 10, NOT NULL)
- address_line1 (VARCHAR 255)
- address_line2 (VARCHAR 255)
- city (VARCHAR 100)
- state_id (INT, FK to states.id)
- pincode (VARCHAR 10)
- email (VARCHAR 100)
- phone (VARCHAR 20)
- mobile (VARCHAR 20)
- website (VARCHAR 100)
- logo (VARCHAR 255)
- last_invoice_number (INT, DEFAULT 0)
- last_challan_number (INT, DEFAULT 0)
- invoice_prefix (VARCHAR 10, DEFAULT 'INV')
- challan_prefix (VARCHAR 10, DEFAULT 'CHN')
- tax_rate (DECIMAL 5,2, DEFAULT 3.00)
- is_active (BOOLEAN, DEFAULT TRUE)
- is_deleted (BOOLEAN, DEFAULT FALSE)
- created_at (TIMESTAMP NULL)
- updated_at (TIMESTAMP NULL)

INDEXES:
- PRIMARY KEY (id)
- INDEX (state_id)
- UNIQUE (company_code)
- UNIQUE (gst_number)

FOREIGN KEYS:
- state_id REFERENCES states(id) ON DELETE RESTRICT

METHODS REQUIRED:
- up(): Create table with all columns, indexes, constraints
- down(): Drop table

ADDITIONAL REQUIREMENTS:
- Use $this->forge->addField() for columns
- Use $this->forge->addKey() for indexes
- Use $this->forge->addForeignKey() for FK
- Include complete error handling
- Test both up() and down() methods

DELIVERABLES:
Complete migration file ready to run with php spark migrate

ACCEPTANCE CRITERIA:
- Migration runs without errors
- All columns created with correct types
- Foreign key constraint working
- Rollback works (php spark migrate:rollback)
```

#### Subtask 1.2.2: Create Migration - States Table

```
[PASTE .antigravity RULES FIRST]

TASK: Generate migration file for states table

FILE: app/Database/Migrations/2026-01-01-000002_create_states_table.php

CONTEXT:
- Master data table for Indian states
- Global scope (company_id = 0)
- Used for address validation and tax calculation (CGST/SGST vs IGST)

REQUIREMENTS:
Create migration for states table:

TABLE STRUCTURE:
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- state_name (VARCHAR 100, NOT NULL)
- state_code (VARCHAR 10, NOT NULL)
- gst_code (VARCHAR 2, NOT NULL) // 01 to 37
- is_union_territory (BOOLEAN, DEFAULT FALSE)
- is_active (BOOLEAN, DEFAULT TRUE)

INDEXES:
- PRIMARY KEY (id)
- UNIQUE (state_code)
- UNIQUE (gst_code)
- INDEX (state_name)

METHODS:
- up(): Create table
- down(): Drop table

DELIVERABLES:
Complete migration file

ACCEPTANCE CRITERIA:
- Migration runs successfully
- Unique constraints working
- Ready for seeder data
```

#### Subtask 1.2.7: Create StateSeeder

```
[PASTE .antigravity RULES FIRST]

TASK: Generate seeder for Indian states and union territories

FILE: app/Database/Seeds/StateSeeder.php

CONTEXT:
- Populate states table with all 28 states + 8 union territories
- GST codes as per official state codes (01-37)
- Required for address and tax calculation

REQUIREMENTS:
Create seeder that inserts all Indian states:

STATES TO INSERT (sample, include all 36):
1. Andhra Pradesh (state_code: AP, gst_code: 37)
2. Telangana (state_code: TS, gst_code: 36)
3. Gujarat (state_code: GJ, gst_code: 24)
4. Maharashtra (state_code: MH, gst_code: 27)
5. Karnataka (state_code: KA, gst_code: 29)
... [all 28 states]

UNION TERRITORIES (mark is_union_territory = TRUE):
1. Delhi (state_code: DL, gst_code: 07)
2. Chandigarh (state_code: CH, gst_code: 04)
3. Puducherry (state_code: PY, gst_code: 34)
... [all 8 UTs]

METHOD:
- run(): Use $this->db->table('states')->insertBatch()
- Insert all 36 entries in single batch

DELIVERABLES:
Complete seeder file

ACCEPTANCE CRITERIA:
- Seeder inserts all 36 states/UTs
- GST codes accurate
- No duplicate entries
- Run: php spark db:seed StateSeeder
```

#### Subtask 1.2.8: Create RoleSeeder

```
[PASTE .antigravity RULES FIRST]

TASK: Generate seeder for predefined user roles with permissions

FILE: app/Database/Seeds/RoleSeeder.php

CONTEXT:
- RBAC (Role-Based Access Control) system
- Permissions stored as JSON array in roles table
- Additive permissions (union of all roles)

REQUIREMENTS:
Create seeder that inserts predefined roles:

ROLES TO CREATE:
1. Super Administrator
   - is_system_role: TRUE
   - is_global: TRUE
   - permissions: ['*'] (all permissions)

2. Company Administrator
   - is_system_role: TRUE
   - is_global: FALSE
   - permissions: [
       'company.manage', 'users.manage', 'roles.manage',
       'challans.*', 'invoices.*', 'payments.*',
       'reports.*', 'masters.*', 'deliveries.*',
       'settings.manage'
     ]

3. Billing Manager
   - permissions: [
       'challans.*', 'invoices.*',
       'reports.ledger', 'reports.outstanding',
       'customers.view', 'masters.view'
     ]

4. Accounts Manager
   - permissions: [
       'payments.*', 'reports.*',
       'invoices.view', 'challans.view',
       'customers.*'
     ]

5. Delivery Personnel
   - permissions: [
       'deliveries.view_assigned',
       'deliveries.mark_complete',
       'invoices.view_assigned'
     ]

6. Report Viewer
   - permissions: [
       'reports.view_all',
       'invoices.view', 'challans.view',
       'customers.view'
     ]

METHOD:
- run(): Insert all roles using insertBatch()
- JSON encode permissions array

DELIVERABLES:
Complete seeder with all 6 roles

ACCEPTANCE CRITERIA:
- All roles inserted
- Permissions stored as valid JSON
- System roles marked correctly
```

#### Subtask 1.2.9: Create SuperAdminSeeder

```
[PASTE .antigravity RULES FIRST]

TASK: Generate seeder for default super admin user

FILE: app/Database/Seeds/SuperAdminSeeder.php

CONTEXT:
- Create system super admin for initial access
- This user can create companies and company admins
- Password must be hashed using password_hash()

REQUIREMENTS:
Create seeder that:
1. Creates default super admin user
2. Hashes password securely
3. Assigns Super Administrator role
4. Creates user_roles entry

USER DATA:
- username: 'superadmin'
- email: 'admin@gmail.com'
- password: 'Admin@123' (hash this using password_hash())
- full_name: 'System Administrator'
- company_id: NULL (super admin not tied to company)
- mobile: '9999999999'
- is_active: TRUE
- is_system_user: TRUE

STEPS:
1. Check if superadmin already exists (skip if yes)
2. Insert user record
3. Get user ID
4. Get Super Administrator role ID
5. Insert user_roles record

METHOD:
- run(): Complete seeder logic with checks

DELIVERABLES:
Complete seeder file

ACCEPTANCE CRITERIA:
- Super admin user created
- Password properly hashed
- Role assigned
- Can login with credentials
- Idempotent (can run multiple times)
```

---

### 🎯 TASK 1.3: AUTHENTICATION SYSTEM

#### Subtask 1.3.1: Create UserModel

```
[PASTE .antigravity RULES FIRST]

TASK: Generate UserModel with authentication features

FILE: app/Models/UserModel.php

CONTEXT:
- Multi-tenant system
- Users belong to companies (except super admin)
- Password hashing on insert/update
- Failed login tracking
- Soft delete support

REQUIREMENTS:
Create CodeIgniter 4 Model extending \CodeIgniter\Model with:

PROPERTIES:
- protected $table = 'users';
- protected $primaryKey = 'id';
- protected $useTimestamps = true;
- protected $allowedFields = [
    'company_id', 'username', 'email', 'password', 'full_name',
    'mobile', 'role_id', 'is_active', 'failed_login_attempts',
    'last_failed_login', 'last_login_at', 'is_system_user',
    'is_deleted'
  ]
- protected $validationRules = [
    'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username,id,{id}]',
    'email' => 'required|valid_email|is_unique[users.email,id,{id}]',
    'password' => 'required|min_length[8]',
    'full_name' => 'required|min_length[3]',
    'mobile' => 'required|regex_match[/^[0-9]{10}$/]'
  ]

CALLBACKS:
- protected $beforeInsert = ['hashPassword'];
- protected $beforeUpdate = ['hashPassword'];

METHODS REQUIRED:
1. protected function hashPassword(array $data): array
   - If password field exists and not already hashed
   - Hash using password_hash($password, PASSWORD_DEFAULT)
   - Return modified data array

2. public function findByUsername(string $username): ?array
   - Where username = $username AND is_deleted = FALSE
   - Return user array or null

3. public function findByEmail(string $email): ?array
   - Similar to findByUsername

4. public function incrementFailedAttempts(int $userId): bool
   - Increment failed_login_attempts
   - Update last_failed_login timestamp
   - No validation rules

5. public function resetFailedAttempts(int $userId): bool
   - Set failed_login_attempts = 0
   - Set last_failed_login = NULL

6. public function updateLastLogin(int $userId): bool
   - Set last_login_at = NOW()

7. protected function applyCompanyFilter()
   - Get company_id from session
   - If not super admin: where('company_id', $companyId)
   - Return $this for chaining

8. public function findAll(int $limit = 0, int $offset = 0)
   - Override parent method
   - Apply company filter
   - where('is_deleted', FALSE)
   - Return parent::findAll($limit, $offset)

ADDITIONAL REQUIREMENTS:
- Use proper type hints on all methods
- Handle errors gracefully
- Never store plain text passwords
- Company filter auto-applied (except for super admin)

DELIVERABLES:
Complete UserModel.php file

ACCEPTANCE CRITERIA:
- Password auto-hashed on insert/update
- Failed login tracking works
- Company filter applied automatically
- Soft delete respected
- Validation rules enforced
```

#### Subtask 1.3.3: Create AuthService

```
[PASTE .antigravity RULES FIRST]

TASK: Generate AuthService for login/logout and session management

FILE: app/Services/Auth/AuthService.php

CONTEXT:
- Handle user authentication
- Session management
- Failed login rate limiting (lock after 5 attempts)
- Security best practices

REQUIREMENTS:
Create AuthService class with:

DEPENDENCIES (inject in __construct):
- UserModel
- RoleModel (or PermissionService)
- AuditService
- Session library

METHODS REQUIRED:

1. public function login(string $username, string $password): array|false
   - Find user by username
   - Check if user exists and is_active = TRUE
   - Check if account locked (failed_attempts >= 5)
   - Verify password using password_verify()
   - If password valid:
     - Reset failed attempts
     - Update last_login_at
     - Set session data:
       - user_id
       - company_id
       - username
       - full_name
       - is_super_admin
       - permissions (load from PermissionService)
     - Audit log login
     - Return user array
   - If password invalid:
     - Increment failed attempts
     - Audit log failed attempt
     - Return FALSE
   - Lock account if attempts >= 5

2. public function logout(): bool
   - Get current user from session
   - Audit log logout
   - Destroy session
   - Return TRUE

3. public function getCurrentUser(): ?array
   - Get user_id from session
   - Return user data or NULL if not logged in

4. public function isLoggedIn(): bool
   - Check if user_id exists in session
   - Return TRUE/FALSE

5. public function isSuperAdmin(): bool
   - Check session: is_super_admin === TRUE

6. private function setSession(array $userData): void
   - Set all session variables
   - Load user permissions
   - Set session timeout

7. private function handleFailedLogin(int $userId): void
   - Increment failed attempts
   - Check if >= 5, lock account
   - Log failed attempt

8. private function validatePassword(string $password, string $hash): bool
   - Use password_verify()
   - Return TRUE/FALSE

ERROR HANDLING:
- Throw AuthenticationException for invalid credentials
- Throw AccountLockedException if account locked
- Log all authentication attempts

DELIVERABLES:
Complete AuthService.php file

ACCEPTANCE CRITERIA:
- Login works with valid credentials
- Failed attempts tracked
- Account locked after 5 failed attempts
- Session set correctly
- Logout clears session
- All actions audit logged
```

#### Subtask 1.3.7: Create AuthFilter

````
[PASTE .antigravity RULES FIRST]

TASK: Generate AuthFilter to protect routes

FILE: app/Filters/AuthFilter.php

CONTEXT:
- Apply to all routes except login/public pages
- Redirect unauthenticated users to login
- Check session validity

REQUIREMENTS:
Create CI4 Filter implementing FiltersInterface:

CLASS: AuthFilter implements FiltersInterface

METHODS:

1. public function before(RequestInterface $request, $arguments = null)
   - Get session
   - Check if user_id exists in session
   - If not logged in:
     - Store intended URL in session
     - Redirect to /login
     - Return redirect response
   - If logged in:
     - Check session not expired
     - Verify user still active (optional: query DB)
     - Return $request (allow access)

2. public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
   - No action needed
   - Return $response

EXCLUDE ROUTES:
- /login
- /logout
- /public/*
- /api/public/*

CONFIGURATION:
Add to app/Config/Filters.php:
```php
public $aliases = [
    'auth' => \App\Filters\AuthFilter::class,
];

public $globals = [
    'before' => ['auth' => ['except' => ['login', 'logout', 'public/*']]],
];
````

DELIVERABLES:
Complete AuthFilter.php file

ACCEPTANCE CRITERIA:

- Unauthenticated users redirected to login
- Authenticated users allowed access
- Intended URL stored and redirected after login
- Public routes accessible without auth

```

---

### 🎯 TASK 1.4: MULTI-TENANT SETUP

#### Subtask 1.4.1: Create CompanyModel
```

[PASTE .antigravity RULES FIRST]

TASK: Generate CompanyModel with concurrency-safe numbering

FILE: app/Models/CompanyModel.php

CONTEXT:

- Companies are tenants in multi-tenant system
- Sequential invoice/challan numbering (gap-free)
- Concurrency-safe number generation using row locks

REQUIREMENTS:
Create CompanyModel extending \CodeIgniter\Model:

PROPERTIES:

- protected $table = 'companies';
- protected $primaryKey = 'id';
- protected $useTimestamps = true;
- protected $allowedFields = [
  'company_name', 'company_code', 'gst_number', 'pan_number',
  'address_line1', 'address_line2', 'city', 'state_id', 'pincode',
  'email', 'phone', 'mobile', 'website', 'logo',
  'last_invoice_number', 'last_challan_number',
  'invoice_prefix', 'challan_prefix', 'tax_rate',
  'is_active', 'is_deleted'
  ]

VALIDATION RULES:

- company_name: required, min 3, max 255
- company_code: required, unique
- gst_number: required, regex (GST format), unique
- pan_number: required, regex (PAN format)
- email: valid_email
- tax_rate: numeric, greater_than[0], less_than_equal_to[100]

METHODS REQUIRED:

1. public function getNextInvoiceNumber(int $companyId): int
   - Use FOR UPDATE lock to prevent race conditions
   - SQL: SELECT last_invoice_number FROM companies WHERE id = ? FOR UPDATE
   - Increment: $nextNumber = $lastNumber + 1
   - Update: UPDATE companies SET last_invoice_number = ? WHERE id = ?
   - Return $nextNumber
   - MUST be called within transaction

2. public function getNextChallanNumber(int $companyId): int
   - Same logic as getNextInvoiceNumber
   - Uses last_challan_number

3. public function getActiveCompanies(): array
   - where('is_active', TRUE)
   - where('is_deleted', FALSE)
   - orderBy('company_name', 'ASC')
   - Return findAll()

4. public function validateGSTNumber(string $gst): bool
   - Regex: /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/
   - Return TRUE if valid

5. public function validatePANNumber(string $pan): bool
   - Regex: /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/
   - Return TRUE if valid

TRANSACTION SAFETY:

- getNextInvoiceNumber() and getNextChallanNumber() MUST be transaction-safe
- Use SELECT FOR UPDATE to lock row
- Prevent concurrent access issues

DELIVERABLES:
Complete CompanyModel.php file

ACCEPTANCE CRITERIA:

- CRUD operations work
- Invoice/challan numbering sequential
- No gaps in numbering even under concurrency
- GST/PAN validation working
- Soft delete respected

```

#### Subtask 1.4.4: Modify BaseModel
```

[PASTE .antigravity RULES FIRST]

TASK: Create BaseModel with auto company_id filtering

FILE: app/Models/BaseModel.php

CONTEXT:

- All models will extend BaseModel
- Auto-filter by company_id for multi-tenant isolation
- Super admin can access all companies

REQUIREMENTS:
Create BaseModel extending \CodeIgniter\Model:

METHODS TO ADD:

1. protected function applyCompanyFilter(): self
   - Get company_id from session()->get('company_id')
   - Get is_super_admin from session
   - If is_super_admin == FALSE and company_id exists:
     - $this->where('company_id', $companyId)
   - Return $this for method chaining

2. public function findAll(int $limit = 0, int $offset = 0)
   - Override parent method
   - Call applyCompanyFilter()
   - where('is_deleted', FALSE)
   - Call parent::findAll($limit, $offset)
   - Return results

3. public function find($id = null)
   - Override parent method
   - If $id is array:
     - Call applyCompanyFilter()
     - where('is_deleted', FALSE)
   - Call parent::find($id)
   - Return result

4. public function paginate(int $perPage = 20, string $group = 'default', int $page = 0)
   - Override parent
   - Call applyCompanyFilter()
   - where('is_deleted', FALSE)
   - Call parent::paginate($perPage, $group, $page)
   - Return results

5. public function softDelete(int $id): bool
   - Update: ['is_deleted' => TRUE]
   - Never hard delete
   - Return success

USAGE IN OTHER MODELS:

```php
class UserModel extends BaseModel
{
    // Auto-inherits company filter
}

// Now all queries auto-filtered:
$users = $userModel->findAll(); // Automatically filtered by company_id
```

SUPER ADMIN EXCEPTION:

- If user is super admin, no company filter applied
- Can see all companies' data

DELIVERABLES:
Complete BaseModel.php file

ACCEPTANCE CRITERIA:

- All models extending BaseModel auto-filtered by company_id
- Super admin sees all data
- Regular users see only their company data
- Soft delete applied automatically
- No data leakage between companies

```

---

## PHASE 2: MASTER DATA MANAGEMENT

### 🎯 TASK 2.1: GOLD RATE MANAGEMENT

#### Subtask 2.1.3: Create GoldRateService
```

[PASTE .antigravity RULES FIRST]

TASK: Generate GoldRateService for gold rate management

FILE: app/Services/Master/GoldRateService.php

CONTEXT:

- Gold rates change daily
- Used in invoices and gold adjustments
- Must fetch latest rate for today

REQUIREMENTS:
Create GoldRateService with:

DEPENDENCIES:

- GoldRateModel
- AuditService
- DB connection

METHODS REQUIRED:

1. public function createRate(array $data): int
   - Validate data
   - Check if rate for today already exists
   - If exists: throw exception "Rate already entered for today"
   - Add company_id from session
   - Insert rate
   - Audit log
   - Return rate ID

2. public function updateRate(int $id, array $data): bool
   - Validate rate belongs to company
   - Check if rate used in any invoice (if yes, cannot update)
   - Update rate
   - Audit log
   - Return success

3. public function getLatestRate(string $metalType = '22K'): ?float
   - Get company_id from session
   - Query: SELECT rate_per_gram FROM gold_rates
     WHERE company_id = ? AND metal_type = ? AND is_deleted = FALSE
     ORDER BY rate_date DESC, created_at DESC LIMIT 1
   - Return rate or NULL

4. public function getRateByDate(string $date, string $metalType = '22K'): ?float
   - Get rate for specific date
   - Used in historical invoices

5. public function checkIfTodayRateEntered(): bool
   - Check if rate entered for today's date
   - Return TRUE/FALSE

6. public function getRateHistory(string $fromDate, string $toDate): array
   - Get all rates between dates
   - For charting/reporting

VALIDATION:

- rate_per_gram > 0
- rate_date <= today
- metal_type in ['22K', '24K', 'Silver']

ERROR HANDLING:

- Throw ValidationException for invalid data
- Throw BusinessRuleException if rate already exists

DELIVERABLES:
Complete GoldRateService.php file

ACCEPTANCE CRITERIA:

- Rate can be created/updated
- Latest rate retrieved correctly
- Cannot enter duplicate rate for same date
- Company isolation enforced

```

---

### 🎯 TASK 2.3: ACCOUNT & CASH CUSTOMER MANAGEMENT

#### Subtask 2.3.5: Create AccountService
```

[PASTE .antigravity RULES FIRST]

TASK: Generate AccountService with opening balance ledger entry

FILE: app/Services/Customer/AccountService.php

CONTEXT:

- Account customers have ledger (credit terms)
- Opening balance creates initial ledger entry
- Cannot delete account with transactions

REQUIREMENTS:
Create AccountService with:

DEPENDENCIES:

- AccountModel
- LedgerService
- AuditService
- DB connection

METHODS REQUIRED:

1. public function createAccount(array $data): int
   - START TRANSACTION
   - Validate data
   - Auto-generate account_code (e.g., ACC001, ACC002)
   - Add company_id from session
   - Insert account
   - If opening_balance > 0:
     - Call LedgerService.createOpeningBalanceEntry($accountId, $openingBalance)
   - Audit log
   - COMMIT or ROLLBACK
   - Return account ID

2. public function updateAccount(int $id, array $data): bool
   - Validate account belongs to company
   - Cannot update opening_balance (immutable after creation)
   - Update account details
   - Audit log
   - Return success

3. public function deleteAccount(int $id): bool
   - Check if account has any invoices/payments
   - If has transactions: throw exception "Cannot delete account with transactions"
   - Soft delete (is_deleted = TRUE)
   - Audit log
   - Return success

4. public function getLedgerBalance(int $accountId): float
   - Call LedgerService.getLastBalance($accountId)
   - Return current balance

5. public function searchAccounts(string $query): array
   - Search by name, account_code, mobile
   - For autocomplete
   - Limit 10 results
   - Return array of accounts

6. private function generateAccountCode(): string
   - Get last account code for company
   - Extract number, increment
   - Format: ACC{padded_number} (e.g., ACC0001)
   - Return unique code

VALIDATION:

- account_name required, min 3 chars
- email: valid_email format
- mobile: 10 digits
- gst_number: GST format (if provided)
- opening_balance >= 0
- opening_balance_date required if opening_balance > 0

TRANSACTION SAFETY:

- createAccount() MUST use transaction (account + ledger entry atomic)

ERROR HANDLING:

- ValidationException for invalid data
- BusinessRuleException for business rules

DELIVERABLES:
Complete AccountService.php file

ACCEPTANCE CRITERIA:

- Account created with auto-generated code
- Opening balance creates ledger entry
- Cannot delete account with transactions
- Search/autocomplete working
- All operations transaction-safe

```

---

## PHASE 3: CHALLAN MANAGEMENT

### 🎯 TASK 3.3: CHALLAN SERVICE (CORE LOGIC)

#### Subtask 3.3.1: Create ChallanService
```

[PASTE .antigravity RULES FIRST]

TASK: Generate ChallanService with complete CRUD and workflow

FILE: app/Services/Challan/ChallanService.php

CONTEXT:

- Challans are work orders for processing
- Status workflow: Draft → Submitted → Approved → Invoiced/Cancelled
- Cannot edit after invoice generated
- Sequential numbering (gap-free)

REQUIREMENTS:
Create ChallanService with:

DEPENDENCIES:

- ChallanModel
- ChallanLineModel
- ChallanCalculationService
- ChallanValidationService
- NumberingService
- AuditService
- DB connection

METHODS REQUIRED:

1. public function createChallan(array $data): int
   - START TRANSACTION
   - Validate challan data (call ChallanValidationService)
   - Generate challan_number using NumberingService.getNextChallanNumber()
   - Format: {prefix}{padded_number} (e.g., CHN0001)
   - Add company_id from session
   - Set status = 'Draft'
   - Insert challan header
   - Get challan ID
   - Insert line items (challan_lines):
     - For each line in $data['lines']:
       - Add challan_id
       - Calculate line amount using ChallanCalculationService.calculateLineAmount()
       - Insert line
   - Calculate totals using ChallanCalculationService.calculateChallanTotal()
   - Update challan header with totals
   - Audit log creation
   - COMMIT or ROLLBACK on error
   - Return challan ID

2. public function updateChallan(int $id, array $data): bool
   - START TRANSACTION
   - Check if challan can be edited:
     - Call ChallanValidationService.canEdit($id)
     - Throw exception if cannot edit
   - Get existing challan
   - Delete existing line items
   - Insert new line items
   - Recalculate totals
   - Update header
   - Audit log (before/after data)
   - COMMIT or ROLLBACK
   - Return success

3. public function deleteChallan(int $id): bool
   - Check if can delete:
     - Call ChallanValidationService.canDelete($id)
     - Cannot delete if status = Approved or invoice_generated = TRUE
   - Soft delete challan (is_deleted = TRUE)
   - Audit log deletion
   - Return success

4. public function submitForApproval(int $id): bool
   - Check status = Draft
   - Update status = 'Submitted'
   - Set submitted_at = NOW()
   - Audit log
   - Return success

5. public function approveChallan(int $id): bool
   - Check permission (user has challan.approve permission)
   - Check status = Submitted
   - Update status = 'Approved'
   - Set approved_at = NOW()
   - Set approved_by = current user ID
   - Audit log
   - Return success

6. public function cancelChallan(int $id): bool
   - Check if can cancel (not invoiced)
   - Update status = 'Cancelled'
   - Set cancelled_at = NOW()
   - Audit log
   - Return success

7. public function getChallanById(int $id): ?array
   - Get challan with lines
   - Company filter applied
   - Return challan array with 'lines' relationship

8. public function getAvailableChallansForInvoice(int $accountId): array
   - Get challans where:
     - account_id = $accountId
     - status = 'Approved'
     - invoice_generated = FALSE
     - is_deleted = FALSE
   - Return array

VALIDATION (in each method):

- Check company ownership
- Validate status transitions
- Validate line items (product_ids, process_ids)

BUSINESS RULES:

- Challan number sequential and gap-free (use NumberingService with row lock)
- Cannot edit after invoice generated
- Cannot delete if approved or invoiced
- Status workflow enforced
- All operations transactional

ERROR HANDLING:

- ValidationException for invalid data
- BusinessRuleException for workflow violations
- DatabaseException for DB errors

DELIVERABLES:
Complete ChallanService.php file (expect 400+ lines)

ACCEPTANCE CRITERIA:

- Challan CRUD working
- Status workflow enforced
- Challan numbering sequential
- Totals calculated correctly
- Cannot edit/delete after invoice
- All operations transactional
- Audit trail complete

```

#### Subtask 3.3.3: Create NumberingService
```

[PASTE .antigravity RULES FIRST]

TASK: Generate NumberingService for concurrency-safe sequential numbering

FILE: app/Services/Common/NumberingService.php

CONTEXT:

- Invoice and challan numbers must be sequential without gaps
- Multiple users may create invoices/challans simultaneously
- Use row-level locks to prevent race conditions

REQUIREMENTS:
Create NumberingService with:

DEPENDENCIES:

- CompanyModel
- DB connection

METHODS REQUIRED:

1. public function getNextChallanNumber(int $companyId): string
   - MUST be called within transaction
   - Use SELECT FOR UPDATE to lock company row
   - SQL:
     ```sql
     SELECT last_challan_number, challan_prefix
     FROM companies
     WHERE id = ?
     FOR UPDATE
     ```
   - Increment: $nextNumber = $lastNumber + 1
   - Update companies table:
     ```sql
     UPDATE companies
     SET last_challan_number = ?
     WHERE id = ?
     ```
   - Format number: {prefix}{padded_number}
     - Example: CHN0001, CHN0002, CHN0123
     - Pad to 4 digits minimum
   - Return formatted challan number

2. public function getNextInvoiceNumber(int $companyId): string
   - Same logic as getNextChallanNumber
   - Uses last_invoice_number and invoice_prefix
   - Format: INV0001, INV0002, etc.

3. private function formatNumber(string $prefix, int $number, int $padding = 4): string
   - Pad number with leading zeros
   - Concatenate prefix + padded number
   - Return formatted string

CRITICAL REQUIREMENTS:

- MUST use SELECT FOR UPDATE (row lock)
- MUST be called within transaction
- Row lock prevents concurrent updates
- Guarantees sequential numbers without gaps

EXAMPLE USAGE:

```php
// In ChallanService.createChallan()
$this->db->transStart();
try {
    $challanNumber = $this->numberingService->getNextChallanNumber($companyId);
    // ... rest of challan creation
    $this->db->transComplete();
} catch (Exception $e) {
    $this->db->transRollback();
    throw $e;
}
```

TRANSACTION SAFETY:

- SELECT FOR UPDATE locks the company row
- Other transactions wait until lock released
- Prevents duplicate numbers
- Prevents gaps in numbering

DELIVERABLES:
Complete NumberingService.php file

ACCEPTANCE CRITERIA:

- Challan numbers sequential
- Invoice numbers sequential
- No gaps even with concurrent creation
- Row lock working correctly
- Thread-safe under load

```

---

## PHASE 4: INVOICE MANAGEMENT

### 🎯 TASK 4.2: TAX CALCULATION SERVICE

#### Subtask 4.2.1: Create TaxCalculationService
```

[PASTE .antigravity RULES FIRST]

TASK: Generate TaxCalculationService for GST-compliant tax calculation

FILE: app/Services/Invoice/TaxCalculationService.php

CONTEXT:

- Tax-inclusive pricing (amount includes tax)
- GST split: Same state = CGST + SGST, Different state = IGST
- Tax rate from company settings (default 3%)
- Must be accurate to 2 decimal places

REQUIREMENTS:
Create TaxCalculationService with:

DEPENDENCIES:

- CompanyModel (for tax rate and state)
- StateModel

METHODS REQUIRED:

1. public function calculateTaxFromInclusive(float $amount, float $taxRate): array
   - Formula for tax-inclusive:
     - taxAmount = amount × taxRate / (100 + taxRate)
     - subtotal = amount - taxAmount
   - Example:
     - If amount = ₹103, taxRate = 3%
     - taxAmount = 103 × 3 / (100 + 3) = ₹3
     - subtotal = 103 - 3 = ₹100
   - Return: ['subtotal' => float, 'tax_amount' => float]
   - Round to 2 decimals

2. public function calculateGSTSplit(float $taxAmount, string $companyState, string $customerState): array
   - If companyState == customerState (Intra-state):
     - cgst = taxAmount / 2
     - sgst = taxAmount / 2
     - igst = 0
   - If companyState != customerState (Inter-state):
     - cgst = 0
     - sgst = 0
     - igst = taxAmount
   - Return: ['cgst' => float, 'sgst' => float, 'igst' => float]
   - Round to 2 decimals

3. public function calculateInvoiceTax(array $lines, float $taxRate, string $companyState, string $customerState): array
   - For each line:
     - Calculate tax from inclusive amount
     - Aggregate totals
   - Split GST based on states
   - Return: [
     'subtotal' => float,
     'total_tax_amount' => float,
     'cgst' => float,
     'sgst' => float,
     'igst' => float,
     'grand_total' => float
     ]

4. public function recalculateTaxAfterAdjustment(int $invoiceId): array
   - Get invoice lines with adjusted amounts
   - Recalculate tax on adjusted amounts
   - Return new totals
   - Used after gold adjustment

FORMULAS (CRITICAL):
Tax-inclusive formula: tax = amount × rate / (100 + rate)
NOT: tax = amount × rate / 100 (this is for tax-exclusive)

EXAMPLES FOR TESTING:
Test Case 1: Intra-state

- Amount: ₹103.00
- Tax rate: 3%
- Tax amount: ₹3.00
- CGST: ₹1.50
- SGST: ₹1.50
- IGST: ₹0.00

Test Case 2: Inter-state

- Amount: ₹206.00
- Tax rate: 3%
- Tax amount: ₹6.00
- CGST: ₹0.00
- SGST: ₹0.00
- IGST: ₹6.00

ROUNDING:

- Always round to 2 decimal places
- Use round($value, 2)

DELIVERABLES:
Complete TaxCalculationService.php file

ACCEPTANCE CRITERIA:

- Tax calculated correctly for inclusive pricing
- CGST/SGST split for intra-state
- IGST for inter-state
- Rounding accurate
- All test cases pass
- Formula correct (most critical!)

```

---

### 🎯 TASK 4.4: INVOICE SERVICE (CORE LOGIC)

#### Subtask 4.4.1: Create InvoiceService - MOST CRITICAL
```

[PASTE .antigravity RULES FIRST]

TASK: Generate InvoiceService with complete invoice management

FILE: app/Services/Invoice/InvoiceService.php

CONTEXT:

- Most critical service in the system
- Creates invoices from approved challans or manually (cash invoice)
- Creates ledger entries (financial impact)
- Sequential numbering (gap-free)
- Tax calculation integrated
- Cannot edit after payment received
- All operations MUST be transactional

REQUIREMENTS:
Create InvoiceService with:

DEPENDENCIES:

- InvoiceModel
- InvoiceLineModel
- ChallanModel
- ChallanLineModel
- InvoiceCalculationService
- TaxCalculationService
- NumberingService
- LedgerService
- AuditService
- DB connection

METHODS REQUIRED:

1. public function createInvoiceFromChallans(array $challanIds, array $invoiceData): int
   - START TRANSACTION
   - Validate:
     - All challans belong to same account
     - All challans are approved
     - No challan already invoiced
     - All challans belong to company
   - Generate invoice_number using NumberingService.getNextInvoiceNumber()
   - Create invoice header:
     - invoice_number
     - invoice_type = 'Accounts Invoice'
     - invoice_date
     - account_id (from challans)
     - company_id from session
     - invoice_status = 'Draft'
     - payment_status = 'Pending'
   - Copy lines from challans to invoice_lines:
     - For each challan in challanIds:
       - Get challan lines
       - For each line:
         - Create invoice line
         - Link: challan_id, challan_line_id
         - Copy: product_ids, process_ids, gold_weight, rate, amount
   - Calculate tax:
     - Get company state and account state
     - Call TaxCalculationService.calculateInvoiceTax()
     - Update invoice:
       - subtotal
       - tax_amount
       - cgst, sgst, igst
       - grand_total
       - amount_due = grand_total
   - Mark challans as invoiced:
     - UPDATE challans SET invoice_generated = TRUE, invoice_id = ?
   - Create ledger entry (debit):
     - Call LedgerService.createInvoiceEntry($invoiceId)
   - Audit log creation
   - COMMIT or ROLLBACK on error
   - Return invoice ID

2. public function createCashInvoice(array $data): int
   - START TRANSACTION
   - Validate data
   - Find or create cash customer:
     - Call CashCustomerService.findOrCreate($name, $mobile)
   - Generate invoice_number
   - Create invoice header:
     - invoice_type = 'Cash Invoice'
     - cash_customer_id
     - No account_id
   - Create invoice lines manually (from $data['lines'])
   - Calculate tax
   - Update invoice totals
   - Create ledger entry
   - Audit log
   - COMMIT or ROLLBACK
   - Return invoice ID

3. public function updateInvoice(int $id, array $data): bool
   - START TRANSACTION
   - Check if can edit:
     - Call InvoiceValidationService.canEdit($id)
     - Cannot edit if total_paid > 0
     - Throw exception if cannot edit
   - Get existing invoice
   - Delete existing lines
   - Recreate lines
   - Recalculate tax and totals
   - Update invoice
   - Update ledger entry (or delete and recreate)
   - Audit log (before/after)
   - COMMIT or ROLLBACK
   - Return success

4. public function deleteInvoice(int $id): bool
   - START TRANSACTION
   - Check if can delete:
     - Cannot delete if total_paid > 0
     - Cannot delete if delivered
   - Get invoice
   - Revert challan flags:
     - UPDATE challans SET invoice_generated = FALSE, invoice_id = NULL
       WHERE invoice_id = ?
   - Delete ledger entry (soft delete)
   - Soft delete invoice (is_deleted = TRUE)
   - Audit log
   - COMMIT or ROLLBACK
   - Return success

5. public function postInvoice(int $id): bool
   - Check permission
   - Update invoice_status = 'Posted'
   - Set posted_at = NOW()
   - Audit log
   - Return success

6. public function getInvoiceById(int $id): ?array
   - Get invoice with:
     - lines
     - account/cash_customer
     - payments
     - challans
   - Company filter applied
   - Return invoice array

7. public function getInvoicesByAccount(int $accountId): array
   - Get all invoices for account
   - Include only unpaid or partially paid
   - For payment screen

VALIDATION (critical):

- Invoice date not in future
- Challan availability
- Tax calculation accuracy
- Amount calculations correct

BUSINESS RULES (enforce strictly):

- Invoice number sequential (use NumberingService with row lock)
- Cannot edit after payment received
- Cannot delete if any payment made
- Ledger entry must be created atomically
- Challans must be locked after invoicing
- All operations in transaction

TRANSACTION BOUNDARIES (critical):

- Each method wrapped in transaction
- Rollback on ANY error
- Commit only if all operations succeed
- No partial state allowed

ERROR HANDLING:

- ValidationException for invalid data
- BusinessRuleException for workflow violations
- DatabaseException for DB errors
- Log all errors before throwing

AUDIT LOGGING:

- Log create, update, delete operations
- Capture before/after JSON snapshots
- Log user, timestamp, IP

DELIVERABLES:
Complete InvoiceService.php file (expect 600+ lines)

ACCEPTANCE CRITERIA:

- Invoice created from multiple challans
- Cash invoice created manually
- Invoice numbering sequential without gaps
- Tax calculated correctly
- Ledger entry created
- Challans marked as invoiced
- Cannot edit after payment
- All operations transactional
- Audit trail complete
- No errors under concurrent load

```

---

## PHASE 5: PAYMENT & GOLD ADJUSTMENT (MOST COMPLEX)

### 🎯 TASK 5.2: GOLD ADJUSTMENT SERVICE

#### Subtask 5.2.1: Create GoldAdjustmentService - MOST COMPLEX
```

[PASTE .antigravity RULES FIRST]

TASK: Generate GoldAdjustmentService for gold weight adjustment with tax recalculation

FILE: app/Services/Payment/GoldAdjustmentService.php

CONTEXT:

- After challan processing, actual gold weight may differ from estimated
- Customer brings jewelry for payment + weight adjustment
- Gold difference is adjusted: (new_weight - old_weight) × gold_rate
- Tax must be recalculated on adjusted amounts
- Creates additional ledger entry (debit or credit)
- Can only be applied ONCE per invoice

COMPLEXITY:
This is the MOST COMPLEX service in the system because:

1. Multi-line gold adjustment calculation
2. Tax recalculation after adjustment
3. Invoice total update
4. Ledger entry creation
5. All atomic (transaction required)
6. Cannot be applied twice

REQUIREMENTS:
Create GoldAdjustmentService with:

DEPENDENCIES:

- InvoiceModel
- InvoiceLineModel
- GoldRateModel
- TaxCalculationService
- LedgerService
- AuditService
- DB connection

METHODS REQUIRED:

1. public function calculateAdjustment(int $invoiceId, array $lineAdjustments, float $goldRate): array
   - Get invoice with lines
   - For each line in lineAdjustments:
     - line_id: invoice_line_id
     - new_gold_weight: float
   - For each line:
     - Get original: original_weight, original_amount
     - Calculate:
       - gold_difference = new_gold_weight - original_weight
       - adjustment_amount = gold_difference × goldRate
       - adjusted_line_amount = original_amount + adjustment_amount
       - adjusted_gold_weight = new_gold_weight
   - Aggregate:
     - total_adjustment_amount = SUM(all adjustment_amounts)
     - adjusted_subtotal = SUM(all adjusted_line_amounts)
   - Calculate tax on adjusted_subtotal:
     - Call TaxCalculationService.calculateTaxFromInclusive()
     - Get: cgst, sgst, igst
   - Calculate adjusted_grand_total = adjusted_subtotal + tax_amount
   - Return: [
     'line_adjustments' => [ // For each line
     'line_id' => int,
     'original_weight' => float,
     'new_weight' => float,
     'gold_difference' => float,
     'adjustment_amount' => float,
     'adjusted_line_amount' => float
     ],
     'total_adjustment_amount' => float,
     'adjusted_subtotal' => float,
     'adjusted_tax_amount' => float,
     'adjusted_cgst' => float,
     'adjusted_sgst' => float,
     'adjusted_igst' => float,
     'adjusted_grand_total' => float,
     'old_grand_total' => float,
     'difference' => float // adjusted_grand_total - old_grand_total
     ]

2. public function applyGoldAdjustment(int $invoiceId, array $lineAdjustments, float $goldRate): bool
   - START TRANSACTION
   - Validate:
     - Check invoice.gold_adjustment_applied = FALSE
     - Throw exception if already adjusted: "Gold adjustment already applied"
     - Check gold rate exists and > 0
     - Validate all line_ids belong to invoice
     - Validate new_gold_weight >= 0 for all lines
   - Call calculateAdjustment() to get calculations
   - Update invoice_lines table:
     - For each line in lineAdjustments:
       - UPDATE invoice_lines SET
         gold_weight = new_gold_weight,
         amount = adjusted_line_amount,
         gold_adjusted = TRUE
         WHERE id = line_id
   - Update invoices table:
     - UPDATE invoices SET
       subtotal = adjusted_subtotal,
       tax_amount = adjusted_tax_amount,
       cgst = adjusted_cgst,
       sgst = adjusted_sgst,
       igst = adjusted_igst,
       grand_total = adjusted_grand_total,
       amount_due = adjusted_grand_total - total_paid,
       gold_adjustment_applied = TRUE,
       gold_adjustment_amount = total_adjustment_amount,
       gold_rate_used = goldRate,
       gold_adjustment_date = NOW()
       WHERE id = invoiceId
   - Create ledger entry for adjustment:
     - If adjustment_amount > 0 (customer owes more):
       - Debit entry: increases balance
     - If adjustment_amount < 0 (customer owes less):
       - Credit entry: decreases balance
     - Call LedgerService.createAdjustmentEntry($invoiceId, $adjustmentAmount)
   - Audit log:
     - Capture before/after invoice data
     - Store line-by-line adjustment details
   - COMMIT or ROLLBACK on error
   - Return TRUE

3. public function validateAdjustment(int $invoiceId, array $lineAdjustments): bool
   - Get invoice
   - Check gold_adjustment_applied = FALSE
   - Check gold rate available (today or latest)
   - For each line in lineAdjustments:
     - Check line_id exists in invoice_lines
     - Check new_gold_weight >= 0
   - Return TRUE or throw ValidationException

4. public function getAdjustmentPreview(int $invoiceId, array $lineAdjustments, float $goldRate): array
   - Call calculateAdjustment()
   - Return preview data (without saving)
   - Used in UI to show preview before confirming

CALCULATION EXAMPLE:

```
Original Invoice:
- Line 1: Gold weight = 10g, Amount = ₹10,300 (with 3% tax)
- Line 2: Gold weight = 5g, Amount = ₹5,150
- Grand Total: ₹15,450

Gold Rate: ₹6,000/gram

New Weights After Processing:
- Line 1: New weight = 10.5g (increased by 0.5g)
- Line 2: New weight = 4.8g (decreased by 0.2g)

Calculations:
Line 1:
- Gold difference = 10.5 - 10 = +0.5g
- Adjustment amount = 0.5 × 6000 = +₹3,000
- Adjusted line amount = 10,300 + 3,000 = ₹13,300

Line 2:
- Gold difference = 4.8 - 5 = -0.2g
- Adjustment amount = -0.2 × 6000 = -₹1,200
- Adjusted line amount = 5,150 - 1,200 = ₹3,950

Total adjustment = +₹3,000 - ₹1,200 = +₹1,800

Adjusted subtotal = ₹13,300 + ₹3,950 = ₹17,250
Tax calculation (3% inclusive):
- Tax amount = 17,250 × 3 / 103 = ₹502.91
- CGST = ₹251.46
- SGST = ₹251.45
Adjusted grand total = ₹17,250

Old grand total = ₹15,450
Difference = ₹17,250 - ₹15,450 = +₹1,800

Ledger Entry:
- Type: Adjustment
- Amount: +₹1,800 (Debit - customer owes more)
```

BUSINESS RULES (enforce strictly):

- Can only apply adjustment ONCE per invoice
- Must have gold rate (today's or specified)
- New weights must be >= 0
- Tax recalculated on adjusted amounts
- Invoice totals updated
- Ledger entry created
- Amount_due updated (grand_total - total_paid)
- All operations in transaction

TRANSACTION SAFETY:

- ENTIRE operation must be atomic
- Update lines + Update invoice + Create ledger = ONE transaction
- Rollback if ANY step fails
- No partial adjustments allowed

ERROR HANDLING:

- ValidationException if already adjusted
- ValidationException if invalid weights
- BusinessRuleException if no gold rate
- DatabaseException for DB errors
- Log all errors

AUDIT LOGGING:

- Log before/after invoice data
- Log line-by-line adjustments
- Log gold rate used
- Log user who applied adjustment

DELIVERABLES:
Complete GoldAdjustmentService.php file (expect 400+ lines)

ACCEPTANCE CRITERIA:

- Adjustment calculated correctly (multi-line)
- Tax recalculated on adjusted amounts
- Invoice totals updated
- Ledger entry created (debit or credit)
- Cannot apply adjustment twice
- All operations transactional
- Rollback working on error
- Audit trail complete
- Preview calculation accurate

```

---

### 🎯 TASK 5.3: PAYMENT SERVICE

#### Subtask 5.3.1: Create PaymentService with Gold Adjustment Integration
```

[PASTE .antigravity RULES FIRST]

TASK: Generate PaymentService with atomic payment + gold adjustment

FILE: app/Services/Payment/PaymentService.php

CONTEXT:

- Record payment against invoice
- Optional: Apply gold adjustment WITH payment (atomic operation)
- Update invoice balances
- Create ledger entries
- All operations MUST be transactional

COMPLEXITY:
This service integrates:

1. Payment recording
2. Gold adjustment (optional)
3. Invoice balance update
4. Ledger entry creation (payment + adjustment)
5. All atomic

REQUIREMENTS:
Create PaymentService with:

DEPENDENCIES:

- PaymentModel
- InvoiceModel
- GoldAdjustmentService
- LedgerService
- AuditService
- DB connection

METHODS REQUIRED:

1. public function recordPayment(int $invoiceId, array $paymentData, ?array $goldAdjustment = null): int
   - START TRANSACTION
   - Validate payment data:
     - payment_amount > 0
     - payment_amount <= invoice.amount_due
     - payment_mode in ['Cash', 'Cheque', 'Bank Transfer', 'UPI', 'Card']
     - If cheque: cheque_number, cheque_date, bank_name required
   - Get invoice (with row lock):
     - SELECT \* FROM invoices WHERE id = ? FOR UPDATE
   - If gold adjustment provided:
     - Validate goldAdjustment array:
       - 'gold_rate' => float
       - 'line_adjustments' => [['line_id' => int, 'new_gold_weight' => float], ...]
     - Call GoldAdjustmentService.applyGoldAdjustment($invoiceId, $lineAdjustments, $goldRate)
     - This updates invoice totals and creates adjustment ledger entry
     - Refetch invoice to get updated amounts
   - Create payment record:
     - payment_date
     - invoice_id
     - payment_mode
     - payment_amount
     - cheque/bank details (if applicable)
     - received_by = current user ID
     - company_id from session
   - Get payment ID
   - Update invoice balances:
     - total_paid = invoice.total_paid + payment_amount
     - amount_due = invoice.grand_total - total_paid
     - payment_status:
       - If amount_due == 0: 'Paid'
       - Else if total_paid > 0: 'Partial Paid'
       - Else: 'Pending'
     - invoice_status:
       - If amount_due == 0: 'Paid'
       - Else if total_paid > 0: 'Partially Paid'
       - Else: 'Pending Payment'
     - UPDATE invoices SET total_paid = ?, amount_due = ?, payment_status = ?, invoice_status = ?
   - Create payment ledger entry (credit):
     - Call LedgerService.createPaymentEntry($paymentId)
     - This decreases customer balance
   - Audit log payment:
     - Log payment details
     - If gold adjustment: log adjustment details
   - COMMIT or ROLLBACK on error
   - Return payment ID

2. public function deletePayment(int $id): bool
   - START TRANSACTION
   - Check if allowed:
     - Only admin can delete payment
     - Cannot delete if invoice delivered
   - Get payment
   - Get invoice
   - Reverse payment:
     - total_paid = invoice.total_paid - payment.payment_amount
     - amount_due = invoice.grand_total - total_paid
     - Update payment_status and invoice_status
   - Delete ledger entry (soft delete)
   - Soft delete payment (is_deleted = TRUE)
   - Audit log deletion
   - COMMIT or ROLLBACK
   - Return success

3. public function getPaymentHistory(int $invoiceId): array
   - Get all payments for invoice
   - Include user who received payment
   - Order by payment_date DESC
   - Return array

4. private function validatePaymentData(int $invoiceId, array $data): void
   - Get invoice
   - Check payment_amount > 0
   - Check payment_amount <= invoice.amount_due
   - Check payment_mode valid
   - If cheque: validate cheque fields
   - Throw ValidationException if invalid

PAYMENT + GOLD ADJUSTMENT FLOW:

```
User submits payment with gold adjustment:
{
  "payment_amount": 15000,
  "payment_mode": "Cash",
  "gold_adjustment": {
    "gold_rate": 6000,
    "line_adjustments": [
      {"line_id": 1, "new_gold_weight": 10.5},
      {"line_id": 2, "new_gold_weight": 4.8}
    ]
  }
}

Execution:
1. START TRANSACTION
2. Apply gold adjustment:
   - Update invoice line weights and amounts
   - Recalculate tax
   - Update invoice totals
   - Create adjustment ledger entry (debit or credit)
3. Refetch invoice (now has adjusted grand_total)
4. Create payment record
5. Update invoice balances (total_paid, amount_due)
6. Create payment ledger entry (credit)
7. COMMIT
```

EXAMPLE SCENARIO:

```
Original Invoice:
- Grand Total: ₹15,450
- Amount Due: ₹15,450

Gold Adjustment Applied:
- Adjustment: +₹1,800 (customer owes more)
- Adjusted Grand Total: ₹17,250
- Ledger Entry: Debit ₹1,800

Payment Received:
- Payment Amount: ₹15,000
- Ledger Entry: Credit ₹15,000

Final State:
- Grand Total: ₹17,250
- Total Paid: ₹15,000
- Amount Due: ₹2,250
- Payment Status: Partial Paid

Ledger Entries Created:
1. Invoice: Debit ₹15,450
2. Adjustment: Debit ₹1,800
3. Payment: Credit ₹15,000
Net Balance: ₹2,250 (customer owes)
```

BUSINESS RULES:

- Payment amount cannot exceed amount_due (before adjustment)
- Gold adjustment + payment is atomic (both succeed or both fail)
- Adjustment ledger entry created BEFORE payment ledger entry
- Invoice balances updated AFTER adjustment
- Cannot delete payment after delivery
- All operations in transaction

TRANSACTION SAFETY:

- CRITICAL: Entire operation must be atomic
- If adjustment fails, payment should not be recorded
- If payment fails, adjustment should be rolled back
- Use try-catch with transRollback()

ERROR HANDLING:

- ValidationException for invalid payment data
- ValidationException if payment exceeds amount due
- GoldAdjustmentException if adjustment fails
- DatabaseException for DB errors
- Rollback on ANY error
- Log all errors

AUDIT LOGGING:

- Log payment details
- If gold adjustment applied: log full adjustment details
- Capture before/after invoice state

DELIVERABLES:
Complete PaymentService.php file (expect 350+ lines)

ACCEPTANCE CRITERIA:

- Payment recorded correctly
- Payment with gold adjustment works atomically
- Invoice balances updated
- Ledger entries created (payment + adjustment)
- Cannot exceed amount due
- All operations transactional
- Rollback working on error
- Audit trail complete
- Integration with GoldAdjustmentService seamless

```

---

## PHASE 6: LEDGER MANAGEMENT

### 🎯 TASK 6.2: LEDGER SERVICE

#### Subtask 6.2.1: Create LedgerService - APPEND-ONLY
```

[PASTE .antigravity RULES FIRST]

TASK: Generate LedgerService with append-only ledger entries

FILE: app/Services/Ledger/LedgerService.php

CONTEXT:

- Ledger is append-only (NEVER update or delete entries)
- Every transaction creates ledger entry
- Running balance calculated
- Immutable financial record

CRITICAL RULE:
❌ NEVER update ledger_entries
❌ NEVER delete ledger_entries  
✅ ONLY insert ledger_entries

REQUIREMENTS:
Create LedgerService with:

DEPENDENCIES:

- LedgerEntryModel
- InvoiceModel
- PaymentModel
- AccountModel
- CashCustomerModel

METHODS REQUIRED:

1. public function createEntry(array $data): int
   - Calculate balance_after:
     - Get last balance for account/customer
     - If entry_type = 'Debit': balance_after = last_balance + amount
     - If entry_type = 'Credit': balance_after = last_balance - amount
   - Insert ledger entry:
     - company_id from session
     - account_id or cash_customer_id
     - entry_date
     - entry_type ('Debit' or 'Credit')
     - amount
     - description
     - reference_type ('Invoice', 'Payment', 'Adjustment', 'Opening Balance')
     - reference_id
     - balance_after
   - Return entry ID
   - NOTE: This is the ONLY method that writes to ledger

2. public function getLastBalance(int $accountId = null, int $cashCustomerId = null): float
   - Get most recent ledger entry for account/customer
   - SQL: SELECT balance_after FROM ledger_entries
     WHERE (account_id = ? OR cash_customer_id = ?)
     AND company_id = ?
     ORDER BY entry_date DESC, id DESC
     LIMIT 1
   - Return balance or 0.00 if no entries

3. public function createOpeningBalanceEntry(int $accountId, float $amount, string $date): int
   - Create ledger entry:
     - entry_type = 'Debit' (opening balance increases customer debt)
     - reference_type = 'Opening Balance'
     - reference_id = $accountId
     - amount = $amount
     - entry_date = $date
     - description = "Opening Balance"
   - Call createEntry()
   - Return entry ID

4. public function createInvoiceEntry(int $invoiceId): int
   - Get invoice
   - Create ledger entry:
     - entry_type = 'Debit' (invoice increases balance)
     - amount = invoice.grand_total
     - reference_type = 'Invoice'
     - reference_id = $invoiceId
     - account_id or cash_customer_id from invoice
     - entry_date = invoice.invoice_date
     - description = "Invoice #{invoice_number}"
   - Call createEntry()
   - Return entry ID

5. public function createPaymentEntry(int $paymentId): int
   - Get payment
   - Get invoice
   - Create ledger entry:
     - entry_type = 'Credit' (payment decreases balance)
     - amount = payment.payment_amount
     - reference_type = 'Payment'
     - reference_id = $paymentId
     - account_id or cash_customer_id from invoice
     - entry_date = payment.payment_date
     - description = "Payment received for Invoice #{invoice_number}"
   - Call createEntry()
   - Return entry ID

6. public function createAdjustmentEntry(int $invoiceId, float $amount, string $description): int
   - Get invoice
   - Create ledger entry:
     - entry_type = $amount > 0 ? 'Debit' : 'Credit'
     - amount = abs($amount)
     - reference_type = 'Adjustment'
     - reference_id = $invoiceId
     - account_id or cash_customer_id from invoice
     - entry_date = NOW()
     - description = $description
   - Call createEntry()
   - Return entry ID

7. public function getLedgerByAccount(int $accountId, string $fromDate, string $toDate): array
   - Get opening balance (before fromDate)
   - Get all entries between dates
   - Return array with:
     - opening_balance
     - entries => [...]
     - closing_balance

8. public function getLedgerByCashCustomer(int $cashCustomerId, string $fromDate, string $toDate): array
   - Same as getLedgerByAccount

IMMUTABILITY ENFORCEMENT:

- LedgerEntryModel should not have update/delete methods
- Only insert allowed
- Use DB constraints if possible (trigger to prevent updates/deletes)

BALANCE CALCULATION:

- Always calculated from previous balance
- Debit increases balance (customer owes)
- Credit decreases balance (customer pays)
- Running balance stored in balance_after column

INTEGRATION:

- InvoiceService calls createInvoiceEntry() after invoice creation
- PaymentService calls createPaymentEntry() after payment
- GoldAdjustmentService calls createAdjustmentEntry() after adjustment
- AccountService calls createOpeningBalanceEntry() on account creation

ERROR HANDLING:

- If createEntry() fails, entire parent transaction should rollback
- Log all ledger operations
- Never silently fail

DELIVERABLES:
Complete LedgerService.php file

ACCEPTANCE CRITERIA:

- Ledger entries append-only (no updates/deletes)
- Balance calculated correctly
- Debit/credit logic correct
- Running balance accurate
- Integrated with Invoice/Payment/Adjustment services
- All entries immutable

```

---

## PHASE 8: REPORTING

### 🎯 TASK 8.1: LEDGER REPORTS

#### Subtask 8.1.1: Create LedgerReportService
```

[PASTE .antigravity RULES FIRST]

TASK: Generate LedgerReportService for customer ledger reports

FILE: app/Services/Report/LedgerReportService.php

CONTEXT:

- Generate account and cash customer ledger statements
- Calculate opening/closing balances
- Export to PDF/Excel
- Must be accurate (matches ledger_entries)

REQUIREMENTS:
Create LedgerReportService with:

DEPENDENCIES:

- LedgerService
- AccountModel
- CashCustomerModel
- Dompdf or TCPDF (for PDF)
- PhpSpreadsheet (for Excel)

METHODS REQUIRED:

1. public function generateAccountLedger(int $accountId, string $fromDate, string $toDate): array
   - Get account details
   - Calculate opening balance:
     - SUM of all entries before fromDate
     - Or call LedgerService to get balance on fromDate-1
   - Get all ledger entries in date range:
     - WHERE account_id = ?
     - AND entry_date BETWEEN ? AND ?
     - ORDER BY entry_date ASC, id ASC
   - For each entry:
     - Show: date, description, debit, credit, balance
     - Running balance maintained
   - Calculate closing balance (last entry's balance_after)
   - Return array:
     [
     'account' => [...],
     'from_date' => date,
     'to_date' => date,
     'opening_balance' => float,
     'entries' => [
     ['date', 'description', 'debit', 'credit', 'balance'],
     ...
     ],
     'closing_balance' => float,
     'total_debits' => float,
     'total_credits' => float
     ]

2. public function generateCashCustomerLedger(int $cashCustomerId, string $fromDate, string $toDate): array
   - Same logic as generateAccountLedger
   - Use cash_customer_id instead

3. public function exportLedgerToPDF(array $ledgerData): string
   - Generate PDF from ledger data
   - Include:
     - Company header
     - Customer details
     - Date range
     - Table: Date | Description | Debit | Credit | Balance
     - Opening balance row
     - All entries
     - Closing balance row
     - Total debits, total credits
   - Return PDF file path or base64 string

4. public function exportLedgerToExcel(array $ledgerData): string
   - Generate Excel from ledger data
   - Similar structure as PDF
   - Return file path

FORMAT EXAMPLE:

```
LEDGER STATEMENT
Company: XYZ Jewellers
Customer: ABC Retail Store
Period: 01/01/2026 to 31/01/2026

Date       | Description               | Debit    | Credit   | Balance
-----------|---------------------------|----------|----------|----------
01/01/2026 | Opening Balance           |          |          | 50,000.00
05/01/2026 | Invoice #INV0001          | 15,450.00|          | 65,450.00
08/01/2026 | Payment received          |          | 10,000.00| 55,450.00
10/01/2026 | Invoice #INV0002          | 20,600.00|          | 76,050.00
15/01/2026 | Gold Adjustment           |  1,800.00|          | 77,850.00
20/01/2026 | Payment received          |          | 15,000.00| 62,850.00
25/01/2026 | Invoice #INV0003          | 10,300.00|          | 73,150.00
31/01/2026 | Closing Balance           |          |          | 73,150.00
-----------|---------------------------|----------|----------|----------
           | TOTALS                    | 48,150.00| 25,000.00|
```

ACCURACY:

- Opening + Debits - Credits = Closing Balance
- Must match ledger_entries exactly
- No discrepancies allowed

DELIVERABLES:
Complete LedgerReportService.php file

ACCEPTANCE CRITERIA:

- Ledger report accurate
- Opening/closing balance correct
- Running balance matches ledger_entries
- PDF export working
- Excel export working
- Company filter applied

```

---

## PHASE 10: TESTING

### 🎯 TASK 10.1: UNIT TESTING

#### Subtask 10.1.3: Test GoldAdjustmentService
```

[PASTE .antigravity RULES FIRST]

TASK: Generate PHPUnit tests for GoldAdjustmentService

FILE: tests/unit/Services/GoldAdjustmentServiceTest.php

CONTEXT:

- Test all scenarios of gold adjustment
- Test positive and negative adjustments
- Test tax recalculation
- Test transaction rollback
- Test validation

REQUIREMENTS:
Create PHPUnit test class:

TEST CLASS: GoldAdjustmentServiceTest extends CIUnitTestCase

SETUP:

- protected $goldAdjustmentService;
- protected $invoiceService;
- protected $db;

- protected function setUp(): void
  - Create test company, account, invoice
  - Create test gold rate
  - Initialize services

TESTS REQUIRED:

1. public function testPositiveAdjustmentIncreasesTotal()
   - Create invoice with 2 lines
   - Apply positive adjustment (new weights > old weights)
   - Assert invoice.grand_total increased
   - Assert invoice.gold_adjustment_applied = TRUE
   - Assert ledger debit entry created

2. public function testNegativeAdjustmentDecreasesTotal()
   - Create invoice
   - Apply negative adjustment (new weights < old weights)
   - Assert invoice.grand_total decreased
   - Assert ledger credit entry created

3. public function testMultiLineAdjustment()
   - Create invoice with 3 lines
   - Apply mixed adjustments (some +, some -)
   - Assert totals calculated correctly
   - Assert each line updated

4. public function testCannotAdjustTwice()
   - Create invoice
   - Apply adjustment
   - Try to apply again
   - Assert exception thrown: "Gold adjustment already applied"

5. public function testTaxRecalculatedAfterAdjustment()
   - Create invoice with known tax
   - Apply adjustment
   - Assert new tax calculated
   - Assert CGST/SGST updated

6. public function testLedgerEntryCreated()
   - Create invoice
   - Apply adjustment
   - Assert ledger entry exists
   - Assert entry_type correct (Debit or Credit)
   - Assert amount matches adjustment

7. public function testTransactionRollbackOnError()
   - Create invoice
   - Mock LedgerService to throw exception
   - Try to apply adjustment
   - Assert transaction rolled back
   - Assert invoice.gold_adjustment_applied still FALSE
   - Assert invoice_lines not updated

8. public function testValidationRequiresGoldRate()
   - Create invoice
   - Try to apply adjustment without gold rate
   - Assert ValidationException thrown

9. public function testValidationRequiresPositiveWeights()
   - Create invoice
   - Try to apply adjustment with negative weight
   - Assert ValidationException thrown

10. public function testCalculateAdjustmentPreview()
    - Create invoice
    - Call calculateAdjustment() (without applying)
    - Assert preview data correct
    - Assert invoice not modified

ASSERTION EXAMPLES:

```php
// Assert invoice updated
$invoice = $this->invoiceModel->find($invoiceId);
$this->assertTrue($invoice['gold_adjustment_applied']);
$this->assertEquals(17250.00, $invoice['grand_total']);

// Assert ledger entry created
$ledgerEntries = $this->ledgerModel
    ->where('reference_type', 'Adjustment')
    ->where('reference_id', $invoiceId)
    ->findAll();
$this->assertCount(1, $ledgerEntries);
$this->assertEquals('Debit', $ledgerEntries[0]['entry_type']);
```

DATA CLEANUP:

- protected function tearDown(): void
  - Delete test data
  - Reset database

DELIVERABLES:
Complete GoldAdjustmentServiceTest.php file with 10+ tests

ACCEPTANCE CRITERIA:

- All tests pass
- Edge cases covered
- Transaction rollback tested
- Validation tested
- 100% code coverage for GoldAdjustmentService

```

---

## USAGE GUIDE

### How to Use These Prompts

**1. For Models:**
- Copy the prompt for the model you're creating
- Provide .antigravity file to AI first
- Paste the model prompt
- AI generates complete model

**2. For Services:**
- Copy the service prompt
- Include table schema from complete_database_schema.sql
- Include method signatures from SERVICES_ARCHITECTURE.md
- Provide .antigravity file
- Paste the service prompt
- AI generates complete service with all methods

**3. For Controllers:**
- Copy the controller prompt
- Reference the service methods
- Provide .antigravity file
- Paste the controller prompt

**4. For Tests:**
- Copy the test prompt
- Reference the service being tested
- Provide .antigravity file
- Paste the test prompt

### Prompt Template Structure

Each prompt follows this structure:
```

[PASTE .antigravity RULES FIRST]

TASK: [Clear task description]

FILE: [Exact file path]

CONTEXT: [Business context and purpose]

REQUIREMENTS: [Detailed requirements]

METHODS REQUIRED: [All methods with signatures and logic]

BUSINESS RULES: [Critical rules to enforce]

TRANSACTION SAFETY: [Transaction requirements]

ERROR HANDLING: [How to handle errors]

DELIVERABLES: [What to generate]

ACCEPTANCE CRITERIA: [How to validate]

```

### Tips for Best Results

1. **Always provide .antigravity first** - This sets the coding standards
2. **Copy complete prompt** - Don't modify or truncate
3. **Include table schema** - For models and services that query DB
4. **Reference dependencies** - Mention related services
5. **Validate output** - Use .antigravity checklist to validate generated code

---

## END OF PROMPTS FILE

**Total Prompts:** 50+
**Coverage:** All 10 phases
**Ready to use with AI:** Yes
**Compatible with:** ChatGPT, Claude, Copilot, any LLM

**Use these prompts to generate production-ready, error-free code for every task! 🚀**
```

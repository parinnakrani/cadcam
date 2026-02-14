

---

### TASK 2.3: ACCOUNT & CASH CUSTOMER MANAGEMENT (CONTINUED)

---

#### Subtask 2.3.5: Create AccountService

```
[PASTE .antigravity RULES FIRST]

TASK: Generate AccountService with ledger integration

FILE: app/Services/Customer/AccountService.php

CONTEXT:
- Handle account customer CRUD operations
- Create opening balance ledger entry on account creation
- Update current balance via ledger service
- Cannot delete if transactions exist
- Validate GST and PAN format
- Auto-generate account code if not provided

REQUIREMENTS:
Create AccountService class with:

DEPENDENCIES (inject in __construct):
- AccountModel
- StateModel (validate state exists)
- LedgerService (for opening balance entry)
- ValidationService (for GST/PAN validation)
- AuditService

METHODS REQUIRED:

1. public function createAccount(array $data): int
   - Validate required fields present
   - Auto-set company_id from session
   - If account_code not provided: auto-generate using AccountModel->generateNextAccountCode()
   - Validate billing_state_id exists
   - If shipping address provided: validate shipping_state_id
   - If same_as_billing = TRUE: copy billing address to shipping
   - Validate GST number format if provided (ValidationService)
   - Validate PAN number format if provided (ValidationService)
   - Start DB transaction
   - Insert account record
   - If opening_balance > 0:
     - Create opening balance ledger entry via LedgerService
     - Entry type: 'Opening Balance'
     - If opening_balance_type = 'Debit': debit entry (customer owes us)
     - If opening_balance_type = 'Credit': credit entry (we owe customer)
   - Commit transaction
   - Audit log create action
   - Return account ID

2. public function updateAccount(int $id, array $data): bool
   - Validate account exists and belongs to company
   - Store before data for audit
   - If account_code changed: check uniqueness
   - Validate states if changed
   - Validate GST/PAN if changed
   - Opening balance cannot be changed after creation (business rule)
   - Update account record
   - Audit log update action (include before/after)
   - Return TRUE

3. public function deleteAccount(int $id): bool
   - Validate account exists and belongs to company
   - Check if account used: AccountModel->isAccountUsedInTransactions($id)
   - If used: throw exception "Cannot delete account with transactions"
   - Soft delete: set is_deleted = TRUE
   - Audit log delete action
   - Return TRUE

4. public function getAccountById(int $id): ?array
   - Call AccountModel->getAccountWithBalance($id)
   - Validate belongs to company
   - Return account data with balance or null

5. public function getLedgerBalance(int $accountId): float
   - Call LedgerService->getAccountBalance($accountId)
   - Return current balance

6. public function updateCurrentBalance(int $accountId, float $newBalance): bool
   - Call AccountModel->updateCurrentBalance($accountId, $newBalance)
   - Return success

7. public function getActiveAccounts(): array
   - Call AccountModel->getActiveAccounts()
   - Return array for dropdown

8. public function searchAccounts(string $query): array
   - Call AccountModel->searchAccounts($query)
   - Return results for autocomplete

9. private function createOpeningBalanceLedgerEntry(int $accountId, float $amount, string $type): void
   - Call LedgerService->createOpeningBalanceEntry()
   - Pass: account_id, amount, type (Debit/Credit)
   - Entry date = account creation date
   - Description = "Opening Balance"

10. private function validateAccountData(array $data): void
    - Check all required fields present
    - Validate mobile format (10 digits)
    - Validate email format if provided
    - Validate pincode format (6 digits)
    - Throw ValidationException if invalid

ERROR HANDLING:
- Throw AccountNotFoundException if not found
- Throw ValidationException for invalid data
- Throw AccountInUseException if cannot delete
- Rollback transaction on error
- Log all exceptions

DELIVERABLES:
Complete AccountService.php file

ACCEPTANCE CRITERIA:
- CRUD operations working
- Opening balance ledger entry created
- GST/PAN validation working
- Cannot delete if transactions exist
- All actions audit logged
- Transaction safety ensured
```

---

#### Subtask 2.3.6: Create CashCustomerService

```
[PASTE .antigravity RULES FIRST]

TASK: Generate CashCustomerService with deduplication

FILE: app/Services/Customer/CashCustomerService.php

CONTEXT:
- Handle cash customer CRUD operations
- Deduplication: prevent duplicate name+mobile combinations
- Find-or-create pattern for quick customer addition
- No opening balance (all transactions immediate)
- Autocomplete search for quick lookup

REQUIREMENTS:
Create CashCustomerService class with:

DEPENDENCIES (inject in __construct):
- CashCustomerModel
- StateModel (validate state if provided)
- ValidationService
- AuditService

METHODS REQUIRED:

1. public function findOrCreate(string $name, string $mobile, array $additionalData = []): int
   - Trim and clean name and mobile
   - Check if customer exists: CashCustomerModel->findByNameAndMobile($name, $mobile)
   - If exists: return existing customer ID
   - If not exists:
     - Create new customer with provided data
     - Return new customer ID
   - This method enables quick customer creation at billing time

2. public function createCashCustomer(array $data): int
   - Validate required fields: name, mobile
   - Auto-set company_id from session
   - Check duplicate: findByNameAndMobile()
   - If duplicate exists: throw exception "Customer already exists with this name and mobile"
   - Validate state_id if provided
   - Insert cash customer record
   - Audit log create action
   - Return customer ID

3. public function updateCashCustomer(int $id, array $data): bool
   - Validate customer exists and belongs to company
   - Store before data for audit
   - If name or mobile changed: check no duplicate with new combination
   - Update cash customer record
   - Audit log update action
   - Return TRUE

4. public function deleteCashCustomer(int $id): bool
   - Validate customer exists and belongs to company
   - Check if used: CashCustomerModel->isCashCustomerUsedInTransactions($id)
   - If used: throw exception "Cannot delete customer with transactions"
   - Soft delete: set is_deleted = TRUE
   - Audit log delete action
   - Return TRUE

5. public function getCashCustomerById(int $id): ?array
   - Call CashCustomerModel->find($id)
   - Validate belongs to company
   - Return customer data or null

6. public function getActiveCashCustomers(): array
   - Call CashCustomerModel->getActiveCashCustomers()
   - Return array for dropdown

7. public function searchCashCustomers(string $query): array
   - Call CashCustomerModel->searchCashCustomers($query)
   - Return results for autocomplete
   - Format: [{id, name, mobile}, ...]

8. public function mergeDuplicates(int $primaryId, int $secondaryId): bool
   - Validate both customers exist and belong to company
   - Update all invoices: set cash_customer_id = primaryId where cash_customer_id = secondaryId
   - Update all challans: set cash_customer_id = primaryId where cash_customer_id = secondaryId
   - Delete secondary customer (soft delete)
   - Audit log merge action
   - Return TRUE
   - **Use transaction**

9. private function validateCashCustomerData(array $data): void
   - Check required: name, mobile
   - Validate mobile format (10 digits)
   - Validate email format if provided
   - Throw ValidationException if invalid

ERROR HANDLING:
- Throw CashCustomerNotFoundException if not found
- Throw ValidationException for invalid data
- Throw DuplicateCustomerException if duplicate found
- Throw CustomerInUseException if cannot delete
- Log all exceptions

DELIVERABLES:
Complete CashCustomerService.php file

ACCEPTANCE CRITERIA:
- Find-or-create pattern works
- Deduplication prevents duplicates
- Cannot delete if transactions exist
- Merge duplicates function works
- All actions audit logged
- Search works for autocomplete
```

---

#### Subtask 2.3.7: Create AccountController

```
[PASTE .antigravity RULES FIRST]

TASK: Generate AccountController with CRUD endpoints

FILE: app/Controllers/Customers/AccountController.php

CONTEXT:
- Handle HTTP requests for account customer management
- Thin controller, business logic in AccountService
- Permission checks: account.create, account.edit, account.delete, account.view
- JSON API responses for AJAX calls
- State dropdown for address forms

REQUIREMENTS:
Create CodeIgniter 4 Controller extending BaseController:

DEPENDENCIES (inject in __construct):
- AccountService
- StateModel (for state dropdown)
- PermissionService

ROUTES REQUIRED:
- GET /customers/accounts → index() (list all accounts)
- GET /customers/accounts/create → create() (show form)
- POST /customers/accounts → store() (create new)
- GET /customers/accounts/{id} → show() (view details)
- GET /customers/accounts/{id}/edit → edit() (show edit form)
- POST /customers/accounts/{id} → update() (update existing)
- DELETE /customers/accounts/{id} → delete() (soft delete)
- GET /customers/accounts/search → search() (autocomplete API)
- GET /customers/accounts/{id}/ledger → ledger() (account ledger view)

METHODS REQUIRED:

1. public function index()
   - Check permission: account.view
   - Get query params: is_active, search, state_id
   - Load accounts via AccountService
   - If AJAX: return JSON
   - Else: load view with accounts data
   - View: app/Views/customers/accounts/index.php

2. public function create()
   - Check permission: account.create
   - Load states dropdown (StateModel->where('is_active', TRUE)->findAll())
   - Load view: app/Views/customers/accounts/create.php
   - Pass states data

3. public function store()
   - Check permission: account.create
   - Validate CSRF token
   - Get POST data
   - If same_as_billing checked: copy billing address to shipping in data
   - Call AccountService->createAccount($data)
   - Set flash message: "Account created successfully"
   - Redirect to /customers/accounts

4. public function show(int $id)
   - Check permission: account.view
   - Load account via AccountService->getAccountById($id)
   - If not found: 404
   - Load current balance via AccountService->getLedgerBalance($id)
   - Load view: app/Views/customers/accounts/show.php
   - Pass account data and balance

5. public function edit(int $id)
   - Check permission: account.edit
   - Load account via AccountService->getAccountById($id)
   - If not found: 404
   - Load states dropdown
   - Load view: app/Views/customers/accounts/edit.php
   - Pass account and states data

6. public function update(int $id)
   - Check permission: account.edit
   - Validate CSRF token
   - Get POST data
   - If same_as_billing checked: copy billing to shipping
   - Call AccountService->updateAccount($id, $data)
   - Set flash message: "Account updated successfully"
   - Redirect to /customers/accounts

7. public function delete(int $id)
   - Check permission: account.delete
   - Try: AccountService->deleteAccount($id)
   - Catch AccountInUseException: return JSON error with message
   - Set flash message: "Account deleted successfully"
   - Return JSON success

8. public function search()
   - Check permission: account.view
   - Get query param: q
   - Call AccountService->searchAccounts($q)
   - Return JSON results: [{id, account_code, account_name, mobile, current_balance}, ...]

9. public function ledger(int $id)
   - Check permission: account.view
   - Redirect to /reports/ledger/account/{id}
   - Or load ledger view directly

ERROR HANDLING:
- Catch all exceptions
- Return JSON error for AJAX
- Flash messages for web
- Log errors

DELIVERABLES:
Complete AccountController.php file

ACCEPTANCE CRITERIA:
- All CRUD operations working
- Permission checks enforced
- Address copy functionality works (same_as_billing)
- Search autocomplete works
- Cannot delete if transactions exist
- Error handling robust
```

---

#### Subtask 2.3.8: Create CashCustomerController

```
[PASTE .antigravity RULES FIRST]

TASK: Generate CashCustomerController with quick-add feature

FILE: app/Controllers/Customers/CashCustomerController.php

CONTEXT:
- Handle HTTP requests for cash customer management
- Quick-add modal for fast customer creation during billing
- Permission checks: cash_customer.create, cash_customer.edit, cash_customer.delete, cash_customer.view
- JSON API for find-or-create pattern

REQUIREMENTS:
Create CodeIgniter 4 Controller extending BaseController:

DEPENDENCIES (inject in __construct):
- CashCustomerService
- StateModel (for state dropdown)
- PermissionService

ROUTES REQUIRED:
- GET /customers/cash-customers → index() (list all)
- GET /customers/cash-customers/create → create() (show form)
- POST /customers/cash-customers → store() (create new)
- POST /customers/cash-customers/find-or-create → findOrCreate() (API)
- GET /customers/cash-customers/{id} → show() (view details)
- GET /customers/cash-customers/{id}/edit → edit() (show edit form)
- POST /customers/cash-customers/{id} → update() (update existing)
- DELETE /customers/cash-customers/{id} → delete() (soft delete)
- GET /customers/cash-customers/search → search() (autocomplete API)

METHODS REQUIRED:

1. public function index()
   - Check permission: cash_customer.view
   - Get query params: is_active, search
   - Load cash customers via CashCustomerService
   - If AJAX: return JSON
   - Else: load view with customers data
   - View: app/Views/customers/cash_customers/index.php

2. public function create()
   - Check permission: cash_customer.create
   - Load states dropdown
   - Load view: app/Views/customers/cash_customers/create.php
   - Pass states data

3. public function store()
   - Check permission: cash_customer.create
   - Validate CSRF token
   - Get POST data
   - Try: Call CashCustomerService->createCashCustomer($data)
   - Catch DuplicateCustomerException:
     - Set flash error: "Customer with this name and mobile already exists"
     - Redirect back with input
   - Set flash message: "Cash customer created successfully"
   - Redirect to /customers/cash-customers

4. public function findOrCreate()
   - Check permission: cash_customer.create
   - Validate CSRF token
   - Get POST data: name, mobile, (optional: address fields)
   - Call CashCustomerService->findOrCreate($name, $mobile, $additionalData)
   - Return JSON: {success: true, customer_id: id, message: "Customer found/created"}
   - Used during invoice creation for quick customer add

5. public function show(int $id)
   - Check permission: cash_customer.view
   - Load customer via CashCustomerService->getCashCustomerById($id)
   - If not found: 404
   - Load view: app/Views/customers/cash_customers/show.php
   - Pass customer data

6. public function edit(int $id)
   - Check permission: cash_customer.edit
   - Load customer via CashCustomerService->getCashCustomerById($id)
   - If not found: 404
   - Load states dropdown
   - Load view: app/Views/customers/cash_customers/edit.php
   - Pass customer and states data

7. public function update(int $id)
   - Check permission: cash_customer.edit
   - Validate CSRF token
   - Get POST data
   - Try: Call CashCustomerService->updateCashCustomer($id, $data)
   - Catch DuplicateCustomerException: error message
   - Set flash message: "Cash customer updated successfully"
   - Redirect to /customers/cash-customers

8. public function delete(int $id)
   - Check permission: cash_customer.delete
   - Try: CashCustomerService->deleteCashCustomer($id)
   - Catch CustomerInUseException: return JSON error
   - Set flash message: "Cash customer deleted successfully"
   - Return JSON success

9. public function search()
   - Check permission: cash_customer.view
   - Get query param: q
   - Call CashCustomerService->searchCashCustomers($q)
   - Return JSON results: [{id, name, mobile}, ...]

ERROR HANDLING:
- Catch all exceptions
- Return JSON for API endpoints
- Flash messages for web
- Log errors

DELIVERABLES:
Complete CashCustomerController.php file

ACCEPTANCE CRITERIA:
- All CRUD operations working
- Find-or-create API works
- Duplicate prevention working
- Search autocomplete works
- Cannot delete if transactions exist
- Quick-add modal integration ready
```

---

### ADDITIONAL SUBTASKS FOR TASK 2.3 (VIEWS, ROUTES, SIDEBAR)

---

#### Subtask 2.3.9: Create Account Customer Views

```
[PASTE .antigravity RULES FIRST]

TASK: Create view files for Account Customer management

FILES TO CREATE:
1. app/Views/customers/accounts/index.php
2. app/Views/customers/accounts/create.php
3. app/Views/customers/accounts/edit.php
4. app/Views/customers/accounts/show.php

CONTEXT:
- Follow existing view pattern from app/Views/masters/
- Complex forms with billing and shipping address
- "Same as billing" checkbox functionality
- State dropdowns
- Current balance display
- GST and PAN fields

VIEW 1: index.php (List View)
REQUIREMENTS:
- Page title: "Account Customers"
- Breadcrumb: Home > Customers > Accounts
- Action button: "Add New Account"
- Filters:
  - Status dropdown (Active/Inactive/All)
  - State dropdown (filter by billing state)
- DataTable columns:
  - Account Code
  - Account Name
  - Contact Person
  - Mobile
  - Billing City/State
  - Current Balance (₹ formatted, color: green if credit, red if debit)
  - Status
  - Actions (View, Edit, Delete)
- Click on account name: navigate to show page
- Current balance with debit/credit indicator

VIEW 2: create.php (Create Form)
REQUIREMENTS:
- Page title: "Add Account Customer"
- Form sections:

  **Basic Information:**
  - Account Code (auto-generate button, or manual entry)
  - Account Name (required)
  - Business Name (optional)
  - Contact Person
  - Mobile (required)
  - Email
  - GST Number (with validation helper)
  - PAN Number (with validation helper)

  **Billing Address:**
  - Address Line 1 (required)
  - Address Line 2
  - City (required)
  - State (dropdown, required)
  - Pincode (required, 6 digits)

  **Shipping Address:**
  - Checkbox: "Same as Billing Address"
  - If unchecked: show shipping address fields (same structure as billing)
  - If checked: hide shipping fields, copy billing to shipping on submit

  **Financial:**
  - Opening Balance (default 0.00)
  - Opening Balance Type (radio: Debit [default] / Credit)
  - Credit Limit (optional, future use)
  - Payment Terms (text, e.g., "Net 30 days")

  **Notes:**
  - Notes (textarea)
  - Is Active (checkbox, default checked)

- JavaScript:
  - "Same as billing" toggle: show/hide shipping fields
  - Auto-generate account code button
  - GST/PAN format validation
  - Pincode validation (6 digits)
  - Mobile validation (10 digits)

- Submit button
- Cancel button

VIEW 3: edit.php (Edit Form)
REQUIREMENTS:
- Same as create.php but:
  - Page title: "Edit Account Customer"
  - Form pre-filled
  - Account Code read-only (cannot change)
  - Opening Balance read-only (cannot change after creation)
  - Opening Balance Type read-only
  - Current Balance displayed (read-only, informational)

VIEW 4: show.php (Details View)
REQUIREMENTS:
- Page title: "Account Details"
- Display all fields in read-only format
- Sections:
  - Basic Information
  - Billing Address (formatted)
  - Shipping Address (formatted, or "Same as billing")
  - Financial Information
  - Current Balance (highlighted, with debit/credit indicator)
  - Opening Balance
- Action buttons:
  - Edit (if has permission)
  - Delete (if has permission, with confirmation)
  - View Ledger (navigate to ledger report for this account)
  - Back to List

DELIVERABLES:
4 complete view files with address handling

ACCEPTANCE CRITERIA:
- Same as billing checkbox works
- Address fields toggle correctly
- GST/PAN validation works
- Current balance displayed correctly
- All CRUD operations functional
```

---

#### Subtask 2.3.10: Create Cash Customer Views

```
[PASTE .antigravity RULES FIRST]

TASK: Create view files for Cash Customer management

FILES TO CREATE:
1. app/Views/customers/cash_customers/index.php
2. app/Views/customers/cash_customers/create.php
3. app/Views/customers/cash_customers/edit.php
4. app/Views/customers/cash_customers/show.php
5. app/Views/customers/cash_customers/quick_add_modal.php

CONTEXT:
- Simpler than accounts (no financial fields)
- Quick-add modal for use during invoice creation
- Duplicate detection on name+mobile

VIEW 1: index.php (List View)
REQUIREMENTS:
- Page title: "Cash Customers"
- Breadcrumb: Home > Customers > Cash Customers
- Action button: "Add New Cash Customer"
- Filters:
  - Status dropdown
- DataTable columns:
  - Customer Name
  - Mobile
  - Email
  - City/State
  - Status
  - Actions
- Search: name or mobile
- Row click: navigate to show page

VIEW 2: create.php (Create Form)
REQUIREMENTS:
- Page title: "Add Cash Customer"
- Form fields:
  - Customer Name (required)
  - Mobile (required, 10 digits, validation)
  - Email (optional)
  - Address Line 1
  - Address Line 2
  - City
  - State (dropdown)
  - Pincode (6 digits)
  - Notes
  - Is Active (checkbox)
- Duplicate warning: if name+mobile exists, show alert
- Submit button
- Cancel button

VIEW 3: edit.php (Edit Form)
REQUIREMENTS:
- Same as create.php but:
  - Page title: "Edit Cash Customer"
  - Form pre-filled
  - Duplicate detection on name+mobile change

VIEW 4: show.php (Details View)
REQUIREMENTS:
- Page title: "Cash Customer Details"
- Display all fields read-only
- Action buttons: Edit, Delete, Back

VIEW 5: quick_add_modal.php (Modal Component)
REQUIREMENTS:
- Bootstrap modal
- Title: "Quick Add Cash Customer"
- Minimal fields:
  - Customer Name (required)
  - Mobile (required)
- Submit button: "Add Customer"
- JavaScript:
  - AJAX POST to /customers/cash-customers/find-or-create
  - On success: populate customer dropdown in invoice form
  - On error: show error message in modal
- Used during invoice creation for quick customer addition

DELIVERABLES:
5 complete view files including quick-add modal

ACCEPTANCE CRITERIA:
- Quick-add modal works via AJAX
- Duplicate detection shows warning
- All CRUD operations functional
- Mobile/email validation works
```

---

#### Subtask 2.3.11: Add Customer Routes

```
[PASTE .antigravity RULES FIRST]

TASK: Configure routes for Accounts and Cash Customers

FILE: app/Config/Routes.php

CONTEXT:
- RESTful route structure
- Apply AuthFilter and PermissionFilter
- Group routes under /customers prefix
- API endpoints for search and find-or-create

REQUIREMENTS:
Add the following route groups:

ACCOUNT ROUTES:
```php
$routes->group('customers/accounts', ['filter' => 'auth', 'filter' => 'permission:account'], function($routes) {
    $routes->get('/', 'Customers\AccountController::index');
    $routes->get('create', 'Customers\AccountController::create');
    $routes->post('/', 'Customers\AccountController::store');
    $routes->get('search', 'Customers\AccountController::search'); // API
    $routes->get('(:num)', 'Customers\AccountController::show/$1');
    $routes->get('(:num)/edit', 'Customers\AccountController::edit/$1');
    $routes->post('(:num)', 'Customers\AccountController::update/$1');
    $routes->delete('(:num)', 'Customers\AccountController::delete/$1');
    $routes->get('(:num)/ledger', 'Customers\AccountController::ledger/$1');
});
```

CASH CUSTOMER ROUTES:
```php
$routes->group('customers/cash-customers', ['filter' => 'auth', 'filter' => 'permission:cash_customer'], function($routes) {
    $routes->get('/', 'Customers\CashCustomerController::index');
    $routes->get('create', 'Customers\CashCustomerController::create');
    $routes->post('/', 'Customers\CashCustomerController::store');
    $routes->post('find-or-create', 'Customers\CashCustomerController::findOrCreate'); // API
    $routes->get('search', 'Customers\CashCustomerController::search'); // API
    $routes->get('(:num)', 'Customers\CashCustomerController::show/$1');
    $routes->get('(:num)/edit', 'Customers\CashCustomerController::edit/$1');
    $routes->post('(:num)', 'Customers\CashCustomerController::update/$1');
    $routes->delete('(:num)', 'Customers\CashCustomerController::delete/$1');
});
```

DELIVERABLES:
Updated Routes.php with customer routes

ACCEPTANCE CRITERIA:
- All routes working
- Filters applied correctly
- Permission checks enforced
- API endpoints accessible
```

---

#### Subtask 2.3.12: Add Customers to Sidebar

```
[PASTE .antigravity RULES FIRST]

TASK: Add Customers menu items to sidebar navigation

FILE: app/Views/layouts/sidebar.php

CONTEXT:
- Add "Customers" dropdown menu
- Show based on permissions
- Font Awesome icons

REQUIREMENTS:
Add the following menu structure:

```html
<!-- Customers Dropdown -->
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="customersDropdown" role="button" data-bs-toggle="dropdown">
        <i class="fas fa-users"></i> Customers
    </a>
    <ul class="dropdown-menu" aria-labelledby="customersDropdown">
        <?php if (can('account.view')): ?>
        <li>
            <a class="dropdown-item" href="<?= base_url('customers/accounts') ?>">
                <i class="fas fa-building"></i> Account Customers
            </a>
        </li>
        <?php endif; ?>

        <?php if (can('cash_customer.view')): ?>
        <li>
            <a class="dropdown-item" href="<?= base_url('customers/cash-customers') ?>">
                <i class="fas fa-user"></i> Cash Customers
            </a>
        </li>
        <?php endif; ?>
    </ul>
</li>
```

DELIVERABLES:
Updated sidebar with Customers menu

ACCEPTANCE CRITERIA:
- Menu items visible based on permissions
- Icons display correctly
- Navigation works
- Active menu highlighting
```

---

## ✅ TASK-02 COMPLETE

**Total Subtasks Covered:**
- Task 2.2.1-2.2.14: Products, Processes, Categories (14 subtasks)
- Task 2.3.1-2.3.12: Accounts, Cash Customers (12 subtasks)
- **Total: 26 subtasks**

**Files Generated:**
- 9 Migrations
- 5 Models
- 4 Services
- 4 Controllers
- 15 Views
- Routes configuration
- Sidebar navigation

**Key Features:**
✅ Product & Process management
✅ Product categories
✅ Account customers with opening balance
✅ Cash customers with deduplication
✅ GST/PAN validation
✅ Address handling (billing/shipping)
✅ Ledger integration
✅ Image upload for products
✅ Autocomplete search
✅ Quick-add modal for cash customers
✅ Cannot delete if used in transactions

---

**END OF TASK-02 - MASTER DATA MANAGEMENT**

---

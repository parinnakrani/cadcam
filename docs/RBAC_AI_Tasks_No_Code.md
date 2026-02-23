# RBAC Implementation — AI Agent Task List
## CodeIgniter 4 · Invoice & Challan Management System

> **How to use this file:** Give this to your AI coding agent (Cursor, Windsurf, Claude Code, etc.)
> one PHASE at a time. Complete and verify each phase before starting the next.
> The AI must READ existing files before editing — never overwrite blindly.

---

## Context the AI Must Know Before Starting

### Existing database tables (do not recreate these)
- `roles` — already exists, has a `permissions` column storing a JSON array of strings
- `user_roles` — already exists, links users to roles
- `users` — already exists, no changes needed
- `invoices` — already exists, has an `invoice_type` column with values: `Cash Invoice`, `Accounts Invoice`, `Wax Invoice`
- `challans` — already exists, has a `challan_type` column with values: `Rhodium`, `Meena`, `Wax`

### Permission string format used throughout this system
Every permission is a dot-separated string in the format: `module.sub_module.action`

Modules and their sub-modules:
- `invoices` → sub-modules: `all`, `account`, `cash`, `wax`
- `challans` → sub-modules: `all`, `rhodium`, `meena`, `wax`

Actions for every sub-module: `list`, `create`, `edit`, `view`, `print`, `status_change`, `record_payment`

Wildcards are supported:
- `*` means everything (Super Admin)
- `invoices.*` means all invoice permissions
- `invoices.account.*` means all actions on account invoices
- `invoices.account.list` means one specific action

### How sub_module maps to database column values
When checking permissions for a specific record, translate the DB value to sub_module:
- `invoice_type = "Accounts Invoice"` or `"Account Invoice"` → sub_module = `account`
- `invoice_type = "Cash Invoice"` → sub_module = `cash`
- `invoice_type = "Wax Invoice"` → sub_module = `wax`
- `challan_type = "Rhodium"` → sub_module = `rhodium`
- `challan_type = "Meena"` → sub_module = `meena`
- `challan_type = "Wax"` → sub_module = `wax`

### How wildcard permission resolution works
When checking if a user has `invoices.account.print`, check in this order:
1. Does the user have exact string `invoices.account.print`?
2. Does the user have `invoices.account.*`?
3. Does the user have `invoices.*`?
4. Does the user have `*`?
If any match → permission granted.

---

## PHASE 1 — Database
> Safest phase. No PHP changes. Do this first and verify before moving on.

---

### TASK 1.1 — Create the `permissions` registry table via migration

**What:** Create a new CI4 migration file that creates a `permissions` table.

**Why:** This table is a registry of every valid permission string in the system. It is used by the admin UI when assigning permissions to roles. It does NOT affect runtime permission checks — those read directly from the `roles.permissions` JSON column.

**Instructions for AI:**
- Look at the existing migration files in `app/Database/Migrations/` to understand the naming convention and code style used in this project
- Create a new migration file following the same naming convention
- The migration must create a table called `permissions` with these columns:
  - `id` — primary key, unsigned int, auto increment
  - `permission` — varchar 150, not null, must be unique (unique index)
  - `label` — varchar 200, not null (human-readable name, e.g. "Account Invoice - Print")
  - `module` — varchar 50, not null (e.g. "invoices" or "challans")
  - `sub_module` — varchar 50, not null (e.g. "account", "cash", "rhodium")
  - `action` — varchar 50, not null (e.g. "list", "print", "record_payment")
  - `sort_order` — int, not null, default 0
  - `is_active` — tinyint 1, not null, default 1
- Add an index on `module` and on `sub_module` for query performance
- Include a `down()` method that drops the table

**Verify after running:** Run `php spark migrate` then confirm `permissions` table exists in the database.

---

### TASK 1.2 — Seed the `permissions` table with all 56 permission strings

**What:** Create a seeder that inserts one row per permission string into the `permissions` table.

**Why:** Populates the registry so the admin UI has a complete list to work from.

**Instructions for AI:**
- Look at existing seeders in `app/Database/Seeds/` for code style
- Create a seeder called `PermissionsSeeder`
- It must generate and insert rows for every combination of:
  - For `invoices` module: sub-modules `all`, `account`, `cash`, `wax`
  - For `challans` module: sub-modules `all`, `rhodium`, `meena`, `wax`
  - For both modules and all sub-modules: actions `list`, `create`, `edit`, `view`, `print`, `status_change`, `record_payment`
- That is 2 modules × 4 sub-modules × 7 actions = **56 rows total**
- The `permission` column value must be `{module}.{sub_module}.{action}` e.g. `invoices.account.print`
- The `label` column must be human-readable e.g. "Account Invoice - Print"
- Assign incrementing `sort_order` values
- The seeder must be safe to run only once — if records already exist, skip or use insert-ignore

**Verify after running:** `SELECT COUNT(*) FROM permissions;` must return 56.

---

### TASK 1.3 — Back up the `roles` table then update existing role permission JSON

**What:** Update the `permissions` JSON column of 5 existing roles (IDs 2–6) to use the new dot-notation permission string format.

**Why:** The existing roles use old/inconsistent permission strings. They need to be upgraded to the new `module.sub_module.action` format so the new PermissionService can resolve them correctly.

**Instructions for AI:**
- First, create a backup: `CREATE TABLE roles_backup_20260221 AS SELECT * FROM roles;`
- Do NOT touch Role ID 1 (Super Administrator) — it already has `["*"]` which is correct
- Update Role ID 2 (Company Administrator) — assign full wildcard access to all modules: invoices, challans, payments, reports, masters, deliveries, plus company/user/role/settings management permissions
- Update Role ID 3 (Billing Manager) — assign `invoices.*` and `challans.*` wildcards plus read permissions for reports and customers
- Update Role ID 4 (Accounts Manager) — assign specific permissions: list/view/print/record_payment on invoices (all and account sub-modules), list/view/print on challans (all sub-module), plus full payments and reports access
- Update Role ID 5 (Delivery Personnel) — assign only: `invoices.all.list`, `invoices.all.view`, `invoices.all.print`, `deliveries.view_assigned`, `deliveries.mark_complete`
- Update Role ID 6 (Report Viewer) — assign only: list/view/print on `invoices.all` and `challans.all`, plus `reports.view_all` and `customers.view`
- Each update must store a valid JSON array in the `permissions` column
- Wrap all UPDATEs in a transaction so they can be rolled back together if anything fails

**Verify after running:** `SELECT id, role_name, JSON_LENGTH(permissions) as perm_count FROM roles;` — each role should have a non-zero permission count.

---

### TASK 1.4 — Insert 3 new granular roles for the example permission scenarios

**What:** Insert 3 new rows into the `roles` table representing specific real-world permission configurations.

**Why:** These roles demonstrate the 4 user examples from the requirements. Role 4 (full access) is already covered by Company Administrator (ID 2).

**Instructions for AI:**
- Insert into the `roles` table using the same columns as existing rows
- Set `is_system_role = 0` for all 3 new roles (they are company-specific, not system roles)
- Set `is_active = 1` for all
- Set `company_id = 1` for all (they belong to the first company)

**Role A — "Account Invoice Viewer"**
- Description: Can see All Invoices and Account Invoice menu. List, View, Print actions only. No status change or payment recording.
- Permissions JSON array must contain only these strings: `invoices.all.list`, `invoices.all.view`, `invoices.all.print`, `invoices.account.list`, `invoices.account.view`, `invoices.account.print`

**Role B — "Cash Invoice Operator"**
- Description: Can see All Invoices and Cash Invoice menu. Has List, View, Print, Status Change, and Record Payment on Cash Invoices. List/View/Print only on All Invoices page.
- Permissions JSON array: `invoices.all.list`, `invoices.all.view`, `invoices.all.print`, `invoices.cash.list`, `invoices.cash.view`, `invoices.cash.print`, `invoices.cash.status_change`, `invoices.cash.record_payment`

**Role C — "Challan Viewer"**
- Description: Can see All Challans, Rhodium Challan, and Meena Challan menus. Has List/View/Print/Status Change on Rhodium and Meena. List/View/Print only on All Challans. No record payment on any.
- Permissions JSON array: `challans.all.list`, `challans.all.view`, `challans.all.print`, `challans.rhodium.list`, `challans.rhodium.view`, `challans.rhodium.print`, `challans.rhodium.status_change`, `challans.meena.list`, `challans.meena.view`, `challans.meena.print`, `challans.meena.status_change`

**Verify after running:** `SELECT id, role_name FROM roles ORDER BY id;` — should show the 3 new rows at the bottom.

---

## PHASE 2 — Core PHP Infrastructure (New Files Only)
> All tasks in this phase create brand-new files. Nothing existing is touched.
> Safe to implement all at once, but verify each file has no syntax errors before moving on.

---

### TASK 2.1 — Create the PermissionService class

**File location:** `app/Services/PermissionService.php`

**What:** A singleton service class that is the single source of truth for all permission checks in the application.

**Why this design:**
- Loads all permissions for a user with ONE database query per session
- Caches the result (5 minutes) so repeat page loads have zero DB queries
- Every permission check after boot is a simple in-memory array lookup
- All controllers and views go through this one class — no scattered logic

**Instructions for AI:**
- Look at any existing service classes in `app/Services/` to match the namespace and code style of this project
- The class must accept a cache instance via constructor (use CI4's cache service)
- It must have a `boot(int $userId)` method that:
  - Checks if already booted — if yes, return immediately (prevents double-loading)
  - Checks cache for key `user_perms_{userId}` — if found, restore from cache and return
  - If not cached: run a single database query joining `user_roles` and `roles` tables to get all `permissions` JSON arrays for this user's roles (where role `is_active = 1` and `is_deleted = 0`)
  - Merge all permission arrays from all assigned roles into one flat unique array
  - Check if `*` is in the merged array — if yes, set an internal `$isSuperAdmin = true` flag
  - Store the resolved permissions in the cache for 5 minutes
- It must have a `can(string $permission): bool` method that:
  - Returns true immediately if `$isSuperAdmin` is true
  - Checks for exact match of the permission string in the resolved array
  - Checks wildcard cascade: for `invoices.account.print`, also check `invoices.account.*` then `invoices.*`
  - Returns false if nothing matches
- It must have a `canAny(string $prefix): bool` method that:
  - Returns true if the user has ANY permission string that starts with the given prefix
  - Also resolves wildcards — a user with `invoices.*` satisfies `canAny('invoices.account')`
  - Used for menu visibility: `canAny('invoices')` → show the Invoices menu section
- It must have shortcut methods that call `can()` internally: `canList`, `canCreate`, `canEdit`, `canView`, `canPrint`, `canChangeStatus`, `canRecordPayment` — each takes `(string $module, string $subModule = 'all')`
- It must have a `getActionFlags(string $module, string $subModule): array` method that returns an associative array with keys `canList`, `canCreate`, `canEdit`, `canView`, `canPrint`, `canStatusChange`, `canRecordPayment` — each value is a boolean. For each flag, check permission on the specific sub_module AND on the `all` sub_module (either passing = true). This array is passed to views so they don't need to call `can()` multiple times.
- It must have menu builder methods `getInvoiceMenuItems(): array` and `getChallanMenuItems(): array` that return an array of menu entries (with label, url, and sub_module keys) filtered to only include sub-modules the user has any permission on
- It must have methods to translate DB column values to sub_module keys: `resolveInvoiceSubModule(string $invoiceType): string` and `resolveChallanSubModule(string $challanType): string`
- It must have cache flush methods: `flushUser(int $userId)` and `flushAll()`

---

### TASK 2.2 — Register PermissionService in CI4 Services config

**File:** `app/Config/Services.php`

**What:** Register the PermissionService so it can be retrieved anywhere in the app via `\Config\Services::permissions()` or `service('permissions')`.

**Instructions for AI:**
- Read the existing `Services.php` file to understand how other services are registered in this project
- Add a static method called `permissions` that returns an instance of `\App\Services\PermissionService`
- It must support the `$getShared = true` pattern that CI4 uses (shared instance = singleton per request)
- Pass the CI4 cache service instance to the PermissionService constructor
- Do NOT remove or modify any existing service registrations

---

### TASK 2.3 — Create the permission helper file

**File:** `app/Helpers/permission_helper.php`

**What:** Global helper functions that wrap the PermissionService. These make permission checks available in views and controllers without needing to import or inject the service manually.

**Instructions for AI:**
- Look at existing helper files in `app/Helpers/` for code style
- Each function must be wrapped in `if (!function_exists(...))` to prevent conflicts
- Create these functions:
  - `can(string $permission): bool` — calls `\Config\Services::permissions()->can($permission)`
  - `cannot(string $permission): bool` — returns the opposite of `can()`
  - `can_any(string $prefix): bool` — calls `canAny()` on the service
  - `abort_if_cannot(string $permission, string $redirectTo = 'dashboard'): void` — checks `can()`, and if false: for AJAX requests returns a JSON response with HTTP 403 status and a `success: false` message; for normal requests sets a flash error message and redirects to the given path

---

### TASK 2.4 — Create the PermissionFilter (middleware) class

**File:** `app/Filters/PermissionFilter.php`

**What:** A CI4 filter that runs before a route is executed. It checks if the logged-in user has the required permission for that route.

**Instructions for AI:**
- Look at existing filter files in `app/Filters/` for code style and namespace
- Implement the CI4 `FilterInterface` with `before()` and `after()` methods
- In `before()`:
  - Check if `user_id` exists in session — if not, handle unauthenticated state: for AJAX requests return JSON 401; for normal requests redirect to the login page
  - Boot the PermissionService with the session user_id
  - If no `$arguments` are passed to the filter, stop here (this is just an auth check, no permission required)
  - If arguments are provided, loop through each one and call `can()` on the service — if any check fails: for AJAX return JSON 403; for normal requests redirect to the dashboard with a flash error message
- `after()` method should do nothing (empty implementation)

---

## PHASE 3 — Configuration Updates (Low Risk Edits)
> Each task edits one config file. Read the file first, add only what's needed, change nothing else.

---

### TASK 3.1 — Register the permission helper in Autoload config

**File:** `app/Config/Autoload.php`

**What:** Add `'permission'` to the helpers array so it is automatically loaded on every request.

**Instructions for AI:**
- Read the file and find the `$helpers` property
- Add the string `'permission'` to the existing array — do not remove anything already there
- If the property does not exist, ask the developer — do not assume where to add it

---

### TASK 3.2 — Register the PermissionFilter alias in Filters config

**File:** `app/Config/Filters.php`

**What:** Give the PermissionFilter a short alias name so it can be used in routes.

**Instructions for AI:**
- Read the file and find the `$aliases` array
- Add one entry: key `'permission'`, value is the fully-qualified class name `\App\Filters\PermissionFilter::class`
- Do not modify any existing aliases

---

## PHASE 4 — BaseController Update
> This is the highest-risk edit because BaseController is the parent of every controller.
> Read the existing file completely before touching it. Test immediately after.

---

### TASK 4.1 — Add permission bootstrapping to BaseController

**File:** `app/Controllers/BaseController.php`

**What:** Boot the PermissionService on every request and make the permission data available to all controllers and views automatically.

**Instructions for AI:**
- Read the entire existing `BaseController.php` file before making any changes
- Identify where `initController()` is defined and what it currently does — preserve ALL existing logic
- Add `'permission'` to the `$helpers` array on the class (if the class already has a `$helpers` property, add to it — do not replace it)
- Add a protected property `$permissions` typed as the PermissionService class
- Add a protected property `$viewData` as an empty array — this will hold data shared to all views
- Inside `initController()`, AFTER the existing code runs:
  - Check if the user is logged in by checking session for `user_id`
  - If logged in: get the PermissionService via `\Config\Services::permissions()`, call `boot()` with the user ID, store it in `$this->permissions`
  - Add to `$viewData`: the permissions object, the result of `getInvoiceMenuItems()`, and the result of `getChallanMenuItems()`
- Add a protected method `render(string $view, array $data = []): string` that calls `view()` with the given view path and merges `$viewData` with `$data` — this lets controllers pass extra data while sidebar/menu data is always available
- Add a protected method `gate(string $permission): void` that calls `abort_if_cannot()` — a clean one-liner for controllers to use
- Add protected methods `resolveInvoiceSub(string $invoiceType): string` and `resolveChallanSub(string $challanType): string` that delegate to the PermissionService
- Do NOT change the constructor signature, do NOT remove existing properties or methods

**Verify:** After saving, load any existing page while logged in as superadmin. If the page loads correctly with no errors, BaseController is working.

---

## PHASE 5 — Routes Configuration
> Add permission filter arguments to existing route groups.
> Do this one module group at a time and test between each.

---

### TASK 5.1 — Add permission filter to Invoice routes

**File:** `app/Config/Routes.php`

**What:** Attach the permission filter to invoice list routes so unauthorized users are blocked at the routing layer before the controller even runs.

**Instructions for AI:**
- Read the entire Routes.php file first to understand the current route structure
- Find the existing invoice routes
- For list/index routes, add the filter `permission:invoices.all.list` to the All Invoices route, `permission:invoices.account.list` to the Account Invoices route, `permission:invoices.cash.list` to the Cash Invoices route, and `permission:invoices.wax.list` to the Wax Invoices route
- For view, print, edit, create routes — do NOT add a specific permission filter at the route level. These will be handled inside the controller because the required permission depends on the record's type (which is only known after fetching the record)
- For POST routes (status change, record payment, store, update) — similarly leave route-level filter off; handled in controllers
- Do NOT change any route path, method name, or group structure — only add filter arguments to the specific routes listed above
- If the routes are inside a group, add the filter as an option on the individual route, not on the group

**Verify:** Log in as a user with no invoice permissions and try to visit the All Invoices URL. They should be redirected to the dashboard. Log in as superadmin — all invoice routes must work normally.

---

### TASK 5.2 — Add permission filter to Challan routes

**File:** `app/Config/Routes.php`

**What:** Same as Task 5.1 but for challan routes.

**Instructions for AI:**
- Find the existing challan routes in Routes.php
- Apply filters: `permission:challans.all.list` to All Challans route, `permission:challans.rhodium.list` to Rhodium route, `permission:challans.meena.list` to Meena route, `permission:challans.wax.list` to Wax route
- Same rules apply: view/edit/print/create/POST routes are handled inside the controller
- Do not change any other route settings

**Verify:** Same test as Task 5.1 but for challan URLs.

---

## PHASE 6 — Controller Updates
> Update each controller method to add permission gates.
> Work on one controller at a time. Within each controller, work one method at a time.

---

### TASK 6.1 — Add permission gates to InvoiceController list methods

**File:** The invoice controller file (find it by looking at what the invoice list routes point to)

**What:** Pass action flags to the view so it knows which buttons to show.

**Instructions for AI:**
- Read the entire controller file first to understand its structure
- Find the method that handles the All Invoices list page
  - After the route filter has already verified list permission, call `$this->permissions->getActionFlags('invoices', 'all')` to get a flags array
  - Merge this flags array into the data passed to the view
- Find the method that handles the Account Invoices list page
  - Do the same with sub_module `'account'`
- Do the same for Cash Invoices (sub_module `'cash'`) and Wax Invoices (sub_module `'wax'`)
- Do NOT change anything else in these methods — only add the flags and merge into view data

---

### TASK 6.2 — Add permission gate to InvoiceController view method

**File:** The invoice controller

**What:** Verify the user has view permission for this specific invoice type before showing the record.

**Instructions for AI:**
- Find the method that handles viewing a single invoice (typically receives an invoice ID)
- After fetching the invoice record from the database, call `$this->resolveInvoiceSub()` with the invoice's `invoice_type` value to get the sub_module key
- Check permission: the user must have view permission on the specific sub_module OR on `all` — if neither, call `abort_if_cannot()` with any invoices view permission (it will fail and redirect)
- Call `getActionFlags()` with the module and resolved sub_module
- Merge the flags array into the data passed to the view
- The view will use these flags to show/hide buttons

---

### TASK 6.3 — Add permission gate to InvoiceController create method

**File:** The invoice controller

**What:** Block users who cannot create any invoice type. Also restrict which invoice types appear in the type dropdown.

**Instructions for AI:**
- Find the create method
- At the top of the method, check if the user can create ANY invoice type: check `canCreate('invoices', 'all')`, `canCreate('invoices', 'account')`, `canCreate('invoices', 'cash')`, `canCreate('invoices', 'wax')` — if ALL are false, call `abort_if_cannot()` which will redirect
- Build an array of types the user is allowed to create — loop through each type and include it only if the user has create permission on that sub_module or on `all`
- Pass this filtered types array to the view so the type dropdown only shows allowed options

---

### TASK 6.4 — Add permission gate to InvoiceController edit method

**File:** The invoice controller

**What:** Verify the user can edit this specific invoice type.

**Instructions for AI:**
- Find the edit method
- After fetching the invoice, resolve the sub_module from `invoice_type`
- Check if user has edit permission on that sub_module or on `all` — if not, call `abort_if_cannot()`
- Do not change anything else in the method

---

### TASK 6.5 — Add permission gates to InvoiceController changeStatus and recordPayment methods

**File:** The invoice controller

**What:** These POST endpoints must be double-gated — the route level didn't check (because sub_module depends on the record), so the controller must check.

**Instructions for AI:**
- Find the changeStatus method (or equivalent — look for what handles status change POST requests)
- After fetching the invoice, resolve sub_module, then check `canChangeStatus` on sub_module OR `all` — if not, `abort_if_cannot()`
- Find the recordPayment method
- Same pattern: resolve sub_module, check `canRecordPayment` on sub_module OR `all` — if not, `abort_if_cannot()`
- These methods likely return JSON (since they're usually called by AJAX) — `abort_if_cannot` already handles this correctly

---

### TASK 6.6 — Add permission check to InvoiceController AJAX DataTable data method

**File:** The invoice controller

**What:** The DataTable data endpoint is an AJAX POST. It must verify list permission and must only render action buttons the user is allowed to use.

**Instructions for AI:**
- Find the method that returns JSON data for DataTables (look for where `draw`, `recordsTotal`, `recordsFiltered`, `data` are returned as JSON)
- At the very top, determine which sub_module is being requested (likely from a URL segment or POST parameter), validate it against the allowed values (`all`, `account`, `cash`, `wax`), and call `abort_if_cannot("invoices.{$sub}.list")`
- When building each row's action column HTML, check permissions before adding each button link: view button requires view permission, print button requires print permission, edit button requires edit permission — for each, check on the specific sub_module OR on `all`
- Do not change anything else about the query or response structure

---

### TASK 6.7 — Repeat TASKS 6.1 through 6.6 for ChallanController

**File:** The challan controller

**What:** Identical permission gate pattern as invoice controller, but using challan module and sub_modules.

**Instructions for AI:**
- Work through the same 6 sub-tasks as above
- Use `'challans'` as the module string everywhere
- Use `resolveChallanSub()` instead of `resolveInvoiceSub()`
- Sub_modules for challans are: `all`, `rhodium`, `meena`, `wax`
- For the DataTable endpoint, validate sub_module against `all`, `rhodium`, `meena`, `wax`
- Everything else is the same pattern

---

### TASK 6.8 — Add cache flush to role management controller

**File:** The controller that handles creating/editing roles and assigning roles to users (likely in a settings or admin area)

**What:** When a role's permissions are changed or a user's role assignment changes, the cached permissions for affected users must be cleared.

**Instructions for AI:**
- Find the method that saves/updates a role's permissions
- After a successful save, call `\Config\Services::permissions()->flushAll()` — this clears all cached permissions so the changes take effect immediately
- Find the method that assigns or removes a role from a user
- After a successful assignment change, call `\Config\Services::permissions()->flushUser($targetUserId)` where `$targetUserId` is the ID of the user whose role changed
- Do not change anything else about these methods

---

## PHASE 7 — View Updates
> Update view files to show/hide buttons based on permission flags passed from the controller.
> The flags (`canPrint`, `canEdit`, `canStatusChange`, `canRecordPayment`, `canCreate`) are already
> being passed from the controller after Phase 6. Views just need to use them.

---

### TASK 7.1 — Update the sidebar/navigation view to generate menus dynamically

**File:** The sidebar or navigation partial view (look in the layout views folder)

**What:** Replace the hardcoded Invoices and Challans menu items with dynamic generation based on `$invoiceMenuItems` and `$challanMenuItems` arrays (which are auto-injected by BaseController).

**Instructions for AI:**
- Read the existing sidebar view file completely before changing anything
- Identify where the Invoices menu section is hardcoded
- Replace it so that: the Invoices menu section only appears if `$invoiceMenuItems` is not empty; within the section, loop through `$invoiceMenuItems` and render one menu link per item using the item's `label` and `url` keys; match active state using the current URI
- Do the same for the Challans menu section using `$challanMenuItems`
- Keep all existing HTML structure, CSS classes, and icons — only change the logic that generates the links
- Do not change any other menu sections (dashboard, settings, etc.)

---

### TASK 7.2 — Update the Invoice view page to show/hide action buttons

**File:** The invoice single-record view template

**What:** Wrap each action button in a conditional so it only renders if the user has permission.

**Instructions for AI:**
- Read the existing view file to find all action buttons (Print, Edit, Status Change, Record Payment)
- Wrap the Print button (or link) in a check for `$canPrint`
- Wrap the Edit button in a check for `$canEdit`
- Wrap the Status Change button in a check for `$canStatusChange`
- Wrap the Record Payment button in a check for `$canRecordPayment`
- Use the null-coalescing pattern (`$canPrint ?? false`) so the page does not error if the flag is missing
- Do NOT change button styles, IDs, data attributes, or any JavaScript wiring — only wrap in conditionals

---

### TASK 7.3 — Update the Invoice list page to show/hide the Create button

**File:** The invoice list/index view template

**What:** The "New Invoice" / "Create Invoice" button should only appear if the user has create permission.

**Instructions for AI:**
- Find the Create Invoice button or link in the list view
- Wrap it in a check for `$canCreate ?? false`
- The DataTable action column buttons (View, Print, Edit) are generated server-side in the controller's AJAX method — the view does not need to handle those

---

### TASK 7.4 — Update the Challan view page to show/hide action buttons

**File:** The challan single-record view template

**What:** Same as Task 7.2 but for challan views.

**Instructions for AI:**
- Same pattern as Task 7.2
- Wrap Print, Edit, Status Change, and Record Payment buttons in their respective flags
- Use `$canPrint ?? false`, `$canEdit ?? false`, `$canStatusChange ?? false`, `$canRecordPayment ?? false`

---

### TASK 7.5 — Update the Challan list page to show/hide the Create button

**File:** The challan list view template

**What:** Same as Task 7.3 but for challans.

---

## PHASE 8 — Verification
> Do not skip this. Run through every test case before calling implementation complete.

---

### TASK 8.1 — Console and syntax verification

**What:** Confirm no PHP errors exist in any new or modified file.

**Instructions for AI:**
- Run `php spark migrate:status` — confirm the new migration shows as "ran"
- Run `SELECT COUNT(*) FROM permissions;` — must return 56
- Run `SELECT id, role_name, JSON_LENGTH(permissions) as cnt FROM roles;` — all roles must have cnt > 0
- Run `php -l` on every new PHP file to verify no syntax errors
- Run `php spark cache:clear` to clear CI4's compiled files

---

### TASK 8.2 — Functional test matrix

**What:** Log in as different users and verify the correct behaviour at each page.

**Test cases — run all of them:**

| Step | Login as | Go to | Expected result |
|---|---|---|---|
| 1 | Superadmin (role: Super Administrator) | All Invoices page | Page loads, all buttons visible |
| 2 | Superadmin | All Challans page | Page loads, all buttons visible |
| 3 | User with "Account Invoice Viewer" role | All Invoices page | Page loads, only View and Print buttons in DataTable rows, no Create button |
| 4 | User with "Account Invoice Viewer" role | Account Invoice page | Page loads, same button restrictions |
| 5 | User with "Account Invoice Viewer" role | Cash Invoice URL directly | Redirected to dashboard with error message |
| 6 | User with "Account Invoice Viewer" role | Any Challan URL directly | Redirected to dashboard with error message |
| 7 | User with "Account Invoice Viewer" role | Single invoice view page | Print button visible, Status Change hidden, Record Payment hidden |
| 8 | User with "Cash Invoice Operator" role | Cash Invoice page | Page loads, Status Change and Record Payment buttons visible in rows |
| 9 | User with "Cash Invoice Operator" role | Single Cash Invoice view page | Print, Status Change, Record Payment all visible |
| 10 | User with "Cash Invoice Operator" role | Account Invoice URL directly | Redirected (no account invoice permissions) |
| 11 | User with "Challan Viewer" role | All Challans page | Page loads, only View and Print in rows |
| 12 | User with "Challan Viewer" role | Rhodium Challan page | Page loads, View, Print, Status Change visible |
| 13 | User with "Challan Viewer" role | Single Rhodium Challan view | Print and Status Change visible, Record Payment hidden |
| 14 | User with "Challan Viewer" role | Wax Challan URL directly | Redirected (no wax challan permission) |
| 15 | User with "Challan Viewer" role | Any Invoice URL directly | Redirected (no invoice permissions) |
| 16 | Any limited user | POST directly to record payment URL | JSON 403 response with `success: false` |
| 17 | Superadmin | Edit and save a role's permissions | After save, affected users' cached permissions are cleared |
| 18 | Check sidebar for each limited user | — | Only the menu items they have permission for appear |

---

## Implementation Order Summary

| Phase | Tasks | Risk | Prerequisite |
|---|---|---|---|
| Phase 1 — Database | 1.1, 1.2, 1.3, 1.4 | 🟢 Low–Medium | Backup roles table before 1.3 |
| Phase 2 — New PHP Files | 2.1, 2.2, 2.3, 2.4 | 🟢 Zero | Phase 1 complete |
| Phase 3 — Config Files | 3.1, 3.2 | 🟢 Low | Phase 2 complete |
| Phase 4 — BaseController | 4.1 | 🟡 Medium | Phase 3 complete |
| Phase 5 — Routes | 5.1, 5.2 | 🟡 Medium | Phase 4 verified working |
| Phase 6 — Controllers | 6.1–6.8 | 🟡 Medium | Phase 5 complete |
| Phase 7 — Views | 7.1–7.5 | 🟢 Low | Phase 6 complete |
| Phase 8 — Verification | 8.1, 8.2 | — | All phases complete |

**Total: 23 tasks across 8 phases.**

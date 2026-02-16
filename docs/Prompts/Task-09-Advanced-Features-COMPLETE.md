# AI CODING PROMPTS - TASK 09

## Advanced Features & Helpers

**Version:** 1.0  
**Phase:** 9 - Polish & Enhancement (Week 20)  
**Generated:** February 10, 2026

---

## SUBTASKS: 9.1.1-9.1.4 (Audit Logging), 9.2.1-9.2.3 (File Upload & Helpers)

---

## 🎯 TASK 9.1: AUDIT LOGGING

### Subtask 9.1.1: Create audit_logs Migration

IMPORTANT: The `audit_logs` table ALREADY EXISTS.
The migration must:

1. Check if the table exists.
2. If it exists, MODIFY columns `module` and `record_type` to VARCHAR(100).
3. UPDATE `action_type` ENUM to include: 'create', 'update', 'delete', 'view', 'login', 'logout', 'print', 'export', 'switch_company', 'access_denied'.
4. If it does not exist, CREATE it with the full structure.

TABLE STRUCTURE (Target):

- id, company_id
- user_id (INT, FK to users.id, NOT NULL)
- module (VARCHAR 100) // e.g., 'Invoice', 'Payment', 'User'
- action_type (ENUM('create', 'update', 'delete', 'view', 'login', 'logout', 'print', 'export', 'switch_company', 'access_denied'))
- record_type (VARCHAR 100) // e.g., 'Invoice', 'User'
- record_id (INT)
- before_data (JSON, NULL) // State before change
- after_data (JSON, NULL) // State after change
- ip_address (VARCHAR 45)
- user_agent (VARCHAR 255)
- created_at (TIMESTAMP)

INDEXES: company_id, user_id, module, action_type, record_type, record_id, created_at

DELIVERABLES: Robust migration handling existing table

ACCEPTANCE CRITERIA: Migration runs without error on existing DB, schema updated

---

### Subtask 9.1.2: Create AuditLogModel

```
read .antigravity content and then

FILE: app/Models/AuditLogModel.php

METHODS:
1. findAll() - with company filter
2. getAuditTrail(string $recordType, int $recordId): array
   - Get all audit logs for specific record
   - Order by created_at DESC
3. getRecentActivity(int $limit = 50): array
   - Get recent audit logs
4. getUserActivity(int $userId, $fromDate, $toDate): array
   - Get audit logs for specific user

DELIVERABLES: Complete AuditLogModel.php
```

---

### Subtask 9.1.3: Create AuditService

```
read .antigravity content and then

FILE: app/Services/Audit/AuditService.php

DEPENDENCIES: AuditLogModel, session library, request library

METHODS:

1. public function log(string $module, string $actionType, string $recordType, int $recordId, $beforeData = null, $afterData = null): int
   - Get current user ID from session
   - Get IP address from request
   - Get user agent from request
   - JSON encode before/after data
   - Insert audit log entry
   - Return log ID

2. public function logCreate(string $module, string $recordType, int $recordId, array $data): int
   - Call log() with action_type = 'create'
   - after_data = $data

3. public function logUpdate(string $module, string $recordType, int $recordId, array $beforeData, array $afterData): int
   - Call log() with action_type = 'update'
   - Store both before and after

4. public function logDelete(string $module, string $recordType, int $recordId, array $beforeData): int
   - Call log() with action_type = 'delete'
   - before_data = $beforeData

5. public function logView(string $module, string $recordType, int $recordId): int
   - Call log() with action_type = 'view'
   - Optional: only log sensitive views

6. public function logPrint(string $module, string $recordType, int $recordId): int
   - Call log() with action_type = 'print'

7. public function logExport(string $module, string $recordType, array $filters = []): int
   - Call log() with action_type = 'export'
   - store filters in before_data or after_data

8. public function logAccessDenied(string $module, string $action, string $details = ''): int
   - Call log() with action_type = 'access_denied'
   - store details in after_data

9. public function logCompanySwitch(int $fromCompanyId, int $toCompanyId): int
   - Call log() with action_type = 'switch_company'
   - Store details in data

10. public function getAuditTrail(string $recordType, int $recordId): array
   - Call AuditLogModel->getAuditTrail()
   - Return audit history

DELIVERABLES: Complete AuditService.php

ACCEPTANCE CRITERIA: All actions logged, audit trail retrievable
```

---

### Subtask 9.1.4: Create AuditLogController & View

```
read .antigravity content and then

FILE: app/Controllers/Audit/AuditLogController.php

ROUTES:
- GET /audit-logs → index()
- GET /audit-logs/record/{type}/{id} → recordAuditTrail()
- GET /audit-logs/user/{id} → userActivity()

METHODS:
1. index() - list all audit logs (admin only)
2. recordAuditTrail($type, $id) - show audit trail for specific record
3. userActivity($userId) - show user activity log

VIEW: app/Views/audit/index.php
- DataTable: Date, User, Module, Action, Record, IP Address

DELIVERABLES: Controller, view, routes

ACCEPTANCE CRITERIA: Audit logs viewable, filterable
```

---

## 🎯 TASK 9.2: FILE UPLOAD & HELPERS

### Subtask 9.2.1: Create FileUploadService

```
read .antigravity content and then

FILE: app/Services/Common/FileUploadService.php

METHODS:

1. public function uploadImage(UploadedFile $file, string $destination): string
   - Validate file type (JPG, PNG, GIF)
   - Validate file size (< 10 MB)
   - Generate random filename
   - Move file to public/uploads/$destination/
   - Return relative file path

2. public function uploadDocument(UploadedFile $file, string $destination): string
   - Validate file type (PDF, DOC, DOCX, XLS, XLSX)
   - Validate size (< 20 MB)
   - Upload to public/uploads/$destination/
   - Return file path

3. public function deleteFile(string $filePath): bool
   - Check file exists
   - Delete file from filesystem
   - Return success

4. private function generateRandomFilename(string $originalName): string
   - Generate unique filename using timestamp + random string
   - Preserve file extension
   - Return: "20260210_abc123.jpg"

DELIVERABLES: Complete FileUploadService.php

ACCEPTANCE CRITERIA: Image upload works, file validation enforced
```

---

### Subtask 9.2.2: Create Helper Functions

````
read .antigravity content and then

FILE 1: app/Helpers/permission_helper.php

```php
if (!function_exists('can')) {
    function can(string $permission): bool {
        $permissionService = service('PermissionService');
        return $permissionService->can($permission);
    }
}

if (!function_exists('hasRole')) {
    function hasRole(string $roleName): bool {
        $permissionService = service('PermissionService');
        return $permissionService->hasRole($roleName);
    }
}
````

FILE 2: app/Helpers/format_helper.php

```php
if (!function_exists('formatCurrency')) {
    function formatCurrency(float $amount): string {
        return '₹ ' . number_format($amount, 2);
    }
}

if (!function_exists('formatDate')) {
    function formatDate($date, string $format = 'd-M-Y'): string {
        return date($format, strtotime($date));
    }
}

if (!function_exists('formatWeight')) {
    function formatWeight(float $grams, int $decimals = 3): string {
        return number_format($grams, $decimals) . ' g';
    }
}
```

FILE 3: app/Helpers/number_helper.php

```php
if (!function_exists('formatNumber')) {
    function formatNumber(float $number, int $decimals = 2): string {
        return number_format($number, $decimals);
    }
}

if (!function_exists('convertToWords')) {
    function convertToWords(float $number): string {
        // Convert number to words (for invoice amounts)
        // Return: "One Thousand Five Hundred Rupees Only"
    }
}
```

DELIVERABLES: 3 helper files

ACCEPTANCE CRITERIA: Helpers autoloaded, functions working

```

---

### Subtask 9.2.3: Create ValidationService & Custom Rules

```

read .antigravity content and then

FILE 1: app/Services/Common/ValidationService.php

METHODS:

1. validateGST(string $gst): bool
   - Regex: 15 chars, format validation
2. validatePAN(string $pan): bool
   - Regex: 10 chars, format validation
3. validateMobile(string $mobile): bool
   - Regex: 10 digits
4. validateEmail(string $email): bool
   - Standard email validation
5. validateDate(string $date): bool
   - Check valid date format, not future date
6. validateAmount(float $amount): bool
   - Check > 0, <= max value

FILE 2: app/Validation/CustomRules.php

Custom validation rules for CodeIgniter:

- gst_number (format validation)
- pan_number (format validation)
- indian_mobile (10 digit validation)
- future_date (date not in future)
- positive_amount (amount > 0)

DELIVERABLES: ValidationService, CustomRules

ACCEPTANCE CRITERIA: Validation rules working, custom rules registered

```

---

**END OF TASK-09 COMPLETE**
```

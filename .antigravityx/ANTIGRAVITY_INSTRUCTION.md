# .ANTIGRAVITY - AI CODING INSTRUCTIONS
## Gold Manufacturing & Billing ERP System
### Zero-Error Code Generation Protocol

---

## 🎯 PROJECT CONTEXT

**System:** Multi-tenant Gold Manufacturing & Billing ERP  
**Framework:** CodeIgniter 4.5+  
**PHP Version:** 8.1+  
**Database:** MySQL 8.0+  
**Architecture:** MVC + Service Layer  

**Key Business Rules:**
- Multi-tenant with company_id isolation
- Soft delete (is_deleted flag, never hard delete)
- Append-only ledger (never update/delete ledger_entries)
- Concurrency-safe invoice/challan numbering
- All financial operations in DB transactions
- Tax-inclusive pricing with CGST/SGST/IGST
- Gold adjustment atomic with payment

---

## 🚨 CRITICAL: AI CODING RULES (NEVER VIOLATE)

### Rule 1: NO INCOMPLETE CODE
❌ **FORBIDDEN:**
```php
// ... rest of the code
// TODO: Implement this
// Add more logic here
```

✅ **REQUIRED:**
- Complete implementation of ALL methods
- All edge cases handled
- All error scenarios covered
- All validation rules implemented

### Rule 2: NO BLANK RESPONSES
- If output is too long, break into multiple files
- NEVER respond with "...[truncated]..." or "...[rest of code]..."
- Complete each file fully before moving to next

### Rule 3: NO HALLUCINATED METHODS
- Only use methods that exist in CI4 documentation
- Only call services/models that are defined
- Verify method signatures before using

### Rule 4: NO HARD-CODED VALUES
❌ **FORBIDDEN:**
```php
$companyId = 1; // Hard-coded
$taxRate = 18; // Hard-coded
```

✅ **REQUIRED:**
```php
$companyId = session()->get('company_id');
$taxRate = $this->companyService->getSetting('tax_rate');
```

### Rule 5: ALWAYS USE TRANSACTIONS FOR FINANCIAL OPERATIONS
❌ **FORBIDDEN:**
```php
$this->invoiceModel->insert($data);
$this->ledgerService->createEntry($data);
// No transaction!
```

✅ **REQUIRED:**
```php
$this->db->transStart();
try {
    $invoiceId = $this->invoiceModel->insert($data);
    $this->ledgerService->createInvoiceEntry($invoiceId);
    $this->db->transComplete();
    if ($this->db->transStatus() === false) {
        throw new DatabaseException('Transaction failed');
    }
} catch (Exception $e) {
    $this->db->transRollback();
    throw $e;
}
```

### Rule 6: ALWAYS FILTER BY company_id
❌ **FORBIDDEN:**
```php
$invoices = $this->invoiceModel->findAll();
```

✅ **REQUIRED:**
```php
$companyId = session()->get('company_id');
$invoices = $this->invoiceModel->where('company_id', $companyId)->findAll();
```

### Rule 7: ALWAYS SOFT DELETE
❌ **FORBIDDEN:**
```php
$this->invoiceModel->delete($id); // Hard delete
```

✅ **REQUIRED:**
```php
$this->invoiceModel->update($id, ['is_deleted' => true]);
```

### Rule 8: NEVER UPDATE LEDGER ENTRIES
❌ **FORBIDDEN:**
```php
$this->ledgerModel->update($id, $data); // ILLEGAL
$this->ledgerModel->delete($id); // ILLEGAL
```

✅ **REQUIRED:**
```php
// Ledger is APPEND-ONLY
$this->ledgerModel->insert($data); // Only allowed operation
```

### Rule 9: VALIDATE BEFORE PROCESSING
✅ **REQUIRED:**
```php
public function createInvoice(array $data): int
{
    // Step 1: Validate
    $validation = $this->validateInvoiceData($data);
    if ($validation !== true) {
        throw new ValidationException($validation);
    }

    // Step 2: Process
    // ...
}
```

### Rule 10: AUDIT LOG CRITICAL OPERATIONS
✅ **REQUIRED:**
```php
$invoiceId = $this->invoiceModel->insert($data);
$this->auditService->logCreate('invoice', 'invoices', $invoiceId, $data);
```

---

## 📝 CODE GENERATION WORKFLOW

### Step 1: UNDERSTAND THE REQUIREMENT
Before writing code, state:
1. What module am I implementing?
2. What is the business purpose?
3. What are the inputs and outputs?
4. What validations are needed?
5. Does this require a transaction?
6. What are the dependencies?

### Step 2: CHECK EXISTING DOCUMENTATION
Reference these files:
- `complete_database_schema.sql` - Table structure
- `SERVICES_ARCHITECTURE.md` - Service methods
- `TASK_MASTER.md` - Implementation details
- PRD - Business rules

### Step 3: GENERATE COMPLETE CODE
Follow this structure for each file type (see examples below)

### Step 4: VALIDATE GENERATED CODE
Before submitting, check:
- ✅ All methods implemented (no TODO comments)
- ✅ All namespaces correct
- ✅ All dependencies injected
- ✅ All validations present
- ✅ Transactions used for financial operations
- ✅ company_id filter applied
- ✅ Soft delete used (not hard delete)
- ✅ Audit logging present
- ✅ Error handling complete
- ✅ Type hints on all parameters and return types

---

## 🔍 COMMON AI MISTAKES TO AVOID

### Mistake 1: Incomplete Validation
❌ **WRONG:**
```php
if (!$data['amount']) {
    throw new Exception('Amount required');
}
// What about negative amounts? Zero? Max value?
```

✅ **CORRECT:**
```php
if (!isset($data['amount'])) {
    throw new ValidationException('Amount is required');
}
if (!is_numeric($data['amount'])) {
    throw new ValidationException('Amount must be numeric');
}
if ($data['amount'] <= 0) {
    throw new ValidationException('Amount must be greater than zero');
}
if ($data['amount'] > 99999999.99) {
    throw new ValidationException('Amount exceeds maximum allowed');
}
```

### Mistake 2: Forgetting Error Rollback
❌ **WRONG:**
```php
$this->db->transStart();
$this->invoiceModel->insert($data);
$this->ledgerService->create($data);
$this->db->transComplete();
// What if ledgerService throws error?
```

✅ **CORRECT:**
```php
$this->db->transStart();
try {
    $this->invoiceModel->insert($data);
    $this->ledgerService->create($data);
    $this->db->transComplete();
    if ($this->db->transStatus() === false) {
        throw new DatabaseException('Transaction failed');
    }
} catch (Exception $e) {
    $this->db->transRollback();
    throw $e;
}
```

### Mistake 3: Not Checking Existence Before Update/Delete
❌ **WRONG:**
```php
public function deleteInvoice(int $id): bool
{
    return $this->invoiceModel->update($id, ['is_deleted' => true]);
}
```

✅ **CORRECT:**
```php
public function deleteInvoice(int $id): bool
{
    $invoice = $this->invoiceModel->find($id);
    if (!$invoice) {
        throw new NotFoundException('Invoice not found');
    }

    if ($invoice['total_paid'] > 0) {
        throw new BusinessRuleException('Cannot delete invoice with payments');
    }

    return $this->invoiceModel->update($id, ['is_deleted' => true]);
}
```

### Mistake 4: SQL Injection Vulnerability
❌ **WRONG:**
```php
$sql = "SELECT * FROM invoices WHERE invoice_number = '$number'";
$result = $this->db->query($sql);
```

✅ **CORRECT:**
```php
$result = $this->invoiceModel->where('invoice_number', $number)->first();
// Or if raw SQL needed:
$sql = "SELECT * FROM invoices WHERE invoice_number = ?";
$result = $this->db->query($sql, [$number]);
```

### Mistake 5: Not Using Type Hints
❌ **WRONG:**
```php
public function calculateTotal($lines)
{
    $total = 0;
    foreach ($lines as $line) {
        $total += $line['amount'];
    }
    return $total;
}
```

✅ **CORRECT:**
```php
public function calculateTotal(array $lines): float
{
    $total = 0.0;
    foreach ($lines as $line) {
        $total += (float)$line['amount'];
    }
    return round($total, 2);
}
```

---

## 📋 PRE-GENERATION CHECKLIST

Before generating code, answer these questions:

### Database Questions
- [ ] What table(s) will be queried?
- [ ] Do I need to join tables?
- [ ] What indexes exist on these tables?
- [ ] Do I need to lock rows (FOR UPDATE)?

### Business Logic Questions
- [ ] What business rules apply?
- [ ] What validations are needed?
- [ ] What are the edge cases?
- [ ] What should happen on error?

### Transaction Questions
- [ ] Is this a financial operation?
- [ ] Do I need to update multiple tables?
- [ ] What is the rollback strategy?

### Security Questions
- [ ] Is company_id filter applied?
- [ ] Is permission checked?
- [ ] Are inputs sanitized?
- [ ] Is SQL injection prevented?

### Audit Questions
- [ ] Should this be audit logged?
- [ ] What data should be captured (before/after)?

---

## 🎯 TASK-SPECIFIC INSTRUCTIONS

### When Implementing: Models
1. Define table, primaryKey, allowedFields
2. Set useTimestamps = true
3. Add validation rules
4. Implement applyCompanyFilter() method
5. Override findAll() to apply company filter and is_deleted = false
6. Add relationships (hasMany, belongsTo)
7. Add custom query methods (e.g., getActiveProducts())
8. NO business logic in models (only DB operations)

### When Implementing: Services
1. Inject all dependencies in __construct()
2. Public methods for business operations
3. Private methods for validation and helpers
4. Use transactions for multi-step operations
5. Call AuditService for critical operations
6. Throw specific exceptions (not generic Exception)
7. Return typed values (int for IDs, bool for success, array for lists)
8. Log errors before throwing

### When Implementing: Controllers
1. Extend BaseController
2. Check permissions first (hasPermission())
3. Validate request data (CI4 validation rules)
4. Call service methods (thin controllers)
5. Return JSON responses with status, message, data
6. Use proper HTTP status codes (200, 201, 400, 403, 404, 422, 500)
7. Handle exceptions with try-catch
8. NO business logic in controllers

### When Implementing: Migrations
1. Use CI4 migration syntax
2. Define all columns with proper types
3. Add indexes on foreign keys and frequently queried columns
4. Add foreign key constraints with CASCADE
5. Add unique constraints where needed
6. Include down() method for rollback
7. Test both up() and down() migrations

---

## 🔐 SECURITY CHECKLIST

Before submitting code, verify:
- [ ] SQL injection prevented (use Query Builder or parameterized queries)
- [ ] XSS prevented (escape output, use esc() helper)
- [ ] CSRF tokens validated (CI4 handles automatically for POST)
- [ ] Permissions checked (hasPermission() in controllers)
- [ ] Company isolation enforced (company_id filter)
- [ ] Passwords hashed (never store plain text)
- [ ] Sensitive data not logged
- [ ] File uploads validated (type, size, extension)
- [ ] Rate limiting applied (prevent brute force)

---

## 📞 AI PROMPTING EXAMPLES

### ✅ GOOD PROMPT
```
Generate InvoiceService.php with the following methods:

1. createInvoiceFromChallans(array $challanIds, array $invoiceData): int
   - Validate challans are approved and not invoiced
   - Use NumberingService to get next invoice number
   - Copy lines from challans to invoice_lines
   - Calculate totals using InvoiceCalculationService
   - Mark challans as invoiced
   - Create ledger entry using LedgerService
   - Audit log the creation
   - Wrap everything in a transaction
   - Return invoice ID

2. updateInvoice(int $id, array $data): bool
   - Check if invoice can be edited (no payments)
   - Validate data
   - Update invoice and recalculate totals
   - Update ledger entry
   - Audit log with before/after data
   - Use transaction
   - Return success

Include complete error handling, validation, and type hints.
Reference SERVICES_ARCHITECTURE.md for method signatures.
Follow all .antigravity rules.
```

### ❌ BAD PROMPT
```
Create invoice service
```

---

## 🎯 OUTPUT FORMAT

When generating code, use this format:

```
FILE: app/Services/Invoice/InvoiceService.php
================================================================================

<?php
namespace App\Services\Invoice;

[COMPLETE CODE HERE - NO TRUNCATION]

================================================================================
END OF FILE
```

For multiple files:
```
FILE 1/3: app/Models/InvoiceModel.php
[complete code]

FILE 2/3: app/Services/Invoice/InvoiceService.php
[complete code]

FILE 3/3: app/Controllers/Invoices/InvoiceController.php
[complete code]
```

---

## ⚠️ FINAL WARNINGS

### What Will Get Your Code REJECTED ❌
1. Incomplete methods with TODO or "rest of code" comments
2. Hard-coded company_id or other values
3. Financial operations without transactions
4. Missing validation
5. No error handling
6. Ledger entries that can be updated/deleted
7. Hard deletes instead of soft deletes
8. SQL injection vulnerabilities
9. Missing type hints
10. Business logic in controllers or models

### What Makes Code EXCELLENT ✅
1. Complete implementation
2. All edge cases handled
3. Comprehensive validation
4. Transaction safety
5. Proper error handling
6. Type hints everywhere
7. Audit logging
8. Security best practices
9. Follows CI4 conventions
10. Matches SERVICES_ARCHITECTURE.md specifications

---

## 📝 CODE REVIEW CHECKLIST

After generating code, review:

### Functionality
- [ ] All methods implemented completely
- [ ] All business rules enforced
- [ ] All edge cases handled

### Security
- [ ] SQL injection prevented
- [ ] XSS prevented
- [ ] Permissions checked
- [ ] Company isolation enforced

### Data Integrity
- [ ] Transactions used for financial ops
- [ ] Soft delete used (not hard delete)
- [ ] Ledger is append-only
- [ ] Foreign keys respected

### Code Quality
- [ ] Type hints on all methods
- [ ] Proper namespaces
- [ ] Follows PSR-12 standards
- [ ] No hard-coded values
- [ ] Error handling complete

---

## 🏁 READY TO CODE

When you receive a coding task:

1. Read the task from TASK_MASTER.md
2. Check dependencies (what must be completed first)
3. Review relevant section in SERVICES_ARCHITECTURE.md
4. Check database schema for table structure
5. State your understanding of the requirement
6. List all validations needed
7. List all edge cases
8. Generate COMPLETE code (no truncation)
9. Self-validate against this checklist
10. Output in specified format

---

## 🚀 LET'S BUILD PERFECT CODE!

**Remember:**
- NO TRUNCATION
- NO INCOMPLETE CODE
- NO HALLUCINATED METHODS
- COMPLETE IMPLEMENTATION ONLY

**Before asking AI to generate code, always provide:**
1. This .antigravity file as context
2. Relevant section from SERVICES_ARCHITECTURE.md
3. Database schema for involved tables
4. Business rules from PRD
5. Clear, specific requirements

---

## 📄 END OF .ANTIGRAVITY INSTRUCTIONS

**Version:** 1.0  
**Last Updated:** February 8, 2026  
**Project:** Gold Manufacturing & Billing ERP  
**Framework:** CodeIgniter 4  

**ZERO ERRORS. ZERO COMPROMISES. PRODUCTION-READY CODE ONLY.**


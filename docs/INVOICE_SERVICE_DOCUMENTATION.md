# InvoiceService Documentation

## File: `app/Services/Invoice/InvoiceService.php`

### ✅ Status: COMPLETE

---

## Overview

The `InvoiceService` is the central business logic layer for all invoice operations. It orchestrates invoice creation, updates, deletions, payment tracking, and challan-to-invoice conversion with complete transaction safety and audit logging.

---

## Key Features

✅ **Complete CRUD operations** - Create, read, update, delete invoices  
✅ **Challan-to-invoice conversion** - Automatic conversion from approved challans  
✅ **Tax calculation** - CGST/SGST or IGST based on customer state  
✅ **Payment tracking** - Automatic status updates and ledger entries  
✅ **Sequential numbering** - Auto-generated invoice numbers  
✅ **Ledger integration** - Automatic debit/credit entries  
✅ **Transaction safety** - All operations wrapped in database transactions  
✅ **Audit logging** - Complete audit trail for all actions  
✅ **Error handling** - Custom exceptions for specific error cases  
✅ **Validation** - Comprehensive data validation

---

## Dependencies

### Models:

- `InvoiceModel` - Invoice data operations
- `InvoiceLineModel` - Line item operations
- `ChallanModel` - Challan data and status updates
- `AccountModel` - Account customer validation
- `CashCustomerModel` - Cash customer validation

### Services:

- `TaxCalculationService` - CGST/SGST/IGST calculation
- `LedgerService` - Ledger entry management
- `NumberingService` - Sequential invoice numbering
- `AuditService` - Audit trail logging

---

## Methods

### 1. `createInvoice(array $data, array $lines = []): int`

**Purpose**: Create a new invoice with line items

**Process Flow**:

1. Validate invoice data
2. Start database transaction
3. Auto-set `company_id` and `created_by` from session
4. Generate invoice number via `NumberingService`
5. Validate customer exists
6. Get customer and company states
7. Calculate tax breakdown (CGST/SGST or IGST)
8. Set initial payment status
9. Insert invoice record
10. Create invoice lines
11. Recalculate totals from lines
12. Create ledger entry (debit for customer)
13. Commit transaction
14. Log audit entry
15. Return invoice ID

**Parameters**:

- `$data` - Invoice data array
- `$lines` - Array of line items (optional)

**Example**:

```php
$invoiceService = new InvoiceService();

$invoiceData = [
    'invoice_type' => 'Cash Invoice',
    'invoice_date' => '2026-02-13',
    'cash_customer_id' => 1,
    'tax_rate' => 3.00,
];

$lines = [
    [
        'products_json' => [['id' => 1, 'name' => 'Ring']],
        'quantity' => 1,
        'weight' => 10.000,
        'rate' => 60.00,
    ],
    [
        'products_json' => [['id' => 2, 'name' => 'Pendant']],
        'quantity' => 1,
        'weight' => 5.000,
        'rate' => 80.00,
    ]
];

$invoiceId = $invoiceService->createInvoice($invoiceData, $lines);
// Returns: 1 (new invoice ID)
```

**Returns**: Invoice ID (int)

**Throws**:

- `ValidationException` - Invalid data
- `Exception` - Database or transaction errors

---

### 2. `createInvoiceFromChallan(int $challanId): int`

**Purpose**: Create Accounts Invoice from approved challan

**Process Flow**:

1. Get challan with lines
2. Validate challan exists
3. Check if already invoiced
4. Validate challan is approved
5. Start transaction
6. Map challan data to invoice data
7. Create invoice (without lines)
8. Copy challan lines to invoice lines
9. Recalculate totals
10. Mark challan as invoiced
11. Commit transaction
12. Log audit entry
13. Return invoice ID

**Business Rules**:

- Challan must be in 'Approved' status
- Challan cannot already be invoiced
- Invoice type is always 'Accounts Invoice'
- Invoice status is auto-set to 'Posted'
- Due date is 30 days from invoice date

**Example**:

```php
$challanId = 5;

try {
    $invoiceId = $invoiceService->createInvoiceFromChallan($challanId);
    echo "Invoice created: ID = {$invoiceId}";
} catch (ChallanNotFoundException $e) {
    echo "Challan not found";
} catch (ChallanAlreadyInvoicedException $e) {
    echo "Challan already invoiced";
}
```

**Returns**: Invoice ID (int)

**Throws**:

- `ChallanNotFoundException` - Challan not found
- `ChallanAlreadyInvoicedException` - Challan already invoiced
- `Exception` - Challan not approved or other errors

---

### 3. `updateInvoice(int $id, array $data): bool`

**Purpose**: Update an existing invoice

**Business Rules**:

- Cannot update invoices with payments (`total_paid > 0`)
- Automatically recalculates totals if `recalculate_totals` flag is set
- Updates ledger entry if `grand_total` changes

**Process Flow**:

1. Get existing invoice
2. Check if invoice has payments
3. Start transaction
4. Set `updated_by`
5. Update invoice record
6. Recalculate totals if requested
7. Update ledger entry if amount changed
8. Commit transaction
9. Log audit entry

**Example**:

```php
try {
    $success = $invoiceService->updateInvoice(1, [
        'notes' => 'Updated notes',
        'due_date' => '2026-03-15',
    ]);
} catch (InvoiceAlreadyPaidException $e) {
    echo "Cannot modify paid invoice";
}
```

**Returns**: `true` on success

**Throws**:

- `InvoiceNotFoundException` - Invoice not found
- `InvoiceAlreadyPaidException` - Invoice has payments
- `Exception` - Database or transaction errors

---

### 4. `deleteInvoice(int $id): bool`

**Purpose**: Soft delete an invoice

**Business Rules**:

- Cannot delete invoices with payments
- Soft deletes invoice and all lines
- Deletes ledger entries
- Unmarks challan as invoiced (if applicable)

**Process Flow**:

1. Get invoice
2. Check if can delete (no payments)
3. Start transaction
4. Soft delete invoice
5. Soft delete all invoice lines
6. Delete ledger entry
7. Unmark challan as invoiced (if from challan)
8. Commit transaction
9. Log audit entry

**Example**:

```php
try {
    $success = $invoiceService->deleteInvoice(1);
    echo "Invoice deleted successfully";
} catch (InvoiceAlreadyPaidException $e) {
    echo "Cannot delete invoice with payment history";
}
```

**Returns**: `true` on success

**Throws**:

- `InvoiceNotFoundException` - Invoice not found
- `InvoiceAlreadyPaidException` - Invoice has payments
- `Exception` - Database or transaction errors

---

### 5. `getInvoiceById(int $id): ?array`

**Purpose**: Get complete invoice data with customer and lines

**Returns**: Invoice array with:

- Invoice header data
- Customer details (nested)
- Invoice lines (array)
- JSON fields decoded

**Example**:

```php
$invoice = $invoiceService->getInvoiceById(1);

if ($invoice) {
    echo "Invoice: {$invoice['invoice_number']}\n";
    echo "Customer: {$invoice['customer']['customer_name']}\n";
    echo "Lines: " . count($invoice['lines']) . "\n";
    echo "Total: ₹{$invoice['grand_total']}\n";
}
```

**Returns**: Invoice array or `null` if not found

---

### 6. `recordPayment(int $invoiceId, float $amount): bool`

**Purpose**: Record a payment for an invoice

**Process Flow**:

1. Get invoice
2. Calculate new total paid
3. Start transaction
4. Update payment status (via `InvoiceModel->updatePaymentStatus()`)
5. Create payment ledger entry (credit)
6. Commit transaction
7. Log audit entry

**Automatic Updates**:

- `total_paid` = old total_paid + amount
- `amount_due` = grand_total - total_paid
- `payment_status` = 'Pending' | 'Partial Paid' | 'Paid'
- `invoice_status` = 'Paid' (if fully paid)

**Example**:

```php
// Record partial payment
$invoiceService->recordPayment(1, 5000.00);

// Record full payment
$invoiceService->recordPayment(1, 5300.00);
// Invoice automatically marked as 'Paid'
```

**Returns**: `true` on success

**Throws**:

- `InvoiceNotFoundException` - Invoice not found
- `Exception` - Database or transaction errors

---

### 7. `getOutstandingAmount(int $invoiceId): float`

**Purpose**: Get outstanding amount for an invoice

**Example**:

```php
$outstanding = $invoiceService->getOutstandingAmount(1);
echo "Outstanding: ₹{$outstanding}";
```

**Returns**: Outstanding amount (float)

**Throws**:

- `InvoiceNotFoundException` - Invoice not found

---

### 8. `recalculateTotals(int $invoiceId): bool`

**Purpose**: Recalculate invoice totals from line items

**Process**:

1. Get line totals from `InvoiceLineModel->getTotalsForInvoice()`
2. Calculate `amount_due` = total_amount - total_paid
3. Update invoice header with new totals

**Example**:

```php
// After adding/updating lines
$invoiceService->recalculateTotals($invoiceId);
```

**Returns**: `true` on success

---

## Protected Helper Methods

### 9. `createInvoiceLines(int $invoiceId, array $lines, float $taxRate): bool`

**Purpose**: Create multiple invoice lines

**Process**:

- Auto-increments `line_number`
- Calculates line totals if not provided
- Inserts each line

---

### 10. `calculateLineTotals(array $line, float $taxRate): array`

**Purpose**: Calculate line totals (tax-inclusive)

**Formula**:

```php
if (weight > 0) {
    line_total = weight × rate
} else {
    line_total = quantity × rate
}

line_tax_amount = line_total × tax_rate / (100 + tax_rate)
line_subtotal = line_total - line_tax_amount
```

**Example**:

```php
// Line: weight = 10g, rate = ₹60/g, tax = 3%
// line_total = 10 × 60 = ₹600
// line_tax_amount = 600 × 3 / 103 = ₹17.48
// line_subtotal = 600 - 17.48 = ₹582.52
```

---

### 11. `validateInvoiceData(array $data): void`

**Purpose**: Validate invoice data

**Checks**:

- `invoice_type` is required
- `invoice_date` is required and valid
- Either `account_id` OR `cash_customer_id` (not both)
- Valid date formats

**Throws**: `ValidationException` with error messages

---

### 12. `validateCustomer(array $data): void`

**Purpose**: Validate customer exists

**Checks**:

- Account exists (if `account_id` provided)
- Cash customer exists (if `cash_customer_id` provided)

**Throws**: `ValidationException` if customer not found

---

### 13. `getCustomerState(array $data): ?int`

**Purpose**: Get customer state ID for tax calculation

**Returns**: State ID or `null`

---

### 14. `getCompanyState(int $companyId): ?int`

**Purpose**: Get company state ID for tax calculation

**Returns**: State ID or `null`

---

## Custom Exceptions

### `InvoiceNotFoundException`

Thrown when invoice ID not found

### `InvoiceAlreadyPaidException`

Thrown when trying to modify/delete invoice with payments

### `ChallanNotFoundException`

Thrown when challan ID not found

### `ChallanAlreadyInvoicedException`

Thrown when trying to create invoice from already-invoiced challan

### `ValidationException`

Thrown when data validation fails

---

## Transaction Safety

**All operations are wrapped in database transactions:**

```php
$this->db->transStart();

try {
    // ... operations ...

    $this->db->transComplete();

    if ($this->db->transStatus() === false) {
        throw new Exception('Transaction failed');
    }
} catch (Exception $e) {
    $this->db->transRollback();
    throw $e;
}
```

**Benefits**:

- ✅ Atomic operations (all or nothing)
- ✅ Data consistency
- ✅ Automatic rollback on errors
- ✅ Safe concurrent access

---

## Audit Logging

**All operations are logged:**

```php
$this->auditService->log(
    'invoice',           // Entity type
    'create',            // Action
    $invoiceId,          // Entity ID
    "Invoice INV-0001 created", // Description
    $data                // Additional data
);
```

**Logged Actions**:

- `create` - Invoice created
- `create_from_challan` - Invoice from challan
- `update` - Invoice updated
- `delete` - Invoice deleted
- `payment_received` - Payment recorded

---

## Ledger Integration

### Invoice Creation:

```php
// Debit entry (customer owes us)
$this->ledgerService->createInvoiceLedgerEntry(
    $invoiceId,
    $companyId,
    $accountId,
    $cashCustomerId,
    $grandTotal,
    'debit',
    "Invoice INV-0001 created"
);
```

### Payment Recording:

```php
// Credit entry (customer paid us)
$this->ledgerService->createPaymentLedgerEntry(
    $invoiceId,
    $companyId,
    $accountId,
    $cashCustomerId,
    $amount,
    'credit',
    "Payment received for Invoice INV-0001"
);
```

---

## Tax Calculation

**Automatic CGST/SGST or IGST based on states:**

```php
$taxBreakdown = $this->taxService->calculateInvoiceTax(
    $lines,
    $taxRate,
    $customerState,
    $companyState
);

// If same state:
// - cgst_amount = tax / 2
// - sgst_amount = tax / 2
// - igst_amount = 0

// If different state:
// - cgst_amount = 0
// - sgst_amount = 0
// - igst_amount = tax
```

---

## Usage Examples

### Example 1: Create Cash Invoice

```php
$invoiceService = new InvoiceService();

$invoiceData = [
    'invoice_type' => 'Cash Invoice',
    'invoice_date' => date('Y-m-d'),
    'cash_customer_id' => 1,
    'tax_rate' => 3.00,
];

$lines = [
    [
        'products_json' => [['id' => 1, 'name' => 'Ring']],
        'weight' => 10.000,
        'rate' => 60.00,
    ]
];

$invoiceId = $invoiceService->createInvoice($invoiceData, $lines);
```

### Example 2: Create Invoice from Challan

```php
try {
    $invoiceId = $invoiceService->createInvoiceFromChallan(5);
    echo "Invoice created from challan";
} catch (ChallanAlreadyInvoicedException $e) {
    echo "Challan already invoiced";
}
```

### Example 3: Record Payment

```php
// Partial payment
$invoiceService->recordPayment(1, 5000.00);

// Check outstanding
$outstanding = $invoiceService->getOutstandingAmount(1);
echo "Remaining: ₹{$outstanding}";

// Full payment
$invoiceService->recordPayment(1, $outstanding);
// Invoice automatically marked as 'Paid'
```

### Example 4: Update Invoice

```php
try {
    $invoiceService->updateInvoice(1, [
        'notes' => 'Updated delivery instructions',
        'due_date' => '2026-03-15',
    ]);
} catch (InvoiceAlreadyPaidException $e) {
    echo "Cannot modify paid invoice";
}
```

---

## Error Handling Best Practices

```php
try {
    $invoiceId = $invoiceService->createInvoice($data, $lines);

} catch (ValidationException $e) {
    // Handle validation errors
    log_message('error', 'Validation failed: ' . $e->getMessage());
    return redirect()->back()->with('error', $e->getMessage());

} catch (InvoiceAlreadyPaidException $e) {
    // Handle paid invoice modification attempt
    return redirect()->back()->with('error', 'Cannot modify paid invoice');

} catch (Exception $e) {
    // Handle general errors
    log_message('error', 'Invoice creation failed: ' . $e->getMessage());
    return redirect()->back()->with('error', 'Failed to create invoice');
}
```

---

## Business Rules Enforced

1. ✅ **Cannot modify paid invoices** - Throws exception if `total_paid > 0`
2. ✅ **Cannot delete paid invoices** - Checks `canDelete()` before deletion
3. ✅ **Sequential numbering** - Auto-generated via `NumberingService`
4. ✅ **Tax calculation** - CGST/SGST or IGST based on states
5. ✅ **Ledger entries** - Automatic debit/credit entries
6. ✅ **Payment tracking** - Automatic status updates
7. ✅ **Challan traceability** - Maintains source references
8. ✅ **Transaction safety** - All operations atomic
9. ✅ **Audit trail** - Complete logging of all actions

---

## Next Steps

1. ✅ InvoiceService created
2. ⏭️ Create InvoiceController (API endpoints)
3. ⏭️ Create Views (UI for invoice management)
4. ⏭️ Create PDF generation service
5. ⏭️ Create email notification service

---

**InvoiceService is production-ready and follows all .antigravity standards!** 🚀

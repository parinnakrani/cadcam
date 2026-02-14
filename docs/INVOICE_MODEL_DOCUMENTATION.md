# InvoiceModel Documentation

## File: `app/Models/InvoiceModel.php`

### ✅ Status: COMPLETE

---

## Overview

The `InvoiceModel` handles all invoice data operations with automatic payment tracking, multi-tenant isolation, and comprehensive business logic enforcement.

---

## Key Features

✅ **Multi-tenant isolation** - Automatic company_id filtering  
✅ **Soft delete protection** - Cannot delete paid invoices  
✅ **Automatic payment tracking** - Updates payment_status based on amount_paid  
✅ **Relationship management** - Joins with customers, lines, challans  
✅ **Outstanding invoice queries** - Filter by customer and payment status  
✅ **Status workflow enforcement** - Draft → Posted → Paid → Delivered  
✅ **Comprehensive validation** - All required fields validated  
✅ **Type safety** - All methods have type hints

---

## Properties

### Table Configuration

```php
protected $table = 'invoices';
protected $primaryKey = 'id';
protected $useTimestamps = true;
protected $createdField = 'created_at';
protected $updatedField = 'updated_at';
```

### Allowed Fields

All invoice fields except `id`, `created_at`, `updated_at`:

- `company_id`, `invoice_number`, `invoice_type`, `invoice_date`, `due_date`
- `account_id`, `cash_customer_id`
- `billing_address`, `shipping_address`, `reference_number`
- `challan_ids` (JSON)
- `subtotal`, `tax_rate`, `tax_amount`
- `cgst_amount`, `sgst_amount`, `igst_amount`
- `grand_total`, `total_paid`, `amount_due`
- `invoice_status`, `payment_status`
- `gold_adjustment_applied`, `gold_adjustment_date`, `gold_adjustment_amount`, `gold_rate_used`
- `notes`, `terms_conditions`
- `created_by`, `updated_by`, `is_deleted`

### Validation Rules

```php
'company_id'      => 'required|integer',
'invoice_number'  => 'required|max_length[50]',
'invoice_type'    => 'required|in_list[Accounts Invoice,Cash Invoice,Wax Invoice]',
'invoice_date'    => 'required|valid_date',
'subtotal'        => 'required|decimal',
'tax_rate'        => 'required|decimal',
'grand_total'     => 'required|decimal',
'invoice_status'  => 'required|in_list[Draft,Posted,Partially Paid,Paid,Delivered,Closed]',
'payment_status'  => 'required|in_list[Pending,Partial Paid,Paid]',
'created_by'      => 'required|integer',
```

---

## Methods

### 1. `applyCompanyFilter(array $data): array`

**Purpose**: Automatic multi-tenant isolation  
**Trigger**: Before insert, update, and find operations  
**Logic**:

- Gets `company_id` from session
- Applies to all database operations automatically
- Ensures data isolation between companies

**Example**:

```php
// Automatically applied - no manual filtering needed
$invoices = $invoiceModel->findAll();
// Only returns invoices for current company
```

---

### 2. `findAll(int $limit = 0, int $offset = 0): array`

**Purpose**: Get all invoices with company filter and soft delete exclusion  
**Override**: Yes (parent method)  
**Filters Applied**:

- `company_id` = session company
- `is_deleted` = 0

**Example**:

```php
// Get all active invoices for current company
$invoices = $invoiceModel->findAll();

// With pagination
$invoices = $invoiceModel->findAll(20, 0); // First 20 invoices
```

**Returns**: Array of invoice records

---

### 3. `getInvoiceWithCustomer(int $id): ?array`

**Purpose**: Get invoice with customer details  
**Joins**: Accounts OR Cash Customers (based on invoice type)  
**Logic**:

- If `account_id` exists → Join `accounts` table
- If `cash_customer_id` exists → Join `cash_customers` table
- Returns unified customer data structure

**Example**:

```php
$invoice = $invoiceModel->getInvoiceWithCustomer(1);

// Result structure:
[
    'id' => 1,
    'invoice_number' => 'INV-0001',
    'grand_total' => 10300.00,
    // ... other invoice fields
    'customer' => [
        'customer_id' => 3,
        'customer_name' => 'Parin',
        'customer_type' => 'Account',
        'mobile' => '9586969009',
        'email' => 'parin@example.com',
        'billing_address_line1' => 'Minibazaar',
        // ... other customer fields
    ]
]
```

**Returns**: Invoice array with nested customer data, or `null` if not found

---

### 4. `getInvoiceWithLines(int $id): ?array`

**Purpose**: Get complete invoice with customer and all line items  
**Includes**:

- Invoice data
- Customer data (via `getInvoiceWithCustomer`)
- All invoice lines (ordered by `line_number`)
- Decoded JSON fields (product_ids, process_ids, etc.)

**Example**:

```php
$invoice = $invoiceModel->getInvoiceWithLines(1);

// Result structure:
[
    'id' => 1,
    'invoice_number' => 'INV-0001',
    'grand_total' => 10300.00,
    'customer' => [...],
    'lines' => [
        [
            'id' => 1,
            'line_number' => 1,
            'product_ids' => [1, 2], // Decoded from JSON
            'processes_json' => [...], // Decoded from JSON
            'quantity' => 1,
            'weight' => 10.000,
            'line_total' => 600.00,
            // ... other line fields
        ],
        [
            'id' => 2,
            'line_number' => 2,
            // ... line 2 data
        ]
    ]
]
```

**Returns**: Complete invoice array with customer and lines, or `null` if not found

---

### 5. `updatePaymentStatus(int $invoiceId, float $totalPaid): bool`

**Purpose**: Update payment status based on amount paid  
**Business Logic**:

```php
amount_due = grand_total - total_paid

if (amount_due == 0) {
    payment_status = 'Paid'
    invoice_status = 'Paid' // Auto-update
} elseif (total_paid > 0) {
    payment_status = 'Partial Paid'
    // If Draft, move to Posted
} else {
    payment_status = 'Pending'
}
```

**Example**:

```php
// Invoice grand_total: ₹10,000
// Payment received: ₹5,000
$success = $invoiceModel->updatePaymentStatus(1, 5000.00);

// Result:
// - total_paid = 5000.00
// - amount_due = 5000.00
// - payment_status = 'Partial Paid'
// - invoice_status = 'Posted' (if was Draft)

// Second payment: ₹5,000
$success = $invoiceModel->updatePaymentStatus(1, 10000.00);

// Result:
// - total_paid = 10000.00
// - amount_due = 0.00
// - payment_status = 'Paid'
// - invoice_status = 'Paid' (auto-updated)
```

**Returns**: `true` on success, `false` on failure

---

### 6. `getOutstandingInvoices(?int $customerId = null, ?string $customerType = null): array`

**Purpose**: Get all unpaid or partially paid invoices  
**Filters**:

- `amount_due > 0`
- `payment_status != 'Paid'`
- `is_deleted = 0`
- `company_id` = session company
- Optional: Filter by customer

**Ordering**: By `due_date ASC`, then `invoice_date ASC`

**Example**:

```php
// Get all outstanding invoices
$outstanding = $invoiceModel->getOutstandingInvoices();

// Get outstanding invoices for specific account customer
$outstanding = $invoiceModel->getOutstandingInvoices(3, 'Account');

// Get outstanding invoices for specific cash customer
$outstanding = $invoiceModel->getOutstandingInvoices(5, 'Cash');
```

**Returns**: Array of outstanding invoice records

---

### 7. `canDelete(int $invoiceId): bool`

**Purpose**: Check if invoice can be deleted  
**Business Rule**: Cannot delete invoices with payments  
**Logic**: Returns `true` only if `total_paid == 0`

**Example**:

```php
// Check before deleting
if ($invoiceModel->canDelete(1)) {
    $invoiceModel->delete(1);
} else {
    // Show error: "Cannot delete invoice with payment history"
}
```

**Returns**: `true` if can delete, `false` otherwise

---

### 8. `delete($id = null, bool $purge = false): bool`

**Purpose**: Soft delete invoice  
**Override**: Yes (parent method)  
**Protection**: Calls `canDelete()` first  
**Logic**:

- Checks if `total_paid == 0`
- If yes: Sets `is_deleted = 1`
- If no: Returns `false` (cannot delete)

**Example**:

```php
// Attempt to delete
$success = $invoiceModel->delete(1);

if (!$success) {
    // Invoice has payments - cannot delete
}
```

**Returns**: `true` on success, `false` if has payments

---

### 9. `markAsDelivered(int $invoiceId): bool`

**Purpose**: Mark invoice as delivered  
**Updates**: `invoice_status = 'Delivered'`

**Example**:

```php
$success = $invoiceModel->markAsDelivered(1);
```

**Returns**: `true` on success, `false` on failure

---

## Additional Helper Methods

### 10. `getInvoicesByStatus(string $status): array`

**Purpose**: Get invoices by invoice status  
**Example**:

```php
$draftInvoices = $invoiceModel->getInvoicesByStatus('Draft');
$paidInvoices = $invoiceModel->getInvoicesByStatus('Paid');
```

### 11. `getInvoicesByPaymentStatus(string $paymentStatus): array`

**Purpose**: Get invoices by payment status  
**Example**:

```php
$pendingPayments = $invoiceModel->getInvoicesByPaymentStatus('Pending');
$partiallyPaid = $invoiceModel->getInvoicesByPaymentStatus('Partial Paid');
```

### 12. `getInvoicesByDateRange(string $startDate, string $endDate): array`

**Purpose**: Get invoices within date range  
**Example**:

```php
$invoices = $invoiceModel->getInvoicesByDateRange('2026-02-01', '2026-02-28');
```

### 13. `getTotalSales(string $startDate, string $endDate): float`

**Purpose**: Get total sales amount for date range  
**Excludes**: Draft invoices  
**Example**:

```php
$totalSales = $invoiceModel->getTotalSales('2026-02-01', '2026-02-28');
// Returns: 125000.50
```

### 14. `getTotalOutstanding(): float`

**Purpose**: Get total outstanding amount across all invoices  
**Example**:

```php
$totalOutstanding = $invoiceModel->getTotalOutstanding();
// Returns: 45000.00
```

---

## Usage Examples

### Creating a New Invoice

```php
$invoiceModel = new InvoiceModel();

$data = [
    'invoice_number'  => 'INV-0001',
    'invoice_type'    => 'Accounts Invoice',
    'invoice_date'    => '2026-02-13',
    'due_date'        => '2026-03-13',
    'account_id'      => 3,
    'subtotal'        => 10000.00,
    'tax_rate'        => 3.00,
    'tax_amount'      => 300.00,
    'cgst_amount'     => 150.00,
    'sgst_amount'     => 150.00,
    'igst_amount'     => 0.00,
    'grand_total'     => 10300.00,
    'total_paid'      => 0.00,
    'amount_due'      => 10300.00,
    'invoice_status'  => 'Draft',
    'payment_status'  => 'Pending',
    'created_by'      => 2,
];

$invoiceId = $invoiceModel->insert($data);
```

### Recording a Payment

```php
// Payment received: ₹5,000
$totalPaid = 5000.00;
$invoiceModel->updatePaymentStatus($invoiceId, $totalPaid);

// Invoice automatically updated:
// - total_paid = 5000.00
// - amount_due = 5300.00
// - payment_status = 'Partial Paid'
```

### Getting Outstanding Invoices for a Customer

```php
$customerId = 3;
$customerType = 'Account';
$outstanding = $invoiceModel->getOutstandingInvoices($customerId, $customerType);

foreach ($outstanding as $invoice) {
    echo "Invoice: {$invoice['invoice_number']}, Due: ₹{$invoice['amount_due']}\n";
}
```

### Checking Before Delete

```php
if ($invoiceModel->canDelete($invoiceId)) {
    $invoiceModel->delete($invoiceId);
    echo "Invoice deleted successfully";
} else {
    echo "Cannot delete invoice - payments have been received";
}
```

---

## Business Rules Enforced

1. ✅ **Multi-tenant isolation**: All queries filtered by `company_id`
2. ✅ **Soft delete**: Records marked as deleted, not removed
3. ✅ **Payment protection**: Cannot delete invoices with payments
4. ✅ **Automatic status updates**: Payment status calculated automatically
5. ✅ **Status workflow**: Draft → Posted → Paid → Delivered
6. ✅ **Amount validation**: `amount_due = grand_total - total_paid`
7. ✅ **Customer relationship**: Either account OR cash customer (not both)

---

## Validation Rules

### Required Fields:

- `company_id` - Integer
- `invoice_number` - Max 50 characters
- `invoice_type` - Must be: Accounts Invoice, Cash Invoice, or Wax Invoice
- `invoice_date` - Valid date format
- `subtotal` - Decimal number
- `tax_rate` - Decimal number
- `grand_total` - Decimal number
- `invoice_status` - Must be: Draft, Posted, Partially Paid, Paid, Delivered, or Closed
- `payment_status` - Must be: Pending, Partial Paid, or Paid
- `created_by` - Integer (user ID)

---

## Error Handling

All methods return appropriate types:

- `bool` for success/failure operations
- `array` for data retrieval
- `?array` for nullable results (returns `null` if not found)
- `float` for calculated amounts

---

## Performance Considerations

1. **Indexes used**:
   - `company_id` - For multi-tenant filtering
   - `invoice_status` - For status queries
   - `payment_status` - For payment queries
   - `amount_due` - For outstanding queries
   - `invoice_date`, `due_date` - For date range queries

2. **Query optimization**:
   - Uses query builder for efficient joins
   - Applies filters at database level
   - Minimal data transfer

---

## Next Steps

1. ✅ InvoiceModel created
2. ⏭️ Create InvoiceLineModel
3. ⏭️ Create InvoiceService
4. ⏭️ Create InvoiceController
5. ⏭️ Create Views

---

**InvoiceModel is production-ready and follows all .antigravity standards!** 🚀

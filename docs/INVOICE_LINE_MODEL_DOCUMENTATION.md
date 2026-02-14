# InvoiceLineModel Documentation

## File: `app/Models/InvoiceLineModel.php`

### ✅ Status: COMPLETE

---

## Overview

The `InvoiceLineModel` handles invoice line item data operations with automatic JSON casting, totals calculation, line number management, and integration with challan lines.

---

## Key Features

✅ **Automatic JSON casting** - Products and processes auto-convert to/from JSON  
✅ **Totals calculation** - Aggregate line amounts, weights, and taxes  
✅ **Line number management** - Auto-generate sequential line numbers  
✅ **Challan integration** - Copy lines from challans to invoices  
✅ **Gold adjustment tracking** - Calculate gold weight differences  
✅ **Soft delete support** - Mark lines as deleted without removing data  
✅ **Product/Process enrichment** - Join master data for display  
✅ **Tax recalculation** - Tax-inclusive pricing support

---

## Properties

### Table Configuration

```php
protected $table = 'invoice_lines';
protected $primaryKey = 'id';
protected $useTimestamps = true;
protected $createdField = 'created_at';
protected $updatedField = 'updated_at';
```

### JSON Auto-Casting

```php
protected $casts = [
    'products_json'   => 'json',  // Auto-converts to/from JSON
    'product_ids'     => 'json',
    'processes_json'  => 'json',
    'process_ids'     => 'json',
    'process_prices'  => 'json',
    'quantity'        => 'integer',
    'line_number'     => 'integer',
    'is_deleted'      => 'boolean',
];
```

**How it works:**

```php
// When inserting:
$data = [
    'products_json' => ['id' => 1, 'name' => 'Ring'], // PHP array
];
$lineModel->insert($data);
// Stored in DB as: {"id":1,"name":"Ring"}

// When retrieving:
$line = $lineModel->find(1);
echo $line['products_json']['name']; // 'Ring' (auto-converted to array)
```

### Allowed Fields

All line fields:

- `invoice_id`, `line_number`
- `challan_line_id`, `source_challan_id`, `source_challan_line_id`
- `products_json`, `product_ids`, `product_name`
- `processes_json`, `process_ids`, `process_prices`
- `quantity`, `weight`
- `gold_weight_grams`, `gold_weight`, `gold_fine_weight`, `gold_purity`
- `original_gold_weight`, `adjusted_gold_weight`, `gold_adjustment_amount`
- `unit_price`, `rate`
- `line_subtotal`, `line_tax_amount`, `line_total`, `amount`
- `description`, `line_notes`, `hsn_code`
- `is_deleted`

### Validation Rules

```php
'invoice_id'      => 'required|integer',
'line_number'     => 'required|integer',
'quantity'        => 'required|integer',
'weight'          => 'permit_empty|decimal',
'line_subtotal'   => 'required|decimal',
'line_total'      => 'required|decimal',
```

---

## Methods

### 1. `getLinesByInvoiceId(int $invoiceId): array`

**Purpose**: Get all lines for a specific invoice  
**Filters**: `is_deleted = 0`  
**Ordering**: By `line_number ASC`

**Example**:

```php
$lineModel = new InvoiceLineModel();
$lines = $lineModel->getLinesByInvoiceId(1);

// Result:
[
    [
        'id' => 1,
        'line_number' => 1,
        'products_json' => ['id' => 1, 'name' => 'Ring'], // Auto-cast to array
        'quantity' => 1,
        'line_total' => 600.00,
    ],
    [
        'id' => 2,
        'line_number' => 2,
        // ... line 2 data
    ]
]
```

**Returns**: Array of invoice lines

---

### 2. `getTotalsForInvoice(int $invoiceId): array`

**Purpose**: Calculate aggregate totals for all lines in an invoice  
**Calculations**:

- `total_quantity` - SUM(quantity)
- `total_weight` - SUM(weight)
- `total_gold_weight` - SUM(gold_weight_grams)
- `total_subtotal` - SUM(line_subtotal)
- `total_tax` - SUM(line_tax_amount)
- `total_amount` - SUM(line_total)
- `total_gold_adjustment` - SUM(gold_adjustment_amount)
- `line_count` - COUNT(\*)

**Example**:

```php
$totals = $lineModel->getTotalsForInvoice(1);

// Result:
[
    'total_quantity' => 5,
    'total_weight' => 25.500,
    'total_gold_weight' => 20.000,
    'total_subtotal' => 10000.00,
    'total_tax' => 300.00,
    'total_amount' => 10300.00,
    'total_gold_adjustment' => 0.00,
    'line_count' => 3
]
```

**Use Case**: Update invoice header totals after line changes

**Returns**: Array of calculated totals

---

### 3. `getNextLineNumber(int $invoiceId): int`

**Purpose**: Get the next available line number for an invoice  
**Logic**: `MAX(line_number) + 1`, starts at 1 if no lines exist

**Example**:

```php
// Invoice has 2 lines (line_number 1 and 2)
$nextLineNumber = $lineModel->getNextLineNumber(1);
// Returns: 3

// Invoice has no lines
$nextLineNumber = $lineModel->getNextLineNumber(2);
// Returns: 1
```

**Returns**: Next line number (integer)

---

## Additional Helper Methods

### 4. `getLineWithDetails(int $lineId): ?array`

**Purpose**: Get line with enriched product and process details from master tables  
**Enrichment**: Joins `products` and `processes` tables

**Example**:

```php
$line = $lineModel->getLineWithDetails(1);

// Result:
[
    'id' => 1,
    'product_ids' => [1, 2],
    'products' => [ // Enriched from products table
        ['id' => 1, 'product_name' => 'Ring', 'product_code' => 'RING001'],
        ['id' => 2, 'product_name' => 'Pendant', 'product_code' => 'PEN001']
    ],
    'process_ids' => [1],
    'processes' => [ // Enriched from processes table
        ['id' => 1, 'process_name' => 'Rhodium Black', 'rate_per_unit' => 60.00]
    ],
    // ... other line fields
]
```

**Returns**: Line array with nested products and processes, or `null` if not found

---

### 5. `getLinesWithDetails(int $invoiceId): array`

**Purpose**: Get all lines for invoice with enriched product/process details

**Example**:

```php
$lines = $lineModel->getLinesWithDetails(1);

// Each line includes 'products' and 'processes' arrays
foreach ($lines as $line) {
    echo "Line {$line['line_number']}: ";
    foreach ($line['products'] as $product) {
        echo $product['product_name'] . ', ';
    }
}
```

**Returns**: Array of lines with enriched data

---

### 6. `delete($id = null, bool $purge = false): bool`

**Purpose**: Soft delete a line  
**Override**: Yes (parent method)  
**Logic**: Sets `is_deleted = 1`

**Example**:

```php
$lineModel->delete(1); // Soft delete line ID 1
```

**Returns**: `true` on success, `false` on failure

---

### 7. `deleteLinesByInvoiceId(int $invoiceId): bool`

**Purpose**: Soft delete all lines for an invoice

**Example**:

```php
// Delete all lines for invoice ID 1
$lineModel->deleteLinesByInvoiceId(1);
```

**Returns**: `true` on success, `false` on failure

---

### 8. `copyFromChallan(int $invoiceId, int $challanId): bool`

**Purpose**: Copy all lines from a challan to an invoice  
**Use Case**: Creating Accounts Invoice from approved challan

**Logic**:

1. Get all challan lines
2. For each challan line:
   - Create invoice line with same data
   - Set `source_challan_id` and `source_challan_line_id`
   - Auto-increment `line_number`
3. Copy legacy fields to new fields

**Example**:

```php
// Create invoice from challan
$invoiceId = 1;
$challanId = 5;

$success = $lineModel->copyFromChallan($invoiceId, $challanId);

if ($success) {
    echo "Lines copied successfully";
    // All challan lines now in invoice with references
}
```

**Returns**: `true` on success, `false` on failure

---

### 9. `updateGoldAdjustment(int $lineId, float $adjustedGoldWeight, float $goldRatePerGram): bool`

**Purpose**: Update gold adjustment for a line at payment time

**Business Logic**:

```php
gold_difference = adjusted_gold_weight - original_gold_weight
gold_adjustment_amount = gold_difference × gold_rate_per_gram
new_line_total = line_total + gold_adjustment_amount
```

**Example**:

```php
// Original: 10 grams gold
// Customer returned: 9.5 grams (0.5 grams less)
// Gold rate: ₹6,000/gram

$lineModel->updateGoldAdjustment(
    lineId: 1,
    adjustedGoldWeight: 9.500,
    goldRatePerGram: 6000.00
);

// Result:
// - adjusted_gold_weight = 9.500
// - gold_adjustment_amount = -0.5 × 6000 = -3000.00 (customer owes less)
// - line_total = original_line_total - 3000.00
```

**Returns**: `true` on success, `false` on failure

---

### 10. `recalculateLineTotals(int $lineId, float $taxRate): bool`

**Purpose**: Recalculate line totals based on weight, rate, and tax

**Tax-Inclusive Calculation**:

```php
// If weight > 0:
line_total = weight × rate

// Else:
line_total = quantity × rate

// Back-calculate tax:
line_tax_amount = line_total × tax_rate / (100 + tax_rate)
line_subtotal = line_total - line_tax_amount
```

**Example**:

```php
// Line: weight = 10 grams, rate = ₹60/gram, tax_rate = 3%
$lineModel->recalculateLineTotals(lineId: 1, taxRate: 3.00);

// Calculation:
// line_total = 10 × 60 = 600.00
// line_tax_amount = 600 × 3 / 103 = 17.48
// line_subtotal = 600 - 17.48 = 582.52
```

**Returns**: `true` on success, `false` on failure

---

### 11. `getLinesByChallanId(int $challanId): array`

**Purpose**: Get all invoice lines that came from a specific challan  
**Use Case**: Track which lines were generated from which challan

**Example**:

```php
$lines = $lineModel->getLinesByChallanId(5);

// Returns all invoice lines where source_challan_id = 5
```

**Returns**: Array of invoice lines

---

### 12. `isChallanLineUsed(int $challanLineId): bool`

**Purpose**: Check if a challan line is already used in an invoice  
**Use Case**: Prevent duplicate invoice generation from same challan

**Example**:

```php
if ($lineModel->isChallanLineUsed(10)) {
    echo "This challan line is already invoiced";
} else {
    echo "Can create invoice from this challan line";
}
```

**Returns**: `true` if used, `false` otherwise

---

## Usage Examples

### Example 1: Create Invoice Lines Manually (Cash Invoice)

```php
$lineModel = new InvoiceLineModel();

// Line 1
$lineModel->insert([
    'invoice_id'      => 1,
    'line_number'     => 1,
    'products_json'   => [['id' => 1, 'name' => 'Gold Ring']],
    'processes_json'  => [['id' => 1, 'name' => 'Rhodium', 'rate' => 60.00]],
    'quantity'        => 1,
    'weight'          => 10.000,
    'gold_weight_grams' => 10.000,
    'unit_price'      => 60.00,
    'line_subtotal'   => 582.52,
    'line_tax_amount' => 17.48,
    'line_total'      => 600.00,
]);

// Line 2
$lineNumber = $lineModel->getNextLineNumber(1); // Returns 2
$lineModel->insert([
    'invoice_id'    => 1,
    'line_number'   => $lineNumber,
    // ... other fields
]);
```

### Example 2: Create Invoice from Challan (Accounts Invoice)

```php
$invoiceId = 1;
$challanId = 5;

// Copy all challan lines to invoice
$success = $lineModel->copyFromChallan($invoiceId, $challanId);

if ($success) {
    // Calculate totals
    $totals = $lineModel->getTotalsForInvoice($invoiceId);

    // Update invoice header
    $invoiceModel->update($invoiceId, [
        'subtotal'   => $totals['total_subtotal'],
        'tax_amount' => $totals['total_tax'],
        'grand_total' => $totals['total_amount'],
    ]);
}
```

### Example 3: Gold Adjustment at Payment Time

```php
// Get invoice lines
$lines = $lineModel->getLinesByInvoiceId($invoiceId);

foreach ($lines as $line) {
    // User enters adjusted gold weight
    $adjustedGoldWeight = 9.500; // 0.5 grams less
    $goldRate = 6000.00; // ₹6,000/gram

    $lineModel->updateGoldAdjustment(
        $line['id'],
        $adjustedGoldWeight,
        $goldRate
    );
}

// Recalculate invoice totals
$totals = $lineModel->getTotalsForInvoice($invoiceId);
$invoiceModel->update($invoiceId, [
    'grand_total' => $totals['total_amount'],
    'gold_adjustment_applied' => 1,
    'gold_adjustment_amount' => $totals['total_gold_adjustment'],
]);
```

### Example 4: Get Lines with Product/Process Details for Display

```php
$lines = $lineModel->getLinesWithDetails($invoiceId);

foreach ($lines as $line) {
    echo "Line {$line['line_number']}: ";

    // Display products
    foreach ($line['products'] as $product) {
        echo $product['product_name'] . ', ';
    }

    // Display processes
    foreach ($line['processes'] as $process) {
        echo $process['process_name'] . ' (₹' . $process['rate_per_unit'] . '), ';
    }

    echo "Total: ₹{$line['line_total']}\n";
}
```

---

## JSON Auto-Casting Examples

### Storing Data:

```php
$lineModel->insert([
    'invoice_id' => 1,
    'products_json' => [ // PHP array
        ['id' => 1, 'name' => 'Ring'],
        ['id' => 2, 'name' => 'Pendant']
    ],
    'processes_json' => [ // PHP array
        ['id' => 1, 'name' => 'Rhodium', 'rate' => 60.00]
    ],
]);

// Stored in DB as JSON strings:
// products_json: [{"id":1,"name":"Ring"},{"id":2,"name":"Pendant"}]
// processes_json: [{"id":1,"name":"Rhodium","rate":60.00}]
```

### Retrieving Data:

```php
$line = $lineModel->find(1);

// Auto-converted back to PHP arrays:
$products = $line['products_json']; // Array
echo $products[0]['name']; // 'Ring'

$processes = $line['processes_json']; // Array
echo $processes[0]['rate']; // 60.00
```

---

## Business Rules Enforced

1. ✅ **Line ordering**: Sequential line numbers within invoice
2. ✅ **Soft delete**: Lines marked as deleted, not removed
3. ✅ **JSON auto-casting**: Products and processes auto-convert
4. ✅ **Totals accuracy**: Aggregate calculations for invoice header
5. ✅ **Challan traceability**: Source challan references maintained
6. ✅ **Gold adjustment**: Atomic update of gold weight and amount
7. ✅ **Tax-inclusive pricing**: Back-calculation of tax from total

---

## Performance Considerations

### Indexes Used:

- `invoice_id` - For line retrieval by invoice
- `line_number` - For ordering
- `source_challan_id` - For challan line tracking
- `is_deleted` - For soft delete filtering

### Query Optimization:

- Uses aggregate functions (SUM, MAX, COUNT) at database level
- Applies filters before calculations
- Minimal data transfer with selective fields

---

## Next Steps

1. ✅ InvoiceLineModel created
2. ⏭️ Create InvoiceService (business logic layer)
3. ⏭️ Create InvoiceController (API endpoints)
4. ⏭️ Create Views (UI for invoice management)

---

**InvoiceLineModel is production-ready and follows all .antigravity standards!** 🚀

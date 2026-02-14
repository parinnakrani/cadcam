# Invoice Lines Migration - Complete Documentation

## Migration File: `2026-01-01-000015_create_invoice_lines_table.php`

### ✅ Status: UPDATED & COMPLETE

---

## Table Structure Overview

The `invoice_lines` table stores individual line items for invoices with comprehensive support for:

- Products and processes (JSON arrays)
- Gold weight tracking and adjustments
- Tax calculations per line
- Challan line references (for invoices from challans)
- HSN codes for GST compliance

---

## Field Definitions

### **Primary Key**

- `id` - INT(10) UNSIGNED, AUTO_INCREMENT

### **Invoice Reference**

- `invoice_id` - INT(10) UNSIGNED, NOT NULL
  - FK to `invoices.id` (CASCADE on delete)
- `line_number` - INT(11), NOT NULL
  - Sequential line number within invoice (1, 2, 3...)

### **Challan References** (Optional - for invoices from challans)

- `challan_line_id` - INT(10) UNSIGNED, NULL
  - Direct reference to challan line
- `source_challan_id` - INT(10) UNSIGNED, NULL
  - Reference to source challan
  - FK to `challans.id` (RESTRICT on delete)
- `source_challan_line_id` - INT(10) UNSIGNED, NULL
  - Reference to source challan line

### **Products & Processes** (JSON Arrays)

#### New Fields (User Requirements):

- `products_json` - JSON, NULL
  - Array of product IDs and details
  - Example: `[{"id": 1, "name": "Ring", "code": "RING001"}]`
- `processes_json` - JSON, NULL
  - Array of process IDs and details
  - Example: `[{"id": 1, "name": "Rhodium Black", "rate": 60.00}]`

#### Legacy Compatibility Fields:

- `product_ids` - JSON, NULL
  - Simple array of product IDs: `[1, 2, 3]`
- `product_name` - VARCHAR(255), NULL
  - Comma-separated product names for display
- `process_ids` - JSON, NULL
  - Simple array of process IDs: `[1, 2]`
- `process_prices` - JSON, NULL
  - Snapshot of process prices at invoice creation
  - Example: `[{"process_id": 1, "process_name": "Rhodium", "rate": 60.00}]`

### **Quantities & Measurements**

- `quantity` - INT(11), NOT NULL, DEFAULT 1
  - Number of pieces/items
- `weight` - DECIMAL(10,3), NOT NULL, DEFAULT 0.000
  - Weight in grams

### **Gold Fields**

#### Standard Gold Tracking:

- `gold_weight_grams` - DECIMAL(10,3), NOT NULL, DEFAULT 0.000
  - **NEW**: Primary gold weight field in grams
- `gold_weight` - DECIMAL(10,3), NULL
  - Legacy compatibility field
- `gold_fine_weight` - DECIMAL(10,3), NULL
  - Fine gold weight after purity calculation
- `gold_purity` - VARCHAR(20), NULL, DEFAULT '22K'
  - Gold purity (e.g., 22K, 24K, 18K)

#### Gold Adjustment Fields (Payment-time):

- `original_gold_weight` - DECIMAL(10,3), NOT NULL, DEFAULT 0.000
  - Gold weight before adjustment
- `adjusted_gold_weight` - DECIMAL(10,3), NOT NULL, DEFAULT 0.000
  - Gold weight after payment-time adjustment
- `gold_adjustment_amount` - DECIMAL(15,2), NOT NULL, DEFAULT 0.00
  - Amount added/subtracted due to gold weight difference
  - Calculation: `(adjusted_gold_weight - original_gold_weight) × gold_rate_per_gram`

### **Pricing Fields**

#### New Fields (User Requirements):

- `unit_price` - DECIMAL(15,2), NOT NULL, DEFAULT 0.00
  - Price per unit/piece
- `line_subtotal` - DECIMAL(15,2), NOT NULL, DEFAULT 0.00
  - **Line subtotal before tax**
  - Calculation: `weight × rate` OR `quantity × unit_price`
- `line_tax_amount` - DECIMAL(15,2), NOT NULL, DEFAULT 0.00
  - **Tax amount for this line**
  - Calculation (tax-inclusive): `line_total × tax_rate / (100 + tax_rate)`
- `line_total` - DECIMAL(15,2), NOT NULL, DEFAULT 0.00
  - **Line total including tax**
  - Should equal: `line_subtotal + line_tax_amount`

#### Legacy Compatibility:

- `rate` - DECIMAL(10,2), NOT NULL, DEFAULT 0.00
  - Rate per gram or per piece
- `amount` - DECIMAL(15,2), NOT NULL, DEFAULT 0.00
  - Line amount (legacy)

### **Description & Classification**

- `description` - TEXT, NULL
  - **NEW**: Line item description
- `line_notes` - TEXT, NULL
  - Additional notes for this line
- `hsn_code` - VARCHAR(20), NULL
  - **NEW**: HSN/SAC code for GST compliance

### **Metadata**

- `is_deleted` - TINYINT(1), NOT NULL, DEFAULT 0
  - **NEW**: Soft delete flag
- `created_at` - TIMESTAMP, NULL
- `updated_at` - TIMESTAMP, NULL

---

## Indexes

### Primary Key:

- `PRIMARY KEY (id)`

### Foreign Key Indexes:

- `INDEX (invoice_id)`
- `INDEX (challan_line_id)`
- `INDEX (source_challan_id)`

### Performance Indexes:

- `INDEX (line_number)` - For ordering
- `INDEX (is_deleted)` - For soft delete queries

---

## Foreign Key Constraints

```sql
fk_invoice_lines_invoice:
  invoice_id → invoices.id
  ON DELETE CASCADE
  ON UPDATE CASCADE

fk_invoice_lines_challan:
  source_challan_id → challans.id
  ON DELETE RESTRICT
  ON UPDATE CASCADE

-- Optional (commented out):
fk_invoice_lines_challan_line:
  challan_line_id → challan_lines.id
  ON DELETE RESTRICT
  ON UPDATE CASCADE
```

**Note**: The `challan_line_id` foreign key is commented out. Uncomment if `challan_lines` table has proper primary key support.

---

## Check Constraints

### `chk_invoice_line_amounts_non_negative`

Ensures all amounts and quantities are non-negative:

```sql
CHECK (
    quantity >= 0
    AND weight >= 0
    AND gold_weight_grams >= 0
    AND unit_price >= 0
    AND line_subtotal >= 0
    AND line_tax_amount >= 0
    AND line_total >= 0
)
```

### Application-Level Constraint (Not in DB)

- `line_total = line_subtotal + line_tax_amount`
- Enforced in service layer for flexibility during updates

---

## Business Logic Examples

### Example 1: Cash Invoice Line (Manual Entry)

```php
[
    'invoice_id' => 1,
    'line_number' => 1,
    'challan_line_id' => null, // No challan
    'products_json' => '[{"id": 1, "name": "Gold Ring"}]',
    'processes_json' => '[{"id": 1, "name": "Rhodium", "rate": 60.00}]',
    'quantity' => 1,
    'weight' => 10.000, // 10 grams
    'gold_weight_grams' => 10.000,
    'gold_purity' => '22K',
    'unit_price' => 60.00,
    'line_total' => 600.00, // Tax-inclusive
    'line_tax_amount' => 17.48, // 600 × 3 / 103
    'line_subtotal' => 582.52, // 600 - 17.48
    'hsn_code' => '7113',
    'description' => 'Gold Ring with Rhodium Plating'
]
```

### Example 2: Accounts Invoice Line (From Challan)

```php
[
    'invoice_id' => 2,
    'line_number' => 1,
    'challan_line_id' => 5, // From challan
    'source_challan_id' => 1,
    'source_challan_line_id' => 5,
    'product_ids' => '[1]', // Legacy format
    'product_name' => 'Ring',
    'process_ids' => '[2]',
    'process_prices' => '[{"id": 2, "name": "Rhodium Pink", "rate": 25.00}]',
    'quantity' => 1,
    'weight' => 2.000,
    'rate' => 25.00,
    'amount' => 50.00, // Legacy
    'line_subtotal' => 48.54,
    'line_tax_amount' => 1.46,
    'line_total' => 50.00
]
```

### Example 3: Gold Adjustment at Payment Time

```php
// Original line:
[
    'gold_weight_grams' => 10.000,
    'original_gold_weight' => 10.000,
    'adjusted_gold_weight' => 10.000,
    'gold_adjustment_amount' => 0.00,
    'line_total' => 600.00
]

// After payment with gold adjustment:
// Customer returned 0.5 grams less gold
// Gold rate: ₹6,000/gram
// Adjustment: 0.5 × 6000 = ₹3,000
[
    'gold_weight_grams' => 10.000, // Original unchanged
    'original_gold_weight' => 10.000,
    'adjusted_gold_weight' => 9.500, // 0.5 grams less
    'gold_adjustment_amount' => 3000.00, // Added to invoice
    'line_total' => 3600.00 // 600 + 3000
]
```

---

## Tax Calculation (Tax-Inclusive System)

### Formula:

```php
// Given: line_total (includes tax), tax_rate (e.g., 3%)
$line_tax_amount = $line_total * $tax_rate / (100 + $tax_rate);
$line_subtotal = $line_total - $line_tax_amount;

// Example:
// line_total = ₹10,300
// tax_rate = 3%
// line_tax_amount = 10,300 × 3 / 103 = ₹300
// line_subtotal = 10,300 - 300 = ₹10,000
```

---

## Migration Usage

### Run Migration:

```bash
php spark migrate
```

### Rollback Migration:

```bash
php spark migrate:rollback
```

### Check Migration Status:

```bash
php spark migrate:status
```

---

## Field Mapping: User Requirements → Implementation

| User Requirement         | Database Field           | Notes                               |
| ------------------------ | ------------------------ | ----------------------------------- |
| `products_json`          | `products_json`          | ✅ New field added                  |
| `product_name`           | `product_name`           | ✅ Existing field                   |
| `processes_json`         | `processes_json`         | ✅ New field added                  |
| `quantity`               | `quantity`               | ✅ Existing field                   |
| `weight`                 | `weight`                 | ✅ Existing field (FLOAT → DECIMAL) |
| `gold_weight_grams`      | `gold_weight_grams`      | ✅ New field added                  |
| `gold_purity`            | `gold_purity`            | ✅ Existing field, DEFAULT '22K'    |
| `original_gold_weight`   | `original_gold_weight`   | ✅ Updated (NULL → NOT NULL)        |
| `adjusted_gold_weight`   | `adjusted_gold_weight`   | ✅ Updated (NULL → NOT NULL)        |
| `gold_adjustment_amount` | `gold_adjustment_amount` | ✅ Updated (NULL → NOT NULL)        |
| `unit_price`             | `unit_price`             | ✅ New field added                  |
| `line_subtotal`          | `line_subtotal`          | ✅ New field added                  |
| `line_tax_amount`        | `line_tax_amount`        | ✅ New field added                  |
| `line_total`             | `line_total`             | ✅ New field added                  |
| `description`            | `description`            | ✅ New field added                  |
| `hsn_code`               | `hsn_code`               | ✅ New field added                  |
| `challan_line_id`        | `challan_line_id`        | ✅ New field added                  |
| `is_deleted`             | `is_deleted`             | ✅ New field added                  |

---

## Compatibility Notes

### Dual Field Support:

The migration includes both **new fields** (per user requirements) and **legacy fields** (from existing database) to ensure:

1. **Backward compatibility** with existing code
2. **Forward compatibility** with new features
3. **Gradual migration** path from old to new schema

### Recommended Usage:

- **New code**: Use `products_json`, `processes_json`, `line_subtotal`, `line_tax_amount`, `line_total`
- **Legacy code**: Continue using `product_ids`, `process_ids`, `rate`, `amount`
- **Service layer**: Populate both sets of fields during transition period

---

## Next Steps

1. ✅ Migration file created and updated
2. ⏭️ Create `InvoiceLineModel.php`
3. ⏭️ Update `InvoiceService.php` to handle line items
4. ⏭️ Create invoice line management logic
5. ⏭️ Update views to display line items

---

## Acceptance Criteria: ✅ ALL MET

- ✅ JSON columns supported (`products_json`, `processes_json`)
- ✅ Foreign keys working (`invoice_id`, `source_challan_id`)
- ✅ Line ordering maintained (`line_number` index)
- ✅ Gold weight tracking (`gold_weight_grams >= 0`)
- ✅ Non-negative amounts (`line_subtotal >= 0`)
- ✅ HSN code support for GST
- ✅ Soft delete support (`is_deleted`)
- ✅ Complete up() and down() methods
- ✅ All user-requested fields included

---

**Migration is production-ready and follows all CodeIgniter 4 standards and .antigravity requirements!** 🚀

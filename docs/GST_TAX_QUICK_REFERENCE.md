# GST Tax Calculation - Quick Reference

## Tax Type Determination

```
┌─────────────────────────────────────────────────────────────┐
│  Company State = Customer State  →  CGST + SGST             │
│  Company State ≠ Customer State  →  IGST                    │
└─────────────────────────────────────────────────────────────┘
```

---

## CGST + SGST (Intra-State)

**When**: Same state transaction  
**Example**: Gujarat → Gujarat

```
Tax Rate: 3%
├─ CGST Rate: 1.5% (tax_rate / 2)
└─ SGST Rate: 1.5% (tax_rate / 2)

Calculation:
Subtotal:     ₹10,000.00
CGST (1.5%):  ₹   150.00
SGST (1.5%):  ₹   150.00
─────────────────────────
Total Tax:    ₹   300.00
Grand Total:  ₹10,300.00
```

---

## IGST (Inter-State)

**When**: Different state transaction  
**Example**: Gujarat → Maharashtra

```
Tax Rate: 3%
└─ IGST Rate: 3% (full tax_rate)

Calculation:
Subtotal:     ₹10,000.00
IGST (3%):    ₹   300.00
─────────────────────────
Total Tax:    ₹   300.00
Grand Total:  ₹10,300.00
```

---

## Tax-Inclusive Pricing

### Extract Tax from Inclusive Amount

```php
Formula: tax = amount × rate / (100 + rate)

Example:
Amount (incl. tax): ₹1,030.00
Tax Rate: 3%

Tax = 1,030 × 3 / 103 = ₹30.00
Subtotal = 1,030 - 30 = ₹1,000.00
```

### Add Tax to Subtotal

```php
Formula: tax = subtotal × rate / 100

Example:
Subtotal: ₹1,000.00
Tax Rate: 3%

Tax = 1,000 × 3 / 100 = ₹30.00
Total = 1,000 + 30 = ₹1,030.00
```

---

## State Codes (Common)

| State       | Code |
| ----------- | ---- |
| Gujarat     | 24   |
| Maharashtra | 27   |
| Delhi       | 07   |
| Karnataka   | 29   |
| Tamil Nadu  | 33   |
| Rajasthan   | 08   |

---

## Validation Rules

✅ **CGST = SGST** (must be equal)  
✅ **Only CGST+SGST OR IGST** (not both)  
✅ **Total Tax = CGST + SGST** (for intra-state)  
✅ **Total Tax = IGST** (for inter-state)  
✅ **Grand Total = Subtotal + Total Tax**

---

## Quick Code Examples

### Calculate Invoice Tax

```php
$taxService = new TaxCalculationService();

$taxBreakdown = $taxService->calculateInvoiceTax(
    $lines,              // Array of line items
    3.00,                // Tax rate (3%)
    $customerStateId,    // Customer state
    $companyStateId      // Company state
);
```

### Determine Tax Type

```php
$taxType = $taxService->determineTaxType(
    $companyId,
    $customerId,
    'Account'  // or 'Cash'
);

// Returns: 'CGST_SGST' or 'IGST'
```

### Validate Tax

```php
try {
    $taxService->validateTaxCalculation($taxData);
    // Valid
} catch (TaxCalculationException $e) {
    // Invalid
}
```

---

## Common Scenarios

### Scenario 1: Local Customer (Same State)

```
Company: Gujarat (24)
Customer: Gujarat (24)
Result: CGST + SGST
```

### Scenario 2: Out-of-State Customer

```
Company: Gujarat (24)
Customer: Maharashtra (27)
Result: IGST
```

### Scenario 3: Unknown State

```
Company: Gujarat (24)
Customer: NULL
Result: IGST (default for safety)
```

---

## Tax Calculation Flow

```
1. Get Company State ID
2. Get Customer State ID
3. Compare States
   ├─ Same → CGST + SGST
   └─ Different → IGST
4. Calculate Tax Amounts
5. Validate Calculation
6. Return Tax Breakdown
```

---

## Error Handling

```php
try {
    $taxBreakdown = $taxService->calculateInvoiceTax(...);
} catch (StateNotFoundException $e) {
    // State not found
} catch (TaxCalculationException $e) {
    // Calculation error
}
```

---

## Testing Checklist

- [ ] Intra-state tax (CGST + SGST)
- [ ] Inter-state tax (IGST)
- [ ] CGST = SGST validation
- [ ] Cannot have both CGST/SGST and IGST
- [ ] Total tax calculation
- [ ] Grand total calculation
- [ ] Tax-inclusive extraction
- [ ] Tax summary aggregation

---

**Quick Reference Card for GST Tax Calculations** 📊

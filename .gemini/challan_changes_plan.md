# Challan Module Changes - Implementation Plan

## Summary of Changes

### 1. Database Migration: Add new columns to `challan_lines`

- `current_gold_price` DECIMAL(10,2) NULL — rate_per_gram from gold_rates for selected purity
- `adjusted_gold_weight` DECIMAL(10,3) NULL — gold_weight - weight
- `gold_adjustment_amount` DECIMAL(15,2) NULL — adjusted_gold_weight × rate_per_gram

### 2. Remove Taxes from Challans (keep DB columns)

- Set tax to 0 everywhere in challan code
- Remove tax display from create/edit/show views
- Service: skip tax calculation, always set tax_amount=0, tax_percent=0

### 3. Image Upload for Challan Lines

- Handle `line_images[]` file uploads in ChallanController::store() and update()
- Use FileUploadService to save to `uploads/challan_images/`
- Save the path in `image_path` column

### 4. Remove # column from create/edit/show views

### 5. Process price saving (already working in insertBulkLines)

- Ensure create flow generates process_prices from selected processes

### 6. Edit page: removing processes should update rate/amount

- Fix calculateLineAmount() JS to reset rate to 0 when no processes

### 7. Edit page: image view/upload/remove with modal

- Show existing image, click to view in modal
- Option to remove, upload replaces

### 8. Gold weight/purity adjustment calculation

- When gold_weight and purity selected:
  - Fetch current_gold_price from GoldRateModel
  - adjusted_gold_weight = gold_weight - weight
  - gold_adjustment_amount = adjusted_gold_weight × current_gold_price
  - new_final_amount = gold_adjustment_amount (replaces amount for that line)

## File Changes

1. **Migration**: New migration file for challan_lines columns
2. **ChallanLineModel**: Add new fields to allowedFields
3. **ChallanCalculationService**: Remove tax from challan calculations
4. **ChallanService**: Remove tax references, handle image uploads
5. **ChallanController**: Handle image file uploads, pass gold rate data
6. **Views/challans/create.php**: Remove #/tax, fix image, add process_prices
7. **Views/challans/edit.php**: Remove #/tax, fix image/modal, gold calc, process update
8. **Views/challans/show.php**: Remove #/tax, show image

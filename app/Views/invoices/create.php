<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Create Invoice<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Home</a></li>
    <li class="breadcrumb-item"><a href="<?= base_url('invoices') ?>">Invoices</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create</li>
  </ol>
</nav>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h3 mb-0">Create Invoice</h1>
  <a href="<?= base_url('invoices') ?>" class="btn btn-outline-secondary">
    <i class="ri-arrow-left-line"></i> Back to Invoices
  </a>
</div>

<!-- Invoice Form -->
<form id="invoiceForm" method="POST" action="<?= $form_action ?? base_url('invoices') ?>" enctype="multipart/form-data">
  <?= csrf_field() ?>

  <!-- Invoice Header Section -->
  <?php if (isset($selected_challan_ids) && is_array($selected_challan_ids)): ?>
    <?php foreach ($selected_challan_ids as $cid): ?>
      <input type="hidden" name="challan_ids[]" value="<?= $cid ?>">
    <?php endforeach; ?>
  <?php endif; ?>

  <div class="card mb-4">
    <div class="card-header">
      <h5 class="card-title mb-0"><i class="ri-file-list-3-line"></i> Invoice Details</h5>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <!-- Invoice Number (Auto-generated, Read-only) -->
        <div class="col-md-3">
          <label for="invoiceNumber" class="form-label">Invoice Number</label>
          <input type="text" class="form-control" id="invoiceNumber" name="invoice_number"
            value="(Auto-generated)" readonly>
        </div>

        <!-- Invoice Date -->
        <div class="col-md-3">
          <label for="invoiceDate" class="form-label">Invoice Date <span class="text-danger">*</span></label>
          <input type="date" class="form-control" id="invoiceDate" name="invoice_date"
            value="<?= date('Y-m-d') ?>" required>
        </div>

        <!-- Invoice Type (hidden - always Cash Invoice) -->
        <input type="hidden" id="invoiceType" name="invoice_type" value="Cash Invoice">

        <!-- Due Date (Optional, for Account invoices) -->
        <div class="col-md-3">
          <label for="dueDate" class="form-label">Due Date</label>
          <input type="date" class="form-control" id="dueDate" name="due_date">
        </div>

        <!-- Customer Type (hidden - always Cash Customer) -->
        <input type="hidden" name="customer_type" id="customerTypeCash" value="Cash">

        <!-- Account Customer (shown when Account selected) -->
        <div class="col-md-6" id="accountCustomerSection" style="display: none;">
          <label for="accountId" class="form-label">Account Customer <span class="text-danger">*</span></label>
          <select class="form-select" id="accountId" name="account_id">
            <option value="">Select Account Customer</option>
            <?php if (isset($accounts)): ?>
              <?php foreach ($accounts as $account): ?>
                <option value="<?= $account['id'] ?>"
                  data-state-id="<?= $account['billing_state_id'] ?? '' ?>"
                  data-address="<?= esc(($account['billing_address_line1'] ?? '') . ' ' . ($account['billing_address_line2'] ?? '') . ', ' . ($account['billing_city'] ?? '') . ' - ' . ($account['billing_pincode'] ?? '')) ?>">
                  <?= esc($account['account_name']) ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>

        <!-- Cash Customer (always visible) -->
        <div class="col-md-6" id="cashCustomerSection">
          <label class="form-label">Cash Customer <span class="text-danger">*</span></label>
          <div class="row g-2 mb-2">
            <div class="col-md-6">
              <label for="cashCustomerName" class="form-label small mb-1">Customer Name</label>
              <input
                id="cashCustomerName"
                class="form-control typeahead-customer-name"
                type="text"
                name="cash_customer_name"
                autocomplete="off"
                placeholder="Start typing name..." />
            </div>
            <div class="col-md-6">
              <label for="cashCustomerMobile" class="form-label small mb-1">Mobile Number</label>
              <input
                id="cashCustomerMobile"
                class="form-control typeahead-customer-mobile"
                type="text"
                name="cash_customer_mobile"
                autocomplete="off"
                placeholder="Start typing mobile..." />
            </div>
          </div>
          <input type="hidden" id="cashCustomerId" name="cash_customer_id">
          <small class="text-muted">Start typing to search. New customers will be created automatically.</small>
        </div>

      </div>
    </div>
  </div>

  <!-- Address Section (Hidden for Cash Customers) -->
  <div class="card mb-4" id="addressSection">
    <div class="card-header">
      <h5 class="card-title mb-0"><i class="bi bi-geo-alt"></i> Addresses</h5>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <!-- Billing Address -->
        <div class="col-md-6">
          <label for="billingAddress" class="form-label">Billing Address</label>
          <textarea class="form-control" id="billingAddress" name="billing_address" rows="3"></textarea>
        </div>

        <!-- Shipping Address -->
        <div class="col-md-6">
          <label for="shippingAddress" class="form-label">Shipping Address</label>
          <textarea class="form-control" id="shippingAddress" name="shipping_address" rows="3"></textarea>
          <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" id="sameAsBilling">
            <label class="form-check-label" for="sameAsBilling">
              Same as billing address
            </label>
          </div>
        </div>
      </div>
    </div>
  </div>


  <?= $this->section('vendorStyles') ?>
  <link rel="stylesheet" href="<?= base_url('admintheme/assets/vendor/libs/select2/select2.css') ?>">
  <link rel="stylesheet" href="<?= base_url('admintheme/assets/vendor/libs/typeahead-js/typeahead.css') ?>">
  <?= $this->endSection() ?>

  <?= $this->section('vendorScripts') ?>
  <script src="<?= base_url('admintheme/assets/vendor/libs/select2/select2.js') ?>"></script>
  <script src="<?= base_url('admintheme/assets/vendor/libs/typeahead-js/typeahead.js') ?>"></script>
  <script src="<?= base_url('admintheme/assets/vendor/libs/bloodhound/bloodhound.js') ?>"></script>
  <?= $this->endSection() ?>

  <!-- Line Items Section -->
  <div class="card mb-4" id="lineItemsCard">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0"><i class="ri-list-unordered"></i> Line Items</h5>
      <button type="button" class="btn btn-sm btn-primary" id="btn-add-line">
        <i class="ri-add-line"></i> Add Line
      </button>
    </div>
    <div class="card-body">
      <div id="linesBody">
        <!-- Line cards added dynamically -->
      </div>
      <div class="text-center py-4" id="noLinesAlert">
        <i class="ri-list-check ri-2x text-muted mb-2 d-block"></i>
        <p class="text-muted mb-0">Click <strong>Add Line</strong> to add invoice line items.</p>
      </div>
    </div>
  </div>

  <!-- Totals Section -->
  <div class="row justify-content-end mb-4">
    <div class="col-md-5 col-lg-4">
      <div class="card">
        <div class="card-body">
          <table class="table table-borderless table-sm mb-0">
            <tbody>
              <tr>
                <td class="text-end text-muted">Subtotal:</td>
                <td class="text-end fw-semibold" style="width: 120px;">
                  <span id="subtotalDisplay">₹0.00</span>
                </td>
              </tr>
              <tr>
                <td class="text-end text-muted">Tax (Inclusive):</td>
                <td class="text-end fw-semibold">
                  <span id="totalTaxDisplay">₹0.00</span>
                </td>
              </tr>
              <tr class="border-top">
                <td class="text-end fw-bold fs-5">Grand Total:</td>
                <td class="text-end fw-bold fs-5 text-primary">
                  <span id="grandTotalDisplay">₹0.00</span>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Hidden Inputs for Submission -->
          <input type="hidden" name="subtotal" id="subtotalInput" value="0">
          <input type="hidden" name="tax_amount" id="taxAmountInput" value="0">
          <input type="hidden" name="grand_total" id="grandTotalInput" value="0">

          <!-- Tax Breakdown Inputs (Hidden) -->
          <input type="hidden" id="taxType" value="">
          <input type="hidden" name="tax_rate" id="taxRateInput" value="<?= $default_tax_rate ?? 3.00 ?>">
          <input type="hidden" name="cgst_amount" id="cgstAmountInput" value="0">
          <input type="hidden" name="sgst_amount" id="sgstAmountInput" value="0">
          <input type="hidden" name="igst_amount" id="igstAmountInput" value="0">
          <input type="hidden" id="cgstRate" value="">
          <input type="hidden" id="sgstRate" value="">
          <input type="hidden" id="igstRate" value="">
          <!-- Hidden visual containers if needed later -->
          <div id="cgstSgstSection" style="display: none;"></div>
          <div id="igstSection" style="display: none;"></div>
        </div>
      </div>
    </div>
  </div>
  <!-- Terms & Conditions -->
  <div class="card mb-4">
    <div class="card-header">
      <h5 class="card-title mb-0"><i class="ri-file-text-line"></i> Terms & Conditions</h5>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Terms & Conditions</label>
          <textarea class="form-control" name="terms_conditions" rows="4"
            placeholder="Enter terms and conditions..."></textarea>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="4"
              placeholder="Additional notes or instructions..."></textarea>
          </div>
        </div>
      </div>
      <div class="row g-3">
        <div class="col-md-12">
          <div class="mb-3">
            <label for="referenceNumber" class="form-label">Reference Number</label>
            <input type="text" class="form-control" id="referenceNumber" name="reference_number"
              placeholder="PO Number, etc.">
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Form Actions -->
  <div class="d-flex justify-content-between mb-4">
    <a href="<?= base_url('invoices') ?>" class="btn btn-outline-secondary">
      <i class="ri-close-circle-line"></i> Cancel
    </a>
    <div>
      <button type="button" class="btn btn-outline-primary" id="saveAsDraftBtn">
        <i class="ri-save-line"></i> Save as Draft
      </button>
      <button type="submit" class="btn btn-primary" id="submitBtn">
        <i class="ri-check-circle-line"></i> Create Invoice
      </button>
    </div>
  </div>
</form>

<!-- Line Card Template -->
<template id="line-row-template">
  <div class="line-card mb-3" data-line-index="__INDEX__">
    <div class="line-card-header d-flex align-items-center justify-content-between">
      <span class="line-card-number fw-semibold">
        <i class="ri-circle-line me-1"></i> Line #__NUM__
      </span>
      <button type="button" class="btn btn-sm btn-text-danger btn-remove-line" title="Remove Line">
        <i class="ri-delete-bin-line"></i> Remove
      </button>
    </div>
    <div class="line-card-body">
      <!-- Row 1: Products + Processes -->
      <div class="row g-3 mb-3">
        <div class="col-12 col-md-6">
          <label class="form-label form-label-sm">Product(s)</label>
          <select class="form-select line-product" name="lines[__INDEX__][products][]" multiple>
            <?php if (isset($products)): foreach ($products as $product): ?>
                <option value="<?= $product['id'] ?>" data-name="<?= esc($product['product_name']) ?>">
                  <?= esc($product['product_name']) ?>
                </option>
            <?php endforeach;
            endif; ?>
          </select>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label form-label-sm">Process(es)</label>
          <select class="form-select line-process" name="lines[__INDEX__][processes][]" multiple>
            <?php if (isset($processes)): foreach ($processes as $process): ?>
                <option value="<?= $process['id'] ?>"
                  data-rate="<?= $process['rate_per_unit'] ?? 0 ?>"
                  data-name="<?= esc($process['process_name']) ?>">
                  <?= esc($process['process_name']) ?> (₹<?= number_format($process['rate_per_unit'] ?? 0, 2) ?>)
                </option>
            <?php endforeach;
            endif; ?>
          </select>
        </div>
      </div>
      <!-- Row 2: Qty + Weight + Rate + Amount -->
      <div class="row g-3 align-items-end">
        <div class="col-6 col-md-2">
          <label class="form-label form-label-sm">Qty</label>
          <input type="number" class="form-control line-qty"
            name="lines[__INDEX__][quantity]" value="1" min="1" step="1">
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label form-label-sm">Weight (g)</label>
          <input type="number" class="form-control line-weight"
            name="lines[__INDEX__][weight]" value="0.000" min="0" step="0.001">
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label form-label-sm">Rate (₹)</label>
          <input type="number" class="form-control line-rate"
            name="lines[__INDEX__][rate]" value="0.00" min="0" step="0.01">
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label form-label-sm">Amount (₹)</label>
          <input type="text" class="form-control line-amount fw-bold text-end"
            name="lines[__INDEX__][amount]" value="0.00" readonly tabindex="-1">
        </div>
        <!-- Image Upload -->
        <div class="col-12 col-md-3 mt-2">
          <label class="form-label form-label-sm">Image (Optional)</label>
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <input type="file" class="line-image-input d-none" name="line_images[__INDEX__]" accept="image/*">
            <input type="hidden" class="line-existing-image" name="lines[__INDEX__][existing_image]" value="">
            <div class="line-image-preview" style="display:none;">
              <img src="" alt="Preview" class="img-thumbnail" style="max-height:60px; max-width:80px; cursor:pointer;" data-full-src="">
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary btn-upload-image">
              <i class="ri-camera-line me-1"></i> Upload Image
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-image d-none">
              <i class="ri-close-line"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
  // Debounce function
  function debounce(func, wait) {
    let timeout;
    return function() {
      const context = this,
        args = arguments;
      clearTimeout(timeout);
      timeout = setTimeout(() => func.apply(context, args), wait);
    };
  }

  document.addEventListener('DOMContentLoaded', function() {
    // Ensure jQuery is loaded
    if (typeof jQuery === 'undefined') {
      console.error('jQuery is not loaded!');
      return;
    }

    const $ = jQuery;

    // Global variables
    let lineIndex = 0;
    const companyStateId = <?= session()->get('company_state_id') ?? 'null' ?>;
    const defaultTaxRate = <?= $default_tax_rate ?? 3.00 ?>;
    const prefilledLines = <?= isset($prefilled_lines) ? json_encode($prefilled_lines) : '[]' ?>;
    const selectedAccountId = <?= isset($selected_account_id) ? $selected_account_id : 'null' ?>;

    // Initialize
    initializeForm();

    // Event Listeners
    setupEventListeners();

    // Add lines (Prefilled or Default)
    if (prefilledLines.length > 0) {
      prefilledLines.forEach(line => addLine(line));

      // Trigger calculations after adding all lines
      // We select the first line's process to trigger change?
      // Or just re-calculate totals.
      calculateTotals();

    } else {
      // Add first line item
      addLine();
    }

    if (selectedAccountId) {
      $('#accountId').val(selectedAccountId).trigger('change');
      // If note is prefilled
      <?php if (isset($prefilled_notes)): ?>
        $('textarea[name="notes"]').val(<?= json_encode($prefilled_notes) ?>);
      <?php endif; ?>
    }

    // =========================================================================
    // FUNCTIONS
    // =========================================================================

    function initializeForm() {
      // Set default date to today
      $('#invoiceDate').val(new Date().toISOString().split('T')[0]);

      // Hide no lines alert initially
      $('#noLinesAlert').hide();

      // Always Cash Customer - initialize accordingly
      toggleCustomerType('Cash');
    }

    function setupEventListeners() {
      // Customer type toggle
      $('input[name="customer_type"]').on('change', function() {
        toggleCustomerType($(this).val());
      });

      // Customer selection
      $('#accountId').on('change', function() {
        onCustomerChange();
      });

      // Cash Customer Typeahead is initialized after DOMContentLoaded setup
      initCustomerTypeahead();

      // Add line item
      $('#btn-add-line').on('click', function() {
        addLine();
      });

      // Remove Line
      $(document).on('click', '.btn-remove-line', function() {
        var $row = $(this).closest('.line-card');
        // Destroy Select2 before removing
        $row.find('.line-product, .line-process').select2('destroy');
        $row.remove();
        renumberLines();
        calculateTotals();

        if ($('#linesBody .line-card').length === 0) {
          $('#noLinesAlert').show();
        }
      });

      // Calculation Events
      $(document).on('change', '.line-process', function() {
        calculateLineAmount($(this).closest('.line-card'));
      });

      $(document).on('input change', '.line-qty, .line-rate, .line-weight', function() {
        calculateLineAmount($(this).closest('.line-card'));
      });

      // Form submission
      // Form submission
      $('#invoiceForm').on('submit', function(e) {
        e.preventDefault();
        submitInvoice();
      });

      // Invoice Type Change -> Auto Customer Type
      $('#invoiceType').on('change', function() {
        const type = $(this).val();
        if (type === 'Cash Invoice') {
          $('input[name="customer_type"][value="Cash"]').prop('checked', true).trigger('change');
        } else if (type === 'Accounts Invoice' || type === 'Wax Invoice') {
          $('input[name="customer_type"][value="Account"]').prop('checked', true).trigger('change');
        }
      });
    }

    // =========================================================================
    // LINE ITEMS Logic
    // =========================================================================

    function addLine(data = null) {
      var template = document.getElementById('line-row-template').innerHTML;
      var lineNum = lineIndex + 1;

      var html = template
        .replace(/__INDEX__/g, lineIndex)
        .replace(/__NUM__/g, lineNum);

      var $row = $(html);
      $('#linesBody').append($row);

      // Initialize Select2
      var $productSelect = $row.find('.line-product');
      $productSelect.select2({
        width: '100%',
        placeholder: 'Select products',
        allowClear: true
      });

      var $processSelect = $row.find('.line-process');
      $processSelect.select2({
        width: '100%',
        placeholder: 'Select processes',
        allowClear: true
      });

      // Pre-fill data if available
      if (data) {
        if (data.products && Array.isArray(data.products)) {
          $productSelect.val(data.products).trigger('change');
        }
        if (data.processes && Array.isArray(data.processes)) {
          $processSelect.val(data.processes).trigger('change');
        }

        // Force values again in case trigger('change') recalculated them
        if (data.quantity) $row.find('.line-qty').val(data.quantity);
        if (data.weight) $row.find('.line-weight').val(data.weight);
        if (data.rate) $row.find('.line-rate').val(data.rate);
        if (data.amount) $row.find('.line-amount').val(data.amount);
      }

      lineIndex++;
      $('#noLinesAlert').hide();
      renumberLines();
    }

    function renumberLines() {
      $('#linesBody .line-card').each(function(i) {
        $(this).find('.line-card-number').html('<i class="ri-circle-line me-1"></i> Line #' + (i + 1));
      });
    }

    function calculateLineAmount($row) {
      var quantity = parseInt($row.find('.line-qty').val()) || 1;
      var weight = parseFloat($row.find('.line-weight').val()) || 0;
      var $processSelect = $row.find('.line-process');
      var selectedProcesses = $processSelect.val() || [];

      // Sum rates from selected processes
      var totalRate = 0;
      selectedProcesses.forEach(function(processId) {
        var $option = $processSelect.find('option[value="' + processId + '"]');
        var rate = parseFloat($option.data('rate')) || 0;
        totalRate += rate;
      });

      // If no processes selected, check manual rate
      var manualRate = parseFloat($row.find('.line-rate').val()) || 0;

      // If processes selected, override rate. Else keep manual rate.
      // Note: We update the rate field if processes are selected.
      if (selectedProcesses.length > 0) {
        $row.find('.line-rate').val(totalRate.toFixed(2));
      } else {
        // If we allow manual entry when no process is selected, we use the value in the input
        totalRate = manualRate;
      }

      // Amount calculation
      var amount = 0;
      if (weight > 0) {
        amount = weight * totalRate;
      } else {
        amount = quantity * totalRate;
      }
      $row.find('.line-amount').val(amount.toFixed(2));

      calculateTotals();
    }

    function calculateTotals() {
      let lineTotal = 0;

      $('#linesBody .line-card').each(function() {
        var amount = parseFloat($(this).find('.line-amount').val()) || 0;
        lineTotal += amount;
      });

      // Tax-inclusive calculation:
      // Line amounts already include tax, so grand total = sum of line amounts
      // Tax is back-calculated (extracted) from the total
      const taxRate = parseFloat($('#taxRateInput').val()) || 0;
      const taxAmount = lineTotal * taxRate / (100 + taxRate);
      const subtotal = lineTotal - taxAmount; // taxable amount (excl. tax)
      const grandTotal = lineTotal; // grand total = sum of line amounts (tax already included)

      // Update Displays
      $('#subtotalDisplay').text('₹' + subtotal.toFixed(2));
      $('#totalTaxDisplay').text('₹' + taxAmount.toFixed(2));
      $('#grandTotalDisplay').text('₹' + grandTotal.toFixed(2));

      // Update Inputs
      $('#subtotalInput').val(subtotal.toFixed(2));
      $('#taxAmountInput').val(taxAmount.toFixed(2));
      $('#grandTotalInput').val(grandTotal.toFixed(2));

      updateTaxBreakdown(taxAmount);
    }

    // ... (rest of helper functions like toggleCustomerType, onCustomerChange, determineTaxType, etc.)
    // We need to keep them or re-declare them.
    // Since I'm creating a new script block, I need to include all necessary functions.

    function toggleCustomerType(type) {
      if (type === 'Account') {
        $('#accountCustomerSection').show();
        $('#cashCustomerSection').hide();
        $('#addressSection').show(); // Show address for Account
        $('#accountId').prop('required', true).prop('disabled', false);
        $('#cashCustomerId').prop('required', false).prop('disabled', true).val('');
        $('#cashCustomerName').prop('disabled', true);
        $('#cashCustomerMobile').prop('disabled', true);
      } else if (type === 'Cash') {
        $('#accountCustomerSection').hide();
        $('#cashCustomerSection').show();
        $('#addressSection').hide(); // Hide address for Cash
        $('#accountId').prop('required', false).prop('disabled', true).val('');
        $('#cashCustomerId').prop('required', true).prop('disabled', false);
        $('#cashCustomerName').prop('disabled', false);
        $('#cashCustomerMobile').prop('disabled', false);
      }
      $('#billingAddress').val('');
      if (type === 'Account') {
        $('#cashCustomerName').val('');
        $('#cashCustomerMobile').val('');
        $('#cashCustomerId').val('');
      }
      calculateTotals();
    }

    function onCustomerChange() {
      const customerType = $('input[name="customer_type"]:checked').val();
      let selectedOption;
      if (customerType === 'Account') {
        selectedOption = $('#accountId option:selected');
        const address = selectedOption.data('address');
        if (address) $('#billingAddress').val(address);
      }
      determineTaxType();
      calculateTotals();
    }

    function determineTaxType() {
      // ... (Logic from previous step)
      const customerType = $('input[name="customer_type"]:checked ').val();
      let customerStateId;
      // ... implementation same as before ...
      // Re-implementing simplified for brevity in this replace block, but logic stands
      if (customerType === 'Account') {
        customerStateId = $('#accountId option:selected').data('state-id');
      } else {
        // Cash logic
        customerStateId = companyStateId;
      }

      if (!customerStateId || !companyStateId) {
        $('#taxType').val('(Select customer to determine)');
        return;
      }

      if (parseInt(customerStateId) === parseInt(companyStateId)) {
        $('#taxType').val('CGST + SGST (Intra-state)');
        $('#cgstSgstSection').show();
        $('#igstSection').hide();
        const halfRate = (defaultTaxRate / 2).toFixed(2);
        $('#cgstRate').val(halfRate + '%');
        $('#sgstRate').val(halfRate + '%');
      } else {
        $('#taxType').val('IGST (Inter-state)');
        $('#cgstSgstSection').hide();
        $('#igstSection').show();
        $('#igstRate').val(defaultTaxRate.toFixed(2) + '%');
      }
    }

    function updateTaxBreakdown(totalTax) {
      const taxType = $('#taxType').val();
      if (taxType.includes('CGST')) {
        const halfTax = totalTax / 2;
        $('#cgstAmount').val('₹' + halfTax.toFixed(2));
        $('#sgstAmount').val('₹' + halfTax.toFixed(2));
        $('#cgstAmountInput').val(halfTax.toFixed(2));
        $('#sgstAmountInput').val(halfTax.toFixed(2));
        $('#igstAmountInput').val('0.00');
      } else if (taxType.includes('IGST')) {
        $('#igstAmount').val('₹' + totalTax.toFixed(2));
        $('#igstAmountInput').val(totalTax.toFixed(2));
        $('#cgstAmountInput').val('0.00');
        $('#sgstAmountInput').val('0.00');
      }
    }

    function initCustomerTypeahead() {
      // Bloodhound remote source for customer search
      var customerBloodhound = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('customer_name', 'mobile_number'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        identify: function(obj) {
          return String(obj.id);
        },
        sufficient: 1,
        remote: {
          url: '<?= base_url("customers/cash-customers/search") ?>?q=%QUERY',
          wildcard: '%QUERY'
        }
      });

      // Shared suggestion template
      var suggestionTemplate = function(data) {
        return '<div><strong>' + data.customer_name + '</strong> &ndash; <span class="text-muted">' + (data.mobile_number || data.mobile || '') + '</span></div>';
      };

      // Typeahead on Customer Name field
      $('#cashCustomerName').typeahead({
        hint: true,
        highlight: true,
        minLength: 2
      }, {
        name: 'customers-by-name',
        display: 'customer_name',
        source: customerBloodhound,
        limit: 20, // Show up to 20 suggestions
        templates: {
          empty: '<div class="p-2 text-muted">No customers found</div>',
          suggestion: suggestionTemplate
        }
      }).on('typeahead:select', function(e, customer) {
        $('#cashCustomerMobile').typeahead('val', customer.mobile_number || customer.mobile || '');
        $('#cashCustomerId').val(customer.id);
      });

      // Typeahead on Mobile Number field
      $('#cashCustomerMobile').typeahead({
        hint: true,
        highlight: true,
        minLength: 2
      }, {
        name: 'customers-by-mobile',
        display: function(data) {
          return data.mobile_number || data.mobile || '';
        },
        source: customerBloodhound,
        limit: 20, // Show up to 20 suggestions
        templates: {
          empty: '<div class="p-2 text-muted">No customers found</div>',
          suggestion: suggestionTemplate
        }
      }).on('typeahead:select', function(e, customer) {
        $('#cashCustomerName').typeahead('val', customer.customer_name);
        $('#cashCustomerId').val(customer.id);
      });

      // Clear cash_customer_id when user manually edits either field
      $('#cashCustomerName, #cashCustomerMobile').on('input', function() {
        $('#cashCustomerId').val('');
      });
    }

    function findOrCreateCashCustomer() {
      return new Promise((resolve, reject) => {
        const name = $('#cashCustomerName').val();
        const mobile = $('#cashCustomerMobile').val();
        $.ajax({
          url: '<?= base_url("customers/cash-customers/find-or-create") ?>',
          type: 'POST',
          data: {
            customer_name: name,
            mobile_number: mobile,
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
          },
          success: function(response) {
            if (response.success) {
              $('#cashCustomerId').val(response.customer_id);
              resolve(true);
            } else {
              alert('Error: ' + response.message);
              resolve(false);
            }
          },
          error: function(xhr) {
            resolve(false);
          }
        });
      });
    }

    function submitInvoice() {
      // Validate
      const customerType = $('input[name="customer_type"]:checked').val();
      if (customerType === 'Cash' && (!$('#cashCustomerName').val() || !$('#cashCustomerMobile').val())) {
        alert('Please enter cash customer details');
        return;
      }
      if ($('#linesBody .line-card').length === 0) {
        alert('Add at least one line item');
        return;
      }
      // Button loading state
      $('#submitBtn').prop('disabled', true).html('Creating...');

      if (customerType === 'Cash') {
        findOrCreateCashCustomer().then(success => {
          if (success) submitData();
          else $('#submitBtn').prop('disabled', false).html('Create Invoice');
        });
      } else {
        submitData();
      }
    }

    function submitData() {
      const form = document.getElementById('invoiceForm');
      const formData = new FormData(form);
      $.ajax({
        url: form.action,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
          if (response.success) {
            window.location.href = response.redirect;
          } else {
            alert(response.error || 'Error');
            $('#submitBtn').prop('disabled', false).html('Create Invoice');
          }
        },
        error: function(xhr) {
          var msg = 'Failed to submit';
          if (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message)) {
            msg = xhr.responseJSON.error || xhr.responseJSON.message;
          }
          alert(msg);
          $('#submitBtn').prop('disabled', false).html('Create Invoice');
        }
      }); // End ajax submit
    } // End submitData

    // =========================================================================
    // LINE IMAGE HANDLERS
    // =========================================================================
    $(document).on('click', '.btn-upload-image', function() {
      $(this).closest('.line-card-body').find('.line-image-input').trigger('click');
    });

    $(document).on('change', '.line-image-input', function() {
      const file = this.files[0];
      if (!file) return;
      const $card = $(this).closest('.line-card-body');
      const reader = new FileReader();
      reader.onload = function(e) {
        const $preview = $card.find('.line-image-preview');
        $preview.find('img').attr('src', e.target.result).attr('data-full-src', e.target.result);
        $preview.show();
        $card.find('.btn-remove-image').removeClass('d-none');
        $card.find('.btn-upload-image').html('<i class="ri-camera-line me-1"></i> Change');
      };
      reader.readAsDataURL(file);
    });

    $(document).on('click', '.btn-remove-image', function() {
      const $card = $(this).closest('.line-card-body');
      $card.find('.line-image-input').val('');
      $card.find('.line-existing-image').val('');
      $card.find('.line-image-preview').hide().find('img').attr('src', '').attr('data-full-src', '');
      $(this).addClass('d-none');
      $card.find('.btn-upload-image').html('<i class="ri-camera-line me-1"></i> Upload Image');
    });

    $(document).on('click', '.line-image-preview img', function() {
      const fullSrc = $(this).attr('data-full-src') || $(this).attr('src');
      if (!fullSrc) return;
      $('#lineImageModalImg').attr('src', fullSrc);
      var modal = new bootstrap.Modal(document.getElementById('lineImageModal'));
      modal.show();
    });

  }); // End DOMContentLoaded
</script>

<!-- Image Modal -->
<div class="modal fade" id="lineImageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Line Image</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <img id="lineImageModalImg" src="" alt="Line Image" class="img-fluid rounded">
      </div>
    </div>
  </div>
</div>

<style>
  /* ===== LINE CARD LAYOUT ===== */
  .line-card {
    border: 1px solid var(--bs-border-color);
    border-radius: 0.5rem;
    overflow: hidden;
    transition: box-shadow 0.2s ease;
  }

  .line-card:hover {
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  }

  .line-card-header {
    background: var(--bs-light);
    padding: 0.6rem 1rem;
    border-bottom: 1px solid var(--bs-border-color);
  }

  .line-card-number {
    font-size: 0.875rem;
    color: var(--bs-primary);
  }

  .line-card-body {
    padding: 1rem;
    background: #fff;
  }

  /* Larger form controls for tablet touch */
  .line-card .form-control,
  .line-card .form-select {
    font-size: 1rem;
    min-height: 44px;
    padding: 0.5rem 0.75rem;
  }

  /* Select2 inside cards */
  .line-card .select2-container {
    width: 100% !important;
  }

  .line-card .select2-container--bootstrap-5 .select2-selection,
  .line-card .select2-container--default .select2-selection--multiple {
    min-height: 44px;
    font-size: 1rem;
    padding: 0.5rem 0.75rem;
  }

  /* Amount field highlighted */
  .line-amount {
    background-color: var(--bs-primary-bg-subtle) !important;
    color: var(--bs-primary);
    font-size: 1.05rem !important;
  }

  /* Remove button */
  .btn-text-danger {
    color: var(--bs-danger);
    background: transparent;
    border: none;
    font-size: 0.8125rem;
  }

  .btn-text-danger:hover {
    background-color: rgba(var(--bs-danger-rgb), 0.1);
    border-radius: 0.25rem;
  }
</style>
<?= $this->endSection() ?>


```
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Create Invoice<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$baseRoute = 'invoices';
$baseTitle = 'Invoices';

if ($invoice['invoice_type'] === 'Accounts Invoice') {
  $baseRoute = 'account-invoices';
  $baseTitle = 'Account Invoices';
} elseif ($invoice['invoice_type'] === 'Cash Invoice') {
  $baseRoute = 'cash-invoices';
  $baseTitle = 'Cash Invoices';
} elseif ($invoice['invoice_type'] === 'Wax Invoice') {
  $baseRoute = 'wax-invoices';
  $baseTitle = 'Wax Invoices';
}
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Home</a></li>
    <li class="breadcrumb-item"><a href="<?= base_url($baseRoute) ?>"><?= $baseTitle ?></a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit #<?= esc($invoice['invoice_number']) ?></li>
  </ol>
</nav>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h3 mb-0">Edit Invoice #<?= esc($invoice['invoice_number']) ?></h1>
  <a href="<?= base_url($baseRoute) ?>" class="btn btn-outline-secondary">
    <i class="ri-arrow-left-line"></i> Back to <?= $baseTitle ?>
  </a>
</div>

<!-- Invoice Form -->
<form id="invoiceForm" method="POST" action="<?= base_url("{$baseRoute}/{$invoice['id']}") ?>">
  <?= csrf_field() ?>

  <!-- Invoice Header Section -->
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
            value="<?= esc($invoice['invoice_number']) ?>" readonly>
        </div>

        <!-- Invoice Date -->
        <div class="col-md-3">
          <label for="invoiceDate" class="form-label">Invoice Date <span class="text-danger">*</span></label>
          <input type="date" class="form-control" id="invoiceDate" name="invoice_date"
            value="<?= esc($invoice['invoice_date']) ?>" required>
        </div>

        <!-- Invoice Type -->
        <div class="col-md-3">
          <label for="invoiceType" class="form-label">Invoice Type <span class="text-danger">*</span></label>
          <select class="form-select" id="invoiceType" name="invoice_type" required>
            <option value="">Select Type</option>
            <option value="Accounts Invoice" <?= ($invoice['invoice_type'] === 'Accounts Invoice') ? 'selected' : '' ?>>Accounts Invoice</option>
            <option value="Cash Invoice" <?= ($invoice['invoice_type'] === 'Cash Invoice') ? 'selected' : '' ?>>Cash Invoice</option>
            <option value="Wax Invoice" <?= ($invoice['invoice_type'] === 'Wax Invoice') ? 'selected' : '' ?>>Wax Invoice</option>
          </select>
        </div>

        <!-- Due Date (Optional, for Account invoices) -->
        <div class="col-md-3">
          <label for="dueDate" class="form-label">Due Date</label>
          <input type="date" class="form-control" id="dueDate" name="due_date" value="<?= esc($invoice['due_date'] ?? '') ?>">
        </div>

        <!-- Customer Type (Radio) -->
        <?php $isAccount = !empty($invoice['account_id']); ?>
        <div class="col-md-12">
          <label class="form-label">Customer Type <span class="text-danger">*</span></label>
          <div class="btn-group w-100" role="group">
            <input type="radio" class="btn-check" name="customer_type" id="customerTypeAccount"
              value="Account" autocomplete="off" <?= $isAccount ? 'checked' : '' ?>>
            <label class="btn btn-outline-primary" for="customerTypeAccount">
              <i class="ri-building-line"></i> Account Customer
            </label>

            <input type="radio" class="btn-check" name="customer_type" id="customerTypeCash"
              value="Cash" autocomplete="off" <?= !$isAccount ? 'checked' : '' ?>>
            <label class="btn btn-outline-success" for="customerTypeCash">
              <i class="ri-bank-card-line"></i> Cash Customer
            </label>
          </div>
        </div>

        <!-- Account Customer (shown when Account selected) -->
        <div class="col-md-6" id="accountCustomerSection" style="display: none;">
          <label for="accountId" class="form-label">Account Customer <span class="text-danger">*</span></label>
          <select class="form-select" id="accountId" name="account_id">
            <option value="">Select Account Customer</option>
            <?php if (isset($accounts)): ?>
              <?php foreach ($accounts as $account): ?>
                <option value="<?= $account['id'] ?>"
                  data-state-id="<?= $account['billing_state_id'] ?? '' ?>"
                  data-address="<?= esc($account['billing_address'] ?? '') ?>"
                  <?= (isset($invoice['account_id']) && $invoice['account_id'] == $account['id']) ? 'selected' : '' ?>>
                  <?= esc($account['account_name']) ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>

        <!-- Cash Customer (shown when Cash selected) -->
        <div class="col-md-12 row mt-4" id="cashCustomerSection" style="display: none;">
          <div class="col-md-6">
            <input type="hidden" id="cashCustomerId" name="cash_customer_id" value="<?= esc($invoice['cash_customer_id'] ?? '') ?>">
            <div class="mb-2">
              <label for="cashCustomerName" class="form-label">Cash Customer Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="cashCustomerName" name="cash_customer_name"
                value="<?= esc($invoice['customer']['customer_name'] ?? '') ?>" placeholder="Search or Type Name">
              <div id="customerSearchResults" class="list-group position-absolute w-100" style="z-index: 1000; display: none;"></div>
            </div>
          </div>
          <div class="col-md-6">
            <div>
              <label for="cashCustomerMobile" class="form-label">Mobile Number <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="cashCustomerMobile" name="cash_customer_mobile"
                value="<?= esc($invoice['customer']['mobile'] ?? '') ?>" placeholder="Enter Mobile">
            </div>
          </div>
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
          <textarea class="form-control" id="billingAddress" name="billing_address" rows="3"><?= esc($invoice['billing_address'] ?? '') ?></textarea>
        </div>

        <!-- Shipping Address -->
        <div class="col-md-6">
          <label for="shippingAddress" class="form-label">Shipping Address</label>
          <textarea class="form-control" id="shippingAddress" name="shipping_address" rows="3"><?= esc($invoice['shipping_address'] ?? '') ?></textarea>
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
  <?= $this->endSection() ?>

  <?= $this->section('vendorScripts') ?>
  <script src="<?= base_url('admintheme/assets/vendor/libs/select2/select2.js') ?>"></script>
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
                  <span id="subtotalDisplay">₹<?= number_format($invoice['subtotal'] ?? 0, 2) ?></span>
                </td>
              </tr>
              <tr>
                <td class="text-end text-muted">Tax (Inclusive):</td>
                <td class="text-end fw-semibold">
                  <span id="totalTaxDisplay">₹<?= number_format($invoice['tax_amount'] ?? 0, 2) ?></span>
                </td>
              </tr>
              <tr class="border-top">
                <td class="text-end fw-bold fs-5">Grand Total:</td>
                <td class="text-end fw-bold fs-5 text-primary">
                  <span id="grandTotalDisplay">₹<?= number_format($invoice['grand_total'] ?? 0, 2) ?></span>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Hidden Inputs for Submission -->
          <input type="hidden" name="subtotal" id="subtotalInput" value="<?= esc($invoice['subtotal'] ?? 0) ?>">
          <input type="hidden" name="tax_amount" id="taxAmountInput" value="<?= esc($invoice['tax_amount'] ?? 0) ?>">
          <input type="hidden" name="grand_total" id="grandTotalInput" value="<?= esc($invoice['grand_total'] ?? 0) ?>">

          <!-- Tax Breakdown Inputs (Hidden) -->
          <input type="hidden" id="taxType" value="">
          <input type="hidden" name="tax_rate" id="taxRateInput" value="<?= $invoice['tax_rate'] ?? $default_tax_rate ?? 3.00 ?>">
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
            placeholder="Enter terms and conditions..."><?= esc($invoice['terms_conditions'] ?? '') ?></textarea>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="4"
              placeholder="Additional notes or instructions..."><?= esc($invoice['notes'] ?? '') ?></textarea>
          </div>
        </div>
      </div>
      <div class="row g-3">
        <div class="col-md-12">
          <div class="mb-3">
            <label for="referenceNumber" class="form-label">Reference Number</label>
            <input type="text" class="form-control" id="referenceNumber" name="reference_number"
              value="<?= esc($invoice['reference_number'] ?? '') ?>" placeholder="PO Number, etc.">
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Form Actions -->
  <div class="d-flex justify-content-between mb-4">
    <a href="<?= base_url($baseRoute) ?>" class="btn btn-outline-secondary">
      <i class="ri-close-circle-line"></i> Cancel
    </a>
    <div>
      <button type="button" class="btn btn-outline-primary" id="saveAsDraftBtn">
        <i class="ri-save-line"></i> Save as Draft
      </button>
      <button type="submit" class="btn btn-primary" id="submitBtn">
        <i class="ri-check-circle-line"></i> Update Invoice
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
      <!-- Row 2: Qty + Weight + Gold Weight + Purity + Rate + Amount -->
      <div class="row g-3 mb-3">
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
          <label class="form-label form-label-sm">Gold Wt (g)</label>
          <input type="number" class="form-control line-gold-weight"
            name="lines[__INDEX__][gold_weight]" value="" min="0" step="0.001" placeholder="0.000">
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label form-label-sm">Purity</label>
          <select class="form-select line-purity" name="lines[__INDEX__][gold_purity]">
            <option value="">--</option>
            <option value="24K">24K</option>
            <option value="22K">22K</option>
            <option value="18K">18K</option>
            <option value="14K">14K</option>
          </select>
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label form-label-sm">Rate (₹)</label>
          <input type="number" class="form-control line-rate"
            name="lines[__INDEX__][rate]" value="0.00" min="0" step="0.01">
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label form-label-sm">Amount (₹)</label>
          <input type="text" class="form-control line-amount fw-bold text-end"
            name="lines[__INDEX__][amount]" value="0.00" readonly tabindex="-1">
          <input type="hidden" class="line-current-gold-price" name="lines[__INDEX__][current_gold_price]" value="">
          <input type="hidden" class="line-adjusted-gold-weight" name="lines[__INDEX__][adjusted_gold_weight]" value="">
          <input type="hidden" class="line-gold-adjustment-amount" name="lines[__INDEX__][gold_adjustment_amount]" value="">
        </div>
      </div>
      <!-- Gold Adjustment Info (shown dynamically) -->
      <div class="gold-adjustment-info-container d-none">
        <div class="alert alert-info py-2 mb-0 small gold-adjustment-info-text"></div>
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
    let isInitialLoad = true;
    const companyStateId = <?= session()->get('company_state_id') ?? 'null' ?>;
    const defaultTaxRate = <?= $default_tax_rate ?? 3.00 ?>;
    const goldRates = <?= json_encode($gold_rates ?? []) ?>;

    // Initialize
    initializeForm();

    // Event Listeners
    setupEventListeners();

    // Add first line item removed - handled in initializeForm

    // =========================================================================
    // FUNCTIONS
    // =========================================================================

    function initializeForm() {
      // Set default date check (if value exists, don't overwrite)
      if (!$('#invoiceDate').val()) {
        $('#invoiceDate').val(new Date().toISOString().split('T')[0]);
      }

      // Hide no lines alert initially
      $('#noLinesAlert').hide();

      // Trigger customer type toggle if selected
      const customerType = $('input[name="customer_type"]:checked').val();
      if (customerType) {
        toggleCustomerType(customerType, false);
      }

      // Load Existing Lines
      const existingLines = <?= json_encode($invoice['lines'] ?? []) ?>;
      if (existingLines.length > 0) {
        existingLines.forEach(line => {
          addLine();
          const $row = $('#linesBody .line-card:last');

          $row.find('.line-qty').val(line.quantity);
          $row.find('.line-weight').val(parseFloat(line.weight || 0).toFixed(3));
          $row.find('.line-rate').val(line.rate);
          $row.find('.line-amount').val(line.amount);

          // Gold fields
          if (line.gold_weight) $row.find('.line-gold-weight').val(parseFloat(line.gold_weight).toFixed(3));
          if (line.gold_purity) $row.find('.line-purity').val(line.gold_purity);
          if (line.current_gold_price) $row.find('.line-current-gold-price').val(line.current_gold_price);
          if (line.adjusted_gold_weight) $row.find('.line-adjusted-gold-weight').val(line.adjusted_gold_weight);
          if (line.gold_adjustment_amount) $row.find('.line-gold-adjustment-amount').val(line.gold_adjustment_amount);

          // Products
          if (line.product_ids) {
            let pIds = line.product_ids;
            if (typeof pIds === 'string') {
              try {
                pIds = JSON.parse(pIds);
              } catch (e) {
                pIds = [pIds];
              }
            }
            $row.find('.line-product').val(pIds).trigger('change');
          }

          // Processes
          if (line.process_ids) {
            let procIds = line.process_ids;
            if (typeof procIds === 'string') {
              try {
                procIds = JSON.parse(procIds);
              } catch (e) {
                procIds = [procIds];
              }
            }
            $row.find('.line-process').val(procIds).trigger('change');
          }
        });
        calculateTotals();
      } else {
        // Add one empty line if none exist
        addLine();
      }

      isInitialLoad = false;
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

      // Cash Customer Autocomplete
      $('#cashCustomerName').on('input', debounce(function() {
        searchCashCustomer($(this).val());
      }, 300));

      // Hide results on click outside
      $(document).on('click', function(e) {
        if (!$(e.target).closest('#cashCustomerSection').length) {
          $('#customerSearchResults').hide();
        }
      });

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

      $(document).on('input change', '.line-qty, .line-rate, .line-weight, .line-gold-weight, .line-purity', function() {
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

    function addLine() {
      var template = document.getElementById('line-row-template').innerHTML;
      var lineNum = lineIndex + 1;

      var html = template
        .replace(/__INDEX__/g, lineIndex)
        .replace(/__NUM__/g, lineNum);

      var $row = $(html);
      $('#linesBody').append($row);

      // Initialize Select2
      $row.find('.line-product').select2({
        width: '100%',
        placeholder: 'Select products',
        allowClear: true
      });

      $row.find('.line-process').select2({
        width: '100%',
        placeholder: 'Select processes',
        allowClear: true
      });

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
      if (typeof isInitialLoad !== 'undefined' && isInitialLoad) return;

      var quantity = parseInt($row.find('.line-qty').val()) || 1;
      var weight = parseFloat($row.find('.line-weight').val()) || 0;
      var goldWeight = parseFloat($row.find('.line-gold-weight').val()) || 0;
      var purity = $row.find('.line-purity').val() || '';
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

      if (selectedProcesses.length > 0) {
        $row.find('.line-rate').val(totalRate.toFixed(2));
      } else {
        totalRate = manualRate;
      }

      // Base Amount = weight × rate (if weight > 0), otherwise qty × rate
      var baseAmount = 0;
      if (weight > 0) {
        baseAmount = weight * totalRate;
      } else {
        baseAmount = quantity * totalRate;
      }

      // Gold adjustment calculation
      var currentGoldPrice = 0;
      var adjustedGoldWeight = 0;
      var goldAdjustmentAmount = 0;

      if (goldWeight > 0 && purity && goldRates[purity]) {
        currentGoldPrice = parseFloat(goldRates[purity]);
        adjustedGoldWeight = goldWeight - weight; // gold difference
        goldAdjustmentAmount = adjustedGoldWeight * currentGoldPrice;
      }

      var finalAmount = baseAmount + goldAdjustmentAmount;

      // Store gold adjustment values in hidden fields
      $row.find('.line-current-gold-price').val(currentGoldPrice.toFixed(2));
      $row.find('.line-adjusted-gold-weight').val(adjustedGoldWeight.toFixed(3));
      $row.find('.line-gold-adjustment-amount').val(goldAdjustmentAmount.toFixed(2));

      $row.find('.line-amount').val(finalAmount.toFixed(2));

      // Show/hide gold adjustment info inside the card
      var $infoContainer = $row.find('.gold-adjustment-info-container');
      var $infoText = $row.find('.gold-adjustment-info-text');
      if (goldAdjustmentAmount !== 0) {
        var sign = goldAdjustmentAmount > 0 ? '+' : '';
        var infoHtml =
          '<i class="ri-information-line me-1"></i>' +
          'Gold Adj: Wt Diff = <strong>' + adjustedGoldWeight.toFixed(3) + 'g</strong>' +
          ' &times; Rate &#8377;' + currentGoldPrice.toLocaleString('en-IN', {
            minimumFractionDigits: 2
          }) +
          ' = <strong class="' + (goldAdjustmentAmount > 0 ? 'text-success' : 'text-danger') + '">' +
          sign + '&#8377;' + goldAdjustmentAmount.toLocaleString('en-IN', {
            minimumFractionDigits: 2
          }) +
          '</strong>' +
          ' | Base &#8377;' + baseAmount.toLocaleString('en-IN', {
            minimumFractionDigits: 2
          }) +
          ' &rarr; Final &#8377;' + finalAmount.toLocaleString('en-IN', {
            minimumFractionDigits: 2
          });
        $infoText.html(infoHtml);
        $infoContainer.removeClass('d-none');
      } else {
        $infoContainer.addClass('d-none');
        $infoText.html('');
      }

      calculateTotals();
    }

    function calculateTotals() {
      if (typeof isInitialLoad !== 'undefined' && isInitialLoad) return;

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

    function toggleCustomerType(type, clearAddress = true) {
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

      if (clearAddress) {
        $('#billingAddress').val('');
        if (type === 'Account') {
          $('#cashCustomerName').val('');
          $('#cashCustomerMobile').val('');
          $('#cashCustomerId').val('');
        }
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

    function searchCashCustomer(query) {
      // ... (Same as before)
      if (query.length < 2) {
        $('#customerSearchResults').hide();
        return;
      }
      $.ajax({
        url: '<?= base_url("customers/cash-customers/search") ?>',
        data: {
          q: query
        },
        success: function(data) {
          const results = $('#customerSearchResults');
          results.empty();
          if (data.length > 0) {
            data.forEach(customer => {
              const item = $(`<a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                          <h6 class="mb-1">${customer.customer_name}</h6>
                          <small>${customer.mobile}</small>
                        </div></a>`);
              item.on('click', function(e) {
                e.preventDefault();
                $('#cashCustomerName').val(customer.customer_name);
                $('#cashCustomerMobile').val(customer.mobile);
                $('#cashCustomerId').val(customer.id);
                $('#customerSearchResults').hide();
                if (customer.address) $('#billingAddress').val(customer.address);
                // Trigger tax recalculation?
              });
              results.append(item);
            });
            results.show();
          } else {
            results.hide();
          }
        }
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
      $.ajax({
        url: $('#invoiceForm').attr('action'),
        type: 'POST',
        data: $('#invoiceForm').serialize(),
        success: function(response) {
          if (response.success) {
            window.location.href = response.redirect;
          } else {
            alert(response.error || 'Error');
            $('#submitBtn').prop('disabled', false).html('Create Invoice');
          }
        },
        error: function() {
          alert('Failed to submit');
          $('#submitBtn').prop('disabled', false).html('Create Invoice');
        }
      }); // End ajax submit
    } // End submitData
  }); // End DOMContentLoaded
</script>

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
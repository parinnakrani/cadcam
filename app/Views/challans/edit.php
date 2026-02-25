<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Edit Challan: <?= esc($challan['challan_number'] ?? '') ?><?= $this->endSection() ?>

<?= $this->section('vendorStyles') ?>
<link rel="stylesheet" href="<?= base_url('admintheme/assets/vendor/libs/select2/select2.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$challanType   = $challan['challan_type'] ?? 'Rhodium';
$challanLines  = $challan['lines'] ?? [];
$challanNumber = $challan['challan_number'] ?? '';
$challanId     = $challan['id'] ?? 0;
?>
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">Challans /</span> Edit <?= esc($challanNumber) ?>
</h4>

<form action="<?= base_url('challans/' . $challanId) ?>" method="POST" id="challanForm" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <input type="hidden" name="challan_type" value="<?= esc($challanType) ?>">
  <input type="hidden" name="customer_type" value="Account">

  <!-- Flash Messages -->
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?= esc(session()->getFlashdata('error')) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <ul class="mb-0">
        <?php foreach (session()->getFlashdata('errors') as $error): ?>
          <li><?= esc($error) ?></li>
        <?php endforeach ?>
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- ================================================================== -->
  <!-- SECTION 1: CHALLAN HEADER -->
  <!-- ================================================================== -->
  <div class="card mb-4">
    <div class="card-header d-flex align-items-center">
      <i class="ri-file-text-line me-2"></i>
      <h5 class="mb-0">Challan Details</h5>
      <span class="badge bg-label-<?= $challanType === 'Rhodium' ? 'primary' : ($challanType === 'Meena' ? 'success' : 'warning') ?> ms-2">
        <?= esc($challanType) ?>
      </span>
      <span class="badge bg-label-secondary ms-2"><?= esc($challan['challan_status'] ?? 'Draft') ?></span>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <!-- Challan Number (read-only) -->
        <div class="col-md-2">
          <label class="form-label">Challan Number</label>
          <input type="text" class="form-control" value="<?= esc($challanNumber) ?>" readonly
            style="background-color: var(--bs-gray-100);">
        </div>

        <!-- Challan Date -->
        <div class="col-md-2">
          <label class="form-label" for="challan_date">Challan Date <span class="text-danger">*</span></label>
          <input type="date" class="form-control" id="challan_date" name="challan_date"
            value="<?= old('challan_date', $challan['challan_date'] ?? date('Y-m-d')) ?>" required>
        </div>

        <!-- Delivery Date -->
        <div class="col-md-2">
          <label class="form-label" for="delivery_date">Expected Delivery Date</label>
          <input type="date" class="form-control" id="delivery_date" name="delivery_date"
            value="<?= old('delivery_date', $challan['delivery_date'] ?? '') ?>">
        </div>

        <!-- Account Customer -->
        <div class="col-md-4">
          <label class="form-label" for="account_id">Account Customer <span class="text-danger">*</span></label>
          <select class="form-select select2" id="account_id" name="account_id">
            <option value="">Select Account</option>
            <?php foreach ($accounts as $account): ?>
              <option value="<?= $account['id'] ?>" <?= ($challan['account_id'] ?? '') == $account['id'] ? 'selected' : '' ?>>
                <?= esc($account['account_name']) ?>
                <?php if (!empty($account['mobile'])): ?> - <?= esc($account['mobile']) ?><?php endif; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Notes -->
      <div class="row g-3 mt-1">
        <div class="col-12">
          <label class="form-label" for="notes">Notes</label>
          <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Optional notes..."><?= old('notes', $challan['notes'] ?? '') ?></textarea>
        </div>
      </div>
    </div>
  </div>

  <!-- ================================================================== -->
  <!-- SECTION 2: LINE ITEMS -->
  <!-- ================================================================== -->
  <div class="card mb-4" id="lineItemsCard">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0"><i class="ri-list-check me-2"></i> Line Items</h5>
      <button type="button" class="btn btn-sm btn-primary" id="btn-add-line">
        <i class="ri-add-line me-1"></i> Add Line
      </button>
    </div>
    <div class="card-body">
      <div id="linesBody">
        <!-- Line cards added dynamically -->
      </div>
      <div class="text-center py-4 <?= !empty($challanLines) ? 'd-none' : '' ?>" id="no-lines-msg">
        <i class="ri-list-check ri-2x text-muted mb-2 d-block"></i>
        <p class="text-muted mb-0">No line items. Click <strong>Add Line</strong> to add.</p>
      </div>
    </div>
  </div>

  <!-- ================================================================== -->
  <!-- SECTION 3: TOTALS -->
  <!-- ================================================================== -->
  <div class="card mb-4">
    <div class="card-body">
      <div class="row justify-content-end">
        <div class="col-md-5 col-lg-4">
          <table class="table table-sm table-borderless mb-0">
            <tbody>
              <tr>
                <td class="text-muted">Total Weight:</td>
                <td class="text-end fw-semibold"><span id="total-weight"><?= number_format((float)($challan['total_weight'] ?? 0), 3) ?></span> g</td>
              </tr>
              <tr class="border-top">
                <td class="fw-bold fs-5">Total:</td>
                <td class="text-end fw-bold fs-5 text-primary">₹ <span id="total-amount"><?= number_format((float)($challan['total_amount'] ?? 0), 2) ?></span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- ================================================================== -->
  <!-- ACTIONS -->
  <!-- ================================================================== -->
  <div class="d-flex justify-content-between mb-4">
    <a href="<?= base_url('challans/' . $challanId) ?>" class="btn btn-outline-secondary">
      <i class="ri-arrow-left-line me-1"></i> Back to Challan
    </a>
    <div>
      <button type="submit" class="btn btn-primary">
        <i class="ri-save-line me-1"></i> Update Challan
      </button>
    </div>
  </div>
</form>

<!-- ================================================================== -->
<!-- LINE CARD TEMPLATE (hidden, cloned via JS) -->
<!-- ================================================================== -->
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
      <!-- Row 1: Product + Process -->
      <div class="row g-3 mb-3">
        <div class="col-12 col-md-6">
          <label class="form-label form-label-sm">Product(s)</label>
          <select class="form-select line-product" name="lines[__INDEX__][product_ids][]" multiple>
            <?php foreach ($products as $product): ?>
              <option value="<?= $product['id'] ?>" data-name="<?= esc($product['product_name']) ?>">
                <?= esc($product['product_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <input type="hidden" class="line-product-name" name="lines[__INDEX__][product_name]" value="">
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label form-label-sm">Process(es)</label>
          <select class="form-select line-process" name="lines[__INDEX__][process_ids][]" multiple>
            <?php foreach ($processes as $process): ?>
              <option value="<?= $process['id'] ?>"
                data-rate="<?= $process['rate_per_unit'] ?>"
                data-name="<?= esc($process['process_name']) ?>">
                <?= esc($process['process_name']) ?> (₹<?= number_format($process['rate_per_unit'], 2) ?>)
              </option>
            <?php endforeach; ?>
          </select>
          <input type="hidden" class="line-process-prices" name="lines[__INDEX__][process_prices]" value="">
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
            name="lines[__INDEX__][rate]" value="0.00" min="0" step="0.01" readonly>
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label form-label-sm">Amount (₹)</label>
          <input type="text" class="form-control line-amount fw-bold text-end"
            name="lines[__INDEX__][amount]" value="0.00" readonly tabindex="-1">
        </div>
      </div>
      <!-- Row 3: Image -->
      <div class="row g-3">
        <div class="col-12 col-md-6">
          <label class="form-label form-label-sm">Image</label>
          <div class="line-image-wrapper">
            <input type="file" class="d-none line-image-input"
              name="line_images[__INDEX__]" accept="image/*">
            <input type="hidden" class="line-existing-image" name="lines[__INDEX__][existing_image]" value="">
            <input type="hidden" class="line-current-gold-price" name="lines[__INDEX__][current_gold_price]" value="">
            <input type="hidden" class="line-adjusted-gold-weight" name="lines[__INDEX__][adjusted_gold_weight]" value="">
            <input type="hidden" class="line-gold-adjustment-amount" name="lines[__INDEX__][gold_adjustment_amount]" value="">
            <div class="d-flex align-items-center gap-2">
              <div class="line-image-preview d-none">
                <img src="" alt="Preview" class="img-thumbnail" style="height:42px; width:52px; object-fit:cover; cursor:pointer;">
              </div>
              <button type="button" class="btn btn-outline-secondary btn-upload-image flex-grow-1" title="Upload Image">
                <i class="ri-camera-line me-1"></i> Photo
              </button>
              <button type="button" class="btn btn-outline-danger btn-remove-image d-none" title="Remove">
                <i class="ri-close-line"></i>
              </button>
            </div>
          </div>
        </div>
        <!-- Gold Adjustment Info (shown dynamically) -->
        <div class="col-12 col-md-6 gold-adjustment-info-container d-none">
          <label class="form-label form-label-sm">Gold Adjustment</label>
          <div class="gold-adjustment-info-text alert alert-info py-2 mb-0 small"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Line Image</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <img id="modalImage" src="" alt="Line Image" class="img-fluid rounded">
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('vendorScripts') ?>
<script src="<?= base_url('admintheme/assets/vendor/libs/select2/select2.js') ?>"></script>
<?= $this->endSection() ?>

<?= $this->section('page_js') ?>
<script>
  $(document).ready(function() {

    // =========================================================================
    // CONFIGURATION
    // =========================================================================
    var lineIndex = 0;
    var challanType = '<?= esc($challanType) ?>';
    var baseUrl = '<?= base_url() ?>';
    var goldRates = <?= json_encode($gold_rates ?? []) ?>;

    // Existing lines data from server (JSON)
    var existingLines = <?= json_encode($challanLines) ?>;

    // Initialize Select2 on account dropdown
    $('#account_id').select2({
      width: '100%',
      placeholder: 'Search accounts...',
      allowClear: true
    });

    // =========================================================================
    // LINE ITEM MANAGEMENT
    // =========================================================================

    $('#btn-add-line').on('click', function() {
      addLine();
    });

    function addLine(lineData) {
      var template = document.getElementById('line-row-template').innerHTML;
      var lineNum = lineIndex + 1;

      var html = template
        .replace(/__INDEX__/g, lineIndex)
        .replace(/__NUM__/g, lineNum);

      var $row = $(html);

      // Store historical prices if available
      var historicalPrices = {};
      if (lineData && lineData.process_prices) {
        if (Array.isArray(lineData.process_prices)) {
          lineData.process_prices.forEach(function(pp) {
            if (pp.process_id) historicalPrices[pp.process_id] = parseFloat(pp.rate);
          });
        }
      }
      $row.data('historical-prices', historicalPrices);

      // Pre-fill values if editing existing line
      if (lineData) {
        if (lineData.quantity) $row.find('.line-qty').val(lineData.quantity);
        if (lineData.weight) $row.find('.line-weight').val(parseFloat(lineData.weight).toFixed(3));
        if (lineData.gold_weight) $row.find('.line-gold-weight').val(parseFloat(lineData.gold_weight).toFixed(3));
        if (lineData.gold_purity) $row.find('.line-purity').val(lineData.gold_purity);
        if (lineData.rate) $row.find('.line-rate').val(parseFloat(lineData.rate).toFixed(2));
        if (lineData.amount) $row.find('.line-amount').val(parseFloat(lineData.amount).toFixed(2));
        if (lineData.product_name) $row.find('.line-product-name').val(lineData.product_name);

        // Restore gold adjustment values
        if (lineData.current_gold_price) $row.find('.line-current-gold-price').val(lineData.current_gold_price);
        if (lineData.adjusted_gold_weight) $row.find('.line-adjusted-gold-weight').val(lineData.adjusted_gold_weight);
        if (lineData.gold_adjustment_amount) $row.find('.line-gold-adjustment-amount').val(lineData.gold_adjustment_amount);

        // Show existing image if available
        if (lineData.image_path) {
          $row.find('.line-existing-image').val(lineData.image_path);
          var imgUrl = baseUrl + lineData.image_path;
          $row.find('.line-image-preview img').attr('src', imgUrl);
          $row.find('.line-image-preview').removeClass('d-none');
          $row.find('.btn-upload-image').addClass('d-none');
          $row.find('.btn-remove-image').removeClass('d-none');
        }
      }

      $('#linesBody').append($row);

      // Initialize Select2
      var $productSelect = $row.find('.line-product');
      var $processSelect = $row.find('.line-process');

      $productSelect.select2({
        width: '100%',
        placeholder: 'Select products',
        allowClear: true
      });

      $processSelect.select2({
        width: '100%',
        placeholder: 'Select processes',
        allowClear: true
      });

      // Pre-select products and processes if editing
      if (lineData) {
        var productIds = parseJsonOrArray(lineData.product_ids);
        if (productIds.length > 0) {
          $productSelect.val(productIds.map(String)).trigger('change');
        }

        var processIds = parseJsonOrArray(lineData.process_ids);
        if (processIds.length > 0) {
          $processSelect.val(processIds.map(String)).trigger('change');
        }
      }

      lineIndex++;
      $('#no-lines-msg').addClass('d-none');

      // Calculate amounts to populate hidden process_prices input
      if (lineData) {
        calculateLineAmount($row);
      }
    }

    function parseJsonOrArray(val) {
      if (Array.isArray(val)) return val;
      if (typeof val === 'string' && val.length > 0) {
        try {
          return JSON.parse(val);
        } catch (e) {
          return [];
        }
      }
      return [];
    }

    // Remove Line
    $(document).on('click', '.btn-remove-line', function() {
      var $row = $(this).closest('.line-card');
      // Remove associated gold adjustment info if exists (now inline in card)
      $row.find('.line-product, .line-process').select2('destroy');
      $row.remove();
      recalculateTotals();
      if ($('#linesBody .line-card').length === 0) {
        $('#no-lines-msg').removeClass('d-none');
      }
    });

    // =========================================================================
    // IMAGE UPLOAD PER LINE
    // =========================================================================

    $(document).on('click', '.btn-upload-image', function() {
      $(this).closest('.line-image-wrapper').find('.line-image-input').trigger('click');
    });

    $(document).on('change', '.line-image-input', function() {
      var $wrapper = $(this).closest('.line-image-wrapper');
      var file = this.files[0];

      if (file) {
        if (!file.type.startsWith('image/')) {
          alert('Please select a valid image file.');
          this.value = '';
          return;
        }

        if (file.size > 5 * 1024 * 1024) {
          alert('Image size must be less than 5MB.');
          this.value = '';
          return;
        }

        var reader = new FileReader();
        reader.onload = function(e) {
          $wrapper.find('.line-image-preview img').attr('src', e.target.result);
          $wrapper.find('.line-image-preview').removeClass('d-none');
          $wrapper.find('.btn-upload-image').addClass('d-none');
          $wrapper.find('.btn-remove-image').removeClass('d-none');
          // Clear existing image since we have a new one
          $wrapper.find('.line-existing-image').val('');
        };
        reader.readAsDataURL(file);
      }
    });

    $(document).on('click', '.btn-remove-image', function() {
      var $wrapper = $(this).closest('.line-image-wrapper');
      $wrapper.find('.line-image-input').val('');
      $wrapper.find('.line-existing-image').val('');
      $wrapper.find('.line-image-preview').addClass('d-none');
      $wrapper.find('.line-image-preview img').attr('src', '');
      $wrapper.find('.btn-upload-image').removeClass('d-none');
      $(this).addClass('d-none');
    });

    // Click to view image in modal
    $(document).on('click', '.line-image-preview img', function() {
      var src = $(this).attr('src');
      if (src) {
        $('#modalImage').attr('src', src);
        new bootstrap.Modal(document.getElementById('imageModal')).show();
      }
    });

    // =========================================================================
    // AMOUNT CALCULATION
    // =========================================================================

    $(document).on('change', '.line-process', function() {
      var $row = $(this).closest('.line-card');
      calculateLineAmount($row);
    });

    $(document).on('input change', '.line-qty, .line-rate, .line-weight, .line-gold-weight, .line-purity', function() {
      var $row = $(this).closest('.line-card');
      calculateLineAmount($row);
    });

    // Set product_name hidden field when product is selected
    $(document).on('change', '.line-product', function() {
      var $row = $(this).closest('.line-card');
      var names = [];
      $(this).find(':selected').each(function() {
        names.push($(this).data('name') || $(this).text().trim());
      });
      $row.find('.line-product-name').val(names.join(', '));
    });

    function calculateLineAmount($row) {
      var quantity = parseInt($row.find('.line-qty').val()) || 1;
      var weight = parseFloat($row.find('.line-weight').val()) || 0;
      var goldWeight = parseFloat($row.find('.line-gold-weight').val()) || 0;
      var purity = $row.find('.line-purity').val() || '';
      var $processSelect = $row.find('.line-process');
      var selectedProcesses = $processSelect.val() || [];
      var historicalPrices = $row.data('historical-prices') || {};

      var totalRate = 0;
      var processPricesArray = [];

      selectedProcesses.forEach(function(processId) {
        processId = parseInt(processId);
        var rate = 0;

        // Use historical if available
        if (historicalPrices[processId] !== undefined) {
          rate = historicalPrices[processId];
        } else {
          // Fetch current from option data
          var $option = $processSelect.find('option[value="' + processId + '"]');
          rate = parseFloat($option.data('rate')) || 0;
        }

        totalRate += rate;
        processPricesArray.push({
          process_id: processId,
          process_name: $processSelect.find('option[value="' + processId + '"]').data('name') || '',
          rate: rate
        });
      });

      // Store process prices
      $row.find('.line-process-prices').val(JSON.stringify(processPricesArray));

      // Update rate field
      if (totalRate > 0) {
        $row.find('.line-rate').val(totalRate.toFixed(2));
      } else if (selectedProcesses.length === 0) {
        // Reset rate when no processes selected
        $row.find('.line-rate').val('0.00');
        totalRate = 0;
      } else {
        totalRate = parseFloat($row.find('.line-rate').val()) || 0;
      }

      // Base Amount = weight × rate (if weight > 0), otherwise qty × rate
      var baseAmount = 0;
      if (weight > 0) {
        baseAmount = weight * totalRate;
      } else {
        baseAmount = quantity * totalRate;
      }

      // Gold adjustment calculation
      // Gold Difference = gold_weight - weight (e.g. 1.5 - 1.0 = 0.5)
      // Gold Adjustment = gold_difference × rate_per_gram (e.g. 0.5 × 15000 = 7500)
      // Final Amount = base_amount + gold_adjustment (e.g. 10000 + 7500 = 17500)
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

      // Show/hide gold adjustment info below inside the card
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

      recalculateTotals();
    }

    function recalculateTotals() {
      var total = 0;
      var totalWeight = 0;

      $('#linesBody .line-card').each(function() {
        total += parseFloat($(this).find('.line-amount').val()) || 0;
        totalWeight += parseFloat($(this).find('.line-weight').val()) || 0;
      });

      $('#total-weight').text(totalWeight.toFixed(3));
      $('#total-amount').text(formatIndianNumber(total));
    }

    function formatIndianNumber(num) {
      return num.toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    }

    // =========================================================================
    // FORM VALIDATION
    // =========================================================================
    $('#challanForm').on('submit', function(e) {
      if (!$('#account_id').val()) {
        e.preventDefault();
        alert('Please select an account customer.');
        return false;
      }

      var lineCount = $('#linesBody .line-card').length;
      if (lineCount === 0) {
        e.preventDefault();
        alert('Please add at least one line item.');
        return false;
      }

      var valid = true;
      $('#linesBody .line-card').each(function(i) {
        var processes = $(this).find('.line-process').val() || [];
        var rate = parseFloat($(this).find('.line-rate').val()) || 0;
        if (processes.length === 0 && rate <= 0) {
          alert('Line #' + (i + 1) + ': Please select at least one process or enter a rate.');
          valid = false;
          return false;
        }
      });
      if (!valid) {
        e.preventDefault();
        return false;
      }

      $(this).find('button[type="submit"]').prop('disabled', true);
    });

    // =========================================================================
    // LOAD EXISTING LINES
    // =========================================================================
    if (existingLines && existingLines.length > 0) {
      existingLines.forEach(function(line) {
        addLine(line);
      });
    } else {
      addLine(); // Add one empty line
    }

  });
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

  .line-card .select2-container--bootstrap-5 .select2-selection {
    min-height: 44px;
    font-size: 1rem;
    padding: 0.5rem 0.75rem;
  }

  .line-card .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__rendered {
    padding: 2px 4px;
  }

  /* Amount field: highlighted background */
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

  /* Image upload */
  .line-image-preview img {
    border-radius: 4px;
  }
</style>
<?= $this->endSection() ?>
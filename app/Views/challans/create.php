<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Create <?= esc($challan_type) ?> Challan<?= $this->endSection() ?>

<?= $this->section('vendorStyles') ?>
<link rel="stylesheet" href="<?= base_url('admintheme/assets/vendor/libs/select2/select2.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">Challans /</span> Create <?= esc($challan_type) ?> Challan
</h4>

<form action="<?= base_url('challans') ?>" method="POST" id="challanForm" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <input type="hidden" name="challan_type" value="<?= esc($challan_type) ?>">
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
      <span class="badge bg-label-<?= $challan_type === 'Rhodium' ? 'primary' : ($challan_type === 'Meena' ? 'success' : 'warning') ?> ms-2">
        <?= esc($challan_type) ?>
      </span>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <!-- Challan Date -->
        <div class="col-md-3">
          <label class="form-label" for="challan_date">Challan Date <span class="text-danger">*</span></label>
          <input type="date" class="form-control" id="challan_date" name="challan_date"
            value="<?= old('challan_date', date('Y-m-d')) ?>" required>
        </div>

        <!-- Delivery Date -->
        <div class="col-md-3">
          <label class="form-label" for="delivery_date">Expected Delivery Date</label>
          <input type="date" class="form-control" id="delivery_date" name="delivery_date"
            value="<?= old('delivery_date') ?>">
        </div>

        <!-- Account Customer -->
        <div class="col-md-4">
          <label class="form-label" for="account_id">Account Customer <span class="text-danger">*</span></label>
          <select class="form-select select2" id="account_id" name="account_id">
            <option value="">Select Account</option>
            <?php foreach ($accounts as $account): ?>
              <option value="<?= $account['id'] ?>" <?= old('account_id') == $account['id'] ? 'selected' : '' ?>>
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
          <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Optional notes about this challan..."><?= old('notes') ?></textarea>
        </div>
      </div>
    </div>
  </div>

  <!-- ================================================================== -->
  <!-- SECTION 2: LINE ITEMS -->
  <!-- ================================================================== -->
  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0"><i class="ri-list-check me-2"></i> Line Items</h5>
      <button type="button" class="btn btn-sm btn-primary" id="btn-add-line">
        <i class="ri-add-line me-1"></i> Add Line
      </button>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-bordered mb-0" id="linesTable">
          <thead class="table-light">
            <tr>
              <th style="width:180px">Product</th>
              <th style="width:180px">Processes</th>
              <th style="width:70px">Qty</th>
              <th style="width:100px">Weight (g)</th>
              <th style="width:100px">Rate (₹)</th>
              <th style="width:110px">Amount (₹)</th>
              <th style="width:120px">Image</th>
              <th style="width:45px"></th>
            </tr>
          </thead>
          <tbody id="linesBody">
            <!-- Line rows added dynamically -->
          </tbody>
        </table>
      </div>
      <div class="p-3 text-center" id="no-lines-msg">
        <p class="text-muted mb-0"><i class="ri-information-line me-1"></i> No line items added yet. Click "Add Line" to begin.</p>
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
                <td class="text-end fw-semibold"><span id="total-weight">0.000</span> g</td>
              </tr>
              <tr class="border-top">
                <td class="fw-bold fs-5">Total:</td>
                <td class="text-end fw-bold fs-5 text-primary">₹ <span id="total-amount">0.00</span></td>
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
    <a href="<?= base_url('challans') ?>" class="btn btn-outline-secondary">
      <i class="ri-arrow-left-line me-1"></i> Back to Challans
    </a>
    <div>
      <button type="submit" name="action" value="save_draft" class="btn btn-outline-primary me-2">
        <i class="ri-draft-line me-1"></i> Save as Draft
      </button>
      <button type="submit" name="action" value="save" class="btn btn-primary">
        <i class="ri-save-line me-1"></i> Create Challan
      </button>
    </div>
  </div>
</form>

<!-- ================================================================== -->
<!-- LINE ROW TEMPLATE (hidden, cloned via JS) -->
<!-- ================================================================== -->
<template id="line-row-template">
  <tr class="line-row" data-line-index="__INDEX__">
    <td>
      <select class="form-select form-select-sm line-product" name="lines[__INDEX__][product_ids][]" multiple>
        <?php foreach ($products as $product): ?>
          <option value="<?= $product['id'] ?>" data-name="<?= esc($product['product_name']) ?>">
            <?= esc($product['product_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <input type="hidden" class="line-product-name" name="lines[__INDEX__][product_name]" value="">
    </td>
    <td>
      <select class="form-select form-select-sm line-process" name="lines[__INDEX__][process_ids][]" multiple>
        <?php foreach ($processes as $process): ?>
          <option value="<?= $process['id'] ?>"
            data-rate="<?= $process['rate_per_unit'] ?>"
            data-name="<?= esc($process['process_name']) ?>">
            <?= esc($process['process_name']) ?> (₹<?= number_format($process['rate_per_unit'], 2) ?>)
          </option>
        <?php endforeach; ?>
      </select>
      <input type="hidden" class="line-process-prices" name="lines[__INDEX__][process_prices]" value="">
    </td>
    <td>
      <input type="number" class="form-control form-control-sm line-qty"
        name="lines[__INDEX__][quantity]" value="1" min="1" step="1">
    </td>
    <td>
      <input type="number" class="form-control form-control-sm line-weight"
        name="lines[__INDEX__][weight]" value="0.000" min="0" step="0.001">
    </td>
    <td>
      <input type="number" class="form-control form-control-sm line-rate"
        name="lines[__INDEX__][rate]" value="0.00" min="0" step="0.01" readonly>
    </td>
    <td>
      <input type="text" class="form-control form-control-sm line-amount fw-semibold"
        name="lines[__INDEX__][amount]" value="0.00" readonly tabindex="-1"
        style="background-color: var(--bs-gray-100);">
    </td>
    <td>
      <div class="line-image-wrapper">
        <input type="file" class="d-none line-image-input"
          name="line_images[__INDEX__]" accept="image/*">
        <input type="hidden" class="line-existing-image" name="lines[__INDEX__][existing_image]" value="">
        <div class="line-image-preview d-none mb-1">
          <img src="" alt="Preview" class="img-thumbnail" style="max-height:48px; max-width:60px; cursor:pointer;">
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary btn-upload-image w-100" title="Upload Image">
          <i class="ri-camera-line"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-image w-100 d-none mt-1" title="Remove">
          <i class="ri-close-line"></i>
        </button>
      </div>
    </td>
    <td class="text-center align-middle">
      <button type="button" class="btn btn-sm btn-icon btn-text-danger btn-remove-line" title="Remove Line">
        <i class="ri-delete-bin-line"></i>
      </button>
    </td>
  </tr>
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
    var challanType = '<?= esc($challan_type) ?>';
    var baseUrl = '<?= base_url() ?>';

    // Initialize Select2 on account dropdown
    $('#account_id').select2({
      width: '100%',
      placeholder: 'Search accounts...',
      allowClear: true
    });

    // =========================================================================
    // LINE ITEM MANAGEMENT
    // =========================================================================

    // Add Line
    $('#btn-add-line').on('click', function() {
      addLine();
    });

    function addLine() {
      var template = document.getElementById('line-row-template').innerHTML;
      var lineNum = lineIndex + 1;

      var html = template
        .replace(/__INDEX__/g, lineIndex)
        .replace(/__NUM__/g, lineNum);

      var $row = $(html);
      $('#linesBody').append($row);

      // Initialize Select2 on the new row's selects
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

      // Hide "no lines" message
      $('#no-lines-msg').addClass('d-none');
    }

    // Remove Line
    $(document).on('click', '.btn-remove-line', function() {
      var $row = $(this).closest('.line-row');

      // Destroy Select2 before removing
      $row.find('.line-product, .line-process').select2('destroy');
      $row.remove();

      recalculateTotals();

      // Show "no lines" message if empty
      if ($('#linesBody .line-row').length === 0) {
        $('#no-lines-msg').removeClass('d-none');
      }
    });

    // =========================================================================
    // IMAGE UPLOAD PER LINE
    // =========================================================================

    // Trigger file input when upload button is clicked
    $(document).on('click', '.btn-upload-image', function() {
      $(this).closest('.line-image-wrapper').find('.line-image-input').trigger('click');
    });

    // Preview selected image
    $(document).on('change', '.line-image-input', function() {
      var $wrapper = $(this).closest('.line-image-wrapper');
      var file = this.files[0];

      if (file) {
        // Validate file type
        if (!file.type.startsWith('image/')) {
          alert('Please select a valid image file.');
          this.value = '';
          return;
        }

        // Validate file size (max 5MB)
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
        };
        reader.readAsDataURL(file);
      }
    });

    // Remove image
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

    // Trigger recalculation when process selection, quantity, or rate changes
    $(document).on('change', '.line-process', function() {
      var $row = $(this).closest('.line-row');
      calculateLineAmount($row);
    });

    $(document).on('input change', '.line-qty, .line-rate, .line-weight', function() {
      var $row = $(this).closest('.line-row');
      calculateLineAmount($row);
    });

    // Set product_name hidden field when product is selected
    $(document).on('change', '.line-product', function() {
      var $row = $(this).closest('.line-row');
      var names = [];
      $(this).find(':selected').each(function() {
        names.push($(this).data('name') || $(this).text().trim());
      });
      $row.find('.line-product-name').val(names.join(', '));
    });

    function calculateLineAmount($row) {
      var quantity = parseInt($row.find('.line-qty').val()) || 1;
      var weight = parseFloat($row.find('.line-weight').val()) || 0;
      var $processSelect = $row.find('.line-process');
      var selectedProcesses = $processSelect.val() || [];

      // Sum rates from selected processes
      var totalRate = 0;
      var processPricesArray = [];

      selectedProcesses.forEach(function(processId) {
        var $option = $processSelect.find('option[value="' + processId + '"]');
        var rate = parseFloat($option.data('rate')) || 0;
        totalRate += rate;
        processPricesArray.push({
          process_id: processId,
          process_name: $option.data('name') || '',
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

      // Amount = weight × rate (if weight > 0), otherwise qty × rate
      var amount = 0;
      if (weight > 0) {
        amount = weight * totalRate;
      } else {
        amount = quantity * totalRate;
      }

      $row.find('.line-amount').val(amount.toFixed(2));

      recalculateTotals();
    }

    function recalculateTotals() {
      var total = 0;
      var totalWeight = 0;

      $('#linesBody .line-row').each(function() {
        var amount = parseFloat($(this).find('.line-amount').val()) || 0;
        var weight = parseFloat($(this).find('.line-weight').val()) || 0;

        total += amount;
        totalWeight += weight;
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
    // FORM VALIDATION & SUBMISSION
    // =========================================================================
    $('#challanForm').on('submit', function(e) {
      // Validate account customer
      if (!$('#account_id').val()) {
        e.preventDefault();
        alert('Please select an account customer.');
        $('#account_id').focus();
        return false;
      }

      // Validate at least 1 line
      var lineCount = $('#linesBody .line-row').length;
      if (lineCount === 0) {
        e.preventDefault();
        alert('Please add at least one line item.');
        $('#btn-add-line').focus();
        return false;
      }

      // Validate each line has at least one process or a rate > 0
      var valid = true;
      $('#linesBody .line-row').each(function(i) {
        var $row = $(this);
        var processes = $row.find('.line-process').val() || [];
        var rate = parseFloat($row.find('.line-rate').val()) || 0;

        if (processes.length === 0 && rate <= 0) {
          alert('Line #' + (i + 1) + ': Please select at least one process or enter a rate.');
          valid = false;
          return false; // break
        }
      });

      if (!valid) {
        e.preventDefault();
        return false;
      }

      // Disable submit to prevent double-click
      $(this).find('button[type="submit"]').prop('disabled', true);
    });

    // =========================================================================
    // ADD FIRST LINE AUTOMATICALLY
    // =========================================================================
    addLine();

  });
</script>

<style>
  /* Select2 in table cells */
  #linesTable .select2-container {
    width: 100% !important;
    min-width: 140px;
  }

  #linesTable .select2-container--bootstrap-5 .select2-selection {
    min-height: 31px;
    font-size: 0.8125rem;
    padding: 0.25rem 0.5rem;
  }

  /* Line item amounts */
  .line-amount {
    text-align: right;
  }

  .line-rate {
    text-align: right;
  }

  /* Make remove button subtle */
  .btn-text-danger {
    color: var(--bs-danger);
    background: transparent;
    border: none;
  }

  .btn-text-danger:hover {
    background-color: rgba(var(--bs-danger-rgb), 0.1);
  }

  /* Row hover */
  #linesTable tbody tr:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.04);
  }

  /* Image upload */
  .line-image-wrapper {
    text-align: center;
    min-width: 70px;
  }

  .line-image-preview img {
    border-radius: 4px;
    object-fit: cover;
  }
</style>
<?= $this->endSection() ?>
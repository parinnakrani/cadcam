<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Invoice Details<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Breadcrumb -->
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

// â”€â”€ Invoice Status Config â”€â”€
$invoiceStatus = $invoice['invoice_status'] ?? 'Draft';

$statusBadgeMap = [
  'Draft'          => 'bg-label-secondary',
  'Posted'         => 'bg-label-info',
  'Partially Paid' => 'bg-label-warning',
  'Paid'           => 'bg-label-success',
  'Delivered'      => 'bg-label-primary',
  'Closed'         => 'bg-label-dark',
  'Cancelled'      => 'bg-label-danger',
];
$statusBadgeClass = $statusBadgeMap[$invoiceStatus] ?? 'bg-label-secondary';

// Valid manual transitions
$validTransitions = [
  'Draft'          => ['Posted', 'Cancelled'],
  'Posted'         => ['Closed', 'Cancelled'],
  'Partially Paid' => ['Closed'],
  'Paid'           => ['Delivered', 'Closed'],
  'Delivered'      => ['Closed'],
  'Closed'         => [],
  'Cancelled'      => [],
];
$nextStatuses = $validTransitions[$invoiceStatus] ?? [];

$statusLabels = [
  'Draft'          => ['icon' => 'ri-draft-line',          'text' => 'Revert to Draft'],
  'Posted'         => ['icon' => 'ri-send-plane-line',      'text' => 'Mark as Posted'],
  'Partially Paid' => ['icon' => 'ri-money-dollar-circle-line', 'text' => 'Mark as Partially Paid'],
  'Paid'           => ['icon' => 'ri-checkbox-circle-line', 'text' => 'Mark as Paid'],
  'Delivered'      => ['icon' => 'ri-truck-line',           'text' => 'Mark as Delivered'],
  'Closed'         => ['icon' => 'ri-lock-line',            'text' => 'Close Invoice'],
  'Cancelled'      => ['icon' => 'ri-close-circle-line',    'text' => 'Cancel Invoice'],
];
?>

<!-- Flash Messages -->
<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= esc(session()->getFlashdata('success')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= esc(session()->getFlashdata('error')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<!-- Status Change Success Toast -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 1090">
  <div id="statusToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="statusToastMsg">Status updated.</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Home</a></li>
    <li class="breadcrumb-item"><a href="<?= base_url($baseRoute) ?>"><?= $baseTitle ?></a></li>
    <li class="breadcrumb-item active" aria-current="page"><?= esc($invoice['invoice_number']) ?></li>
  </ol>
</nav>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h1 class="h3 mb-0">Invoice #<?= esc($invoice['invoice_number']) ?></h1>
    <p class="text-muted mb-0">
      <small>Created on <?= date('d M Y, h:i A', strtotime($invoice['created_at'])) ?></small>
    </p>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <!-- Print Button -->
    <a href="<?= base_url("invoices/{$invoice['id']}/print") ?>" target="_blank" class="btn btn-outline-primary">
      <i class="ri-printer-line"></i> Print
    </a>

    <!-- Edit Button (only if not paid) -->
    <?php if ($invoice['total_paid'] == 0): ?>
      <a href="<?= base_url("{$baseRoute}/{$invoice['id']}/edit") ?>" class="btn btn-outline-secondary">
        <i class="ri-pencil-line"></i> Edit
      </a>
    <?php endif; ?>

    <!-- Change Status Dropdown -->
    <?php if (!empty($nextStatuses)): ?>
      <div class="btn-group">
        <button type="button" class="btn btn-outline-info dropdown-toggle" data-bs-toggle="dropdown">
          <i class="ri-exchange-line me-1"></i> Change Status
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <?php foreach ($nextStatuses as $next): ?>
            <?php $lbl = $statusLabels[$next] ?? ['icon' => 'ri-arrow-right-line', 'text' => $next]; ?>
            <li>
              <a class="dropdown-item btn-change-status <?= $next === 'Cancelled' ? 'text-danger' : '' ?>"
                href="javascript:void(0);" data-status="<?= esc($next) ?>">
                <i class="<?= $lbl['icon'] ?> me-1"></i> <?= esc($lbl['text']) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <!-- Record Payment Button (only if amount due > 0) -->
    <?php if ($invoice['amount_due'] > 0): ?>
      <a href="<?= base_url("payments/create?invoice_id={$invoice['id']}") ?>" class="btn btn-success">
        <i class="ri-bank-card-line"></i> Record Payment
      </a>
    <?php endif; ?>

    <!-- Delete Button (only if not paid) -->
    <?php if ($invoice['total_paid'] == 0): ?>
      <button type="button" class="btn btn-outline-danger" onclick="deleteInvoice(<?= $invoice['id'] ?>, '<?= esc($invoice['invoice_number']) ?>')">
        <i class="ri-delete-bin-line"></i> Delete
      </button>
    <?php endif; ?>
  </div>
</div>

<!-- Status Badges -->
<div class="mb-4">
  <!-- Invoice Type Badge -->
  <?php
  $typeBadge = 'secondary';
  if ($invoice['invoice_type'] === 'Accounts Invoice') {
    $typeBadge = 'primary';
  } elseif ($invoice['invoice_type'] === 'Cash Invoice') {
    $typeBadge = 'success';
  } elseif ($invoice['invoice_type'] === 'Wax Invoice') {
    $typeBadge = 'info';
  }
  ?>
  <span class="badge bg-<?= $typeBadge ?> me-2"><?= esc($invoice['invoice_type']) ?></span>

  <!-- Invoice Status Badge (workflow status) -->
  <span class="badge <?= $statusBadgeClass ?> me-2"><?= esc($invoiceStatus) ?></span>

  <!-- Payment Status Badge -->
  <?php
  $statusBadge = 'secondary';
  $statusText = $invoice['payment_status'];

  if ($invoice['payment_status'] === 'Pending') {
    $statusBadge = 'danger';
    $statusText = 'Unpaid';
  } elseif ($invoice['payment_status'] === 'Partial Paid') {
    $statusBadge = 'warning';
  } elseif ($invoice['payment_status'] === 'Paid') {
    $statusBadge = 'success';
  }
  ?>
  <span class="badge bg-<?= $statusBadge ?> me-2"><?= esc($statusText) ?></span>

  <!-- Delivery Status Badge -->
  <?php
  $deliveryStatus = $invoice['delivery_status'] ?? 'Not Delivered';
  $deliveryBadge = $deliveryStatus === 'Delivered' ? 'success' : 'secondary';
  ?>
  <span class="badge bg-<?= $deliveryBadge ?>"><?= esc($deliveryStatus) ?></span>
</div>

<div class="row">
  <!-- Left Column -->
  <div class="col-md-8">
    <!-- Invoice Details Card -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-file-text-line"></i> Invoice Details</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <table class="table table-sm table-borderless">
              <tr>
                <td class="text-muted" style="width: 150px;">Invoice Number:</td>
                <td><strong><?= esc($invoice['invoice_number']) ?></strong></td>
              </tr>
              <tr>
                <td class="text-muted">Invoice Date:</td>
                <td><?= date('d M Y', strtotime($invoice['invoice_date'])) ?></td>
              </tr>
              <?php if (!empty($invoice['due_date'])): ?>
                <tr>
                  <td class="text-muted">Due Date:</td>
                  <td><?= date('d M Y', strtotime($invoice['due_date'])) ?></td>
                </tr>
              <?php endif; ?>
              <?php if (!empty($invoice['reference_number'])): ?>
                <tr>
                  <td class="text-muted">Reference:</td>
                  <td><?= esc($invoice['reference_number']) ?></td>
                </tr>
              <?php endif; ?>
            </table>
          </div>
          <div class="col-md-6">
            <table class="table table-sm table-borderless">
              <tr>
                <td class="text-muted" style="width: 150px;">Invoice Type:</td>
                <td><?= esc($invoice['invoice_type']) ?></td>
              </tr>
              <tr>
                <td class="text-muted">Payment Status:</td>
                <td><span class="badge bg-<?= $statusBadge ?>"><?= esc($statusText) ?></span></td>
              </tr>
              <tr>
                <td class="text-muted">Delivery Status:</td>
                <td><span class="badge bg-<?= $deliveryBadge ?>"><?= esc($deliveryStatus) ?></span></td>
              </tr>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Customer Details Card -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-user-line"></i> Customer Details</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <h6 class="text-muted">Customer Name</h6>
            <p class="">
              <strong><?= esc($invoice['customer']['customer_name']) ?></strong>
            </p>
          </div>
          <div class="col-md-6">
            <h6 class="text-muted">Mobile Number</h6>
            <?php if (!empty($invoice['customer']['mobile'])): ?>
              <p class="">
                <strong><i class="ri-phone-line"></i> <?= esc($invoice['customer']['mobile']) ?></strong>
              </p>
            <?php endif; ?>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <h6 class="text-muted">Billing Address</h6>
            <p class="mb-0">
              <?= nl2br(esc($invoice['billing_address'] ?? 'N/A')) ?>
            </p>
          </div>
          <div class="col-md-6">
            <h6 class="text-muted">Shipping Address</h6>
            <p class="mb-0">
              <?= nl2br(esc($invoice['shipping_address'] ?? 'Same as billing')) ?>
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Line Items Card -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-list-unordered"></i> Line Items</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered table-hover">
            <thead class="table-light">
              <tr>
                <th style="width: 50px;">#</th>
                <th>Products</th>
                <th>Processes</th>
                <th class="text-center">Qty</th>
                <th class="text-end">Weight (g)</th>
                <th class="text-end">Rate (₹)</th>
                <th class="text-end">Amount (₹)</th>
                <th class="text-center">Image</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($invoice['lines'])): ?>
                <?php foreach ($invoice['lines'] as $line): ?>
                  <tr>
                    <td class="text-center"><?= $line['line_number'] ?></td>
                    <td>
                      <?php if (!empty($line['products'])): ?>
                        <?php
                        echo implode(', ', array_column($line['products'], 'product_name'));
                        ?>
                      <?php elseif (!empty($line['product_name'])): ?>
                        <?= esc($line['product_name']) ?>
                      <?php else: ?>
                        <span class="text-muted">N/A</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if (!empty($line['processes'])): ?>
                        <?php
                        echo implode(', ', array_column($line['processes'], 'process_name'));
                        ?>
                      <?php else: ?>
                        <span class="text-muted">N/A</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center"><?= $line['quantity'] ?? 1 ?></td>
                    <td class="text-end"><?= number_format(($line['gold_weight'] > 0 ? $line['gold_weight'] : $line['weight']) ?? 0, 3) ?></td>
                    <td class="text-end">₹<?= number_format($line['rate'] ?? 0, 2) ?></td>
                    <td class="text-end"><strong>₹<?= number_format($line['amount'] ?? 0, 2) ?></strong></td>
                    <td class="text-center">
                      <?php if (!empty($line['image_path'])): ?>
                        <img src="<?= base_url($line['image_path']) ?>" alt="Line Image"
                          class="img-thumbnail line-image-thumb"
                          style="max-height:50px; max-width:65px; cursor:pointer;"
                          data-full-src="<?= base_url($line['image_path']) ?>">
                      <?php else: ?>
                        <span class="text-muted small">â€”</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" class="text-center text-muted">No line items</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Notes Card -->
    <?php if (!empty($invoice['notes']) || !empty($invoice['terms_conditions'])): ?>
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="card-title mb-0"><i class="ri-file-info-line"></i> Additional Information</h5>
        </div>
        <div class="card-body">
          <?php if (!empty($invoice['notes'])): ?>
            <h6 class="text-muted">Notes</h6>
            <p><?= nl2br(esc($invoice['notes'])) ?></p>
          <?php endif; ?>

          <?php if (!empty($invoice['terms_conditions'])): ?>
            <h6 class="text-muted">Terms & Conditions</h6>
            <p><?= nl2br(esc($invoice['terms_conditions'])) ?></p>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Right Column -->
  <div class="col-md-4">
    <!-- Tax Breakdown Card -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-calculator-line"></i> Tax Breakdown</h5>
      </div>
      <div class="card-body">
        <?php if ($invoice['cgst_amount'] > 0 || $invoice['sgst_amount'] > 0): ?>
          <!-- CGST + SGST -->
          <p class="mb-2"><strong>Tax Type:</strong> CGST + SGST (Intra-state)</p>
          <table class="table table-sm table-borderless">
            <tr>
              <td>CGST (<?= number_format($invoice['tax_rate'] / 2, 2) ?>%):</td>
              <td class="text-end">₹<?= number_format($invoice['cgst_amount'], 2) ?></td>
            </tr>
            <tr>
              <td>SGST (<?= number_format($invoice['tax_rate'] / 2, 2) ?>%):</td>
              <td class="text-end">₹<?= number_format($invoice['sgst_amount'], 2) ?></td>
            </tr>
            <tr class="border-top">
              <td><strong>Total Tax:</strong></td>
              <td class="text-end"><strong>₹<?= number_format($invoice['tax_amount'], 2) ?></strong></td>
            </tr>
          </table>
        <?php elseif ($invoice['igst_amount'] > 0): ?>
          <!-- IGST -->
          <p class="mb-2"><strong>Tax Type:</strong> IGST (Inter-state)</p>
          <table class="table table-sm table-borderless">
            <tr>
              <td>IGST (<?= number_format($invoice['tax_rate'], 2) ?>%):</td>
              <td class="text-end">₹<?= number_format($invoice['igst_amount'], 2) ?></td>
            </tr>
            <tr class="border-top">
              <td><strong>Total Tax:</strong></td>
              <td class="text-end"><strong>₹<?= number_format($invoice['tax_amount'], 2) ?></strong></td>
            </tr>
          </table>
        <?php else: ?>
          <p class="text-muted">No tax applied</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Totals Card -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-currency-rupee-line"></i> Invoice Totals</h5>
      </div>
      <div class="card-body">
        <table class="table table-sm table-borderless">
          <tr>
            <td>Subtotal:</td>
            <td class="text-end">₹<?= number_format($invoice['subtotal'], 2) ?></td>
          </tr>
          <tr>
            <td>Tax Amount:</td>
            <td class="text-end">₹<?= number_format($invoice['tax_amount'], 2) ?></td>
          </tr>
          <tr class="table-primary">
            <td><strong>Grand Total:</strong></td>
            <td class="text-end"><strong>₹<?= number_format($invoice['grand_total'], 2) ?></strong></td>
          </tr>
          <tr class="border-top">
            <td>Amount Paid:</td>
            <td class="text-end text-success">₹<?= number_format($invoice['total_paid'], 2) ?></td>
          </tr>
          <tr>
            <td><strong>Amount Due:</strong></td>
            <td class="text-end <?= $invoice['amount_due'] > 0 ? 'text-danger' : 'text-success' ?>">
              <strong>₹<?= number_format($invoice['amount_due'], 2) ?></strong>
            </td>
          </tr>
        </table>
      </div>
    </div>

    <!-- Payment History Card -->
    <?php if (!empty($invoice['payments'])): ?>
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="card-title mb-0"><i class="ri-history-line"></i> Payment History</h5>
        </div>
        <div class="card-body">
          <div class="list-group list-group-flush">
            <?php foreach ($invoice['payments'] as $payment): ?>
              <div class="list-group-item px-0">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <p class="mb-0"><strong>₹<?= number_format($payment['amount'], 2) ?></strong></p>
                    <small class="text-muted"><?= date('d M Y', strtotime($payment['payment_date'])) ?></small>
                  </div>
                  <span class="badge bg-success">Paid</span>
                </div>
                <?php if (!empty($payment['payment_method'])): ?>
                  <small class="text-muted">via <?= esc($payment['payment_method']) ?></small>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Gold Adjustment Card -->
    <?php if ($invoice['gold_adjustment_applied']): ?>
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="card-title mb-0"><i class="ri-gem-line"></i> Gold Adjustment</h5>
        </div>
        <div class="card-body">
          <table class="table table-sm table-borderless">
            <tr>
              <td>Adjustment Amount:</td>
              <td class="text-end">₹<?= number_format($invoice['gold_adjustment_amount'], 2) ?></td>
            </tr>
            <tr>
              <td>Gold Rate Used:</td>
              <td class="text-end">₹<?= number_format($invoice['gold_rate_used'], 2) ?>/g</td>
            </tr>
            <tr>
              <td>Adjustment Date:</td>
              <td class="text-end"><?= date('d M Y', strtotime($invoice['gold_adjustment_date'])) ?></td>
            </tr>
          </table>
        </div>
      </div>
    <?php endif; ?>

    <!-- Status Workflow Card -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-route-line me-1"></i> Invoice Status</h5>
      </div>
      <div class="card-body pb-1">
        <?php
        $allStatuses = ['Draft', 'Posted', 'Paid', 'Delivered', 'Closed'];
        $currentIdx  = array_search($invoiceStatus, $allStatuses);
        if ($currentIdx === false) $currentIdx = -1;
        ?>
        <?php foreach ($allStatuses as $idx => $st): ?>
          <?php
          $isActive = ($st === $invoiceStatus);
          $isPast   = ($idx < $currentIdx);
          $iconClass = 'ri-checkbox-blank-circle-line text-muted';
          $textClass = 'text-muted';
          if ($isPast) {
            $iconClass = 'ri-checkbox-circle-fill text-success';
            $textClass = 'text-success';
          }
          if ($isActive) {
            $iconClass = 'ri-radio-button-line text-primary';
            $textClass = 'fw-semibold text-primary';
          }
          ?>
          <div class="d-flex align-items-center mb-3">
            <i class="<?= $iconClass ?> me-2 ri-lg"></i>
            <span class="<?= $textClass ?>"><?= esc($st) ?></span>
            <?php if ($isActive): ?>
              <span class="badge bg-label-primary ms-2">Current</span>
            <?php endif; ?>
          </div>
          <?php if ($idx < count($allStatuses) - 1): ?>
            <div class="ms-2 ps-1 border-start <?= $isPast ? 'border-success' : 'border-light' ?>" style="height:12px;"></div>
          <?php endif; ?>
        <?php endforeach; ?>
        <?php if ($invoiceStatus === 'Cancelled'): ?>
          <div class="d-flex align-items-center mb-3">
            <i class="ri-close-circle-fill text-danger me-2 ri-lg"></i>
            <span class="text-danger fw-semibold">Cancelled <span class="badge bg-label-danger ms-1">Current</span></span>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<!-- Status Change Confirmation Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="ri-exchange-line me-1"></i> Change Status</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Change invoice status from <strong><?= esc($invoiceStatus) ?></strong> to <strong id="new-status-label"></strong>?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="btn-confirm-status">
          <i class="ri-check-line me-1"></i> Confirm
        </button>
      </div>
    </div>
  </div>
</div>



<?= $this->endSection() ?>


<!-- Line Image View Modal -->
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
<?= $this->section('pageScripts') ?>
<script>
  // Delete Invoice Function
  function deleteInvoice(invoiceId, invoiceNumber) {
    if (confirm(`Are you sure you want to delete invoice ${invoiceNumber}?\n\nThis action cannot be undone.`)) {
      $.ajax({
        url: `<?= base_url('invoices') ?>/${invoiceId}`,
        type: 'DELETE',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
          if (response.success) {
            alert('Invoice deleted successfully!');
            window.location.href = '<?= base_url('invoices') ?>';
          } else {
            alert('Error: ' + (response.error || 'Failed to delete invoice'));
          }
        },
        error: function(xhr) {
          const response = xhr.responseJSON;
          alert('Error: ' + (response?.error || 'Failed to delete invoice'));
        }
      });
    }
  }

  $(document).ready(function() {
    // Line image click to open modal
    $(document).on('click', '.line-image-thumb', function() {
      const fullSrc = $(this).data('full-src') || $(this).attr('src');
      $('#lineImageModalImg').attr('src', fullSrc);
      var modal = new bootstrap.Modal(document.getElementById('lineImageModal'));
      modal.show();
    });

    // =========================================================================
    // STATUS CHANGE
    // =========================================================================
    var statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
    var statusToast = new bootstrap.Toast(document.getElementById('statusToast'));
    var pendingStatus = null;

    $(document).on('click', '.btn-change-status', function(e) {
      e.preventDefault();
      pendingStatus = $(this).data('status');
      $('#new-status-label').text(pendingStatus);
      statusModal.show();
    });

    $('#btn-confirm-status').on('click', function() {
      if (!pendingStatus) return;

      var $btn = $(this);
      $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');

      $.ajax({
        url: '<?= base_url("invoices/{$invoice['id']}/change-status") ?>',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
          new_status: pendingStatus
        }),
        dataType: 'json',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(res) {
          statusModal.hide();
          if (res.status === 'success') {
            $('#statusToastMsg').text('Status changed to ' + pendingStatus);
            statusToast.show();
            setTimeout(function() {
              window.location.reload();
            }, 1000);
          } else {
            alert(res.message || 'Status change failed.');
          }
        },
        error: function(xhr) {
          statusModal.hide();
          var msg = 'Status change failed.';
          try {
            msg = JSON.parse(xhr.responseText).message || msg;
          } catch (e) {}
          alert(msg);
        },
        complete: function() {
          $btn.prop('disabled', false).html('<i class="ri-check-line me-1"></i> Confirm');
          pendingStatus = null;
        }
      });
    });
  });
</script>
<?= $this->endSection() ?>
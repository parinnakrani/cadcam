<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Challan: <?= esc($challan['challan_number'] ?? '') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
// ─── Extract values for cleaner template usage ───
$challanId      = $challan['id'] ?? 0;
$challanNumber  = $challan['challan_number'] ?? '';
$challanType    = $challan['challan_type'] ?? 'Rhodium';
$challanStatus  = $challan['challan_status'] ?? 'Draft';
$customerType   = $challan['customer_type'] ?? 'Account';
$lines          = $challan['lines'] ?? [];
$isInvoiced     = !empty($challan['invoice_generated']) && (int)$challan['invoice_generated'] === 1;
$invoiceId      = $challan['invoice_id'] ?? null;

// Badge classes
$typeBadges = [
  'Rhodium' => 'bg-label-primary',
  'Meena'   => 'bg-label-success',
  'Wax'     => 'bg-label-warning',
];
$statusBadges = [
  'Draft'       => 'bg-label-secondary',
  'Pending'     => 'bg-label-warning',
  'In Progress' => 'bg-label-info',
  'Completed'   => 'bg-label-success',
  'Invoiced'    => 'bg-label-primary',
  'Cancelled'   => 'bg-label-danger',
];

// Valid next statuses (mirrors ChallanModel::$validTransitions)
$validTransitions = [
  'Draft'       => ['Pending', 'Cancelled'],
  'Pending'     => ['In Progress', 'Draft', 'Cancelled'],
  'In Progress' => ['Completed', 'Cancelled'],
  'Completed'   => ['Invoiced'],
  'Invoiced'    => [],
];
$nextStatuses = $validTransitions[$challanStatus] ?? [];

// Status action labels for friendlier display
$statusLabels = [
  'Draft'       => ['icon' => 'ri-draft-line',       'text' => 'Revert to Draft'],
  'Pending'     => ['icon' => 'ri-time-line',        'text' => 'Mark as Pending'],
  'In Progress' => ['icon' => 'ri-loader-4-line',    'text' => 'Start Processing'],
  'Completed'   => ['icon' => 'ri-check-double-line', 'text' => 'Mark as Completed'],
  'Invoiced'    => ['icon' => 'ri-bill-line',        'text' => 'Mark as Invoiced'],
  'Cancelled'   => ['icon' => 'ri-close-circle-line', 'text' => 'Cancel Challan'],
];

// Customer info
$customerName = '';
$customerMobile = '';
if ($customerType === 'Account') {
  $customerName  = $challan['account_name'] ?? 'Account #' . ($challan['account_id'] ?? '');
  $customerMobile = $challan['account_mobile'] ?? '';
} else {
  $customerName  = $challan['customer_name'] ?? 'Cash #' . ($challan['cash_customer_id'] ?? '');
  $customerMobile = $challan['cash_mobile'] ?? '';
}
?>

<!-- Flash Messages -->
<?php if (session()->getFlashdata('message')): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= esc(session()->getFlashdata('message')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= esc(session()->getFlashdata('error')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<!-- Breadcrumb & Page Header -->
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">
    <a href="<?= base_url('challans') ?>" class="text-muted">Challans</a> /
  </span>
  <?= esc($challanNumber) ?>
</h4>

<!-- ================================================================== -->
<!-- INVOICE BANNER (if invoiced) -->
<!-- ================================================================== -->
<?php if ($isInvoiced): ?>
  <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
    <i class="ri-bill-line ri-24px me-2"></i>
    <div>
      This challan has been converted to Invoice
      <?php if ($invoiceId): ?>
        <a href="<?= base_url('invoices/' . $invoiceId) ?>" class="fw-semibold alert-link">
          #<?= esc($invoiceId) ?>
        </a>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<!-- ================================================================== -->
<!-- STATUS CHANGE SUCCESS TOAST (hidden by default, shown via JS) -->
<!-- ================================================================== -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 1090">
  <div id="statusToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="statusToastMsg">Status updated.</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

<div class="row">
  <!-- ================================================================== -->
  <!-- LEFT: CHALLAN DETAILS -->
  <!-- ================================================================== -->
  <div class="col-xl-8 col-lg-7">

    <!-- Challan Header Card -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <h5 class="mb-0 fw-bold"><?= esc($challanNumber) ?></h5>
          <span class="badge <?= $typeBadges[$challanType] ?? 'bg-label-secondary' ?>"><?= esc($challanType) ?></span>
          <span class="badge <?= $statusBadges[$challanStatus] ?? 'bg-label-secondary' ?>"><?= esc($challanStatus) ?></span>
        </div>
        <!-- Action Buttons -->
        <div class="d-flex gap-2 flex-wrap">
          <?php if (!$isInvoiced && ($action_flags['edit'] ?? false)): ?>
            <a href="<?= base_url('challans/' . $challanId . '/edit') ?>" class="btn btn-sm btn-outline-primary">
              <i class="ri-pencil-line me-1"></i> Edit
            </a>
          <?php endif; ?>

          <a href="<?= base_url('challans/' . $challanId . '/print') ?>" class="btn btn-sm btn-outline-secondary" target="_blank">
            <i class="ri-printer-line me-1"></i> Print
          </a>

          <?php if (!empty($nextStatuses) && ($action_flags['edit'] ?? false)): ?>
            <div class="btn-group">
              <button type="button" class="btn btn-sm btn-outline-info dropdown-toggle" data-bs-toggle="dropdown">
                <i class="ri-exchange-line me-1"></i> Change Status
              </button>
              <ul class="dropdown-menu">
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

          <?php if (!$isInvoiced && ($action_flags['delete'] ?? false)): ?>
            <button type="button" class="btn btn-sm btn-outline-danger" id="btn-delete-challan">
              <i class="ri-delete-bin-line me-1"></i> Delete
            </button>
          <?php endif; ?>
        </div>
      </div>

      <div class="card-body">
        <div class="row g-3">
          <div class="col-sm-6 col-md-3">
            <small class="text-muted d-block mb-1">Challan Date</small>
            <span class="fw-semibold">
              <?php
              $d = $challan['challan_date'] ?? null;
              echo $d ? date('d M Y', strtotime($d)) : '-';
              ?>
            </span>
          </div>
          <div class="col-sm-6 col-md-3">
            <small class="text-muted d-block mb-1">Expected Delivery</small>
            <span class="fw-semibold">
              <?php
              $dd = $challan['delivery_date'] ?? null;
              echo $dd ? date('d M Y', strtotime($dd)) : '—';
              ?>
            </span>
          </div>
          <div class="col-sm-6 col-md-3">
            <small class="text-muted d-block mb-1">Created By</small>
            <span class="fw-semibold"><?= esc($challan['created_by_name'] ?? '-') ?></span>
          </div>
          <div class="col-sm-6 col-md-3">
            <small class="text-muted d-block mb-1">Created At</small>
            <span class="fw-semibold">
              <?= !empty($challan['created_at']) ? date('d M Y, h:i A', strtotime($challan['created_at'])) : '-' ?>
            </span>
          </div>
        </div>

        <?php if (!empty($challan['notes'])): ?>
          <hr class="my-3">
          <div>
            <small class="text-muted d-block mb-1">Notes</small>
            <p class="mb-0"><?= nl2br(esc($challan['notes'])) ?></p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Line Items Card -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0"><i class="ri-list-check me-2"></i> Line Items</h5>
      </div>
      <div class="card-body p-0">
        <?php if (!empty($lines)): ?>
          <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>Products</th>
                  <th>Processes</th>
                  <th class="text-center" style="width:60px">Qty</th>
                  <th class="text-end" style="width:95px">Weight</th>
                  <th class="text-end" style="width:95px">Gold Wt</th>
                  <th class="text-center" style="width:70px">Purity</th>
                  <th class="text-end" style="width:95px">Rate</th>
                  <th class="text-end" style="width:110px">Amount</th>
                  <th class="text-center" style="width:80px">Image</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($lines as $i => $line): ?>
                  <tr>
                    <!-- Products -->
                    <td>
                      <?php
                      $productName = $line['product_name'] ?? '';
                      $productIds  = $line['product_ids'] ?? [];
                      if (!empty($productName)) {
                        echo esc($productName);
                      } elseif (!empty($productIds) && is_array($productIds)) {
                        echo '<span class="text-muted">IDs: ' . implode(', ', $productIds) . '</span>';
                      } else {
                        echo '<span class="text-muted">—</span>';
                      }
                      ?>
                    </td>
                    <!-- Processes -->
                    <td>
                      <?php
                      $processIds    = $line['process_ids'] ?? [];
                      $processPrices = $line['process_prices'] ?? [];

                      if (!empty($processPrices) && is_array($processPrices)) {
                        foreach ($processPrices as $pp) {
                          $pName = $pp['process_name'] ?? ($pp['name'] ?? ('Process #' . ($pp['process_id'] ?? ($pp['id'] ?? '?'))));
                          $pRate = number_format((float)($pp['rate'] ?? 0), 2);
                          echo '<div class="d-flex justify-content-between">';
                          echo '<span class="small">' . esc($pName) . '</span>';
                          echo '<span class="small text-muted ms-2">₹' . $pRate . '</span>';
                          echo '</div>';
                        }
                      } elseif (!empty($processIds) && is_array($processIds)) {
                        echo '<span class="text-muted">IDs: ' . implode(', ', $processIds) . '</span>';
                      } else {
                        echo '<span class="text-muted">—</span>';
                      }
                      ?>
                    </td>
                    <td class="text-center"><?= (int)($line['quantity'] ?? 1) ?></td>
                    <td class="text-end"><?= number_format((float)($line['weight'] ?? 0), 3) ?> g</td>
                    <td class="text-end">
                      <?php
                      $gw = $line['gold_weight'] ?? null;
                      echo ($gw !== null && $gw !== '') ? number_format((float)$gw, 3) . ' g' : '<span class="text-muted">—</span>';
                      ?>
                    </td>
                    <td class="text-center">
                      <?= !empty($line['gold_purity']) ? esc($line['gold_purity']) : '<span class="text-muted">—</span>' ?>
                    </td>
                    <td class="text-end">₹ <?= number_format((float)($line['rate'] ?? 0), 2) ?></td>
                    <td class="text-end fw-semibold">₹ <?= number_format((float)($line['amount'] ?? 0), 2) ?></td>
                    <td class="text-center">
                      <?php if (!empty($line['image_path'])): ?>
                        <img src="<?= base_url($line['image_path']) ?>" alt="Line Image"
                          class="img-thumbnail line-image-thumb" style="max-height:40px; max-width:50px; cursor:pointer;"
                          data-full-src="<?= base_url($line['image_path']) ?>">
                      <?php else: ?>
                        <span class="text-muted">—</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <?php
                  $goldAdj = (float)($line['gold_adjustment_amount'] ?? 0);
                  if ($goldAdj != 0):
                    $adjWeight = number_format((float)($line['adjusted_gold_weight'] ?? 0), 3);
                    $goldPrice = number_format((float)($line['current_gold_price'] ?? 0), 2);
                    $sign = $goldAdj > 0 ? '+' : '';
                  ?>
                    <tr>
                      <td colspan="9" class="py-1 ps-3 bg-light border-0">
                        <small class="text-muted">
                          <i class="ri-information-line me-1"></i>
                          Gold Adj: Wt Diff = <strong><?= $adjWeight ?>g</strong>
                          × Rate ₹<?= $goldPrice ?>
                          = <strong class="<?= $goldAdj > 0 ? 'text-success' : 'text-danger' ?>">
                            <?= $sign ?>₹<?= number_format($goldAdj, 2) ?>
                          </strong>
                        </small>
                      </td>
                    </tr>
                  <?php endif; ?>
                  <?php if (!empty($line['line_notes'])): ?>
                    <tr>
                      <td colspan="9">
                        <small class="text-muted"><i class="ri-sticky-note-line me-1"></i><?= esc($line['line_notes']) ?></small>
                      </td>
                    </tr>
                  <?php endif; ?>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="p-4 text-center">
            <p class="text-muted mb-0"><i class="ri-information-line me-1"></i> No line items found for this challan.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>

  <!-- ================================================================== -->
  <!-- RIGHT: SIDEBAR -->
  <!-- ================================================================== -->
  <div class="col-xl-4 col-lg-5">

    <!-- Customer Info Card -->
    <div class="card mb-4">
      <div class="card-header">
        <h6 class="mb-0">
          <?php if ($customerType === 'Account'): ?>
            <i class="ri-building-line me-1"></i> Account Customer
          <?php else: ?>
            <i class="ri-user-line me-1"></i> Cash Customer
          <?php endif; ?>
        </h6>
      </div>
      <div class="card-body">
        <div class="d-flex align-items-center mb-3">
          <div class="avatar avatar-sm me-3">
            <span class="avatar-initial rounded-circle bg-label-<?= $customerType === 'Account' ? 'primary' : 'info' ?>">
              <?= strtoupper(substr($customerName, 0, 1)) ?>
            </span>
          </div>
          <div>
            <h6 class="mb-0"><?= esc($customerName) ?></h6>
            <small class="text-muted"><?= esc($customerType) ?> Customer</small>
          </div>
        </div>

        <?php if (!empty($customerMobile)): ?>
          <div class="mb-2">
            <small class="text-muted d-block">Mobile</small>
            <span><i class="ri-phone-line me-1 text-muted"></i><?= esc($customerMobile) ?></span>
          </div>
        <?php endif; ?>

        <?php if ($customerType === 'Account' && !empty($challan['account_gst'])): ?>
          <div class="mb-2">
            <small class="text-muted d-block">GST Number</small>
            <span><?= esc($challan['account_gst']) ?></span>
          </div>
        <?php endif; ?>

        <?php if ($customerType === 'Account'): ?>
          <div class="mt-3">
            <a href="<?= base_url('customers/accounts/' . ($challan['account_id'] ?? '')) ?>" class="btn btn-sm btn-outline-primary w-100">
              <i class="ri-external-link-line me-1"></i> View Account
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Totals Card -->
    <div class="card mb-4">
      <div class="card-header">
        <h6 class="mb-0"><i class="ri-calculator-line me-1"></i> Amount Summary</h6>
      </div>
      <div class="card-body">
        <table class="table table-sm table-borderless mb-0">
          <tbody>
            <tr>
              <td class="text-muted">Total Weight</td>
              <td class="text-end fw-semibold"><?= number_format((float)($challan['total_weight'] ?? 0), 3) ?> g</td>
            </tr>
            <tr class="border-top">
              <td class="fw-bold fs-5">Total</td>
              <td class="text-end fw-bold fs-5 text-primary">₹ <?= number_format((float)($challan['total_amount'] ?? 0), 2) ?></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Status Timeline Card -->
    <div class="card mb-4">
      <div class="card-header">
        <h6 class="mb-0"><i class="ri-route-line me-1"></i> Status Workflow</h6>
      </div>
      <div class="card-body pb-1">
        <?php
        $allStatuses = ['Draft', 'Pending', 'In Progress', 'Completed', 'Invoiced'];
        $currentIdx  = array_search($challanStatus, $allStatuses);
        ?>
        <?php foreach ($allStatuses as $idx => $st): ?>
          <?php
          $isActive  = ($st === $challanStatus);
          $isPast    = ($idx < $currentIdx);
          $isFuture  = ($idx > $currentIdx);

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
      </div>
    </div>

    <!-- Quick Actions Card -->
    <div class="card mb-4">
      <div class="card-body">
        <a href="<?= base_url('challans') ?>" class="btn btn-outline-secondary w-100 mb-2">
          <i class="ri-arrow-left-line me-1"></i> Back to Challans
        </a>
        <?php if ($challanStatus === 'Completed' && !$isInvoiced && ($action_flags['edit'] ?? false)): ?>
          <a href="javascript:void(0);" class="btn btn-primary w-100 btn-change-status" data-status="Invoiced">
            <i class="ri-bill-line me-1"></i> Generate Invoice
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- ================================================================== -->
<!-- DELETE CONFIRMATION MODAL -->
<!-- ================================================================== -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="ri-error-warning-line text-danger me-1"></i> Confirm Delete</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete challan <strong><?= esc($challanNumber) ?></strong>?</p>
        <p class="text-muted small mb-0">This will remove the challan and all its line items.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="btn-confirm-delete">
          <i class="ri-delete-bin-line me-1"></i> Delete
        </button>
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
        <p>Change challan status from <strong><?= esc($challanStatus) ?></strong> to <strong id="new-status-label"></strong>?</p>
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

<!-- Image View Modal -->
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

<?= $this->section('page_js') ?>
<script>
  $(document).ready(function() {

    var challanId = <?= (int)$challanId ?>;
    var baseUrl = '<?= base_url('challans') ?>';

    // =========================================================================
    // DELETE
    // =========================================================================
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

    $('#btn-delete-challan').on('click', function() {
      deleteModal.show();
    });

    $('#btn-confirm-delete').on('click', function() {
      var $btn = $(this);
      $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Deleting...');

      $.ajax({
        url: baseUrl + '/' + challanId,
        type: 'DELETE',
        dataType: 'json',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(res) {
          deleteModal.hide();
          if (res.status === 'success') {
            window.location.href = baseUrl;
          } else {
            alert(res.message || 'Delete failed.');
          }
        },
        error: function(xhr) {
          deleteModal.hide();
          var msg = 'Delete failed.';
          try {
            msg = JSON.parse(xhr.responseText).message || msg;
          } catch (e) {}
          alert(msg);
        },
        complete: function() {
          $btn.prop('disabled', false).html('<i class="ri-delete-bin-line me-1"></i> Delete');
        }
      });
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
        url: baseUrl + '/' + challanId + '/change-status',
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
          if (res.status === 'success' || res.message) {
            // Show toast and reload
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

    // =========================================================================
    // IMAGE MODAL
    // =========================================================================
    $(document).on('click', '.line-image-thumb', function() {
      var fullSrc = $(this).data('full-src');
      if (fullSrc) {
        $('#modalImage').attr('src', fullSrc);
        new bootstrap.Modal(document.getElementById('imageModal')).show();
      }
    });

  });
</script>
<?= $this->endSection() ?>
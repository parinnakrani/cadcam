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
?>
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
  <div class="btn-group">
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

    <!-- Record Payment Button (only if amount due > 0) -->
    <?php if ($invoice['amount_due'] > 0): ?>
      <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
        <i class="ri-bank-card-line"></i> Record Payment
      </button>
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
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" class="text-center text-muted">No line items</td>
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
  </div>
</div>

<!-- Record Payment Modal -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-labelledby="recordPaymentModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="recordPaymentModalLabel">Record Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="recordPaymentForm">
        <div class="modal-body">
          <div class="mb-3">
            <label for="paymentAmount" class="form-label">Payment Amount <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="paymentAmount" name="amount"
              max="<?= $invoice['amount_due'] ?>" step="0.01" required>
            <small class="text-muted">Amount Due: ₹<?= number_format($invoice['amount_due'], 2) ?></small>
          </div>
          <div class="mb-3">
            <label for="paymentDate" class="form-label">Payment Date <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="paymentDate" name="payment_date"
              value="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="mb-3">
            <label for="paymentMethod" class="form-label">Payment Method</label>
            <select class="form-select" id="paymentMethod" name="payment_method">
              <option value="Cash">Cash</option>
              <option value="Bank Transfer">Bank Transfer</option>
              <option value="Cheque">Cheque</option>
              <option value="UPI">UPI</option>
              <option value="Card">Card</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="paymentNotes" class="form-label">Notes</label>
            <textarea class="form-control" id="paymentNotes" name="notes" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Record Payment</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
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

  // Record Payment Form Submission
  $('#recordPaymentForm').on('submit', function(e) {
    e.preventDefault();

    const amount = parseFloat($('#paymentAmount').val());
    const maxAmount = parseFloat(<?= $invoice['amount_due'] ?>);

    if (amount <= 0) {
      alert('Payment amount must be greater than zero');
      return;
    }

    if (amount > maxAmount) {
      alert(`Payment amount cannot exceed amount due (₹${maxAmount.toFixed(2)})`);
      return;
    }

    // Submit payment
    $.ajax({
      url: '<?= base_url("payments/record") ?>',
      type: 'POST',
      data: {
        invoice_id: <?= $invoice['id'] ?>,
        amount: amount,
        payment_date: $('#paymentDate').val(),
        payment_method: $('#paymentMethod').val(),
        notes: $('#paymentNotes').val()
      },
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          alert('Payment recorded successfully!');
          window.location.reload();
        } else {
          alert('Error: ' + (response.error || 'Failed to record payment'));
        }
      },
      error: function(xhr) {
        const response = xhr.responseJSON;
        alert('Error: ' + (response?.error || 'Failed to record payment'));
      }
    });
  });
</script>
<?= $this->endSection() ?>
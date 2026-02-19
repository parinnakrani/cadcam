FILE: app/Views/payments/index.php
================================================================================

<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Payments<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">Payments</li>
  </ol>
</nav>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h3 mb-0">Payments</h1>
  <a href="<?= base_url('payments/create') ?>" class="btn btn-primary">
    <i class="ri-add-circle-line"></i> Record Payment
  </a>
</div>

<!-- Filters -->
<div class="card mb-4">
  <div class="card-header">
    <h5 class="card-title mb-0">
      <i class="ri-filter-3-line"></i> Filters
    </h5>
  </div>
  <div class="card-body">
    <form method="get" action="<?= base_url('payments') ?>" class="row g-3">
      <div class="col-md-3">
        <label class="form-label">Date From</label>
        <input type="date" name="date_from" class="form-control" value="<?= esc($filters['date_from']) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Date To</label>
        <input type="date" name="date_to" class="form-control" value="<?= esc($filters['date_to']) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Customer Type</label>
        <select name="customer_type" class="form-select">
          <option value="">All</option>
          <option value="Account" <?= $filters['customer_type'] == 'Account' ? 'selected' : '' ?>>Account</option>
          <option value="Cash" <?= $filters['customer_type'] == 'Cash' ? 'selected' : '' ?>>Cash</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Payment Mode</label>
        <select name="payment_mode" class="form-select">
          <option value="">All</option>
          <option value="Cash" <?= $filters['payment_mode'] == 'Cash' ? 'selected' : '' ?>>Cash</option>
          <option value="Cheque" <?= $filters['payment_mode'] == 'Cheque' ? 'selected' : '' ?>>Cheque</option>
          <option value="Bank Transfer" <?= $filters['payment_mode'] == 'Bank Transfer' ? 'selected' : '' ?>>Bank Transfer</option>
          <option value="UPI" <?= $filters['payment_mode'] == 'UPI' ? 'selected' : '' ?>>UPI</option>
          <option value="Card" <?= $filters['payment_mode'] == 'Card' ? 'selected' : '' ?>>Card</option>
        </select>
      </div>
      <div class="col-md-10">
        <input type="text" name="search" class="form-control" placeholder="Search by Payment #, Ref #, Cheque #..." value="<?= esc($filters['search']) ?>">
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">Apply</button>
      </div>
    </form>
  </div>
</div>

<!-- Payments Table -->
<div class="card">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover table-striped">
        <thead>
          <tr>
            <th>Payment #</th>
            <th>Date</th>
            <th>Invoice #</th>
            <th>Customer</th>
            <th class="text-end">Amount</th>
            <th>Mode</th>
            <th>Reference</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($payments)): ?>
            <?php foreach ($payments as $payment): ?>
              <tr>
                <td>
                  <a href="<?= base_url('payments/' . $payment['id']) ?>" class="fw-bold text-decoration-none">
                    <?= esc($payment['payment_number']) ?>
                  </a>
                </td>
                <td><?= date('d M Y', strtotime($payment['payment_date'])) ?></td>
                <td>
                  <!-- We assume invoice_number is joined or available. 
                                         Ideally PaymentModel should join with invoices table. 
                                         If not, we show ID or need to fetch it.
                                         The Service fetches it for 'show', but 'index' uses model pagination.
                                         We should rely on model join if possible.
                                         For now, let's assume valid ID or show ID. -->
                  <a href="<?= base_url('invoices/' . $payment['invoice_id']) ?>">
                    #<?= esc($payment['invoice_id']) ?>
                  </a>
                </td>
                <td>
                  <?= esc($payment['customer_type']) ?>
                  <!-- Could show name if joined -->
                </td>
                <td class="text-end fw-bold">₹<?= number_format($payment['payment_amount'], 2) ?></td>
                <td>
                  <span class="badge bg-secondary"><?= esc($payment['payment_mode']) ?></span>
                </td>
                <td>
                  <?= esc($payment['transaction_reference'] ?: $payment['cheque_number'] ?: '-') ?>
                </td>
                <td class="text-center">
                  <div class="btn-group btn-group-sm">
                    <a href="<?= base_url('payments/' . $payment['id']) ?>" class="btn btn-outline-primary" title="View">
                      <i class="ri-eye-line"></i>
                    </a>
                    <button type="button" class="btn btn-outline-danger" onclick="deletePayment(<?= $payment['id'] ?>)" title="Delete">
                      <i class="ri-delete-bin-line"></i>
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center text-muted py-4">
                No payments found.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>


  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
  function deletePayment(id) {
    if (confirm('Are you sure you want to delete this payment? This will update the invoice balance.')) {
      $.ajax({
        url: '<?= base_url('payments') ?>/' + id,
        type: 'DELETE',
        success: function(res) {
          if (res.status === 'success') {
            location.reload();
          } else {
            alert(res.message);
          }
        },
        error: function() {
          alert('Failed to delete payment.');
        }
      });
    }
  }
</script>
<?= $this->endSection() ?>

================================================================================
END OF FILE
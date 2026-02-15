<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="page-title-box d-sm-flex align-items-center justify-content-between">
        <h4 class="mb-sm-0">Payment Collection Summary</h4>
        <div class="page-title-right">
          <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item active">Payment Report</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter -->
  <div class="row mb-3">
    <div class="col-12">
      <form method="get" action="<?= current_url() ?>" class="row gx-3 gy-2 align-items-center">
        <div class="col-sm-3">
          <label class="visually-hidden" for="from_date">From Date</label>
          <input type="date" class="form-control" id="from_date" name="from_date" value="<?= esc($fromDate) ?>" placeholder="From Date">
        </div>
        <div class="col-sm-3">
          <label class="visually-hidden" for="to_date">To Date</label>
          <input type="date" class="form-control" id="to_date" name="to_date" value="<?= esc($toDate) ?>" placeholder="To Date">
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-primary">Filter</button>
          <a href="<?= current_url() ?>" class="btn btn-light">Reset</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Summary Cards -->
  <div class="row mb-3">
    <div class="col-md-3">
      <div class="card bg-primary text-white">
        <div class="card-body">
          <h5 class="card-title text-white">Total Collected</h5>
          <h3 class="card-text text-white"><?= number_format($totalCollected, 2) ?></h3>
        </div>
      </div>
    </div>

    <?php foreach ($modeBreakdown as $mode => $amount): ?>
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title text-muted"><?= ucfirst($mode) ?></h5>
            <h4 class="card-text text-success"><?= number_format($amount, 2) ?></h4>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            <table id="payments-table" class="table table-bordered table-striped dt-responsive nowrap w-100">
              <thead class="table-light">
                <tr>
                  <th>Date</th>
                  <th>Customer Name</th>
                  <th>Type</th>
                  <th>Ref ID</th>
                  <th>Mode</th>
                  <th class="text-end">Amount Paid</th>
                  <th>Received By</th>
                  <th>Notes</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($payments)): ?>
                  <tr>
                    <td colspan="8" class="text-center">No payments found for this period.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($payments as $payment): ?>
                    <tr>
                      <td><?= esc(date('d-M-Y', strtotime($payment['payment_date']))) ?></td>
                      <td>
                        <?php if ($payment['account_id']): ?>
                          <span class="badge bg-info">ACC</span>
                          <a href="<?= base_url('ledgers/account/' . $payment['account_id']) ?>">
                            <?= esc($payment['account_name']) ?>
                          </a>
                        <?php else: ?>
                          <span class="badge bg-warning text-dark">CASH</span>
                          <a href="<?= base_url('ledgers/cash-customer/' . $payment['cash_customer_id']) ?>">
                            <?= esc($payment['cash_customer_name']) ?>
                          </a>
                        <?php endif; ?>
                      </td>
                      <td><?= ucfirst($payment['payment_type']) ?></td>
                      <td><?= esc($payment['reference_id'] ?: '-') ?></td>
                      <td><?= ucfirst($payment['payment_mode']) ?></td>
                      <td class="text-end fw-bold text-success">
                        <?= number_format($payment['payment_amount'], 2) ?>
                      </td>
                      <td><?= esc($payment['created_by_user_id'] ?? 'System') ?></td> <!-- Use updated logic if username join is needed -->
                      <td><?= esc($payment['notes']) ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
              <tfoot class="table-light fw-bold">
                <tr>
                  <td colspan="5" class="text-end">Total</td>
                  <td class="text-end">
                    <?= number_format($totalCollected, 2) ?>
                  </td>
                  <td colspan="2"></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('page_js') ?>
<script>
  $(document).ready(function() {
    $('#payments-table').DataTable({
      order: [
        [0, 'desc']
      ],
      pageLength: 25,
      lengthMenu: [
        [10, 25, 50, -1],
        [10, 25, 50, "All"]
      ]
    });
  });
</script>
<?= $this->endSection() ?>
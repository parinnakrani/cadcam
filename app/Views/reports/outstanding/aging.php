<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="page-title-box d-sm-flex align-items-center justify-content-between">
        <h4 class="mb-sm-0"><?= esc($title) ?></h4>
        <div class="page-title-right">
          <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item active">Aging Report</li>
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
          <label class="visually-hidden" for="to_date">To Date (As Of)</label>
          <input type="date" class="form-control" id="to_date" name="to_date" value="<?= esc($toDate) ?>" placeholder="To Date">
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-primary">Filter</button>
          <!-- Future: Export Button -->
        </div>
      </form>
    </div>
  </div>

  <!-- Summary Cards -->
  <div class="row">
    <?php foreach ($buckets as $key => $data): ?>
      <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-grow-1 overflow-hidden">
                <p class="text-uppercase fw-medium text-muted text-truncate mb-0"><?= esc($key) ?> Days</p>
              </div>
            </div>
            <div class="d-flex align-items-end justify-content-between mt-4">
              <div>
                <h4 class="fs-22 fw-semibold ff-secondary mb-4">₹ <?= number_format($data['amount'], 2) ?></h4>
                <span class="badge bg-danger-subtle text-danger fs-12"><?= $data['count'] ?> Invoices</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Detailed Aging List -->
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title mb-0">Detailed Aging Breakdown</h4>
        </div>
        <div class="card-body">
          <div class="accordion" id="agingAccordion">
            <?php foreach ($buckets as $key => $data): ?>
              <div class="accordion-item">
                <h2 class="accordion-header" id="heading<?= md5($key) ?>">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= md5($key) ?>" aria-expanded="false" aria-controls="collapse<?= md5($key) ?>">
                    <div class="d-flex w-100 justify-content-between me-3">
                      <span><strong><?= esc($key) ?> Days Overdue</strong></span>
                      <span class="badge bg-secondary rounded-pill"><?= $data['count'] ?> Invoices | ₹ <?= number_format($data['amount'], 2) ?></span>
                    </div>
                  </button>
                </h2>
                <div id="collapse<?= md5($key) ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= md5($key) ?>" data-bs-parent="#agingAccordion">
                  <div class="accordion-body">
                    <?php if (empty($data['invoices'])): ?>
                      <p class="text-muted mb-0">No invoices in this range.</p>
                    <?php else: ?>
                      <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                          <thead>
                            <tr>
                              <th>Invoice #</th>
                              <th>Customer</th>
                              <th>Due Date</th>
                              <th class="text-center">Days Overdue</th>
                              <th class="text-end">Amount Due</th>
                              <th class="text-center">Action</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($data['invoices'] as $inv): ?>
                              <tr>
                                <td><a href="<?= base_url('invoices/view/' . $inv['id']) ?>"><?= esc($inv['invoice_number']) ?></a></td>
                                <td><?= esc($inv['customer_name']) ?></td>
                                <td><?= esc($inv['due_date']) ?></td>
                                <td class="text-center">
                                  <span class="badge bg-<?= ($inv['days_overdue'] > 30) ? 'danger' : 'warning' ?>">
                                    <?= $inv['days_overdue'] ?> Days
                                  </span>
                                </td>
                                <td class="text-end fw-bold"><?= number_format($inv['amount_due'], 2) ?></td>
                                <td class="text-center">
                                  <a href="<?= base_url('ledgers/reminders/send/' . $inv['id']) ?>" class="btn btn-xs btn-outline-primary" onclick="return confirm('Send reminder?')">
                                    <i class="fas fa-bell"></i>
                                  </a>
                                </td>
                              </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
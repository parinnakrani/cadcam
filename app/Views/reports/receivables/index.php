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
            <li class="breadcrumb-item active">Ledger Balances</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Customer Balances</h5>
          <div>
            <a href="<?= base_url('reports/receivables/monthly') ?>" class="btn btn-sm btn-outline-primary">
              <i class="bx bx-calendar"></i> Monthly View
            </a>
            <a href="<?= base_url('reports/outstanding') ?>" class="btn btn-sm btn-outline-danger">
              <i class="bx bx-error"></i> Outstanding Invoices
            </a>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table id="ledger-summary-table" class="table table-bordered table-striped dt-responsive nowrap w-100">
              <thead class="table-light">
                <tr>
                  <th>Customer Name</th>
                  <th>Type</th>
                  <th>Mobile</th>
                  <th class="text-end">Current Balance</th>
                  <th class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($customers as $cust): ?>
                  <tr>
                    <td>
                      <div class="fw-bold"><?= esc($cust['name']) ?></div>
                    </td>
                    <td><span class="badge bg-secondary"><?= esc($cust['type']) ?></span></td>
                    <td><?= esc($cust['mobile']) ?></td>
                    <td class="text-end fw-bold <?= ($cust['balance'] > 0) ? 'text-danger' : 'text-success' ?>">
                      <?= number_format($cust['balance'], 2) ?> <?= ($cust['balance'] > 0) ? 'Dr' : 'Cr' ?>
                    </td>
                    <td class="text-center">
                      <?php
                      // Determine ledger type URL segment
                      $ledgerType = ($cust['type'] === 'Account') ? 'account' : 'cash-customer';
                      ?>
                      <a href="<?= base_url('ledgers/' . $ledgerType . '/' . $cust['id']) ?>" class="btn btn-sm btn-soft-info" title="View Ledger">
                        <i class="bx bx-file"></i> View Ledger
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
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
    $('#ledger-summary-table').DataTable({
      order: [
        [3, 'desc']
      ], // Sort by Balance descending
      pageLength: 25
    });
  });
</script>
<?= $this->endSection() ?>
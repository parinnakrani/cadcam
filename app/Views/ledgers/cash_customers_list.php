<?= $this->extend('admintheme/layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="page-title-box d-sm-flex align-items-center justify-content-between">
        <h4 class="mb-sm-0">Cash Customer Ledgers</h4>
        <div class="page-title-right">
          <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item active">Cash Customers</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <table id="datatable" class="table table-bordered dt-responsive nowrap w-100">
            <thead>
              <tr>
                <th>Customer Name</th>
                <th>Mobile</th>
                <th>Transactions</th>
                <th class="text-end">Current Balance</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($cashCustomers as $customer): ?>
                <tr>
                  <td><?= esc($customer['customer_name']) ?></td>
                  <td><?= esc($customer['mobile_number']) ?></td>
                  <!-- Assume transaction count or last activity if needed, placeholder -->
                  <td>-</td>
                  <td class="text-end">
                    <?php
                    $balance = $customer['current_balance'] ?? 0.00; // Handle missing column gracefully if migration lagged
                    ?>
                    <?php if ($balance > 0): ?>
                      <span class="text-danger">Dr <?= number_format($balance, 2) ?></span>
                    <?php elseif ($balance < 0): ?>
                      <span class="text-success">Cr <?= number_format(abs($balance), 2) ?></span>
                    <?php else: ?>
                      0.00
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <a href="<?= base_url('ledgers/cash-customer/' . $customer['id']) ?>" class="btn btn-sm btn-primary">
                      <i class="fas fa-eye"></i> View Ledger
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
<?= $this->endSection() ?>

<?= $this->section('page_js') ?>
<script>
  $(document).ready(function() {
    $('#datatable').DataTable();
  });
</script>
<?= $this->endSection() ?>
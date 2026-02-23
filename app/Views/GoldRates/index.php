<?= $this->extend('Layouts/main') ?>

<?= $this->section('title') ?>Gold Rates<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row g-4 mb-4">
    <!-- Alerts -->
    <?php if (!empty($alerts)): ?>
      <div class="col-12">
        <?php foreach ($alerts as $alert): ?>
          <div class="alert alert-danger alert-dismissible" role="alert">
            <h6 class="alert-heading d-flex align-items-center mb-1">
              <i class="bx bx-error-circle me-2"></i>Action Required
            </h6>
            <p class="mb-0"><?= esc($alert) ?></p>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Statistics / Today's Rates -->
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2 pb-1">
            <div class="avatar me-2">
              <span class="avatar-initial rounded bg-label-warning"><i class="bx bxs-coin-stack"></i></span>
            </div>
            <h4 class="ms-1 mb-0">22K Gold</h4>
          </div>
          <?php if ($isEntered22K): ?>
            <!-- Retrieve actual rate for display? Controller passed bool only. -->
            <p class="mb-1">Rate Entered</p>
            <span class="badge bg-label-success">Updated</span>
          <?php else: ?>
            <p class="mb-1 text-danger">Not Entered</p>
            <?php if ($action_flags['create'] ?? false): ?>
              <a href="<?= base_url('masters/gold-rates/create?metal=22K') ?>" class="btn btn-sm btn-primary">Add Rate</a>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2 pb-1">
            <div class="avatar me-2">
              <span class="avatar-initial rounded bg-label-warning"><i class="bx bxs-coin-stack"></i></span>
            </div>
            <h4 class="ms-1 mb-0">24K Gold</h4>
          </div>
          <?php if ($isEntered24K): ?>
            <p class="mb-1">Rate Entered</p>
            <span class="badge bg-label-success">Updated</span>
          <?php else: ?>
            <p class="mb-1 text-danger">Not Entered</p>
            <?php if ($action_flags['create'] ?? false): ?>
              <a href="<?= base_url('masters/gold-rates/create?metal=24K') ?>" class="btn btn-sm btn-primary">Add Rate</a>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2 pb-1">
            <div class="avatar me-2">
              <span class="avatar-initial rounded bg-label-secondary"><i class="bx bxs-coin-stack"></i></span>
            </div>
            <h4 class="ms-1 mb-0">Silver</h4>
          </div>
          <?php if ($isEnteredSilver): ?>
            <p class="mb-1">Rate Entered</p>
            <span class="badge bg-label-success">Updated</span>
          <?php else: ?>
            <p class="mb-1 text-danger">Not Entered</p>
            <?php if ($action_flags['create'] ?? false): ?>
              <a href="<?= base_url('masters/gold-rates/create?metal=Silver') ?>" class="btn btn-sm btn-primary">Add Rate</a>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Rate History Table -->
  <div class="card">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">Rate History (Last 30 Days)</h5>
      <div class="d-flex gap-2">
        <a href="<?= base_url('masters/gold-rates/history') ?>" class="btn btn-outline-secondary btn-sm">
          <i class="bx bx-line-chart me-1"></i> View Full History
        </a>
        <?php if ($action_flags['create'] ?? false): ?>
          <a href="<?= base_url('masters/gold-rates/create') ?>" class="btn btn-primary btn-sm">
            <i class="bx bx-plus me-1"></i> Add New Rate
          </a>
        <?php endif; ?>
      </div>
    </div>
    <div class="card-datatable table-responsive">
      <table class="datatables-gold-rates table border-top">
        <thead>
          <tr>
            <th>Date</th>
            <th>Metal Type</th>
            <th>Rate (per gram)</th>
            <th>Updated At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($history as $rate): ?>
            <tr>
              <td><?= date('d M Y', strtotime($rate['rate_date'])) ?></td>
              <td>
                <?php
                $badgeClass = match ($rate['metal_type']) {
                  '22K' => 'bg-label-warning',
                  '24K' => 'bg-label-warning',
                  'Silver' => 'bg-label-secondary',
                  default => 'bg-label-primary'
                };
                ?>
                <span class="badge <?= $badgeClass ?>"><?= esc($rate['metal_type']) ?></span>
              </td>
              <td>₹ <?= number_format($rate['rate_per_gram'], 2) ?></td>
              <td><?= $rate['updated_at'] ? date('d M Y H:i', strtotime($rate['updated_at'])) : '-' ?></td>
              <td>
                <?php if ($action_flags['edit'] ?? false): ?>
                  <a href="<?= base_url('masters/gold-rates/edit/' . $rate['id']) ?>" class="btn btn-sm btn-icon item-edit">
                    <i class="bx bxs-edit"></i>
                  </a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
  // Simple DataTable initialization
  $(document).ready(function() {
    $('.datatables-gold-rates').DataTable({
      order: [
        [0, 'desc']
      ],
      dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      displayLength: 10,
      lengthMenu: [10, 25, 50, 75, 100]
    });
  });
</script>
<?= $this->endSection() ?>
<?= $this->extend('Layouts/main') ?>

<?= $this->section('title') ?>My Deliveries<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h4>My Dashboard</h4>
<div class="row mb-4">
  <div class="col-sm-6 col-xl-4">
    <div class="card bg-label-primary">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar">
            <div class="avatar-initial bg-primary rounded shadow">
              <i class="ri-shopping-bag-3-line ri-24px"></i>
            </div>
          </div>
          <div class="ms-3">
            <div class="mb-1">Assigned (New)</div>
            <h5 class="mb-0 text-primary"><?= $dashboard['assigned'] ?></h5>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-4">
    <div class="card bg-label-warning">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar">
            <div class="avatar-initial bg-warning rounded shadow">
              <i class="ri-truck-line ri-24px"></i>
            </div>
          </div>
          <div class="ms-3">
            <div class="mb-1">In Transit</div>
            <h5 class="mb-0 text-warning"><?= $dashboard['in_transit'] ?></h5>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-4">
    <div class="card bg-label-success">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar">
            <div class="avatar-initial bg-success rounded shadow">
              <i class="ri-checkbox-circle-line ri-24px"></i>
            </div>
          </div>
          <div class="ms-3">
            <div class="mb-1">Delivered Today</div>
            <h5 class="mb-0 text-success"><?= $dashboard['delivered_today'] ?></h5>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="nav-align-top mb-4">
  <ul class="nav nav-tabs nav-fill" role="tablist">
    <li class="nav-item">
      <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-active" aria-controls="navs-active" aria-selected="true">
        <i class="ri-truck-line me-1"></i> Active Deliveries
      </button>
    </li>
    <li class="nav-item">
      <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-history" aria-controls="navs-history" aria-selected="false">
        <i class="ri-history-line me-1"></i> History
      </button>
    </li>
  </ul>
  <div class="tab-content">
    <div class="tab-pane fade show active" id="navs-active" role="tabpanel">
      <?php if (empty($active)): ?>
        <div class="text-center p-4">No active deliveries.</div>
      <?php else: ?>
        <div class="row">
          <?php foreach ($active as $d): ?>
            <div class="col-md-6 mb-3">
              <div class="card h-100 border-left-<?= $d['delivery_status'] == 'Assigned' ? 'info' : 'warning' ?>">
                <div class="card-body">
                  <h5 class="card-title"><?= $d['delivery_contact_name'] ?></h5>
                  <h6 class="card-subtitle mb-2 text-muted">
                    <span class="badge <?= $d['delivery_status'] == 'Assigned' ? 'bg-label-info' : 'bg-label-warning' ?>">
                      <?= $d['delivery_status'] ?>
                    </span>
                  </h6>
                  <p class="card-text">
                    <strong>Address:</strong> <?= $d['delivery_address'] ?><br>
                    <strong>Phone:</strong> <a href="tel:<?= $d['customer_contact_mobile'] ?>"><?= $d['customer_contact_mobile'] ?></a>
                  </p>
                  <div class="d-grid gap-2">
                    <a href="<?= base_url('deliveries/' . $d['id']) ?>" class="btn btn-primary">View Details & Action</a>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
    <div class="tab-pane fade" id="navs-history" role="tabpanel">
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Customer</th>
              <th>Status</th>
              <th>Proof</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($history as $d): ?>
              <tr>
                <td><?= date('d M', strtotime($d['updated_at'])) ?></td>
                <td><?= $d['delivery_contact_name'] ?></td>
                <td>
                  <span class="badge <?= $d['delivery_status'] == 'Delivered' ? 'bg-label-success' : 'bg-label-danger' ?>">
                    <?= $d['delivery_status'] ?>
                  </span>
                </td>
                <td>
                  <?php if ($d['delivery_proof_photo']): ?>
                    <?php
                    $url = $d['delivery_proof_photo'];
                    if (!str_starts_with($url, 'uploads/')) $url = 'uploads/delivery_proofs/' . $url;
                    ?>
                    <button class="btn btn-xs btn-outline-primary view-proof-btn" data-url="<?= base_url($url) ?>">View</button>
                  <?php else: ?>
                    -
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<!-- Image Modal -->
<div class="modal fade" id="proofModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Proof of Delivery</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <img id="proofImage" src="" class="img-fluid rounded" alt="Proof">
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('page_js') ?>
<script>
  $(document).ready(function() {
    // Handle Proof Modal
    $(document).on('click', '.view-proof-btn', function() {
      var url = $(this).data('url');
      $('#proofImage').attr('src', url);
      $('#proofModal').modal('show');
    });
  });
</script>
<?= $this->endSection() ?>
<?= $this->extend('Layouts/main') ?>

<?= $this->section('title') ?>Delivery Details<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
  <div class="col-md-6">
    <div class="card mb-4">
      <h5 class="card-header">Delivery Info</h5>
      <div class="card-body">
        <div class="row mb-2">
          <div class="col-4 fw-bold">Status:</div>
          <div class="col-8">
            <span class="badge bg-label-<?= $delivery['delivery_status'] == 'Assigned' ? 'info' : ($delivery['delivery_status'] == 'In Transit' ? 'warning' : ($delivery['delivery_status'] == 'Delivered' ? 'success' : 'danger')) ?>">
              <?= $delivery['delivery_status'] ?>
            </span>
          </div>
        </div>
        <div class="row mb-2">
          <div class="col-4 fw-bold">Customer:</div>
          <div class="col-8"><?= $delivery['delivery_contact_name'] ?></div>
        </div>
        <div class="row mb-2">
          <div class="col-4 fw-bold">Phone:</div>
          <div class="col-8"><a href="tel:<?= $delivery['customer_contact_mobile'] ?>"><?= $delivery['customer_contact_mobile'] ?></a></div>
        </div>
        <div class="row mb-2">
          <div class="col-4 fw-bold">Address:</div>
          <div class="col-8"><?= nl2br($delivery['delivery_address']) ?></div>
        </div>
        <div class="row mb-2">
          <div class="col-4 fw-bold">Exp Date:</div>
          <div class="col-8"><?= date('d M Y', strtotime($delivery['expected_delivery_date'])) ?></div>
        </div>
        <?php if ($delivery['delivered_timestamp']): ?>
          <div class="row mb-2">
            <div class="col-4 fw-bold">Delivered At:</div>
            <div class="col-8"><?= date('d M Y H:i A', strtotime($delivery['delivered_timestamp'])) ?></div>
          </div>
        <?php endif; ?>
        <?php if ($delivery['delivery_notes']): ?>
          <div class="row mb-2">
            <div class="col-4 fw-bold">Notes:</div>
            <div class="col-8"><?= $delivery['delivery_notes'] ?></div>
          </div>
        <?php endif; ?>

        <?php if ($delivery['delivery_proof_photo']): ?>
          <div class="mt-3">
            <h6>Proof of Delivery:</h6>
            <?php
            $photoUrl = $delivery['delivery_proof_photo'];
            if (!str_starts_with($photoUrl, 'uploads/')) {
              $photoUrl = 'uploads/delivery_proofs/' . $photoUrl;
            }
            ?>
            <img src="<?= base_url($photoUrl) ?>" class="img-fluid rounded" style="max-height: 200px;">
          </div>
        <?php endif; ?>

        <div class="mt-4">
          <!-- Actions -->
          <?php if ($delivery['delivery_status'] == 'Assigned'): ?>
            <button class="btn btn-warning w-100" id="btnStart">
              <i class="ri-truck-line me-1"></i> Start Delivery
            </button>
          <?php elseif ($delivery['delivery_status'] == 'In Transit'): ?>
            <button class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#completeModal">
              <i class="ri-check-double-line me-1"></i> Mark Delivered
            </button>
            <button class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#failModal">
              <i class="ri-close-circle-line me-1"></i> Mark Failed
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card mb-4">
      <h5 class="card-header">Invoice Items (<?= $invoice['invoice_number'] ?>)</h5>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm">
            <thead>
              <tr>
                <th>Item</th>
                <th>Qty/Wt</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($invoice['lines'] as $line): ?>
                <tr>
                  <td>
                    <?php
                    $names = [];
                    foreach ($line['products'] as $p) $names[] = $p['product_name'];
                    echo implode(', ', $names);
                    ?>
                  </td>
                  <td>
                    <?php if ($line['weight'] > 0): ?>
                      <?= $line['weight'] ?> g
                    <?php else: ?>
                      <?= $line['quantity'] ?> pcs
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="mt-3">
          <strong>Total Amount:</strong> ₹<?= $invoice['grand_total'] ?> (Paid)
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Complete Modal -->
<div class="modal fade" id="completeModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" id="completeForm">
      <div class="modal-header">
        <h5 class="modal-title">Complete Delivery</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Proof Photo (Required)</label>
          <input type="file" name="proof_photo" class="form-control" accept="image/*" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Upload & Complete</button>
      </div>
    </form>
  </div>
</div>

<!-- Fail Modal -->
<div class="modal fade" id="failModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" id="failForm">
      <div class="modal-header">
        <h5 class="modal-title">Mark Failed</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Reason</label>
          <textarea name="reason" class="form-control" required placeholder="e.g., Customer not available"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-danger">Confirm Failure</button>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('page_js') ?>
<script>
  $(document).ready(function() {
    $('#btnStart').click(function() {
      if (!confirm('Start delivery now?')) return;
      $.post('<?= base_url("deliveries/{$delivery['id']}/start") ?>', {
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
      }, function(res) {
        if (res.status === 'success') location.reload();
        else alert(res.message);
      });
    });

    $('#completeForm').submit(function(e) {
      e.preventDefault();
      var formData = new FormData(this);
      formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>'); // Append valid token

      $.ajax({
        url: '<?= base_url("deliveries/{$delivery['id']}/complete") ?>',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(res) {
          if (res.status === 'success') location.reload();
          else alert(res.message);
        }
      });
    });

    $('#failForm').submit(function(e) {
      e.preventDefault();
      $.post('<?= base_url("deliveries/{$delivery['id']}/fail") ?>', $(this).serialize() + '&<?= csrf_token() ?>=<?= csrf_hash() ?>', function(res) {
        if (res.status === 'success') location.reload();
        else alert(res.message);
      });
    });
  });
</script>
<?= $this->endSection() ?>
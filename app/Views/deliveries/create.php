<?= $this->extend('Layouts/main') ?>

<?= $this->section('title') ?>Assign Delivery<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
  <div class="col-md-8 mx-auto">
    <div class="card mb-4">
      <h5 class="card-header">Assign New Delivery</h5>
      <div class="card-body">
        <?php if (session()->getFlashdata('error')): ?>
          <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <form action="<?= base_url('deliveries') ?>" method="POST">
          <?= csrf_field() ?>

          <div class="mb-3">
            <label class="form-label">Select Invoice (Paid & Unassigned)</label>
            <select name="invoice_id" class="form-select" required>
              <option value="">-- Select Invoice --</option>
              <?php foreach ($invoices as $inv): ?>
                <option value="<?= $inv['id'] ?>">
                  <?= $inv['invoice_number'] ?>
                  (<?= date('d M', strtotime($inv['invoice_date'])) ?>) -
                  <?= $inv['shipping_address'] ? 'Has Shipping Addr' : 'Billing Addr' ?>
                </option>
              <?php endforeach; ?>
            </select>
            <?php if (empty($invoices)): ?>
              <div class="form-text text-danger">No deliverable invoices found.</div>
            <?php endif; ?>
          </div>

          <div class="mb-3">
            <label class="form-label">Assign To</label>
            <select name="assigned_to" class="form-select" required>
              <option value="">-- Select Personnel --</option>
              <?php foreach ($users as $user): ?>
                <option value="<?= $user['id'] ?>"><?= $user['full_name'] ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Expected Delivery Date</label>
            <input type="date" name="expected_delivery_date" class="form-control"
              value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Notes (Optional)</label>
            <textarea name="notes" class="form-control" rows="3"></textarea>
          </div>

          <div class="d-flex justify-content-end">
            <a href="<?= base_url('deliveries') ?>" class="btn btn-outline-secondary me-2">Cancel</a>
            <button type="submit" class="btn btn-primary" <?= empty($invoices) ? 'disabled' : '' ?>>Assign Delivery</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
<?= $this->extend('Layouts/main') ?>

<?= $this->section('title') ?>Edit Gold Rate<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row">
    <div class="col-md-6 offset-md-3">
      <div class="card mb-4">
        <h5 class="card-header">Edit Gold Rate</h5>
        <div class="card-body">
          <form action="<?= base_url('masters/gold-rates/update/' . $rate['id']) ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="_method" value="PUT">

            <!-- Rate Date (Usually read-only for history integrity, but allowing edit if needed) -->
            <div class="mb-3">
              <label for="rate_date" class="form-label">Rate Date</label>
              <input type="date" class="form-control" id="rate_date" name="rate_date"
                value="<?= old('rate_date', $rate['rate_date']) ?>" readonly>
              <small class="text-muted">Date cannot be changed once entered.</small>
            </div>

            <!-- Metal Type is hidden -->
            <input type="hidden" name="metal_type" value="<?= esc($rate['metal_type']) ?>">

            <!-- Rate Per Gram -->
            <div class="mb-3">
              <label for="rate_per_gram" class="form-label">Rate Per Gram (₹)</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text">₹</span>
                <input type="number" step="0.01" min="1" class="form-control" id="rate_per_gram" name="rate_per_gram"
                  value="<?= old('rate_per_gram', $rate['rate_per_gram']) ?>" required>
              </div>
              <?php if (session('errors.rate_per_gram')): ?>
                <div class="text-danger"><?= session('errors.rate_per_gram') ?></div>
              <?php endif; ?>
            </div>

            <div class="mt-4">
              <button type="submit" class="btn btn-primary me-2">Update Rate</button>
              <a href="<?= base_url('masters/gold-rates') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
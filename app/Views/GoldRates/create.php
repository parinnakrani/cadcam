<?= $this->extend('Layouts/main') ?>

<?= $this->section('title') ?>Enter Gold Rate<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row">
    <div class="col-md-6 offset-md-3">
      <div class="card mb-4">
        <h5 class="card-header">Enter Today's Gold Rate</h5>
        <div class="card-body">
          <form action="<?= base_url('masters/gold-rates/store') ?>" method="POST">
            <?= csrf_field() ?>

            <!-- Rate Date -->
            <div class="mb-3">
              <label for="rate_date" class="form-label">Rate Date</label>
              <input type="date" class="form-control" id="rate_date" name="rate_date"
                value="<?= old('rate_date', $today) ?>" required>
              <?php if (session('errors.rate_date')): ?>
                <div class="text-danger"><?= session('errors.rate_date') ?></div>
              <?php endif; ?>
            </div>

            <!-- Metal Type -->
            <div class="mb-3">
              <label for="metal_type" class="form-label">Metal Type</label>
              <?php
              // On first load: use $selectedMetal (from ?metal= query param)
              // After validation failure: old('metal_type') takes priority
              $currentMetal = old('metal_type', $selectedMetal ?? '22K');
              ?>
              <select class="form-select" id="metal_type" name="metal_type" required>
                <option value="22K" <?= $currentMetal === '22K'    ? 'selected' : '' ?>>22K Gold</option>
                <option value="24K" <?= $currentMetal === '24K'    ? 'selected' : '' ?>>24K Gold</option>
                <option value="Silver" <?= $currentMetal === 'Silver' ? 'selected' : '' ?>>Silver</option>
              </select>
              <?php if (session('errors.metal_type')): ?>
                <div class="text-danger"><?= session('errors.metal_type') ?></div>
              <?php endif; ?>
            </div>

            <!-- Rate Per Gram -->
            <div class="mb-3">
              <label for="rate_per_gram" class="form-label">Rate Per Gram (₹)</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text">₹</span>
                <input type="number" step="0.01" min="1" class="form-control" id="rate_per_gram" name="rate_per_gram"
                  placeholder="Enter rate (e.g., 6500.50)" value="<?= old('rate_per_gram') ?>" required>
              </div>
              <?php if (session('errors.rate_per_gram')): ?>
                <div class="text-danger"><?= session('errors.rate_per_gram') ?></div>
              <?php endif; ?>
            </div>

            <div class="mt-4">
              <button type="submit" class="btn btn-primary me-2">Save Rate</button>
              <a href="<?= base_url('masters/gold-rates') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
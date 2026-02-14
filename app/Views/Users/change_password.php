<?= $this->extend('Layouts/main') ?>

<?= $this->section('title') ?>Change Password<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Change Password</h5>
    <a href="<?= base_url('users') ?>" class="btn btn-secondary">
      <i class="ri-arrow-left-line me-1"></i> Back
    </a>
  </div>
  <div class="card-body">
    <?php if (session()->getFlashdata('error')) : ?>
      <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif ?>
    <?php if (session()->getFlashdata('errors')) : ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach (session()->getFlashdata('errors') as $error) : ?>
            <li><?= esc($error) ?></li>
          <?php endforeach ?>
        </ul>
      </div>
    <?php endif ?>

    <form action="<?= base_url('users/' . $userId . '/password') ?>" method="POST">
      <?= csrf_field() ?>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Current Password <span class="text-danger">*</span></label>
          <input type="password" name="current_password" class="form-control" required>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">New Password <span class="text-danger">*</span></label>
          <input type="password" name="new_password" class="form-control" required minlength="8">
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
          <input type="password" name="confirm_password" class="form-control" required minlength="8">
        </div>

        <div class="col-12 mt-4">
          <button type="submit" class="btn btn-primary">Update Password</button>
        </div>
      </div>
    </form>
  </div>
</div>
<?= $this->endSection() ?>

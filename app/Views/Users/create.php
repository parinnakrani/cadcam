<?= $this->extend('Layouts/main') ?>

<?= $this->section('title') ?>Create User<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Create New User</h5>
    <a href="<?= base_url('users') ?>" class="btn btn-secondary">
      <i class="ri-arrow-left-line me-1"></i> Back
    </a>
  </div>
  <div class="card-body">
    <!-- Flash Messages -->
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

    <form action="<?= base_url('users/create') ?>" method="POST">
      <?= csrf_field() ?>

      <div class="row">
        <!-- Personal Info -->
        <h6 class="mb-3">Personal Information</h6>
        
        <div class="col-md-6 mb-3">
          <label class="form-label">Full Name <span class="text-danger">*</span></label>
          <input type="text" name="full_name" class="form-control" value="<?= old('full_name') ?>" required>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Username <span class="text-danger">*</span></label>
          <input type="text" name="username" class="form-control" value="<?= old('username') ?>" required>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Email <span class="text-danger">*</span></label>
          <input type="email" name="email" class="form-control" value="<?= old('email') ?>" required>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
          <input type="text" name="mobile_number" class="form-control" value="<?= old('mobile_number') ?>" required pattern="[0-9]{10}" title="10 digit mobile number">
        </div>

        <!-- Security -->
        <h6 class="mb-3 mt-4">Security</h6>

        <div class="col-md-6 mb-3">
          <label class="form-label">Password <span class="text-danger">*</span></label>
          <input type="password" name="password" class="form-control" required minlength="8">
          <small class="text-muted">Min 8 chars, mixed case & special char recommended.</small>
        </div>

        <!-- Roles -->
        <h6 class="mb-3 mt-4">Roles</h6>
        
        <div class="col-12 mb-3">
          <?php if (!empty($roles)) : ?>
            <div class="d-flex flex-wrap gap-3">
              <?php foreach ($roles as $role) : ?>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="role_ids[]" value="<?= $role['id'] ?>" id="role_<?= $role['id'] ?>" <?= (in_array($role['id'], old('role_ids', []))) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="role_<?= $role['id'] ?>">
                    <?= esc($role['role_name']) ?>
                  </label>
                </div>
              <?php endforeach ?>
            </div>
          <?php else : ?>
            <p class="text-muted">No roles available. create roles first.</p>
          <?php endif ?>
        </div>

        <div class="col-12 mt-4">
          <button type="submit" class="btn btn-primary">Create User</button>
        </div>
      </div>
    </form>
  </div>
</div>
<?= $this->endSection() ?>

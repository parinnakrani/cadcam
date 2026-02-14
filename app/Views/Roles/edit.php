<?= $this->extend('Layouts/main') ?>

<?= $this->section('title') ?>Edit Role<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Edit Role: <?= esc($role['role_name']) ?></h5>
    <a href="<?= base_url('roles') ?>" class="btn btn-secondary">
      <i class="ri-arrow-left-line me-1"></i> Back
    </a>
  </div>
  <div class="card-body">
    <!-- Errors -->
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

    <form action="<?= base_url('roles/' . $role['id']) ?>" method="POST">
      <?= csrf_field() ?>
      <input type="hidden" name="_method" value="POST"> <!-- Consistent update method -->

      <div class="row">
        <!-- Basic Info -->
        <div class="col-md-6 mb-3">
          <label class="form-label">Role Name <span class="text-danger">*</span></label>
          <input type="text" name="role_name" class="form-control" value="<?= old('role_name', $role['role_name']) ?>" required>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Description</label>
          <textarea name="role_description" class="form-control" rows="1"><?= old('role_description', $role['role_description']) ?></textarea>
        </div>

        <!-- Permissions -->
        <div class="col-12 mt-4">
          <h6 class="mb-3">Permissions</h6>
          <div class="row">
            <?php foreach ($permissions as $module => $modulePerms) : ?>
              <div class="col-md-4 mb-4">
                <div class="card shadow-none border">
                  <div class="card-header bg-light py-2">
                    <h6 class="mb-0 text-uppercase small fw-bold"><?= esc($module) ?></h6>
                  </div>
                  <div class="card-body pt-3 pb-1">
                    <?php foreach ($modulePerms as $key => $label) : ?>
                      <div class="form-check mb-2">
                        <input class="form-check-input permission-check" type="checkbox" name="permissions[]" value="<?= $key ?>" id="perm_<?= str_replace('.', '_', $key) ?>"
                          <?= in_array($key, old('permissions', $rolePermissions)) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="perm_<?= str_replace('.', '_', $key) ?>">
                          <?= esc($label) ?>
                        </label>
                      </div>
                    <?php endforeach ?>
                  </div>
                </div>
              </div>
            <?php endforeach ?>
          </div>
        </div>

        <div class="col-12 mt-4">
          <button type="submit" class="btn btn-primary">Update Role</button>
        </div>
      </div>
    </form>
  </div>
</div>
<?= $this->endSection() ?>

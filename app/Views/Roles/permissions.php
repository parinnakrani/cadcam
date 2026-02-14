<?= $this->extend('Layouts/main') ?>

<?= $this->section('title') ?>Manage Permissions<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <div>
      <h5 class="mb-0">Manage Permissions</h5>
      <small class="text-muted">Role: <strong><?= esc($role['role_name']) ?></strong></small>
    </div>
    <a href="<?= base_url('roles') ?>" class="btn btn-secondary">
      <i class="ri-arrow-left-line me-1"></i> Back
    </a>
  </div>
  <div class="card-body">
    <!-- Errors -->
    <?php if (session()->getFlashdata('message')) : ?>
      <div class="alert alert-success"><?= session()->getFlashdata('message') ?></div>
    <?php endif ?>
    <?php if (session()->getFlashdata('error')) : ?>
      <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif ?>

    <form action="<?= base_url('roles/' . $role['id'] . '/permissions') ?>" method="POST">
      <?= csrf_field() ?>

      <div class="row">
        <!-- Permissions -->
        <div class="col-12">
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
          <button type="submit" class="btn btn-primary">Save Permissions</button>
        </div>
      </div>
    </form>
  </div>
</div>
<?= $this->endSection() ?>

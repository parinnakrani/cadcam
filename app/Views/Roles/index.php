<?= $this->extend('Layouts/main') ?>

<?= $this->section('title') ?>Roles List<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Roles List</h5>
    <a href="<?= base_url('roles/create') ?>" class="btn btn-primary">
      <i class="ri-shield-keyhole-line me-1"></i> Create Role
    </a>
  </div>
  <div class="card-body">
    <?php if (session()->getFlashdata('message')) : ?>
      <div class="alert alert-success"><?= session()->getFlashdata('message') ?></div>
    <?php endif ?>
    <?php if (session()->getFlashdata('error')) : ?>
      <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif ?>

    <div class="table-responsive text-nowrap">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>ID</th>
            <th>Role Name</th>
            <th>Description</th>
            <th>Type</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($roles)) : ?>
            <?php foreach ($roles as $role) : ?>
              <tr>
                <td><?= $role['id'] ?></td>
                <td><strong><?= esc($role['role_name']) ?></strong></td>
                <td><?= esc($role['role_description']) ?></td>
                <td>
                  <?php if ($role['is_system_role']) : ?>
                    <span class="badge bg-label-primary">System</span>
                  <?php else : ?>
                    <span class="badge bg-label-success">Custom</span>
                  <?php endif ?>
                </td>
                <td>
                  <div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ri-more-2-line"></i></button>
                    <div class="dropdown-menu">
                      <a class="dropdown-item" href="<?= base_url('roles/' . $role['id'] . '/permissions') ?>">
                        <i class="ri-shield-star-line me-2"></i> Permissions
                      </a>
                      
                      <?php if (!$role['is_system_role']) : ?>
                        <a class="dropdown-item" href="<?= base_url('roles/' . $role['id'] . '/edit') ?>">
                          <i class="ri-pencil-line me-2"></i> Edit
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" href="<?= base_url('roles/' . $role['id'] . '/delete') ?>" onclick="return confirm('Are you sure you want to delete this role?');">
                          <i class="ri-delete-bin-line me-2"></i> Delete
                        </a>
                      <?php else : ?>
                        <a class="dropdown-item disabled text-muted" href="#">
                           <i class="ri-lock-line me-2"></i> System Locked
                        </a>
                      <?php endif ?>
                    </div>
                  </div>
                </td>
              </tr>
            <?php endforeach ?>
          <?php else : ?>
            <tr>
              <td colspan="5" class="text-center">No roles found.</td>
            </tr>
          <?php endif ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->extend('Layouts/main') ?>

<?= $this->section('title') ?>User List<?= $this->endSection() ?>

<?= $this->section('vendorStyles') ?>
<link rel="stylesheet" href="<?= base_url('admintheme/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') ?>" />
<link rel="stylesheet" href="<?= base_url('admintheme/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') ?>" />
<link rel="stylesheet" href="<?= base_url('admintheme/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') ?>" />
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Stats Cards Row -->
<div class="row g-6 mb-6">
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between">
          <div class="me-1">
            <p class="text-heading mb-1">Session</p>
            <div class="d-flex align-items-center">
              <h4 class="mb-1 me-2" id="stat-total">0</h4>
            </div>
            <small class="mb-0">Total Users</small>
          </div>
          <div class="avatar">
            <div class="avatar-initial bg-label-primary rounded-3">
              <div class="ri-group-line ri-26px"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between">
          <div class="me-1">
            <p class="text-heading mb-1">Active Users</p>
            <div class="d-flex align-items-center">
              <h4 class="mb-1 me-1" id="stat-active">0</h4>
            </div>
            <small class="mb-0">Currently Active</small>
          </div>
          <div class="avatar">
            <div class="avatar-initial bg-label-success rounded-3">
              <div class="ri-user-follow-line ri-26px"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between">
          <div class="me-1">
            <p class="text-heading mb-1">Pending Users</p>
            <div class="d-flex align-items-center">
              <h4 class="mb-1 me-1" id="stat-pending">0</h4>
            </div>
            <small class="mb-0">Awaiting Approval</small>
          </div>
          <div class="avatar">
            <div class="avatar-initial bg-label-warning rounded-3">
              <div class="ri-user-search-line ri-26px"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between">
          <div class="me-1">
            <p class="text-heading mb-1">Inactive Users</p>
            <div class="d-flex align-items-center">
              <h4 class="mb-1 me-1" id="stat-inactive">0</h4>
            </div>
            <small class="mb-0">Terminated/Inactive</small>
          </div>
          <div class="avatar">
            <div class="avatar-initial bg-label-danger rounded-3">
              <div class="ri-user-unfollow-line ri-26px"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Users List Table -->
<div class="card">
  <div class="card-header border-bottom">
    <h5 class="card-title mb-0">Filters</h5>
    <div class="d-flex justify-content-between align-items-center row gx-5 pt-4 gap-5 gap-md-0">
      <div class="col-md-4 user_role">
        <select id="UserRole" class="form-select text-capitalize">
          <option value="">Select Role</option>
        </select>
      </div>
      <div class="col-md-4 user_status">
        <select id="UserStatus" class="form-select text-capitalize">
          <option value="">Select Status</option>
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
          <option value="Pending">Pending</option>
          <option value="Terminated">Terminated</option>
        </select>
      </div>
      <div class="col-md-4"></div>
    </div>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-users table">
      <thead class="table-light">
        <tr>
          <th>User</th>
          <th>Role</th>
          <th>Status</th>
          <th>Mobile</th>
          <th>Actions</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('vendorScripts') ?>
<script src="<?= base_url('admintheme/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') ?>"></script>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>var baseUrl = '<?= base_url() ?>';</script>
<script src="<?= base_url('assets/js/app-user-list.js') ?>"></script>
<?= $this->endSection() ?>

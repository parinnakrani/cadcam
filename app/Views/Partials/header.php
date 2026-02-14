<nav
  class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
  id="layout-navbar">
  <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
    <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
      <i class="ri-menu-fill ri-22px"></i>
    </a>
  </div>

  <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
    <!-- Search -->
    <!-- <div class="navbar-nav align-items-center">
        <div class="nav-item navbar-search-wrapper mb-0">
            <a class="nav-item nav-link search-toggler fw-normal px-0" href="javascript:void(0);">
            <i class="ri-search-line ri-22px scaleX-n1-rtl me-3"></i>
            <span class="d-none d-md-inline-block text-muted">Search (Ctrl+/)</span>
            </a>
        </div>
        </div> -->
    <!-- /Search -->

    <ul class="navbar-nav flex-row align-items-center ms-auto">

      <!-- Company Switcher -->
      <?php
      $companyModel = new \App\Models\CompanyModel();
      $companies = $companyModel->where('is_deleted', 0)->findAll();
      $currentCompanyId = session()->get('company_id') ?? 1;
      $currentCompany = $companyModel->find($currentCompanyId);
      ?>
      <li class="nav-item dropdown-style-switcher dropdown me-2 me-xl-0">
        <a class="nav-link btn btn-text-secondary rounded-pill btn-icon dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
          <?php if ($currentCompany): ?>
            <span class="fw-bold"><?= esc(substr($currentCompany['company_name'], 0, 3)) ?></span>
          <?php else: ?>
            <i class="ri-building-line ri-22px"></i>
          <?php endif; ?>
        </a>
        <ul class="dropdown-menu dropdown-menu-end dropdown-styles">
          <?php foreach ($companies as $company): ?>
            <li>
              <a class="dropdown-item" href="<?= base_url('switch-company/' . $company['id']) ?>">
                <span class="align-middle"><?= esc($company['company_name']) ?></span>
                <?php if ($company['id'] == $currentCompanyId): ?>
                  <i class="ri-check-line ri-16px ms-auto text-primary"></i>
                <?php endif; ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </li>
      <!-- / Company Switcher -->

      <!-- Style Switcher -->
      <li class="nav-item dropdown-style-switcher dropdown me-1 me-xl-0">
        <a
          class="nav-link btn btn-text-secondary rounded-pill btn-icon dropdown-toggle hide-arrow"
          href="javascript:void(0);"
          data-bs-toggle="dropdown">
          <i class="ri-22px"></i>
        </a>
        <ul class="dropdown-menu dropdown-menu-end dropdown-styles">
          <li>
            <a class="dropdown-item" href="javascript:void(0);" data-theme="light">
              <span class="align-middle"><i class="ri-sun-line ri-22px me-3"></i>Light</span>
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="javascript:void(0);" data-theme="dark">
              <span class="align-middle"><i class="ri-moon-clear-line ri-22px me-3"></i>Dark</span>
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="javascript:void(0);" data-theme="system">
              <span class="align-middle"><i class="ri-computer-line ri-22px me-3"></i>System</span>
            </a>
          </li>
        </ul>
      </li>
      <!-- / Style Switcher-->

      <!-- User -->
      <li class="nav-item navbar-dropdown dropdown-user dropdown">
        <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
          <div class="avatar avatar-online">
            <img src="<?= base_url('admintheme/assets/img/avatars/1.png') ?>" alt class="rounded-circle" />
          </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li>
            <a class="dropdown-item" href="javascript:void(0);">
              <div class="d-flex">
                <div class="flex-shrink-0 me-2">
                  <div class="avatar avatar-online">
                    <img src="<?= base_url('admintheme/assets/img/avatars/1.png') ?>" alt class="rounded-circle" />
                  </div>
                </div>
                <div class="flex-grow-1">
                  <span class="fw-medium d-block small"><?= session('full_name') ?? 'Guest' ?></span>
                  <small class="text-muted"><?= session('is_super_admin') ? 'Super Admin' : 'User' ?></small>
                </div>
              </div>
            </a>
          </li>
          <li>
            <div class="dropdown-divider"></div>
          </li>
          <li>
            <a class="dropdown-item" href="<?= base_url('profile') ?>">
              <i class="ri-user-3-line ri-22px me-3"></i><span class="align-middle">My Profile</span>
            </a>
          </li>
          <li>
            <div class="dropdown-divider"></div>
          </li>
          <li>
            <div class="d-grid px-4 pt-2 pb-1">
              <a class="btn btn-sm btn-danger d-flex" href="<?= base_url('logout') ?>">
                <small class="align-middle">Logout</small>
                <i class="ri-logout-box-r-line ms-2 ri-16px"></i>
              </a>
            </div>
          </li>
        </ul>
      </li>
      <!--/ User -->
    </ul>
  </div>
</nav>
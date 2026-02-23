<?= $this->extend('Layouts/main') ?>

<?= $this->section('title') ?>Create Role<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Create New Role</h5>
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

    <form action="<?= base_url('roles/create') ?>" method="POST">
      <?= csrf_field() ?>

      <div class="row">
        <!-- Basic Info -->
        <div class="col-md-6 mb-3">
          <label class="form-label">Role Name <span class="text-danger">*</span></label>
          <input type="text" name="role_name" class="form-control" value="<?= old('role_name') ?>" required>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Description</label>
          <textarea name="role_description" class="form-control" rows="1"><?= old('role_description') ?></textarea>
        </div>

        <!-- Permissions -->
        <div class="col-12 mt-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Permissions</h6>
            <div>
              <button type="button" class="btn btn-sm btn-outline-primary" id="selectAll">Select All</button>
              <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAll">Deselect All</button>
            </div>
          </div>

          <div class="accordion" id="permissionsAccordion">
            <?php
            $oldPermissions = old('permissions', []);
            $moduleIndex = 0;
            foreach ($permissions as $module => $subModules) :
              $moduleId = 'module_' . preg_replace('/[^a-zA-Z0-9]/', '_', $module);
              $moduleLabel = ucwords(str_replace('_', ' ', $module));
              $moduleIndex++;
            ?>
              <div class="accordion-item">
                <h2 class="accordion-header" id="heading_<?= $moduleId ?>">
                  <button class="accordion-button collapsed" type="button"
                    data-bs-toggle="collapse" data-bs-target="#collapse_<?= $moduleId ?>"
                    aria-expanded="false" aria-controls="collapse_<?= $moduleId ?>">
                    <div class="d-flex align-items-center w-100">
                      <div class="form-check me-3" onclick="event.stopPropagation();">
                        <input class="form-check-input module-check" type="checkbox"
                          id="check_<?= $moduleId ?>"
                          data-module="<?= $moduleId ?>">
                      </div>
                      <strong><?= esc($moduleLabel) ?></strong>
                      <span class="badge bg-label-primary ms-2 module-count" data-module="<?= $moduleId ?>">0 / 0</span>
                    </div>
                  </button>
                </h2>
                <div id="collapse_<?= $moduleId ?>" class="accordion-collapse collapse"
                  aria-labelledby="heading_<?= $moduleId ?>" data-bs-parent="#permissionsAccordion">
                  <div class="accordion-body">
                    <?php foreach ($subModules as $subModule => $actions) :
                      $subId = $moduleId . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $subModule);
                      $subLabel = ucwords(str_replace('_', ' ', $subModule));
                    ?>
                      <div class="card shadow-none border mb-3">
                        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                          <div class="form-check mb-0">
                            <input class="form-check-input submodule-check" type="checkbox"
                              id="check_<?= $subId ?>"
                              data-module="<?= $moduleId ?>"
                              data-submodule="<?= $subId ?>">
                            <label class="form-check-label fw-semibold" for="check_<?= $subId ?>">
                              <?= esc($subLabel) ?>
                            </label>
                          </div>
                          <span class="badge bg-label-info sub-count" data-submodule="<?= $subId ?>">0 / <?= count($actions) ?></span>
                        </div>
                        <div class="card-body pt-3 pb-1">
                          <div class="row">
                            <?php foreach ($actions as $actionData) : ?>
                              <div class="col-md-3 col-sm-4 col-6 mb-2">
                                <div class="form-check">
                                  <input class="form-check-input permission-check" type="checkbox"
                                    name="permissions[]"
                                    value="<?= $actionData['permission'] ?>"
                                    id="perm_<?= str_replace('.', '_', $actionData['permission']) ?>"
                                    data-module="<?= $moduleId ?>"
                                    data-submodule="<?= $subId ?>"
                                    <?= in_array($actionData['permission'], $oldPermissions) ? 'checked' : '' ?>>
                                  <label class="form-check-label" for="perm_<?= str_replace('.', '_', $actionData['permission']) ?>">
                                    <?= esc(ucwords(str_replace('_', ' ', $actionData['action']))) ?>
                                  </label>
                                </div>
                              </div>
                            <?php endforeach ?>
                          </div>
                        </div>
                      </div>
                    <?php endforeach ?>
                  </div>
                </div>
              </div>
            <?php endforeach ?>
          </div>
        </div>

        <div class="col-12 mt-4">
          <button type="submit" class="btn btn-primary">Create Role</button>
        </div>
      </div>
    </form>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const allPermChecks = document.querySelectorAll('.permission-check');
    const allSubChecks = document.querySelectorAll('.submodule-check');
    const allModChecks = document.querySelectorAll('.module-check');

    // ── Update badge counts ──
    function updateCounts() {
      // Sub-module counts
      allSubChecks.forEach(subCheck => {
        const subId = subCheck.dataset.submodule;
        const perms = document.querySelectorAll(`.permission-check[data-submodule="${subId}"]`);
        const checked = document.querySelectorAll(`.permission-check[data-submodule="${subId}"]:checked`);
        const badge = document.querySelector(`.sub-count[data-submodule="${subId}"]`);
        if (badge) badge.textContent = `${checked.length} / ${perms.length}`;
        subCheck.checked = perms.length > 0 && checked.length === perms.length;
        subCheck.indeterminate = checked.length > 0 && checked.length < perms.length;
      });

      // Module counts
      allModChecks.forEach(modCheck => {
        const modId = modCheck.dataset.module;
        const perms = document.querySelectorAll(`.permission-check[data-module="${modId}"]`);
        const checked = document.querySelectorAll(`.permission-check[data-module="${modId}"]:checked`);
        const badge = document.querySelector(`.module-count[data-module="${modId}"]`);
        if (badge) badge.textContent = `${checked.length} / ${perms.length}`;
        modCheck.checked = perms.length > 0 && checked.length === perms.length;
        modCheck.indeterminate = checked.length > 0 && checked.length < perms.length;
      });
    }

    // ── Individual permission change ──
    allPermChecks.forEach(check => {
      check.addEventListener('change', updateCounts);
    });

    // ── Sub-module toggle ──
    allSubChecks.forEach(subCheck => {
      subCheck.addEventListener('change', function() {
        const subId = this.dataset.submodule;
        document.querySelectorAll(`.permission-check[data-submodule="${subId}"]`).forEach(p => {
          p.checked = subCheck.checked;
        });
        updateCounts();
      });
    });

    // ── Module toggle ──
    allModChecks.forEach(modCheck => {
      modCheck.addEventListener('change', function() {
        const modId = this.dataset.module;
        document.querySelectorAll(`.permission-check[data-module="${modId}"]`).forEach(p => {
          p.checked = modCheck.checked;
        });
        updateCounts();
      });
    });

    // ── Select All / Deselect All ──
    document.getElementById('selectAll')?.addEventListener('click', function() {
      allPermChecks.forEach(p => p.checked = true);
      updateCounts();
    });

    document.getElementById('deselectAll')?.addEventListener('click', function() {
      allPermChecks.forEach(p => p.checked = false);
      updateCounts();
    });

    // Initial count
    updateCounts();
  });
</script>
<?= $this->endSection() ?>
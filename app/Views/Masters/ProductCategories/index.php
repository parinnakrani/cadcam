<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="fw-bold py-3 mb-4">
    <span class="text-muted fw-light">Masters /</span> Product Categories
  </h4>

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Product Categories</h5>
      <?php if ($action_flags['create'] ?? false): ?>
        <a href="<?= base_url('masters/product-categories/create') ?>" class="btn btn-primary">
          <i class="ri-add-circle-line me-1"></i> Add New Category
        </a>
      <?php endif; ?>
    </div>

    <div class="card-body">
      <!-- Filter -->
      <div class="row mb-4">
        <div class="col-md-3">
          <select id="statusFilter" class="form-select">
            <option value="">All Status</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
          </select>
        </div>
      </div>

      <div class="table-responsive text-nowrap">
        <table class="table table-hover" id="dataTable">
          <thead>
            <tr>
              <th>Display Order</th>
              <th>Code</th>
              <th>Name</th>
              <th>Description</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (isset($categories) && !empty($categories)): ?>
              <?php foreach ($categories as $category): ?>
                <tr>
                  <td><?= esc($category['display_order']) ?></td>
                  <td><strong><?= esc($category['category_code']) ?></strong></td>
                  <td><?= esc($category['category_name']) ?></td>
                  <td><?= esc($category['description']) ?></td>
                  <td>
                    <?php if ($category['is_active']): ?>
                      <span class="badge bg-label-success">Active</span>
                    <?php else: ?>
                      <span class="badge bg-label-secondary">Inactive</span>
                    <?php endif; ?>
                    <!-- Hidden text for DataTable filtering -->
                    <span class="d-none"><?= $category['is_active'] ? 'Active' : 'Inactive' ?></span>
                  </td>
                  <td>
                    <div class="dropdown">
                      <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="ri-more-2-line"></i>
                      </button>
                      <div class="dropdown-menu">
                        <?php if ($action_flags['edit'] ?? false): ?>
                          <a class="dropdown-item" href="<?= base_url('masters/product-categories/edit/' . $category['id']) ?>">
                            <i class="ri-pencil-line me-1"></i> Edit
                          </a>
                        <?php endif; ?>
                        <?php if ($action_flags['delete'] ?? false): ?>
                          <a class="dropdown-item text-danger delete-record"
                            href="javascript:void(0);"
                            data-id="<?= $category['id'] ?>"
                            data-url="<?= base_url('masters/product-categories/delete/' . $category['id']) ?>">
                            <i class="ri-delete-bin-line me-1"></i> Delete
                          </a>
                        <?php endif; ?>
                      </div>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalCenterTitle">Delete Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete this category? This action cannot be undone properly if linked data exists.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('page_scripts') ?>
<script>
  $(document).ready(function() {
    // Initialize DataTable
    var table = $('#dataTable').DataTable({
      "order": [
        [0, "asc"]
      ], // Order by Display Order by default
      "pageLength": 10,
      "language": {
        "search": "Search Categories:"
      }
    });

    // Status Filter
    $('#statusFilter').on('change', function() {
      var status = $(this).val();
      table.column(4).search(status).draw();
    });

    // Delete Logic
    var deleteUrl = '';
    $('.delete-record').on('click', function() {
      deleteUrl = $(this).data('url');
      $('#deleteModal').modal('show');
    });

    $('#confirmDeleteBtn').on('click', function() {
      if (deleteUrl) {
        // AJAX Delete
        $.ajax({
          url: deleteUrl,
          type: 'DELETE',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          },
          success: function(response) {
            $('#deleteModal').modal('hide');
            if (response.status === 'success') {
              // Show toast or reload
              location.reload(); // Simple reload to refresh table
            } else {
              alert(response.message || 'Error deleting record');
            }
          },
          error: function(xhr) {
            $('#deleteModal').modal('hide');
            var msg = 'An error occurred';
            if (xhr.responseJSON && xhr.responseJSON.message) {
              msg = xhr.responseJSON.message;
            }
            alert(msg);
          }
        });
      }
    });
  });
</script>
<?= $this->endSection() ?>
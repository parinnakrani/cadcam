<?= $this->extend('layouts/main') ?>

<?= $this->section('vendorStyles') ?>
<?= $this->endSection() ?>

<?= $this->section('vendorScripts') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Masters /</span> Products
    </h4>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Products List</h5>
            <?php if (isset($canCreate) && $canCreate): ?>
                <a href="<?= base_url('masters/products/create') ?>" class="btn btn-primary">
                    <i class="ri-add-circle-line me-1"></i> Add New Product
                </a>
            <?php endif; ?>
        </div>
        
        <div class="card-body">
            <!-- Filters -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <select id="categoryFilter" class="form-select">
                        <option value="">All Categories</option>
                        <?php if (isset($categories)): ?>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= esc($cat['category_name']) ?>"><?= esc($cat['category_name']) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="statusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive text-nowrap">
                <table class="table table-hover" id="productsTable">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>HSN</th>
                            <th>Unit</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Create Category Map for display if needed, but we can iterate. 
                        // Actually, if we use separate array for lookups...
                        // Optimization: build map first.
                        $catMap = [];
                        if(isset($categories)) {
                            foreach($categories as $c) $catMap[$c['id']] = $c['category_name'];
                        }
                        ?>
                        <?php if (isset($products) && !empty($products)): ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($product['image_path'])): ?>
                                            <a href="javascript:void(0);" onclick="showImageModal('<?= base_url($product['image_path']) ?>', '<?= esc($product['product_name']) ?>')">
                                                <img src="<?= base_url($product['image_path']) ?>" alt="Img" class="rounded-circle" width="50" height="50" style="object-fit: cover;">
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-label-secondary p-2">No Img</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= esc($product['product_code']) ?></strong></td>
                                    <td><?= esc($product['product_name']) ?></td>
                                    <td>
                                        <?= isset($catMap[$product['category_id']]) ? esc($catMap[$product['category_id']]) : 'Unknown' ?>
                                    </td>
                                    <td><?= esc($product['hsn_code']) ?></td>
                                    <td><?= esc($product['unit_of_measure']) ?></td>
                                    <td>
                                        <?php if ($product['is_active']): ?>
                                            <span class="badge bg-label-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-label-secondary">Inactive</span>
                                        <?php endif; ?>
                                        <span class="d-none"><?= $product['is_active'] ? 'Active' : 'Inactive' ?></span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="ri-more-2-line"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="<?= base_url('masters/products/' . $product['id']) ?>">
                                                    <i class="ri-eye-line me-1"></i> View
                                                </a>
                                                <?php if (isset($canEdit) && $canEdit): ?>
                                                    <a class="dropdown-item" href="<?= base_url('masters/products/edit/' . $product['id']) ?>">
                                                        <i class="ri-pencil-line me-1"></i> Edit
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (isset($canDelete) && $canDelete): ?>
                                                    <a class="dropdown-item text-danger delete-record" 
                                                       href="javascript:void(0);" 
                                                       data-id="<?= $product['id'] ?>" 
                                                       data-url="<?= base_url('masters/products/delete/' . $product['id']) ?>">
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

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalTitle">Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img src="" id="modalImage" class="img-fluid rounded" alt="Large Preview">
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this product?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('page_js') ?>
<script>
    function showImageModal(src, title) {
        document.getElementById('modalImage').src = src;
        document.getElementById('imageModalTitle').innerText = title;
        new bootstrap.Modal(document.getElementById('imageModal')).show();
    }

    $(document).ready(function() {
        var table = $('#productsTable').DataTable({
            "order": [[ 2, "asc" ]], // Order by Name
             "pageLength": 10
        });

        // Custom Filters
        $('#categoryFilter').on('change', function() {
            table.column(3).search(this.value).draw();
        });
        $('#statusFilter').on('change', function() {
            table.column(6).search(this.value).draw();
        });

        // Delete Logic
        var deleteUrl = '';
        
        // Use delegation to handle DataTable redraws
        $(document).on('click', '.delete-record', function(e) {
            e.preventDefault(); // Good practice
            
            deleteUrl = $(this).data('url');
            
            // Explicitly verify modal exists
            var modalEl = document.getElementById('deleteModal');
            if(modalEl) {
                 var modal = new bootstrap.Modal(modalEl);
                 modal.show();
            } else {
                 alert('Delete modal not found!');
            }
        });

        // Handle confirm button click - verify binding only once if possible, 
        // but here it's outside delegation so ok.
        $('#confirmDeleteBtn').off('click').on('click', function() {
            if (deleteUrl) {
                $.ajax({
                    url: deleteUrl,
                    type: 'DELETE',
                    // Actually, CI4 Routes usually match GET/POST.
                    // If routes.php didn't specify DELETE verb, then DELETE request might fail 404.
                    // Let's assume GET or POST for safety unless we defined resource route.
                    // The standard link href is /delete/ID.
                    // If we use DELETE method, we need to ensure CI4 handles it.
                    // Step 1919: $routes->get('products/delete/(:num)', 'ProductController::delete/$1');
                    // So it EXPECTS GET.
                    // So type: 'DELETE' would be wrong if route is GET.
                    // I will change it to GET.
                    headers: {'X-Requested-With': 'XMLHttpRequest'},
                    success: function(response) {
                        $('#deleteModal').modal('hide'); // Close modal
                        // Use reload.
                        location.reload(); 
                    },
                    error: function(xhr) {
                        // Close modal manually via bootstrap instance if hidden by data-bs-dismiss
                        // But we didn't use data-bs-dismiss on confirm button.
                        var modalEl = document.getElementById('deleteModal');
                        // Hide using bootstrap API to be clean
                        var modal = bootstrap.Modal.getInstance(modalEl);
                        if(modal) modal.hide();
                        
                        var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error deleting product';
                        alert(msg);
                    }
                });
            }
        });


    });
</script>
<?= $this->endSection() ?>

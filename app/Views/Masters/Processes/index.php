<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Masters /</span> Processes
    </h4>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Manufacturing Processes</h5>
            <?php if (isset($canCreate) && $canCreate): ?>
                <a href="<?= base_url('masters/processes/create') ?>" class="btn btn-primary">
                    <i class="ri-add-circle-line me-1"></i> Add New Process
                </a>
            <?php endif; ?>
        </div>
        
        <div class="card-body">
            <!-- Filter -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <label class="form-label">Process Type</label>
                    <select id="typeFilter" class="form-select" onchange="window.location.href='?process_type='+this.value">
                        <option value="">All Types</option>
                        <?php 
                        $currentType = service('request')->getGet('process_type');
                        if (isset($processTypes)): 
                            foreach ($processTypes as $t): ?>
                                <option value="<?= $t ?>" <?= ($currentType == $t) ? 'selected' : '' ?>><?= $t ?></option>
                            <?php endforeach; 
                        endif; ?>
                    </select>
                </div>
            </div>

            <div class="table-responsive text-nowrap">
                <table class="table table-hover" id="processesTable">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Process Name</th>
                            <th>Type</th>
                            <th>Rate</th>
                            <th>Unit</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $colors = [
                            'Rhodium' => 'primary',
                            'Meena'   => 'success', 
                            'Wax'     => 'warning',
                            'Polish'  => 'info',
                            'Coating' => 'dark',
                            'Other'   => 'secondary'
                        ];
                        ?>
                        <?php if (isset($processes) && !empty($processes)): ?>
                            <?php foreach ($processes as $p): ?>
                                <tr>
                                    <td><strong><?= esc($p['process_code']) ?></strong></td>
                                    <td><?= esc($p['process_name']) ?></td>
                                    <td>
                                        <?php 
                                        $color = $colors[$p['process_type']] ?? 'secondary'; 
                                        ?>
                                        <span class="badge bg-label-<?= $color ?>"><?= esc($p['process_type']) ?></span>
                                    </td>
                                    <td>
                                        <span class="fw-bold">₹ <?= number_format($p['rate_per_unit'], 2) ?></span>
                                    </td>
                                    <td><?= esc($p['unit_of_measure']) ?></td>
                                    <td>
                                        <?php if ($p['is_active']): ?>
                                            <span class="badge bg-label-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-label-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="ri-more-2-line"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="<?= base_url('masters/processes/' . $p['id']) ?>">
                                                    <i class="ri-eye-line me-1"></i> View
                                                </a>
                                                <?php if (isset($canEdit) && $canEdit): ?>
                                                    <a class="dropdown-item" href="<?= base_url('masters/processes/edit/' . $p['id']) ?>">
                                                        <i class="ri-pencil-line me-1"></i> Edit
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (isset($canDelete) && $canDelete): ?>
                                                    <a class="dropdown-item text-danger delete-record" 
                                                       href="javascript:void(0);" 
                                                       data-id="<?= $p['id'] ?>" 
                                                       data-url="<?= base_url('masters/processes/delete/' . $p['id']) ?>">
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
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Process</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this process?</p>
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
    $(document).ready(function() {
        $('#processesTable').DataTable({
            "order": [[ 1, "asc" ]],
            "pageLength": 10
        });

        // Delete Logic
        var deleteUrl = '';
        $('.delete-record').on('click', function() {
            deleteUrl = $(this).data('url');
            $('#deleteModal').modal('show');
        });

        $('#confirmDeleteBtn').on('click', function() {
            if (deleteUrl) {
                $.ajax({
                    url: deleteUrl,
                    type: 'DELETE',
                    headers: {'X-Requested-With': 'XMLHttpRequest'},
                    success: function(response) {
                        $('#deleteModal').modal('hide');
                        location.reload(); 
                    },
                    error: function(xhr) {
                        $('#deleteModal').modal('hide');
                        var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error';
                        alert(msg);
                    }
                });
            }
        });
    });
</script>
<?= $this->endSection() ?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Masters / Processes /</span> Details
    </h4>

    <div class="row">
        <!-- Process Details -->
        <div class="col-md-8 mx-auto mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Information</h5>
                    <div>
                         <?php if (isset($process['company_id'])): ?>
                            <span class="badge bg-label-primary">Company ID: <?= $process['company_id'] ?></span>
                         <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <label class="col-sm-3 fw-bold">Status:</label>
                        <div class="col-sm-9">
                             <?php if ($process['is_active']): ?>
                                <span class="badge bg-label-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-label-secondary">Inactive</span>
                            <?php endif; ?>
                        </div>
                    </div>

                     <div class="row mb-3">
                        <label class="col-sm-3 fw-bold">Process Name:</label>
                        <div class="col-sm-9">
                            <?= esc($process['process_name']) ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 fw-bold">Code:</label>
                        <div class="col-sm-9 font-monospace">
                            <?= esc($process['process_code']) ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 fw-bold">Type:</label>
                        <div class="col-sm-9">
                            <?php 
                            $colors = [
                                'Rhodium' => 'primary',
                                'Meena'   => 'success', 
                                'Wax'     => 'warning',
                                'Polish'  => 'info',
                                'Coating' => 'dark',
                                'Other'   => 'secondary'
                            ];
                            $color = $colors[$process['process_type']] ?? 'secondary';
                            ?>
                            <span class="badge bg-label-<?= $color ?>"><?= esc($process['process_type']) ?></span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 fw-bold">Rate:</label>
                        <div class="col-sm-9 text-primary fw-bold">
                            ₹ <?= number_format($process['rate_per_unit'], 2) ?>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <label class="col-sm-3 fw-bold">Unit:</label>
                        <div class="col-sm-9">
                            <?= esc($process['unit_of_measure']) ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 fw-bold">Description:</label>
                        <div class="col-sm-9 text-wrap">
                            <?= nl2br(esc($process['description'] ?: 'No Description')) ?>
                        </div>
                    </div>

                     <div class="mt-4 text-end">
                        <a href="<?= base_url('masters/processes') ?>" class="btn btn-outline-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Back
                        </a>
                        <a href="<?= base_url('masters/processes/edit/' . $process['id']) ?>" class="btn btn-primary ms-2">
                             <i class="ri-pencil-line me-1"></i> Edit
                        </a>
                     </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

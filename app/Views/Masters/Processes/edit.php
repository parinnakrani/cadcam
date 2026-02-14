="<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-xl">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit Process</h5>
                <small class="text-muted float-end">Masters / Processes / Edit</small>
            </div>
            <div class="card-body">
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger">
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('message')): ?>
                    <div class="alert alert-success">
                        <?= session()->getFlashdata('message') ?>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('masters/processes/update/' . $process['id']) ?>" method="POST" class="needs-validation" novalidate>
                    <?= csrf_field() ?>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="process_type">Process Type <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <select id="process_type" name="process_type" class="form-select" required>
                                <?php if (isset($processTypes)): ?>
                                    <?php foreach ($processTypes as $t): ?>
                                        <option value="<?= $t ?>" <?= ($t == $process['process_type']) ? 'selected' : '' ?>><?= $t ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="process_name">Process Name <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="process_name" name="process_name" 
                                   value="<?= esc($process['process_name']) ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="process_code">Code</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control-plaintext" id="process_code" name="process_code" 
                                   value="<?= esc($process['process_code']) ?>" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="rate_per_unit">Rate <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <!-- Current Rate Badge -->
                            <div class="mb-2">
                                <span class="badge bg-label-info">Current: ₹<?= number_format($process['rate_per_unit'], 2) ?></span>
                            </div>

                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control" id="rate_per_unit" name="rate_per_unit" 
                                       value="<?= esc($process['rate_per_unit']) ?>" min="0.01" step="0.01" required>
                            </div>
                            <!-- Price Change Warning -->
                            <div id="priceChangeAlert" class="alert alert-warning mt-2 py-2 d-none" role="alert">
                                <i class="ri-alert-line me-1"></i> Rate changes will be logged for audit purposes.
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="unit_of_measure">Unit <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                             <select id="unit_of_measure" name="unit_of_measure" class="form-select" required>
                                <?php 
                                $units = ['PCS', 'GRAM', 'PAIR', 'SET'];
                                foreach ($units as $u): ?>
                                    <option value="<?= $u ?>" <?= ($u == $process['unit_of_measure']) ? 'selected' : '' ?>><?= $u ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="description">Description</label>
                        <div class="col-sm-10">
                            <textarea id="description" name="description" class="form-control"><?= esc($process['description']) ?></textarea>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="is_active">Status</label>
                        <div class="col-sm-10">
                            <div class="form-check form-switch mt-2">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                       value="1" <?= $process['is_active'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="row justify-content-end">
                        <div class="col-sm-10">
                            <button type="submit" class="btn btn-primary">Update Process</button>
                            <a href="<?= base_url('masters/processes') ?>" class="btn btn-outline-secondary ms-2">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('page_scripts') ?>
<script>
    const initialRate = parseFloat("<?= $process['rate_per_unit'] ?>");
    const rateInput = document.getElementById('rate_per_unit');
    const alertBox = document.getElementById('priceChangeAlert');

    // Detect price change
    rateInput.addEventListener('input', function() {
        const newRate = parseFloat(this.value);
        if (newRate !== initialRate && !isNaN(newRate)) {
            alertBox.classList.remove('d-none');
        } else {
            alertBox.classList.add('d-none');
        }
    });
</script>
<?= $this->endSection() ?>

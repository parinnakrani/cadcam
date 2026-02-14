<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-xl">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Add New Process</h5>
                <small class="text-muted float-end">Masters / Processes</small>
            </div>
            <div class="card-body">
                <form action="<?= base_url('masters/processes/store') ?>" method="POST" class="needs-validation" novalidate>
                    <?= csrf_field() ?>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="process_type">Process Type <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <select id="process_type" name="process_type" class="form-select" required>
                                <option value="">Select Type</option>
                                <?php if (isset($processTypes)): ?>
                                    <?php foreach ($processTypes as $t): ?>
                                        <option value="<?= $t ?>"><?= $t ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="invalid-feedback">Please select a process type.</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="process_name">Process Name <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="process_name" name="process_name" placeholder="E.g. Full Rhodium" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="process_code">Process Code <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <div class="input-group">
                                <input type="text" class="form-control" id="process_code" name="process_code" placeholder="Enter or Generate Code" required>
                                <button class="btn btn-outline-secondary" type="button" id="generateCodeBtn">Generate</button>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="rate_per_unit">Rate <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control" id="rate_per_unit" name="rate_per_unit" 
                                       placeholder="0.00" min="0.01" step="0.01" required>
                            </div>
                            <div class="form-text">Current rate for this process.</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="unit_of_measure">Unit <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                             <select id="unit_of_measure" name="unit_of_measure" class="form-select" required>
                                <option value="PCS">PCS</option>
                                <option value="GRAM">GRAM</option>
                                <option value="PAIR">PAIR</option>
                                <option value="SET">SET</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="description">Description</label>
                        <div class="col-sm-10">
                            <textarea id="description" name="description" class="form-control" placeholder="Process details"></textarea>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="is_active">Status</label>
                        <div class="col-sm-10">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="row justify-content-end">
                        <div class="col-sm-10">
                            <button type="submit" class="btn btn-primary">Save Process</button>
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
    // Generate Code
    document.getElementById('generateCodeBtn').addEventListener('click', function() {
        var name = document.getElementById('process_name').value;
        var code = '';
        if (name) {
            code = name.substring(0, 3).toUpperCase().replace(/[^A-Z]/g, '') + '-' + Math.floor(Math.random() * 100);
        } else {
            code = 'PROC-' + Math.floor(100 + Math.random() * 900);
        }
        document.getElementById('process_code').value = code;
    });

    // Validation
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
            })
    })()
</script>
<?= $this->endSection() ?>

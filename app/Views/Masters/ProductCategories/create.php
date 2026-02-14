<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-xl">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Add New Category</h5>
                <small class="text-muted float-end">Masters / Product Categories</small>
            </div>
            <div class="card-body">
                <form action="<?= base_url('masters/product-categories/store') ?>" method="POST" id="createForm" class="needs-validation">
                    <?= csrf_field() ?>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="category_name">Category Name <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="category_name" name="category_name" placeholder="Enter Category Name" required>
                            <div class="invalid-feedback">
                                Please enter a category name.
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="category_code">Category Code <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <div class="input-group">
                                <input type="text" class="form-control" id="category_code" name="category_code" placeholder="Enter or Generate Code" required>
                                <button class="btn btn-outline-secondary" type="button" id="generateCodeBtn">Generate</button>
                                <div class="invalid-feedback">
                                    Please provide a unique category code.
                                </div>
                            </div>
                            <div class="form-text">Used for internal identification. Must be unique.</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="description">Description</label>
                        <div class="col-sm-10">
                            <textarea id="description" name="description" class="form-control" placeholder="Optional description of the category"></textarea>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="display_order">Display Order</label>
                        <div class="col-sm-10">
                            <input type="number" class="form-control" id="display_order" name="display_order" value="0" min="0">
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
                            <button type="submit" class="btn btn-primary" id="submitBtn">Save Category</button>
                            <a href="<?= base_url('masters/product-categories') ?>" class="btn btn-outline-secondary ms-2">Cancel</a>
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
    // Generate Code Button Logic
    document.getElementById('generateCodeBtn').addEventListener('click', function() {
        var name = document.getElementById('category_name').value;
        var code = '';
        if (name) {
            // Generate from name: first 3 chars + random number
            code = name.substring(0, 3).toUpperCase().replace(/[^A-Z]/g, '') + '-' + Math.floor(Math.random() * 1000);
        } else {
            // Random CAT-XXX
            code = 'CAT-' + Math.floor(1000 + Math.random() * 9000);
        }
        document.getElementById('category_code').value = code;
    });

    // Client-side Validation Indicator logic (Bootstrap)
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

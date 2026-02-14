<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-xl">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Add New Product</h5>
                <small class="text-muted float-end">Masters / Products</small>
            </div>
            <div class="card-body">
                <form action="<?= base_url('masters/products/store') ?>" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <?= csrf_field() ?>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="category_id">Category <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <select id="category_id" name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                <?php if (isset($categories)): ?>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= esc($cat['category_name']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="invalid-feedback">Please select a category.</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="product_name">Product Name <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="product_name" name="product_name" placeholder="Product Name" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="product_code">Product Code <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <div class="input-group">
                                <input type="text" class="form-control" id="product_code" name="product_code" placeholder="Enter or Generate Code" required>
                                <button class="btn btn-outline-secondary" type="button" id="generateCodeBtn">Generate</button>
                            </div>
                            <div class="form-text">Must be unique per company.</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="hsn_code">HSN Code</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="hsn_code" name="hsn_code" placeholder="Optional">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="unit_of_measure">Unit <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <select id="unit_of_measure" name="unit_of_measure" class="form-select" required>
                                <option value="PCS">PCS</option>
                                <option value="PAIR">PAIR</option>
                                <option value="SET">SET</option>
                                <option value="GRAM">GRAM</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="description">Description</label>
                        <div class="col-sm-10">
                            <textarea id="description" name="description" class="form-control" placeholder="Product Description"></textarea>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="image">Product Image</label>
                        <div class="col-sm-10">
                            <input class="form-control" type="file" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                            <div class="mt-2 text-center" style="display:none;" id="imagePreviewContainer">
                                <img id="imagePreview" src="" alt="Preview" class="img-thumbnail" style="max-height: 200px;">
                            </div>
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
                            <button type="submit" class="btn btn-primary">Save Product</button>
                            <a href="<?= base_url('masters/products') ?>" class="btn btn-outline-secondary ms-2">Cancel</a>
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
    // Image Preview
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imagePreview').src = e.target.result;
                document.getElementById('imagePreviewContainer').style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            document.getElementById('imagePreviewContainer').style.display = 'none';
        }
    }

    // Generate Code
    document.getElementById('generateCodeBtn').addEventListener('click', function() {
        var name = document.getElementById('product_name').value;
        var code = '';
        if (name) {
            code = name.substring(0, 3).toUpperCase().replace(/[^A-Z]/g, '') + '-' + Math.floor(Math.random() * 10000);
        } else {
            code = 'PROD-' + Math.floor(10000 + Math.random() * 90000);
        }
        document.getElementById('product_code').value = code;
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

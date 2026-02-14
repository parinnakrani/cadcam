<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-xl">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit Category</h5>
                <small class="text-muted float-end">Masters / Product Categories / Edit</small>
            </div>
            <div class="card-body">
                <form action="<?= base_url('masters/product-categories/update/' . $category['id']) ?>" method="POST" class="needs-validation">
                    <?= csrf_field() ?>
                    <!-- Method spoofing for PUT if needed, but usually POST is used in CI4 simple routes -->
                    <!-- <input type="hidden" name="_method" value="PUT"> -->

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="category_name">Category Name <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="category_name" name="category_name" 
                                   value="<?= esc($category['category_name']) ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="category_code">Category Code</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control-plaintext" id="category_code" name="category_code" 
                                   value="<?= esc($category['category_code']) ?>" readonly>
                            <div class="form-text">Code cannot be changed once created.</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="description">Description</label>
                        <div class="col-sm-10">
                            <textarea id="description" name="description" class="form-control"><?= esc($category['description']) ?></textarea>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="display_order">Display Order</label>
                        <div class="col-sm-10">
                            <input type="number" class="form-control" id="display_order" name="display_order" 
                                   value="<?= esc($category['display_order']) ?>" min="0">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="is_active">Status</label>
                        <div class="col-sm-10">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                       value="1" <?= $category['is_active'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="row justify-content-end">
                        <div class="col-sm-10">
                            <button type="submit" class="btn btn-primary">Update Category</button>
                            <a href="<?= base_url('masters/product-categories') ?>" class="btn btn-outline-secondary ms-2">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

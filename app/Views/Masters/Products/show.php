<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Masters / Products /</span> Details
    </h4>

    <div class="row">
        <!-- Product Image -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Product Image</h5>
                </div>
                <div class="card-body text-center d-flex align-items-center justify-content-center">
                    <?php if (!empty($product['image_path'])): ?>
                        <img src="<?= base_url($product['image_path']) ?>" alt="Product Image" class="img-fluid rounded border" style="max-height: 400px; object-fit: contain;">
                    <?php else: ?>
                        <div class="text-muted p-5 bg-light rounded w-100">
                            <i class="ri-image-line" style="font-size: 3rem;"></i>
                            <p>No Image Available</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Product Details -->
        <div class="col-md-8 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Information</h5>
                    <div>
                         <?php if (isset($product['company_id'])): ?>
                            <span class="badge bg-label-primary">Company ID: <?= $product['company_id'] ?></span>
                         <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <label class="col-sm-3 fw-bold">Status:</label>
                        <div class="col-sm-9">
                             <?php if ($product['is_active']): ?>
                                <span class="badge bg-label-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-label-secondary">Inactive</span>
                            <?php endif; ?>
                        </div>
                    </div>

                     <div class="row mb-3">
                        <label class="col-sm-3 fw-bold">Product Name:</label>
                        <div class="col-sm-9">
                            <?= esc($product['product_name']) ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 fw-bold">Product Code:</label>
                        <div class="col-sm-9 font-monospace">
                            <?= esc($product['product_code']) ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 fw-bold">Category:</label>
                        <div class="col-sm-9">
                            <?= esc($product['category_name'] ?? 'N/A') ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 fw-bold">Unit:</label>
                        <div class="col-sm-9">
                            <?= esc($product['unit_of_measure']) ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 fw-bold">HSN Code:</label>
                        <div class="col-sm-9">
                            <?= esc($product['hsn_code'] ?: '-') ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 fw-bold">Description:</label>
                        <div class="col-sm-9 text-wrap">
                            <?= nl2br((string)esc($product['description'] ?: 'No Description')) ?>
                        </div>
                    </div>

                     <div class="mt-4">
                        <a href="<?= base_url('masters/products') ?>" class="btn btn-outline-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Back
                        </a>
                        <!-- Permissions check ideally needed here too, but buttons visible if permitted in list usually implies view permitted details -->
                        <a href="<?= base_url('masters/products/edit/' . $product['id']) ?>" class="btn btn-primary ms-2">
                             <i class="ri-pencil-line me-1"></i> Edit
                        </a>
                     </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

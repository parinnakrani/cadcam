<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Cash Customer Details<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Customers / Cash Customers /</span> View Details</h4>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <h5 class="card-header">Customer Information</h5>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Customer Name</label>
                        <p><?= esc($customer['customer_name']) ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Mobile</label>
                        <p><?= esc($customer['mobile']) ?></p>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <p><?= !empty($customer['email']) ? esc($customer['email']) : '-' ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Status</label>
                        <p>
                            <?php if($customer['is_active']): ?>
                                <span class="badge bg-label-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-label-secondary">Inactive</span>
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold">Address</label>
                        <p>
                            <?php 
                                $addressParts = [];
                                if(!empty($customer['address_line1'])) $addressParts[] = $customer['address_line1'];
                                if(!empty($customer['address_line2'])) $addressParts[] = $customer['address_line2'];
                                if(!empty($customer['city'])) $addressParts[] = $customer['city'];
                                if(!empty($state_name)) $addressParts[] = $state_name;
                                if(!empty($customer['pincode'])) $addressParts[] = $customer['pincode'];
                                echo !empty($addressParts) ? esc(implode(', ', $addressParts)) : '-';
                            ?>
                        </p>
                    </div>
                    
                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold">Notes</label>
                        <p><?= !empty($customer['notes']) ? nl2br(esc($customer['notes'])) : '-' ?></p>
                    </div>
                </div>

                <div class="mt-4">
                    <?php if(can('cash_customer.edit')): ?>
                        <a href="<?= base_url('customers/cash-customers/' . $customer['id'] . '/edit') ?>" class="btn btn-primary me-2">Edit Customer</a>
                    <?php endif; ?>
                    <a href="<?= base_url('customers/cash-customers') ?>" class="btn btn-outline-secondary">Back to List</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

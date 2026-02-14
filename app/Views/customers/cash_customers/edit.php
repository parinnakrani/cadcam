<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Edit Cash Customer<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Customers / Cash Customers /</span> Edit Customer</h4>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <h5 class="card-header">Edit Customer Details</h5>
            <div class="card-body">
                <form action="<?= base_url('customers/cash-customers/' . $customer['id']) ?>" method="POST" id="editCashCustomerForm">
                    <?= csrf_field() ?>

                    <?php if(session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
                    <?php endif; ?>
                    <?php if(session()->getFlashdata('errors')): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                            <?php foreach(session()->getFlashdata('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="customer_name">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="customer_name" name="customer_name" required value="<?= old('customer_name', $customer['customer_name']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="mobile">Mobile <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="mobile" name="mobile" required pattern="[0-9]{10}" title="10 digit mobile number" value="<?= old('mobile', $customer['mobile']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= old('email', $customer['email']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="address_line1">Address Line 1</label>
                            <input type="text" class="form-control" id="address_line1" name="address_line1" value="<?= old('address_line1', $customer['address_line1']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="address_line2">Address Line 2</label>
                            <input type="text" class="form-control" id="address_line2" name="address_line2" value="<?= old('address_line2', $customer['address_line2']) ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="city">City</label>
                            <input type="text" class="form-control" id="city" name="city" value="<?= old('city', $customer['city']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="state_id">State</label>
                            <select class="form-select" id="state_id" name="state_id">
                                <option value="">Select State</option>
                                <?php if(isset($states)): ?>
                                    <?php foreach($states as $state): ?>
                                        <option value="<?= $state['id'] ?>" <?= (old('state_id', $customer['state_id']) == $state['id']) ? 'selected' : '' ?>>
                                            <?= esc($state['state_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="pincode">Pincode</label>
                            <input type="text" class="form-control" id="pincode" name="pincode" pattern="[0-9]{6}" maxlength="6" value="<?= old('pincode', $customer['pincode']) ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="notes">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"><?= old('notes', $customer['notes']) ?></textarea>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= old('is_active', $customer['is_active']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active">
                                    Is Active
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary me-2">Update Customer</button>
                        <a href="<?= base_url('customers/cash-customers') ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

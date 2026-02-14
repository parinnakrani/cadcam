<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Edit Account Customer<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Customers / Accounts /</span> Edit Account</h4>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <h5 class="card-header">Edit Account Details</h5>
            <div class="card-body">
                <form action="<?= base_url('customers/accounts/' . $account['id']) ?>" method="POST" id="editAccountForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="POST"> <!-- CI4 uses POST for updates usually, unless configured for PUT -->
                    <!-- But route is POST (:num) -> update. -->
                    
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

                    <!-- Basic Information -->
                    <h6 class="fw-normal">1. Basic Information</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="account_code">Account Code</label>
                            <input type="text" class="form-control" id="account_code" name="account_code" value="<?= esc($account['account_code']) ?>" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="account_name">Account Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="account_name" name="account_name" required value="<?= esc(old('account_name', $account['account_name'])) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="business_name">Business Name</label>
                            <input type="text" class="form-control" id="business_name" name="business_name" value="<?= esc(old('business_name', $account['business_name'])) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="contact_person">Contact Person</label>
                            <input type="text" class="form-control" id="contact_person" name="contact_person" value="<?= esc(old('contact_person', $account['contact_person'])) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="mobile">Mobile <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="mobile" name="mobile" required pattern="[0-9]{10}" title="10 digit mobile number" value="<?= esc(old('mobile', $account['mobile'])) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= esc(old('email', $account['email'])) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="gst_number">GST Number</label>
                            <input type="text" class="form-control" id="gst_number" name="gst_number" value="<?= esc(old('gst_number', $account['gst_number'])) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="pan_number">PAN Number</label>
                            <input type="text" class="form-control" id="pan_number" name="pan_number" value="<?= esc(old('pan_number', $account['pan_number'])) ?>">
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Billing Address -->
                    <h6 class="fw-normal">2. Billing Address</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="billing_address_line1">Address Line 1 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="billing_address_line1" name="billing_address_line1" required value="<?= esc(old('billing_address_line1', $account['billing_address_line1'])) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="billing_address_line2">Address Line 2</label>
                            <input type="text" class="form-control" id="billing_address_line2" name="billing_address_line2" value="<?= esc(old('billing_address_line2', $account['billing_address_line2'])) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="billing_city">City <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="billing_city" name="billing_city" required value="<?= esc(old('billing_city', $account['billing_city'])) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="billing_state_id">State <span class="text-danger">*</span></label>
                            <select class="form-select" id="billing_state_id" name="billing_state_id" required>
                                <option value="">Select State</option>
                                <?php foreach($states as $state): ?>
                                    <option value="<?= $state['id'] ?>" <?= old('billing_state_id', $account['billing_state_id']) == $state['id'] ? 'selected' : '' ?>>
                                        <?= esc($state['state_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="billing_pincode">Pincode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="billing_pincode" name="billing_pincode" required pattern="[0-9]{6}" maxlength="6" value="<?= esc(old('billing_pincode', $account['billing_pincode'])) ?>">
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Shipping Address -->
                    <h6 class="fw-normal">3. Shipping Address</h6>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="same_as_billing" name="same_as_billing" value="1" <?= old('same_as_billing', $account['same_as_billing']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="same_as_billing">
                            Same as Billing Address
                        </label>
                    </div>

                    <div id="shipping_address_section" class="<?= old('same_as_billing', $account['same_as_billing']) ? 'd-none' : '' ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="shipping_address_line1">Address Line 1</label>
                                <input type="text" class="form-control" id="shipping_address_line1" name="shipping_address_line1" value="<?= esc(old('shipping_address_line1', $account['shipping_address_line1'])) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="shipping_address_line2">Address Line 2</label>
                                <input type="text" class="form-control" id="shipping_address_line2" name="shipping_address_line2" value="<?= esc(old('shipping_address_line2', $account['shipping_address_line2'])) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="shipping_city">City</label>
                                <input type="text" class="form-control" id="shipping_city" name="shipping_city" value="<?= esc(old('shipping_city', $account['shipping_city'])) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="shipping_state_id">State</label>
                                <select class="form-select" id="shipping_state_id" name="shipping_state_id">
                                    <option value="">Select State</option>
                                    <?php foreach($states as $state): ?>
                                        <option value="<?= $state['id'] ?>" <?= old('shipping_state_id', $account['shipping_state_id']) == $state['id'] ? 'selected' : '' ?>>
                                            <?= esc($state['state_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="shipping_pincode">Pincode</label>
                                <input type="text" class="form-control" id="shipping_pincode" name="shipping_pincode" pattern="[0-9]{6}" maxlength="6" value="<?= esc(old('shipping_pincode', $account['shipping_pincode'])) ?>">
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Financial & Other -->
                    <h6 class="fw-normal">4. Financial & Settings</h6>
                    <div class="row g-3 mb-3">
                         <div class="col-md-4">
                             <label class="form-label" for="current_balance">Current Balance</label>
                             <div class="input-group">
                                 <span class="input-group-text">₹</span>
                                 <input type="text" class="form-control bg-light" id="current_balance" value="<?= esc(number_format($account['current_balance'], 2)) ?>" readonly>
                             </div>
                             <div class="form-text">Balance includes all transactions.</div>
                         </div>
                         <div class="col-md-4">
                             <label class="form-label" for="opening_balance">Opening Balance (Initial)</label>
                             <div class="input-group">
                                 <span class="input-group-text">₹</span>
                                 <input type="number" class="form-control" id="opening_balance" value="<?= esc($account['opening_balance']) ?>" readonly disabled>
                             </div>
                             <div class="form-text">Cannot be edited after creation.</div>
                         </div>
                         <div class="col-md-4">
                             <label class="form-label" for="payment_terms">Payment Terms</label>
                             <input type="text" class="form-control" id="payment_terms" name="payment_terms" value="<?= esc(old('payment_terms', $account['payment_terms'])) ?>">
                         </div>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label" for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"><?= esc(old('notes', $account['notes'])) ?></textarea>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= old('is_active', $account['is_active']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">
                            Is Active
                        </label>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary me-2">Update Account</button>
                        <a href="<?= base_url('customers/accounts') ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('page_js') ?>
<script>
    document.getElementById('same_as_billing').addEventListener('change', function() {
        const shippingSection = document.getElementById('shipping_address_section');
        if (this.checked) {
            shippingSection.classList.add('d-none');
        } else {
            shippingSection.classList.remove('d-none');
        }
    });
</script>
<?= $this->endSection() ?>

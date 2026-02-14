<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Add Account Customer<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Customers / Accounts /</span> Add New Account</h4>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <h5 class="card-header">Account Details</h5>
            <div class="card-body">
                <form action="<?= base_url('customers/accounts') ?>" method="POST" id="createAccountForm">
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

                    <!-- Basic Information -->
                    <h6 class="fw-normal">1. Basic Information</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="account_code">Account Code</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="account_code" name="account_code" placeholder="Auto-generated if empty" value="<?= old('account_code') ?>">
                                <button class="btn btn-outline-secondary" type="button" id="btn_generate_code">Auto</button>
                            </div>
                            <div class="form-text">Leave empty to auto-generate (e.g., ACC-0001)</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="account_name">Account Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="account_name" name="account_name" required value="<?= old('account_name') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="business_name">Business Name</label>
                            <input type="text" class="form-control" id="business_name" name="business_name" value="<?= old('business_name') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="contact_person">Contact Person</label>
                            <input type="text" class="form-control" id="contact_person" name="contact_person" value="<?= old('contact_person') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="mobile">Mobile <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="mobile" name="mobile" required pattern="[0-9]{10}" title="10 digit mobile number" value="<?= old('mobile') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= old('email') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="gst_number">GST Number</label>
                            <input type="text" class="form-control" id="gst_number" name="gst_number" placeholder="22AAAAA0000A1Z5" value="<?= old('gst_number') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="pan_number">PAN Number</label>
                            <input type="text" class="form-control" id="pan_number" name="pan_number" placeholder="ABCDE1234F" value="<?= old('pan_number') ?>">
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Billing Address -->
                    <h6 class="fw-normal">2. Billing Address</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="billing_address_line1">Address Line 1 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="billing_address_line1" name="billing_address_line1" required value="<?= old('billing_address_line1') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="billing_address_line2">Address Line 2</label>
                            <input type="text" class="form-control" id="billing_address_line2" name="billing_address_line2" value="<?= old('billing_address_line2') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="billing_city">City <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="billing_city" name="billing_city" required value="<?= old('billing_city') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="billing_state_id">State <span class="text-danger">*</span></label>
                            <select class="form-select" id="billing_state_id" name="billing_state_id" required>
                                <option value="">Select State</option>
                                <?php foreach($states as $state): ?>
                                    <option value="<?= $state['id'] ?>" <?= old('billing_state_id') == $state['id'] ? 'selected' : '' ?>>
                                        <?= esc($state['state_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="billing_pincode">Pincode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="billing_pincode" name="billing_pincode" required pattern="[0-9]{6}" maxlength="6" value="<?= old('billing_pincode') ?>">
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Shipping Address -->
                    <h6 class="fw-normal">3. Shipping Address</h6>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="same_as_billing" name="same_as_billing" value="1" <?= old('same_as_billing') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="same_as_billing">
                            Same as Billing Address
                        </label>
                    </div>

                    <div id="shipping_address_section" class="<?= old('same_as_billing') ? 'd-none' : '' ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="shipping_address_line1">Address Line 1</label>
                                <input type="text" class="form-control" id="shipping_address_line1" name="shipping_address_line1" value="<?= old('shipping_address_line1') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="shipping_address_line2">Address Line 2</label>
                                <input type="text" class="form-control" id="shipping_address_line2" name="shipping_address_line2" value="<?= old('shipping_address_line2') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="shipping_city">City</label>
                                <input type="text" class="form-control" id="shipping_city" name="shipping_city" value="<?= old('shipping_city') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="shipping_state_id">State</label>
                                <select class="form-select" id="shipping_state_id" name="shipping_state_id">
                                    <option value="">Select State</option>
                                    <?php foreach($states as $state): ?>
                                        <option value="<?= $state['id'] ?>" <?= old('shipping_state_id') == $state['id'] ? 'selected' : '' ?>>
                                            <?= esc($state['state_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="shipping_pincode">Pincode</label>
                                <input type="text" class="form-control" id="shipping_pincode" name="shipping_pincode" pattern="[0-9]{6}" maxlength="6" value="<?= old('shipping_pincode') ?>">
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Financial & Other -->
                    <h6 class="fw-normal">4. Financial & Settings</h6>
                    <div class="row g-3 mb-3">
                         <div class="col-md-4">
                             <label class="form-label" for="opening_balance">Opening Balance</label>
                             <div class="input-group">
                                 <span class="input-group-text">₹</span>
                                 <input type="number" step="0.01" class="form-control" id="opening_balance" name="opening_balance" value="<?= old('opening_balance', '0.00') ?>">
                             </div>
                         </div>
                         <div class="col-md-4">
                             <label class="form-label">Opening Balance Type</label>
                             <div class="mt-2">
                                 <div class="form-check form-check-inline">
                                     <input class="form-check-input" type="radio" name="opening_balance_type" id="type_debit" value="Debit" <?= old('opening_balance_type', 'Debit') == 'Debit' ? 'checked' : '' ?>>
                                     <label class="form-check-label" for="type_debit">Debit (Receivable)</label>
                                 </div>
                                 <div class="form-check form-check-inline">
                                     <input class="form-check-input" type="radio" name="opening_balance_type" id="type_credit" value="Credit" <?= old('opening_balance_type') == 'Credit' ? 'checked' : '' ?>>
                                     <label class="form-check-label" for="type_credit">Credit (Payable)</label>
                                 </div>
                             </div>
                         </div>
                         <div class="col-md-4">
                             <label class="form-label" for="payment_terms">Payment Terms</label>
                             <input type="text" class="form-control" id="payment_terms" name="payment_terms" placeholder="e.g. Net 30 Days" value="<?= old('payment_terms') ?>">
                         </div>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label" for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"><?= old('notes') ?></textarea>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= old('is_active', '1') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">
                            Is Active
                        </label>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary me-2">Create Account</button>
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
    // Toggle Shipping Address
    document.getElementById('same_as_billing').addEventListener('change', function() {
        const shippingSection = document.getElementById('shipping_address_section');
        if (this.checked) {
            shippingSection.classList.add('d-none');
            // Logic to clear or disable shipping inputs passed to backend? 
            // Backend logic: "If same_as_billing = TRUE: copy billing address to shipping". 
            // So frontend hiding is fine.
        } else {
            shippingSection.classList.remove('d-none');
        }
    });

    // Auto-generate code (Mock functionality or fetch via AJAX?)
    // Prompt says "Auto-generate using AccountModel->generateNextAccountCode()". 
    // Button could fetch via AJAX if desired, OR just leave empty and backend handles.
    // Prompt "account_code (auto-generate button, or manual entry)".
    // If user clicks button, we should fetch.
    document.getElementById('btn_generate_code').addEventListener('click', function() {
        // Simple logic: Fetch next code via simple API or just generate locally as placeholder?
        // Using "Leave empty to auto-generate" is safer.
        // But button implies client-side fetch.
        // I'll leave it as "Clicking this clears field"? No.
        // I'll make it fetch from a route? `customers/accounts/next-code`?
        // No such route defined in prompt.
        // Prompt says "If account_code not provided: auto-generate".
        // Use placeholder text "Left empty -> Auto".
        document.getElementById('account_code').value = '';
        document.getElementById('account_code').placeholder = 'Will be auto-generated on save';
        alert('Code will be auto-generated upon saving.');
    });
</script>
<?= $this->endSection() ?>

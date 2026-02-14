<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Account Details<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Customers / Accounts /</span> Account Details</h4>

<div class="row">
    <!-- Left Column: Details -->
    <div class="col-xl-8 col-lg-7 col-md-7">
        <!-- About User -->
        <div class="card mb-4">
            <div class="card-body">
                <small class="card-text text-uppercase text-muted">About</small>
                <ul class="list-unstyled mb-4 mt-3">
                    <li class="d-flex align-items-center mb-3"><i class="ri-user-3-line ri-20px"></i><span class="fw-medium mx-2">Full Name:</span> <span><?= esc($account['account_name']) ?></span></li>
                    <li class="d-flex align-items-center mb-3"><i class="ri-building-line ri-20px"></i><span class="fw-medium mx-2">Business:</span> <span><?= esc($account['business_name'] ?: '-') ?></span></li>
                    <li class="d-flex align-items-center mb-3"><i class="ri-check-line ri-20px"></i><span class="fw-medium mx-2">Status:</span> 
                        <?php if($account['is_active']): ?>
                            <span class="badge bg-label-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-label-secondary">Inactive</span>
                        <?php endif; ?>
                    </li>
                    <li class="d-flex align-items-center mb-3"><i class="ri-star-smile-line ri-20px"></i><span class="fw-medium mx-2">Code:</span> <span><?= esc($account['account_code']) ?></span></li>
                    <li class="d-flex align-items-center mb-3"><i class="ri-file-text-line ri-20px"></i><span class="fw-medium mx-2">GSTIN:</span> <span><?= esc($account['gst_number'] ?: '-') ?></span></li>
                </ul>
                
                <small class="card-text text-uppercase text-muted">Contacts</small>
                <ul class="list-unstyled mb-4 mt-3">
                    <li class="d-flex align-items-center mb-3"><i class="ri-phone-line ri-20px"></i><span class="fw-medium mx-2">Contact:</span> <span><?= esc($account['mobile']) ?></span></li>
                    <li class="d-flex align-items-center mb-3"><i class="ri-mail-open-line ri-20px"></i><span class="fw-medium mx-2">Email:</span> <span><?= esc($account['email'] ?: '-') ?></span></li>
                    <li class="d-flex align-items-center mb-3"><i class="ri-user-voice-line ri-20px"></i><span class="fw-medium mx-2">Person:</span> <span><?= esc($account['contact_person'] ?: '-') ?></span></li>
                </ul>

                <hr>
                
                <div class="row">
                    <div class="col-md-6">
                        <small class="card-text text-uppercase text-muted">Billing Address</small>
                        <p class="mt-2">
                            <?= esc($account['billing_address_line1']) ?><br>
                            <?php if($account['billing_address_line2']): ?><?= esc($account['billing_address_line2']) ?><br><?php endif; ?>
                            <?= esc($account['billing_city']) ?><br>
                            Pincode: <?= esc($account['billing_pincode']) ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <small class="card-text text-uppercase text-muted">Shipping Address</small>
                        <p class="mt-2">
                            <?php if($account['same_as_billing']): ?>
                                <span class="text-muted">Same as Billing Address</span>
                            <?php else: ?>
                                <?= esc($account['shipping_address_line1']) ?><br>
                                <?php if($account['shipping_address_line2']): ?><?= esc($account['shipping_address_line2']) ?><br><?php endif; ?>
                                <?= esc($account['shipping_city']) ?><br>
                                Pincode: <?= esc($account['shipping_pincode']) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Right Column: Financial & Actions -->
    <div class="col-xl-4 col-lg-5 col-md-5">
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="badge bg-label-primary rounded p-2">
                        <i class="ri-wallet-3-line ri-24px"></i>
                    </span>
                    <div class="d-flex justify-content-between w-100 gap-2 align-items-center">
                        <div class="me-2 text-end w-100">
                            <h6 class="mb-0">Current Balance</h6>
                            <h4 class="mb-0 <?= $balance >= 0 ? 'text-success' : 'text-danger' ?>">
                                ₹ <?= number_format($balance, 2) ?>
                            </h4>
                            <small class="text-muted"><?= $balance >= 0 ? 'Credit (Payable)' : 'Debit (Receivable)' ?></small> 
                            <!-- Check logic: If balance is +ve (Credit), it means we owe them? 
                                 Wait. Standard Ledger:
                                 Sales -> Credit Sales -> Debit Customer.
                                 If Customer has Debit balance, they owe us.
                                 If Credit balance, we owe them (advance).
                                 So Debit (Positive usually in Asset/Receivable context) = They owe us.
                                 Credit (Negative) = We owe them.
                                 My display logic:
                                 If I use standard signed numbers where Debit is +ve in Receivables:
                                 +100 = They owe 100.
                                 -100 = We owe 100.
                                 Let's stick to this convention if Ledger Service uses it.
                                 Display: "Receivable" vs "Payable".
                             -->
                        </div>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <div class="d-grid gap-2">
                    <a href="<?= base_url('customers/accounts/' . $account['id'] . '/edit') ?>" class="btn btn-primary"><i class="ri-pencil-line me-1"></i> Edit Account</a>
                    <a href="<?= base_url('customers/accounts/' . $account['id'] . '/ledger') ?>" class="btn btn-outline-info"><i class="ri-book-read-line me-1"></i> View Ledger</a>
                    <button class="btn btn-outline-danger delete-record" data-id="<?= $account['id'] ?>"><i class="ri-delete-bin-line me-1"></i> Delete</button>
                    <a href="<?= base_url('customers/accounts') ?>" class="btn btn-label-secondary">Back to List</a>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
             <div class="card-body">
                 <h6>Notes</h6>
                 <p class="mb-0"><?= nl2br(esc($account['notes'] ?: 'No notes available.')) ?></p>
             </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('page_js') ?>
<script>
    // Delete Logic (Duplicate from index or shared func?)
    // Best to inline or put in global JS. Inline for now.
    document.querySelector('.delete-record').addEventListener('click', function() {
        var id = this.getAttribute('data-id');
        if(confirm('Are you sure you want to delete this account?')) {
            $.ajax({
                url: '<?= base_url('customers/accounts') ?>/' + id,
                type: 'DELETE',
                dataType: 'json',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                success: function(res) {
                    if(res.status === 'success') {
                        window.location.href = '<?= base_url('customers/accounts') ?>';
                    } else {
                        alert(res.message);
                    }
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>

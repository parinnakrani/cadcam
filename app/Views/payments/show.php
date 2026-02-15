<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Payment Details - <?= esc($payment['payment_number']) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('payments') ?>">Payments</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= esc($payment['payment_number']) ?></li>
    </ol>
</nav>

<!-- Payment Details Card -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            Payment Details: <span class="text-primary"><?= esc($payment['payment_number']) ?></span>
        </h5>
        <div>
            <a href="<?= base_url('invoices/' . $payment['invoice_id']) ?>" class="btn btn-outline-info btn-sm">
                View Invoice
            </a>
            <a href="<?= base_url('payments') ?>" class="btn btn-secondary btn-sm">Back to List</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Payment Date:</strong>
                <p class="text-muted"><?= date('d F Y', strtotime($payment['payment_date'])) ?></p>
            </div>
            <div class="col-md-6">
                <strong>Amount Paid:</strong>
                <p class="h4 text-success">₹<?= number_format($payment['payment_amount'], 2) ?></p>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Payment Mode:</strong>
                <p><span class="badge bg-info text-dark"><?= esc($payment['payment_mode']) ?></span></p>
            </div>
            <div class="col-md-6">
                <strong>Customer Type:</strong>
                <p><?= esc($payment['customer_type']) ?> Customer</p>
            </div>
        </div>

        <?php if ($payment['payment_mode'] != 'Cash'): ?>
            <div class="row mb-3 p-3 bg-light rounded">
                <div class="col-md-12"><h6 class="text-muted border-bottom pb-2">Transaction Details</h6></div>
                
                <?php if ($payment['transaction_reference']): ?>
                    <div class="col-md-6 mt-2">
                        <strong>Transaction Reference:</strong><br>
                        <?= esc($payment['transaction_reference']) ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($payment['bank_name']): ?>
                    <div class="col-md-6 mt-2">
                        <strong>Bank Name:</strong><br>
                        <?= esc($payment['bank_name']) ?>
                    </div>
                <?php endif; ?>

                <?php if ($payment['cheque_number']): ?>
                    <div class="col-md-6 mt-2">
                        <strong>Cheque Number:</strong><br>
                        <?= esc($payment['cheque_number']) ?>
                    </div>
                <?php endif; ?>

                <?php if ($payment['cheque_date']): ?>
                    <div class="col-md-6 mt-2">
                        <strong>Cheque Date:</strong><br>
                        <?= date('d M Y', strtotime($payment['cheque_date'])) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12">
                <strong>Notes:</strong>
                <p class="text-muted"><?= nl2br(esc((string)($payment['notes'] ?: 'No notes provided.'))) ?></p>
            </div>
        </div>
    </div>
    <div class="card-footer text-muted small">
        Recorded by User ID: <?= esc($payment['received_by']) ?> on <?= date('d M Y H:i', strtotime($payment['created_at'])) ?>
    </div>
</div>

<?= $this->endSection() ?>
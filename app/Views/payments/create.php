FILE: app/Views/payments/create.php
================================================================================

<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Record Payment<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Record Payment</h5>
        <a href="<?= base_url('payments') ?>" class="btn btn-secondary btn-sm">
          <i class="ri-arrow-left-line"></i> Back
        </a>
      </div>
      <div class="card-body">

        <?php if (session()->getFlashdata('error')): ?>
          <div class="alert alert-danger" role="alert">
            <?= session()->getFlashdata('error') ?>
          </div>
        <?php endif; ?>

        <form action="<?= base_url('payments') ?>" method="post" id="paymentForm">
          <?= csrf_field() ?>

          <!-- Invoice Selection -->
          <div class="mb-3">
            <label for="invoice_id" class="form-label">Select Invoice <span class="text-danger">*</span></label>
            <select name="invoice_id" id="invoice_id" class="form-select" required>
              <option value="">-- Select Invoice --</option>
              <?php foreach ($invoices as $invoice): ?>
                <option value="<?= $invoice['id'] ?>"
                  data-amount-due="<?= $invoice['amount_due'] ?>"
                  data-grand-total="<?= $invoice['grand_total'] ?>"
                  data-customer="<?= esc($invoice['customer_name'] ?? ($invoice['account_id'] ? 'Account ID: ' . $invoice['account_id'] : 'Cash Customer ID: ' . $invoice['cash_customer_id'])) ?>"
                  <?= (isset($selected_invoice_id) && $selected_invoice_id == $invoice['id']) ? 'selected' : '' ?>>
                  <?= esc($invoice['invoice_number']) ?> (Due: ₹<?= number_format($invoice['amount_due'], 2) ?>)
                </option>
              <?php endforeach; ?>
            </select>
            <div class="form-text" id="invoiceDetails"></div>
          </div>

          <!-- Payment Date -->
          <div class="mb-3">
            <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
            <input type="date" name="payment_date" id="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
          </div>

          <!-- Amount -->
          <div class="mb-3">
            <label for="payment_amount" class="form-label">Amount Paid (₹) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" name="payment_amount" id="payment_amount" class="form-control" required min="0.01">
            <div class="form-text text-muted">Max payable: <span id="maxPayable">0.00</span></div>
          </div>

          <!-- Mode -->
          <div class="mb-3">
            <label for="payment_mode" class="form-label">Payment Mode <span class="text-danger">*</span></label>
            <select name="payment_mode" id="payment_mode" class="form-select" required>
              <option value="Cash">Cash</option>
              <option value="Cheque">Cheque</option>
              <option value="Bank Transfer">Bank Transfer</option>
              <option value="UPI">UPI</option>
              <option value="Card">Card</option>
              <option value="Other">Other</option>
            </select>
          </div>

          <!-- Conditional Fields: Cheque/Bank/Ref -->
          <div id="referenceFields" style="display:none;">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="transaction_reference" class="form-label">Transaction Ref #</label>
                <input type="text" name="transaction_reference" id="transaction_reference" class="form-control">
              </div>
              <div class="col-md-6 mb-3">
                <label for="bank_name" class="form-label">Bank Name</label>
                <input type="text" name="bank_name" id="bank_name" class="form-control">
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="cheque_number" class="form-label">Cheque Number</label>
                <input type="text" name="cheque_number" id="cheque_number" class="form-control">
              </div>
              <div class="col-md-6 mb-3">
                <label for="cheque_date" class="form-label">Cheque Date</label>
                <input type="date" name="cheque_date" id="cheque_date" class="form-control">
              </div>
            </div>
          </div>

          <!-- Notes -->
          <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea name="notes" id="notes" class="form-control" rows="2"></textarea>
          </div>

          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-success">Record Payment</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
  $(document).ready(function() {
    const invoiceSelect = $('#invoice_id');
    const amountInput = $('#payment_amount');
    const maxPayableSpan = $('#maxPayable');
    const invoiceDetailsDiv = $('#invoiceDetails');

    // Initial check
    updateInvoiceDetails();

    invoiceSelect.on('change', function() {
      updateInvoiceDetails();
    });

    function updateInvoiceDetails() {
      const selectedOption = invoiceSelect.find(':selected');
      const amountDue = parseFloat(selectedOption.data('amount-due')) || 0;
      const customer = selectedOption.data('customer') || '';
      const grandTotal = parseFloat(selectedOption.data('grand-total')) || 0;

      if (selectedOption.val()) {
        maxPayableSpan.text(amountDue.toFixed(2));
        amountInput.attr('max', amountDue);
        // Pre-fill amount if empty? Maybe not, user might pay partial.
        // But usually they pay full due.
        if (!amountInput.val()) {
          amountInput.val(amountDue.toFixed(2));
        }

        invoiceDetailsDiv.html(
          `<small class="text-success">
                        <strong>Customer:</strong> ${customer} <br>
                        <strong>Total Invoice Amount:</strong> ₹${grandTotal.toFixed(2)}
                     </small>`
        );
      } else {
        maxPayableSpan.text('0.00');
        amountInput.removeAttr('max');
        amountInput.val('');
        invoiceDetailsDiv.text('');
      }
    }

    // Show/Hide reference fields based on mode
    $('#payment_mode').on('change', function() {
      const mode = $(this).val();
      if (mode === 'Cash') {
        $('#referenceFields').slideUp();
      } else {
        $('#referenceFields').slideDown();
      }
    });

    // Trigger generic change handle
    $('#payment_mode').trigger('change');
  });
</script>
<?= $this->endSection() ?>

================================================================================
END OF FILE
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Invoice <?= esc($invoice['invoice_number']) ?></title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: Arial, sans-serif;
      font-size: 13px;
      color: #333;
      margin: 20px;
      line-height: 1.5;
    }

    .invoice-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      border-bottom: 3px solid #333;
      padding-bottom: 15px;
      margin-bottom: 20px;
    }

    .company-info h1 {
      font-size: 22px;
      color: #222;
      margin-bottom: 4px;
    }

    .company-info p {
      font-size: 12px;
      color: #666;
    }

    .invoice-title {
      text-align: right;
    }

    .invoice-title h2 {
      font-size: 28px;
      color: #333;
      text-transform: uppercase;
      letter-spacing: 2px;
    }

    .invoice-title .invoice-number {
      font-size: 14px;
      color: #666;
      margin-top: 4px;
    }

    .invoice-meta {
      display: flex;
      justify-content: space-between;
      margin-bottom: 20px;
    }

    .meta-section {
      width: 48%;
    }

    .meta-section h3 {
      font-size: 12px;
      text-transform: uppercase;
      color: #999;
      letter-spacing: 1px;
      margin-bottom: 6px;
      border-bottom: 1px solid #eee;
      padding-bottom: 4px;
    }

    .meta-section table {
      width: 100%;
    }

    .meta-section table td {
      padding: 2px 0;
      font-size: 12px;
    }

    .meta-section table td:first-child {
      color: #888;
      width: 130px;
    }

    .meta-section table td:last-child {
      font-weight: 600;
    }

    /* Line Items */
    .items-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    .items-table thead th {
      background-color: #333;
      color: #fff;
      padding: 8px 10px;
      text-align: left;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .items-table thead th.text-end {
      text-align: right;
    }

    .items-table thead th.text-center {
      text-align: center;
    }

    .items-table tbody td {
      padding: 8px 10px;
      border-bottom: 1px solid #eee;
      font-size: 12px;
    }

    .items-table tbody tr:nth-child(even) {
      background-color: #fafafa;
    }

    .text-end {
      text-align: right;
    }

    .text-center {
      text-align: center;
    }

    /* Totals */
    .totals-section {
      display: flex;
      justify-content: flex-end;
      margin-bottom: 20px;
    }

    .totals-table {
      width: 300px;
      border-collapse: collapse;
    }

    .totals-table td {
      padding: 5px 10px;
      font-size: 12px;
    }

    .totals-table td:first-child {
      text-align: left;
      color: #666;
    }

    .totals-table td:last-child {
      text-align: right;
      font-weight: 600;
    }

    .totals-table .grand-total td {
      border-top: 2px solid #333;
      font-size: 15px;
      padding-top: 8px;
      color: #333;
    }

    .totals-table .amount-due td {
      border-top: 1px solid #ccc;
      color: #c00;
    }

    /* Notes */
    .notes-section {
      margin-top: 20px;
      padding-top: 15px;
      border-top: 1px solid #ddd;
    }

    .notes-section h3 {
      font-size: 12px;
      text-transform: uppercase;
      color: #999;
      letter-spacing: 1px;
      margin-bottom: 6px;
    }

    .notes-section p {
      font-size: 12px;
      color: #555;
    }

    .badge {
      display: inline-block;
      padding: 2px 8px;
      border-radius: 4px;
      font-size: 11px;
      font-weight: 600;
    }

    .badge-success {
      background: #d4edda;
      color: #155724;
    }

    .badge-danger {
      background: #f8d7da;
      color: #721c24;
    }

    .badge-warning {
      background: #fff3cd;
      color: #856404;
    }

    .badge-info {
      background: #d1ecf1;
      color: #0c5460;
    }

    .footer {
      margin-top: 40px;
      padding-top: 15px;
      border-top: 1px solid #ddd;
      text-align: center;
      font-size: 11px;
      color: #999;
    }

    /* Print controls */
    .no-print {
      margin-bottom: 20px;
      text-align: right;
    }

    .no-print button {
      padding: 8px 16px;
      margin-left: 8px;
      border: 1px solid #ccc;
      border-radius: 4px;
      background: #fff;
      cursor: pointer;
      font-size: 13px;
    }

    .no-print button:first-child {
      background: #333;
      color: #fff;
      border-color: #333;
    }

    @media print {
      body {
        margin: 0;
      }

      .no-print {
        display: none !important;
      }
    }

    @page {
      size: A4;
      margin: 15mm;
    }
  </style>
</head>

<body onload="window.print()">

  <!-- Print Controls (hidden when printing) -->
  <div class="no-print">
    <button onclick="window.print()">🖨️ Print / Save as PDF</button>
    <button onclick="window.close()">✕ Close</button>
  </div>

  <!-- Invoice Header -->
  <div class="invoice-header">
    <div class="company-info">
      <h1><?= esc(session()->get('company_name') ?? 'Company Name') ?></h1>
      <p><?= esc(session()->get('company_address') ?? '') ?></p>
      <p><?= esc(session()->get('company_phone') ?? '') ?></p>
      <?php if (!empty(session()->get('company_gstin'))): ?>
        <p><strong>GSTIN:</strong> <?= esc(session()->get('company_gstin')) ?></p>
      <?php endif; ?>
    </div>
    <div class="invoice-title">
      <h2><?= esc($invoice['invoice_type'] ?? 'Invoice') ?></h2>
      <p class="invoice-number">#<?= esc($invoice['invoice_number']) ?></p>
    </div>
  </div>

  <!-- Invoice Meta -->
  <div class="invoice-meta">
    <div class="meta-section">
      <h3>Invoice Details</h3>
      <table>
        <tr>
          <td>Invoice Number:</td>
          <td><?= esc($invoice['invoice_number']) ?></td>
        </tr>
        <tr>
          <td>Invoice Date:</td>
          <td><?= date('d M Y', strtotime($invoice['invoice_date'])) ?></td>
        </tr>
        <?php if (!empty($invoice['due_date'])): ?>
          <tr>
            <td>Due Date:</td>
            <td><?= date('d M Y', strtotime($invoice['due_date'])) ?></td>
          </tr>
        <?php endif; ?>
        <?php if (!empty($invoice['reference_number'])): ?>
          <tr>
            <td>Reference:</td>
            <td><?= esc($invoice['reference_number']) ?></td>
          </tr>
        <?php endif; ?>
        <tr>
          <td>Payment Status:</td>
          <td>
            <?php
            $statusClass = 'badge-danger';
            $statusText = $invoice['payment_status'];
            if ($invoice['payment_status'] === 'Paid') {
              $statusClass = 'badge-success';
            } elseif ($invoice['payment_status'] === 'Partial Paid') {
              $statusClass = 'badge-warning';
            } elseif ($invoice['payment_status'] === 'Pending') {
              $statusText = 'Unpaid';
            }
            ?>
            <span class="badge <?= $statusClass ?>"><?= esc($statusText) ?></span>
          </td>
        </tr>
      </table>
    </div>

    <div class="meta-section">
      <h3>Customer Details</h3>
      <table>
        <tr>
          <td>Customer Name:</td>
          <td><?= esc($invoice['customer']['customer_name'] ?? $invoice['customer']['account_name'] ?? 'N/A') ?></td>
        </tr>
        <?php if (!empty($invoice['customer']['mobile']) || !empty($invoice['customer']['mobile_number'])): ?>
          <tr>
            <td>Mobile:</td>
            <td><?= esc($invoice['customer']['mobile'] ?? $invoice['customer']['mobile_number'] ?? '') ?></td>
          </tr>
        <?php endif; ?>
        <?php if (!empty($invoice['billing_address'])): ?>
          <tr>
            <td>Billing Address:</td>
            <td><?= esc($invoice['billing_address']) ?></td>
          </tr>
        <?php endif; ?>
        <?php if (!empty($invoice['shipping_address'])): ?>
          <tr>
            <td>Shipping Address:</td>
            <td><?= esc($invoice['shipping_address']) ?></td>
          </tr>
        <?php endif; ?>
      </table>
    </div>
  </div>

  <!-- Line Items -->
  <table class="items-table">
    <thead>
      <tr>
        <th style="width: 40px;">#</th>
        <th>Products</th>
        <th>Processes</th>
        <th class="text-center">Qty</th>
        <th class="text-end">Weight (g)</th>
        <th class="text-end">Rate (₹)</th>
        <th class="text-end">Amount (₹)</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($invoice['lines'])): ?>
        <?php foreach ($invoice['lines'] as $index => $line): ?>
          <tr>
            <td class="text-center"><?= $index + 1 ?></td>
            <td>
              <?php if (!empty($line['products'])): ?>
                <?= implode(', ', array_column($line['products'], 'product_name')) ?>
              <?php elseif (!empty($line['product_name'])): ?>
                <?= esc($line['product_name']) ?>
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
            <td>
              <?php if (!empty($line['processes'])): ?>
                <?= implode(', ', array_column($line['processes'], 'process_name')) ?>
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
            <td class="text-center"><?= $line['quantity'] ?? 1 ?></td>
            <td class="text-end"><?= number_format(($line['gold_weight'] > 0 ? $line['gold_weight'] : $line['weight']) ?? 0, 3) ?></td>
            <td class="text-end"><?= number_format($line['rate'] ?? 0, 2) ?></td>
            <td class="text-end"><?= number_format($line['amount'] ?? 0, 2) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="7" class="text-center">No line items</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- Totals -->
  <div class="totals-section">
    <table class="totals-table">
      <tr>
        <td>Subtotal:</td>
        <td>₹<?= number_format($invoice['subtotal'], 2) ?></td>
      </tr>

      <?php if ($invoice['cgst_amount'] > 0 || $invoice['sgst_amount'] > 0): ?>
        <tr>
          <td>CGST (<?= number_format($invoice['tax_rate'] / 2, 2) ?>%):</td>
          <td>₹<?= number_format($invoice['cgst_amount'], 2) ?></td>
        </tr>
        <tr>
          <td>SGST (<?= number_format($invoice['tax_rate'] / 2, 2) ?>%):</td>
          <td>₹<?= number_format($invoice['sgst_amount'], 2) ?></td>
        </tr>
      <?php elseif ($invoice['igst_amount'] > 0): ?>
        <tr>
          <td>IGST (<?= number_format($invoice['tax_rate'], 2) ?>%):</td>
          <td>₹<?= number_format($invoice['igst_amount'], 2) ?></td>
        </tr>
      <?php endif; ?>

      <tr>
        <td>Tax Amount:</td>
        <td>₹<?= number_format($invoice['tax_amount'], 2) ?></td>
      </tr>

      <tr class="grand-total">
        <td><strong>Grand Total:</strong></td>
        <td><strong>₹<?= number_format($invoice['grand_total'], 2) ?></strong></td>
      </tr>

      <?php if ($invoice['total_paid'] > 0): ?>
        <tr>
          <td>Amount Paid:</td>
          <td style="color: green;">₹<?= number_format($invoice['total_paid'], 2) ?></td>
        </tr>
      <?php endif; ?>

      <?php if ($invoice['amount_due'] > 0): ?>
        <tr class="amount-due">
          <td><strong>Amount Due:</strong></td>
          <td><strong>₹<?= number_format($invoice['amount_due'], 2) ?></strong></td>
        </tr>
      <?php endif; ?>
    </table>
  </div>

  <!-- Notes & Terms -->
  <?php if (!empty($invoice['notes']) || !empty($invoice['terms_conditions'])): ?>
    <div class="notes-section">
      <?php if (!empty($invoice['notes'])): ?>
        <h3>Notes</h3>
        <p><?= nl2br(esc($invoice['notes'])) ?></p>
      <?php endif; ?>

      <?php if (!empty($invoice['terms_conditions'])): ?>
        <h3 style="margin-top: 10px;">Terms & Conditions</h3>
        <p><?= nl2br(esc($invoice['terms_conditions'])) ?></p>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- Footer -->
  <div class="footer">
    <p>This is a computer-generated invoice. Printed on <?= date('d M Y, h:i A') ?></p>
  </div>

</body>

</html>
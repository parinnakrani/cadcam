<?php
// ─── Extract challan data ───────────────────────────────────────────────────
$challanNumber  = $challan['challan_number']  ?? '';
$challanType    = $challan['challan_type']    ?? 'Rhodium';
$challanStatus  = $challan['challan_status']  ?? 'Draft';
$customerType   = $challan['customer_type']   ?? 'Account';
$challanDate    = $challan['challan_date']    ?? null;
$deliveryDate   = $challan['delivery_date']   ?? null;
$notes          = $challan['notes']           ?? '';
$lines          = $challan['lines']           ?? [];
$totalWeight    = (float)($challan['total_weight']    ?? 0);
$subtotalAmount = (float)($challan['subtotal_amount'] ?? 0);
$totalAmount    = (float)($challan['total_amount']    ?? 0);

// Customer name
if ($customerType === 'Account') {
  $customerName   = $challan['account_name']   ?? ('Account #' . ($challan['account_id'] ?? ''));
  $customerMobile = $challan['account_mobile'] ?? ($challan['mobile'] ?? '');
  $customerGst    = $challan['account_gst']    ?? '';
} else {
  $customerName   = $challan['customer_name']  ?? ('Cash #' . ($challan['cash_customer_id'] ?? ''));
  $customerMobile = $challan['cash_mobile']    ?? ($challan['mobile'] ?? '');
  $customerGst    = '';
}

// Company info from session
$companyName    = session()->get('company_name')    ?? 'Company Name';
$companyAddress = session()->get('company_address') ?? '';
$companyPhone   = session()->get('company_phone')   ?? '';
$companyGstin   = session()->get('company_gstin')   ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Challan <?= esc($challanNumber) ?> — <?= esc($companyName) ?></title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: Arial, sans-serif;
      font-size: 13px;
      color: #222;
      background: #fff;
      margin: 20px;
      line-height: 1.5;
    }

    /* ── Header ── */
    .challan-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      border-bottom: 3px solid #222;
      padding-bottom: 14px;
      margin-bottom: 18px;
    }

    .company-info h1 {
      font-size: 22px;
      color: #111;
      margin-bottom: 3px;
    }

    .company-info p {
      font-size: 11px;
      color: #555;
    }

    .challan-title {
      text-align: right;
    }

    .challan-title h2 {
      font-size: 26px;
      color: #222;
      text-transform: uppercase;
      letter-spacing: 2px;
    }

    .challan-title .challan-number {
      font-size: 13px;
      color: #555;
      margin-top: 3px;
    }

    .challan-title .challan-type-badge {
      display: inline-block;
      margin-top: 4px;
      padding: 2px 10px;
      border-radius: 3px;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .type-Rhodium {
      background: #dbeafe;
      color: #1e40af;
    }

    .type-Meena {
      background: #dcfce7;
      color: #166534;
    }

    .type-Wax {
      background: #fef9c3;
      color: #854d0e;
    }

    /* ── Meta grid ── */
    .meta-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 18px;
      gap: 20px;
    }

    .meta-box {
      flex: 1;
    }

    .meta-box h3 {
      font-size: 10px;
      text-transform: uppercase;
      color: #999;
      letter-spacing: 1px;
      border-bottom: 1px solid #e5e5e5;
      padding-bottom: 4px;
      margin-bottom: 8px;
    }

    .meta-box table {
      width: 100%;
    }

    .meta-box table td {
      padding: 2px 0;
      font-size: 12px;
      vertical-align: top;
    }

    .meta-box table td:first-child {
      color: #777;
      width: 120px;
    }

    .meta-box table td:last-child {
      font-weight: 600;
    }

    /* ── Line Items ── */
    .items-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 18px;
      font-size: 12px;
    }

    .items-table thead th {
      background: #222;
      color: #fff;
      padding: 7px 9px;
      font-size: 10px;
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
      padding: 7px 9px;
      border-bottom: 1px solid #eee;
      vertical-align: top;
    }

    .items-table tbody tr:nth-child(even) {
      background: #fafafa;
    }

    .text-end {
      text-align: right;
    }

    .text-center {
      text-align: center;
    }

    .text-muted {
      color: #888;
      font-size: 11px;
    }

    /* Gold adjustment sub-row */
    .adj-row td {
      font-size: 11px;
      color: #666;
      background: #f8f8f8 !important;
      padding: 3px 9px;
      border-bottom: 1px solid #eee;
    }

    /* ── Totals ── */
    .totals-section {
      display: flex;
      justify-content: flex-end;
      margin-bottom: 20px;
    }

    .totals-table {
      width: 270px;
      border-collapse: collapse;
    }

    .totals-table td {
      padding: 5px 9px;
      font-size: 12px;
    }

    .totals-table td:first-child {
      color: #666;
    }

    .totals-table td:last-child {
      text-align: right;
      font-weight: 600;
    }

    .totals-table .grand-total td {
      border-top: 2px solid #222;
      font-size: 15px;
      padding-top: 8px;
      color: #111;
    }

    /* ── Notes ── */
    .notes-section {
      padding-top: 14px;
      border-top: 1px solid #ddd;
      margin-bottom: 20px;
    }

    .notes-section h3 {
      font-size: 10px;
      text-transform: uppercase;
      color: #999;
      letter-spacing: 1px;
      margin-bottom: 5px;
    }

    .notes-section p {
      font-size: 12px;
      color: #444;
    }

    /* ── Signature ── */
    .signature-row {
      display: flex;
      justify-content: space-between;
      margin-top: 40px;
    }

    .sig-box {
      width: 180px;
      text-align: center;
    }

    .sig-box .sig-line {
      border-top: 1px solid #555;
      margin-bottom: 6px;
    }

    .sig-box .sig-label {
      font-size: 11px;
      color: #666;
    }

    /* ── Footer ── */
    .footer {
      margin-top: 30px;
      padding-top: 10px;
      border-top: 1px solid #ddd;
      text-align: center;
      font-size: 10px;
      color: #aaa;
    }

    /* ── Print controls (screen only) ── */
    .no-print {
      margin-bottom: 20px;
      text-align: right;
    }

    .no-print button {
      padding: 7px 14px;
      margin-left: 8px;
      border: 1px solid #ccc;
      border-radius: 4px;
      background: #fff;
      cursor: pointer;
      font-size: 13px;
    }

    .no-print button.btn-print {
      background: #222;
      color: #fff;
      border-color: #222;
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
    <button class="btn-print" onclick="window.print()">🖨️ Print / Save as PDF</button>
    <button onclick="window.history.back()">← Back</button>
  </div>

  <!-- ══ HEADER ══════════════════════════════════════════════════════════════ -->
  <div class="challan-header">
    <div class="company-info">
      <h1><?= esc($companyName) ?></h1>
      <?php if ($companyAddress): ?><p><?= esc($companyAddress) ?></p><?php endif; ?>
      <?php if ($companyPhone):   ?><p>📞 <?= esc($companyPhone) ?></p><?php endif; ?>
      <?php if ($companyGstin):   ?><p><strong>GSTIN:</strong> <?= esc($companyGstin) ?></p><?php endif; ?>
    </div>
    <div class="challan-title">
      <h2>Job Order</h2>
      <p class="challan-number">#<?= esc($challanNumber) ?></p>
      <span class="challan-type-badge type-<?= esc($challanType) ?>"><?= esc($challanType) ?></span>
    </div>
  </div>

  <!-- ══ META INFO ═══════════════════════════════════════════════════════════ -->
  <div class="meta-row">
    <!-- Challan Details -->
    <div class="meta-box">
      <h3>Challan Details</h3>
      <table>
        <tr>
          <td>Challan No:</td>
          <td><?= esc($challanNumber) ?></td>
        </tr>
        <tr>
          <td>Type:</td>
          <td><?= esc($challanType) ?></td>
        </tr>
        <tr>
          <td>Status:</td>
          <td><?= esc($challanStatus) ?></td>
        </tr>
        <tr>
          <td>Date:</td>
          <td><?= $challanDate ? date('d M Y', strtotime($challanDate)) : '—' ?></td>
        </tr>
        <?php if ($deliveryDate): ?>
          <tr>
            <td>Delivery By:</td>
            <td><?= date('d M Y', strtotime($deliveryDate)) ?></td>
          </tr>
        <?php endif; ?>
        <tr>
          <td>Printed On:</td>
          <td><?= date('d M Y, h:i A') ?></td>
        </tr>
      </table>
    </div>

    <!-- Customer Details -->
    <div class="meta-box">
      <h3>Customer Details</h3>
      <table>
        <tr>
          <td>Name:</td>
          <td><?= esc($customerName) ?></td>
        </tr>
        <tr>
          <td>Type:</td>
          <td><?= esc($customerType) ?></td>
        </tr>
        <?php if ($customerMobile): ?>
          <tr>
            <td>Mobile:</td>
            <td><?= esc($customerMobile) ?></td>
          </tr>
        <?php endif; ?>
        <?php if ($customerGst): ?>
          <tr>
            <td>GST No:</td>
            <td><?= esc($customerGst) ?></td>
          </tr>
        <?php endif; ?>
      </table>
    </div>
  </div>

  <!-- ══ LINE ITEMS ══════════════════════════════════════════════════════════ -->
  <table class="items-table">
    <thead>
      <tr>
        <th style="width: 36px;">#</th>
        <th>Products</th>
        <th>Processes</th>
        <th class="text-center" style="width: 50px;">Qty</th>
        <th class="text-end" style="width: 80px;">Weight (g)</th>
        <th class="text-end" style="width: 80px;">Gold Wt (g)</th>
        <th class="text-center" style="width: 55px;">Purity</th>
        <th class="text-end" style="width: 85px;">Rate (₹)</th>
        <th class="text-end" style="width: 90px;">Amount (₹)</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($lines)): ?>
        <?php foreach ($lines as $i => $line): ?>
          <tr>
            <!-- # -->
            <td class="text-center"><?= $i + 1 ?></td>

            <!-- Products -->
            <td>
              <?php
              $pName = $line['product_name'] ?? '';
              $pIds  = $line['product_ids']  ?? [];
              if (!empty($pName)) {
                echo esc($pName);
              } elseif (!empty($pIds) && is_array($pIds)) {
                echo '<span class="text-muted">IDs: ' . implode(', ', $pIds) . '</span>';
              } else {
                echo '<span class="text-muted">—</span>';
              }
              ?>
            </td>

            <!-- Processes -->
            <td>
              <?php
              $processPrices = $line['process_prices'] ?? [];
              $processIds    = $line['process_ids']    ?? [];
              if (!empty($processPrices) && is_array($processPrices)) {
                $parts = [];
                foreach ($processPrices as $pp) {
                  $pn = $pp['process_name'] ?? $pp['name'] ?? ('Process #' . ($pp['process_id'] ?? ($pp['id'] ?? '?')));
                  $pr = number_format((float)($pp['rate'] ?? 0), 2);
                  $parts[] = esc($pn) . ' (₹' . $pr . ')';
                }
                echo implode(', ', $parts);
              } elseif (!empty($processIds) && is_array($processIds)) {
                echo '<span class="text-muted">IDs: ' . implode(', ', $processIds) . '</span>';
              } else {
                echo '<span class="text-muted">—</span>';
              }
              ?>
            </td>

            <!-- Qty -->
            <td class="text-center"><?= (int)($line['quantity'] ?? 1) ?></td>

            <!-- Weight -->
            <td class="text-end"><?= number_format((float)($line['weight'] ?? 0), 3) ?></td>

            <!-- Gold Weight -->
            <td class="text-end">
              <?php
              $gw = $line['gold_weight'] ?? null;
              echo ($gw !== null && $gw !== '') ? number_format((float)$gw, 3) : '—';
              ?>
            </td>

            <!-- Purity -->
            <td class="text-center">
              <?= !empty($line['gold_purity']) ? esc($line['gold_purity']) : '—' ?>
            </td>

            <!-- Rate -->
            <td class="text-end">₹<?= number_format((float)($line['rate'] ?? 0), 2) ?></td>

            <!-- Amount -->
            <td class="text-end"><strong>₹<?= number_format((float)($line['amount'] ?? 0), 2) ?></strong></td>
          </tr>

          <?php
          // Gold adjustment sub-row
          $goldAdj = (float)($line['gold_adjustment_amount'] ?? 0);
          if ($goldAdj != 0):
            $adjWeight = number_format((float)($line['adjusted_gold_weight'] ?? 0), 3);
            $goldPrice = number_format((float)($line['current_gold_price']   ?? 0), 2);
            $sign = $goldAdj > 0 ? '+' : '';
          ?>
            <tr class="adj-row">
              <td></td>
              <td colspan="8">
                Gold Adj: Wt Diff = <strong><?= $adjWeight ?>g</strong>
                × Rate ₹<?= $goldPrice ?>
                = <strong><?= $sign ?>₹<?= number_format($goldAdj, 2) ?></strong>
              </td>
            </tr>
          <?php endif; ?>

          <?php if (!empty($line['line_notes'])): ?>
            <tr class="adj-row">
              <td></td>
              <td colspan="8" style="font-style: italic;">
                Note: <?= esc($line['line_notes']) ?>
              </td>
            </tr>
          <?php endif; ?>

        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="9" class="text-center text-muted" style="padding: 20px;">
            No line items found for this challan.
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- ══ TOTALS ══════════════════════════════════════════════════════════════ -->
  <div class="totals-section">
    <table class="totals-table">
      <tr>
        <td>Total Weight:</td>
        <td><?= number_format($totalWeight, 3) ?> g</td>
      </tr>
      <?php if ($subtotalAmount > 0 && $subtotalAmount != $totalAmount): ?>
        <tr>
          <td>Subtotal:</td>
          <td>₹<?= number_format($subtotalAmount, 2) ?></td>
        </tr>
      <?php endif; ?>
      <tr class="grand-total">
        <td><strong>Total Amount:</strong></td>
        <td><strong>₹<?= number_format($totalAmount, 2) ?></strong></td>
      </tr>
    </table>
  </div>

  <!-- ══ NOTES ═══════════════════════════════════════════════════════════════ -->
  <?php if (!empty($notes)): ?>
    <div class="notes-section">
      <h3>Notes</h3>
      <p><?= nl2br(esc($notes)) ?></p>
    </div>
  <?php endif; ?>

  <!-- ══ SIGNATURE ═══════════════════════════════════════════════════════════ -->
  <div class="signature-row">
    <div class="sig-box">
      <div class="sig-line"></div>
      <div class="sig-label">Customer Signature</div>
    </div>
    <div class="sig-box">
      <div class="sig-line"></div>
      <div class="sig-label">Received By</div>
    </div>
    <div class="sig-box">
      <div class="sig-line"></div>
      <div class="sig-label">Authorised Signatory</div>
    </div>
  </div>

  <!-- ══ FOOTER ══════════════════════════════════════════════════════════════ -->
  <div class="footer">
    <p>
      <?= esc($companyName) ?> &nbsp;|&nbsp;
      Challan #<?= esc($challanNumber) ?> &nbsp;|&nbsp;
      This is a computer-generated job order. Printed on <?= date('d M Y, h:i A') ?>
    </p>
  </div>

</body>

</html>
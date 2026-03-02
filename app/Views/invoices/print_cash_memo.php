<?php
// ─── Extract invoice data ───────────────────────────────────────────────────
$invoiceNumber  = $invoice['invoice_number']  ?? '';
$invoiceDate    = $invoice['invoice_date']    ?? null;
$customerType   = $invoice['customer_type']   ?? 'Cash';
$lines          = $invoice['lines']           ?? [];
$created_at     = $invoice['created_at']      ?? null;

// Company info
$companyName    = $company['business_legal_name'] ?? ($company['company_name'] ?? session()->get('company_name') ?? 'Company Name');
$companyAddress1 = $company['address_line1'] ?? '';
$companyAddress2 = $company['address_line2'] ?? '';
$companyAddress  = trim($companyAddress1 . ' ' . $companyAddress2) ?: session()->get('company_address');
$companyPhone   = $company['contact_phone'] ?? ($company['mobile'] ?? session()->get('company_phone')) ?? '';

// Customer name
$customerName   = $invoice['customer_name'] ?? 'Cash Customer';

// Time
$timeStr = $created_at ? date('h:i A', strtotime($created_at)) : date('h:i A');
$dateStr = $invoiceDate ? date('d/m/Y', strtotime($invoiceDate)) : date('d/m/Y');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Cash Memo <?= esc($invoiceNumber) ?></title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: Arial, sans-serif;
      font-size: 13px;
      /* Slightly smaller for thermal */
      color: #000;
      background: #fff;
      padding: 10px;
      /* Reduced padding */
      width: 80mm;
      /* Standard thermal printer width */
      margin: 0 auto;
    }

    .receipt-container {
      /* Remove border and padding for thermal */
      border: none;
      padding: 0;
    }

    .header {
      text-align: center;
      margin-bottom: 8px;
    }

    .company-name {
      font-size: 18px;
      /* Slightly smaller */
      font-weight: bold;
      text-transform: uppercase;
      margin-bottom: 3px;
      letter-spacing: 0.5px;
    }

    .company-address {
      font-size: 11px;
      /* Smaller */
      margin-bottom: 2px;
    }

    .company-phone {
      font-size: 11px;
      /* Smaller */
    }

    .divider {
      border-top: 1px dashed #000;
      /* Dashed looks better on thermal */
      margin: 8px 0;
    }

    .memo-title {
      text-align: center;
      font-size: 15px;
      font-weight: bold;
      margin-bottom: 3px;
    }

    .meta-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 5px;
    }

    .meta-table td {
      padding: 3px 0;
      /* Minimal padding */
      font-size: 12px;
      /* Smaller */
      vertical-align: top;
    }

    .meta-table td:nth-child(2) {
      width: 45%;
      text-align: right;
      /* Right align the second column in thermal */
    }

    .items-table {
      width: 100%;
      border-collapse: collapse;
    }

    .items-table th,
    .items-table td {
      padding: 5px 2px;
      /* Minimal padding */
      text-align: left;
      font-size: 11px;
      /* Smaller for items */
    }

    .items-table th {
      font-weight: bold;
      border-bottom: 1px solid #000;
      /* Add bottom border to headers */
    }

    .text-center {
      text-align: center !important;
    }

    .text-right {
      text-align: right !important;
    }

    .no-print {
      margin-bottom: 20px;
      text-align: center;
      /* Center buttons */
    }

    .no-print button {
      padding: 7px 14px;
      margin: 0 4px;
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

    /* Remove simulated empty rows for thermal */
    .empty-row {
      display: none;
    }

    @media print {
      @page {
        margin: 0;
        /* Important for thermal printers */
        /* width: 80mm; */
        /* Define width in media print sometimes helps if driver allows */
      }

      body {
        margin: 0;
        padding: 5px;
        width: 100%;
        /* Take full width of paper */
      }

      .no-print {
        display: none !important;
      }
    }
  </style>
</head>

<body onload="window.print()">
  <div class="no-print">
    <button class="btn-print" onclick="window.print()">🖨️ Print Cash Memo</button>
    <button onclick="window.close()">✖ Close</button>
  </div>

  <div class="receipt-container">
    <div class="header">
      <div class="company-name"><?= esc($companyName) ?></div>
      <?php if ($companyAddress1): ?>
        <div class="company-address"><?= esc($companyAddress1) ?></div>
      <?php endif; ?>
      <?php if ($companyAddress2): ?>
        <div class="company-address"><?= esc($companyAddress2) ?></div>
      <?php endif; ?>
      <?php if (!$companyAddress1 && !$companyAddress2 && $companyAddress): ?>
        <div class="company-address"><?= esc($companyAddress) ?></div>
      <?php endif; ?>
      <?php if ($companyPhone): ?>
        <div class="company-phone">Mobile No <?= esc($companyPhone) ?></div>
      <?php endif; ?>
    </div>

    <div class="divider"></div>
    <div class="memo-title">Cash Memo</div>
    <div class="divider"></div>

    <table class="meta-table">
      <tr>
        <td><strong>Party :</strong> <?= esc($customerName) ?></td>
        <td><strong>Bill No:</strong> <?= str_pad(esc($invoiceNumber), 5, '0', STR_PAD_LEFT) ?></td>
      </tr>
      <tr>
        <td><strong>Delivery:</strong> </td>
        <td><strong>Date &nbsp;&nbsp;:</strong> <?= esc($dateStr) ?></td>
      </tr>
      <tr>
        <td><strong>Receiver:</strong> </td>
        <td><strong>Time &nbsp;&nbsp;:</strong> <?= esc($timeStr) ?></td>
      </tr>
    </table>

    <div class="divider"></div>

    <table class="items-table">
      <thead>
        <tr>
          <th style="width: 40%;">Item Name</th>
          <th style="width: 25%;">Process</th>
          <th style="width: 15%;" class="text-center">Pcs</th>
          <th style="width: 20%;" class="text-right">Weight</th>
        </tr>
      </thead>
      <tbody>
        <tr class="empty-row" style="height: 5px;">
          <td colspan="4" style="border-bottom: 1px solid #555; height: 5px; padding: 0;"></td>
        </tr>

        <?php if (!empty($lines)): ?>
          <?php foreach ($lines as $line): ?>
            <tr>
              <td>
                <?php
                $pids = !empty($line['product_ids']) ? (is_string($line['product_ids']) ? json_decode($line['product_ids'], true) : $line['product_ids']) : [];
                if (!empty($pids) && is_array($pids)) {
                  $codes = [];
                  foreach ($pids as $pid) {
                    if (!empty($productMap[$pid])) {
                      $codes[] = $productMap[$pid];
                    }
                  }
                  if (!empty($codes)) {
                    echo esc(implode(', ', $codes));
                  } else {
                    echo esc($line['product_name'] ?? '');
                  }
                } else {
                  echo esc($line['product_name'] ?? '');
                }
                ?>
              </td>
              <td>
                <?php
                $pids = !empty($line['process_ids']) ? (is_string($line['process_ids']) ? json_decode($line['process_ids'], true) : $line['process_ids']) : [];
                if (!empty($pids) && is_array($pids)) {
                  $codes = [];
                  foreach ($pids as $pid) {
                    if (!empty($processMap[$pid])) {
                      $codes[] = $processMap[$pid];
                    }
                  }
                  if (!empty($codes)) {
                    echo esc(implode(', ', $codes));
                  } else {
                    echo '—';
                  }
                } else {
                  echo '—';
                }
                ?>
              </td>
              <td class="text-center"><?= (int)($line['quantity'] ?? 1) ?></td>
              <td class="text-right"><?= number_format((float)($line['weight'] ?? 0), 3) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="4" class="text-center">No items found</td>
          </tr>
        <?php endif; ?>

        <!-- Empty lines for padding -->
        <tr class="empty-row">
          <td colspan="4"></td>
        </tr>
        <tr class="empty-row">
          <td colspan="4"></td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="4" style="border-top: 1px dashed #000;"></td>
        </tr>
        <tr>
          <td colspan="3" class="text-right"><strong>Total Amount:</strong></td>
          <td class="text-right"><strong>₹ <?= number_format((float)($invoice['grand_total'] ?? 0), 2) ?></strong></td>
        </tr>
      </tfoot>
    </table>
  </div>
</body>

</html>
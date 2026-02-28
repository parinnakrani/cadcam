<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daily Invoice Report - <?= date('d M Y', strtotime($date)) ?></title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      font-size: 12px;
      color: #333;
      padding: 20px;
    }

    .header {
      text-align: center;
      margin-bottom: 20px;
      border-bottom: 2px solid #333;
      padding-bottom: 10px;
    }

    .header h1 {
      font-size: 18px;
      margin-bottom: 4px;
    }

    .header p {
      font-size: 13px;
      color: #666;
    }

    .summary {
      display: flex;
      justify-content: space-between;
      margin-bottom: 15px;
      padding: 8px 12px;
      background: #f5f5f5;
      border-radius: 4px;
    }

    .summary div {
      font-weight: 600;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 15px;
    }

    th,
    td {
      border: 1px solid #ddd;
      padding: 6px 10px;
      text-align: left;
    }

    th {
      background: #f0f0f0;
      font-weight: 700;
      font-size: 11px;
      text-transform: uppercase;
    }

    .text-end {
      text-align: right;
    }

    .text-center {
      text-align: center;
    }

    tfoot td {
      font-weight: 700;
      background: #f0f0f0;
      font-size: 13px;
    }

    .print-actions {
      text-align: center;
      margin-bottom: 15px;
    }

    .print-actions button {
      padding: 8px 24px;
      font-size: 13px;
      cursor: pointer;
      background: #696cff;
      color: #fff;
      border: none;
      border-radius: 4px;
      margin: 0 5px;
    }

    .print-actions button:hover {
      opacity: 0.9;
    }

    .print-actions .btn-close-window {
      background: #8592a3;
    }

    @media print {
      .print-actions {
        display: none;
      }

      body {
        padding: 0;
      }
    }
  </style>
</head>

<body>

  <div class="print-actions">
    <button onclick="window.print()">🖨️ Print</button>
    <button class="btn-close-window" onclick="window.close()">✕ Close</button>
  </div>

  <div class="header">
    <h1>Daily Invoice Report</h1>
    <p>Date: <?= date('d M Y (l)', strtotime($date)) ?></p>
  </div>

  <div class="summary">
    <div>Total Invoices: <?= $totalCount ?></div>
    <div>Grand Total: ₹ <?= number_format($grandTotal, 2) ?></div>
  </div>

  <?php if (!empty($invoices)): ?>
    <table>
      <thead>
        <tr>
          <th class="text-center" style="width:40px">#</th>
          <th>Date</th>
          <th>Invoice Number</th>
          <th>Invoice Type</th>
          <th>Customer Name</th>
          <th class="text-end">Total (₹)</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($invoices as $i => $inv): ?>
          <tr>
            <td class="text-center"><?= $i + 1 ?></td>
            <td><?= date('d M Y', strtotime($inv['invoice_date'])) ?></td>
            <td><?= esc($inv['invoice_number']) ?></td>
            <td><?= esc($inv['invoice_type']) ?></td>
            <td><?= esc($inv['customer_name']) ?></td>
            <td class="text-end">₹ <?= number_format((float)$inv['grand_total'], 2) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="5" class="text-end">Grand Total:</td>
          <td class="text-end">₹ <?= number_format($grandTotal, 2) ?></td>
        </tr>
      </tfoot>
    </table>
  <?php else: ?>
    <p style="text-align:center; color:#999; padding:30px 0;">No invoices found for this date.</p>
  <?php endif; ?>

  <div style="text-align:center; color:#999; font-size:10px; margin-top:20px; border-top:1px solid #eee; padding-top:8px;">
    Generated on <?= date('d M Y, h:i A') ?> | Gold ERP
  </div>

</body>

</html>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ledger Statement - <?= esc($name) ?></title>
  <style>
    body {
      font-family: Arial, sans-serif;
      font-size: 14px;
      margin: 20px;
    }

    .header {
      text-align: center;
      margin-bottom: 20px;
    }

    .details {
      margin-bottom: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    th,
    td {
      border: 1px solid #ccc;
      padding: 8px;
      text-align: left;
    }

    th {
      background-color: #f4f4f4;
    }

    .text-end {
      text-align: right;
    }

    .text-center {
      text-align: center;
    }

    @media print {
      .no-print {
        display: none;
      }
    }
  </style>
</head>

<body onload="window.print()">

  <div class="no-print" style="margin-bottom: 20px; text-align: right;">
    <button onclick="window.print()">Print / Save as PDF</button>
    <button onclick="window.close()">Close</button>
  </div>

  <div class="header">
    <h2>Ledger Statement</h2>
    <h3><?= esc($type) ?>: <?= esc($name) ?></h3>
  </div>

  <div class="details">
    <strong>From Date:</strong> <?= esc($fromDate ?: 'Start') ?> |
    <strong>To Date:</strong> <?= esc($toDate ?: 'Total') ?>
  </div>

  <table>
    <thead>
      <tr>
        <th>Date</th>
        <th>Reference #</th>
        <th>Type</th>
        <th>Description</th>
        <th class="text-end">Debit</th>
        <th class="text-end">Credit</th>
        <th class="text-end">Balance</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $runningBalance = $openingBalance;
      ?>

      <?php if ($fromDate): ?>
        <tr>
          <td><?= esc(date('d-M-Y', strtotime($fromDate))) ?></td>
          <td>-</td>
          <td>OPENING</td>
          <td>Opening Balance b/f</td>
          <td class="text-end"><?= ($openingBalance > 0) ? number_format($openingBalance, 2) : '-' ?></td>
          <td class="text-end"><?= ($openingBalance < 0) ? number_format(abs($openingBalance), 2) : '-' ?></td>
          <td class="text-end">
            <?= number_format(abs($runningBalance), 2) ?>
            <?= ($runningBalance >= 0) ? 'Dr' : 'Cr' ?>
          </td>
        </tr>
      <?php endif; ?>

      <?php if (empty($entries) && !$fromDate): ?>
        <tr>
          <td colspan="7" class="text-center">No transactions found.</td>
        </tr>
      <?php else: ?>
        <?php foreach ($entries as $entry): ?>
          <?php
          $debit  = (float)$entry['debit_amount'];
          $credit = (float)$entry['credit_amount'];
          $runningBalance = $runningBalance + $debit - $credit;
          ?>
          <tr>
            <td><?= esc(date('d-M-Y', strtotime($entry['entry_date']))) ?></td>
            <td><?= esc($entry['reference_number']) ?></td>
            <td><?= ucfirst(str_replace('_', ' ', $entry['reference_type'])) ?></td>
            <td><?= esc($entry['description']) ?></td>
            <td class="text-end"><?= ($debit > 0) ? number_format($debit, 2) : '-' ?></td>
            <td class="text-end"><?= ($credit > 0) ? number_format($credit, 2) : '-' ?></td>
            <td class="text-end">
              <?= number_format(abs($runningBalance), 2) ?>
              <?= ($runningBalance >= 0) ? 'Dr' : 'Cr' ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="6" class="text-end"><strong>Closing Balance</strong></td>
        <td class="text-end">
          <strong>
            <?= number_format(abs($runningBalance), 2) ?>
            <?= ($runningBalance >= 0) ? 'Dr' : 'Cr' ?>
          </strong>
        </td>
      </tr>
    </tfoot>
  </table>

</body>

</html>
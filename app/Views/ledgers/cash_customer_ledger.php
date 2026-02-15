<?= $this->extend('admintheme/layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="page-title-box d-sm-flex align-items-center justify-content-between">
        <h4 class="mb-sm-0">Cash Customer Ledger: <?= esc($cashCustomer['customer_name']) ?></h4>
        <div class="page-title-right">
          <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="<?= base_url('ledgers/cash-customers') ?>">Cash Customers</a></li>
            <li class="breadcrumb-item active">Ledger</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter -->
  <div class="row mb-3">
    <div class="col-12">
      <form method="get" action="<?= current_url() ?>" class="row gx-3 gy-2 align-items-center">
        <div class="col-sm-3">
          <label class="visually-hidden" for="from_date">From Date</label>
          <input type="date" class="form-control" id="from_date" name="from_date" value="<?= esc($fromDate) ?>" placeholder="From Date">
        </div>
        <div class="col-sm-3">
          <label class="visually-hidden" for="to_date">To Date</label>
          <input type="date" class="form-control" id="to_date" name="to_date" value="<?= esc($toDate) ?>" placeholder="To Date">
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-primary">Filter</button>
          <a href="<?= current_url() ?>" class="btn btn-light">Reset</a>
        </div>
        <div class="col-auto ms-auto">
          <div class="btn-group">
            <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fas fa-download"></i> Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="<?= base_url('ledgers/export/cash-customer/' . $cashCustomer['id']) ?>?format=csv&from_date=<?= $fromDate ?>&to_date=<?= $toDate ?>">CSV (Excel)</a></li>
              <li><a class="dropdown-item" href="<?= base_url('ledgers/export/cash-customer/' . $cashCustomer['id']) ?>?format=pdf&from_date=<?= $fromDate ?>&to_date=<?= $toDate ?>" target="_blank">Print / PDF</a></li>
            </ul>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered table-striped dt-responsive nowrap w-100">
              <thead class="table-light">
                <tr>
                  <th>Date</th>
                  <th>Ref #</th>
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

                <!-- Opening Balance Row if date filter applied -->
                <?php if ($fromDate): ?>
                  <tr class="table-active fw-bold">
                    <td><?= esc(date('d-M-Y', strtotime($fromDate))) ?></td>
                    <td>-</td>
                    <td>OPENING</td>
                    <td>Opening Balance b/f</td>
                    <td class="text-end">
                      <?= ($openingBalance > 0) ? number_format($openingBalance, 2) : '-' ?>
                    </td>
                    <td class="text-end">
                      <?= ($openingBalance < 0) ? number_format(abs($openingBalance), 2) : '-' ?>
                    </td>
                    <td class="text-end">
                      <?= number_format($runningBalance, 2) ?> <?= ($runningBalance >= 0) ? 'Dr' : 'Cr' ?>
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
                    // Recalculate running balance based on displayed rows + opening
                    $debit  = (float)$entry['debit_amount'];
                    $credit = (float)$entry['credit_amount'];
                    $runningBalance = $runningBalance + $debit - $credit;
                    ?>
                    <tr>
                      <td><?= esc(date('d-M-Y', strtotime($entry['entry_date']))) ?></td>
                      <td>
                        <?php if ($entry['reference_type'] == 'invoice'): ?>
                          <a href="<?= base_url('invoices/view/' . $entry['reference_id']) ?>">
                            <?= esc($entry['reference_number']) ?>
                          </a>
                        <?php else: ?>
                          <?= esc($entry['reference_number']) ?>
                        <?php endif; ?>
                      </td>
                      <td>
                        <span class="badge badge-soft-<?= $entry['reference_type'] == 'invoice' ? 'primary' : ($entry['reference_type'] == 'payment' ? 'success' : 'secondary') ?>">
                          <?= ucfirst(str_replace('_', ' ', $entry['reference_type'])) ?>
                        </span>
                      </td>
                      <td><?= esc($entry['description']) ?></td>
                      <td class="text-end text-danger">
                        <?= $debit > 0 ? number_format($debit, 2) : '-' ?>
                      </td>
                      <td class="text-end text-success">
                        <?= $credit > 0 ? number_format($credit, 2) : '-' ?>
                      </td>
                      <td class="text-end fw-bold">
                        <?= number_format(abs($runningBalance), 2) ?>
                        <?= ($runningBalance >= 0) ? 'Dr' : 'Cr' ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
              <tfoot class="table-light fw-bold">
                <tr>
                  <td colspan="6" class="text-end">Closing Balance</td>
                  <td class="text-end">
                    <?= number_format(abs($runningBalance), 2) ?>
                    <?= ($runningBalance >= 0) ? 'Dr' : 'Cr' ?>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
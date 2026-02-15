<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="page-title-box d-sm-flex align-items-center justify-content-between">
        <h4 class="mb-sm-0">Monthly Receivable Summary</h4>
        <div class="page-title-right">
          <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item active">Receivable Summary</li>
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
          <label class="visually-hidden" for="start_month">Start Month</label>
          <input type="month" class="form-control" id="start_month" name="start_month" value="<?= esc($startMonth) ?>" placeholder="Start Month">
        </div>
        <div class="col-sm-3">
          <label class="visually-hidden" for="end_month">End Month</label>
          <input type="month" class="form-control" id="end_month" name="end_month" value="<?= esc($endMonth) ?>" placeholder="End Month">
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-primary">Generate Report</button>
          <a href="<?= current_url() ?>" class="btn btn-light">Reset</a>
        </div>
      </form>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            <table id="receivable-table" class="table table-bordered table-striped dt-responsive nowrap w-100 table-sm">
              <thead class="table-light">
                <tr>
                  <th rowspan="2" class="align-middle">Customer Name</th>
                  <th rowspan="2" class="align-middle">Mobile</th>
                  <th rowspan="2" class="align-middle text-end">Opening Bal</th>
                  <?php foreach ($months as $m): ?>
                    <th colspan="3" class="text-center border-bottom-0"><?= date('M Y', strtotime($m . '-01')) ?></th>
                  <?php endforeach; ?>
                  <th rowspan="2" class="align-middle text-end">Closing Bal</th>
                </tr>
                <tr>
                  <?php foreach ($months as $m): ?>
                    <th class="text-end small">Dr</th>
                    <th class="text-end small">Cr</th>
                    <th class="text-end small">Bal</th>
                  <?php endforeach; ?>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($reportData as $row): ?>
                  <tr>
                    <td><?= esc($row['name']) ?></td>
                    <td><?= esc($row['mobile']) ?></td>
                    <td class="text-end fw-bold">
                      <?= ($row['opening_balance'] != 0) ? number_format($row['opening_balance'], 2) . (($row['opening_balance'] > 0) ? ' Dr' : ' Cr') : '-' ?>
                    </td>
                    <?php foreach ($months as $m): ?>
                      <?php $mData = $row['months'][$m] ?? ['debit' => 0, 'credit' => 0, 'balance' => 0]; ?>
                      <td class="text-end text-danger small">
                        <?= ($mData['debit'] > 0) ? number_format($mData['debit'], 2) : '-' ?>
                      </td>
                      <td class="text-end text-success small">
                        <?= ($mData['credit'] > 0) ? number_format($mData['credit'], 2) : '-' ?>
                      </td>
                      <td class="text-end fw-bold small">
                        <?= ($mData['balance'] != 0) ? number_format(abs($mData['balance']), 2) . (($mData['balance'] >= 0) ? ' Dr' : ' Cr') : '-' ?>
                      </td>
                    <?php endforeach; ?>
                    <td class="text-end fw-bold bg-light">
                      <?= ($row['closing_balance'] != 0) ? number_format(abs($row['closing_balance']), 2) . (($row['closing_balance'] >= 0) ? ' Dr' : ' Cr') : '-' ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('page_js') ?>
<script>
  $(document).ready(function() {
    $('#receivable-table').DataTable({
      scrollX: true,
      fixedColumns: {
        left: 1
      },
      lengthMenu: [
        [10, 25, 50, -1],
        [10, 25, 50, "All"]
      ],
      pageLength: 25
    });
  });
</script>
<?= $this->endSection() ?>
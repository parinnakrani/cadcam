<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="page-title-box d-sm-flex align-items-center justify-content-between">
        <h4 class="mb-sm-0">Outstanding Invoices & Reminders</h4>
        <div class="page-title-right">
          <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item active">Reminders</li>
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
          <label class="visually-hidden" for="to_date">To Date (As Of)</label>
          <input type="date" class="form-control" id="to_date" name="to_date" value="<?= esc($toDate) ?>" placeholder="To Date">
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-primary">Filter</button>
        </div>
      </form>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            <table id="outstanding-table" class="table table-bordered table-striped dt-responsive nowrap w-100">
              <thead class="table-light">
                <tr>
                  <th>Invoice #</th>
                  <th>Date</th>
                  <th>Due Date</th>
                  <th>Customer Name</th>
                  <th>Mobile</th>
                  <th>Type</th>
                  <th class="text-end">Amount</th>
                  <th>Status</th>
                  <th class="text-center">Days Overdue</th>
                  <th class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($invoices)): ?>
                  <tr>
                    <td colspan="10" class="text-center">No outstanding invoices.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($invoices as $inv): ?>
                    <tr>
                      <td><a href="<?= base_url('invoices/view/' . $inv['id']) ?>"><?= esc($inv['invoice_number']) ?></a></td>
                      <td><?= esc(date('d-M-Y', strtotime($inv['date']))) ?></td>
                      <td><?= esc(date('d-M-Y', strtotime($inv['due_date']))) ?></td>
                      <td><?= esc($inv['customer_name']) ?></td>
                      <td><?= esc($inv['mobile']) ?></td>
                      <td><span class="badge bg-secondary"><?= esc($inv['type']) ?></span></td>
                      <td class="text-end fw-bold"><?= number_format($inv['amount'], 2) ?></td>
                      <td><span class="badge bg-warning text-dark"><?= esc($inv['status']) ?></span></td>
                      <td class="text-center">
                        <?php if ($inv['days_overdue'] > 0): ?>
                          <span class="badge bg-danger"><?= $inv['days_overdue'] ?> Days</span>
                        <?php else: ?>
                          <span class="badge bg-success">On Time</span>
                        <?php endif; ?>
                      </td>
                      <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary btn-reminder"
                          data-id="<?= $inv['id'] ?>"
                          data-ref="<?= $inv['invoice_number'] ?>">
                          <i class="bx bx-bell"></i> Remind
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
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
    $('#outstanding-table').DataTable({
      order: [
        [8, 'desc']
      ], // Sort by Days Overdue descending
      pageLength: 25
    });

    // Handle Reminder Button
    $(document).on('click', '.btn-reminder', function() {
      var btn = $(this);
      var id = btn.data('id');
      var ref = btn.data('ref');

      if (confirm('Send payment reminder for Invoice ' + ref + '?')) {
        btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i> Sending...');

        $.ajax({
          url: '<?= base_url('ledgers/reminders/send/') ?>' + id,
          type: 'POST',
          dataType: 'json',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
          }, // Ensure CSRF if POST
          success: function(response) {
            if (response.status === 200 || response.status === 'success') {
              alert(response.message);
              btn.html('<i class="bx bx-check"></i> Sent').removeClass('btn-outline-primary').addClass('btn-success');
            } else {
              alert('Error: ' + response.message);
              btn.prop('disabled', false).html('<i class="bx bx-bell"></i> Remind');
            }
          },
          error: function() {
            alert('Failed to send reminder. Please try again.');
            btn.prop('disabled', false).html('<i class="bx bx-bell"></i> Remind');
          }
        });
      }
    });
  });
</script>
<?= $this->endSection() ?>
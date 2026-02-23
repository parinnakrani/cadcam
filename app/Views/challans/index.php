<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Challans<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
  <div class="col-12">

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('message')): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= esc(session()->getFlashdata('message')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= esc(session()->getFlashdata('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <!-- Summary Cards -->
    <div class="row mb-4 g-3">
      <div class="col-sm-6 col-xl-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="avatar me-3">
                <span class="avatar-initial rounded bg-label-primary"><i class="ri-file-list-3-line ri-24px"></i></span>
              </div>
              <div>
                <h6 class="mb-0" id="stat-total">--</h6>
                <small class="text-muted">Total Challans</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="avatar me-3">
                <span class="avatar-initial rounded bg-label-warning"><i class="ri-draft-line ri-24px"></i></span>
              </div>
              <div>
                <h6 class="mb-0" id="stat-draft">--</h6>
                <small class="text-muted">Draft</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="avatar me-3">
                <span class="avatar-initial rounded bg-label-info"><i class="ri-loader-4-line ri-24px"></i></span>
              </div>
              <div>
                <h6 class="mb-0" id="stat-progress">--</h6>
                <small class="text-muted">In Progress</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="avatar me-3">
                <span class="avatar-initial rounded bg-label-success"><i class="ri-check-double-line ri-24px"></i></span>
              </div>
              <div>
                <h6 class="mb-0" id="stat-completed">--</h6>
                <small class="text-muted">Completed</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Card -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <h5 class="mb-0">
          <i class="ri-file-list-3-line me-1"></i> Challans
        </h5>
        <!-- Create Challan Buttons -->
        <div class="d-flex gap-2 flex-wrap">
          <?php if ($canCreateRhodium ?? false): ?>
            <a href="<?= base_url('challans/create?type=Rhodium') ?>" class="btn btn-primary">
              <i class="ri-add-line me-1"></i> <span class="badge bg-white text-primary me-1">R</span> Rhodium Challan
            </a>
          <?php endif; ?>
          <?php if ($canCreateMeena ?? false): ?>
            <a href="<?= base_url('challans/create?type=Meena') ?>" class="btn btn-success">
              <i class="ri-add-line me-1"></i> <span class="badge bg-white text-success me-1">M</span> Meena Challan
            </a>
          <?php endif; ?>
          <?php if ($canCreateWax ?? false): ?>
            <a href="<?= base_url('challans/create?type=Wax') ?>" class="btn btn-warning">
              <i class="ri-add-line me-1"></i> <span class="badge bg-white text-warning me-1">W</span> Wax Challan
            </a>
          <?php endif; ?>
        </div>
      </div>

      <div class="card-body">
        <!-- Filters -->
        <div class="row mb-4 g-3">
          <div class="col-md-3">
            <label class="form-label" for="filter_account">Account</label>
            <select id="filter_account" class="form-select form-select-sm">
              <option value="">All Accounts</option>
              <?php foreach ($accounts as $acc): ?>
                <option value="<?= esc($acc['id']) ?>"
                  <?= (($filters['account_id'] ?? '') == $acc['id']) ? 'selected' : '' ?>>
                  <?= esc($acc['account_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label" for="filter_status">Status</label>
            <select id="filter_status" class="form-select form-select-sm">
              <option value="">All Statuses</option>
              <option value="Draft" <?= (($filters['status'] ?? '') === 'Draft')       ? 'selected' : '' ?>>Draft</option>
              <option value="Pending" <?= (($filters['status'] ?? '') === 'Pending')     ? 'selected' : '' ?>>Pending</option>
              <option value="In Progress" <?= (($filters['status'] ?? '') === 'In Progress') ? 'selected' : '' ?>>In Progress</option>
              <option value="Completed" <?= (($filters['status'] ?? '') === 'Completed')   ? 'selected' : '' ?>>Completed</option>
              <option value="Invoiced" <?= (($filters['status'] ?? '') === 'Invoiced')    ? 'selected' : '' ?>>Invoiced</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label" for="filter_from_date">From Date</label>
            <input type="date" id="filter_from_date" class="form-control form-control-sm" value="<?= esc($filters['date_from'] ?? '') ?>">
          </div>
          <div class="col-md-2">
            <label class="form-label" for="filter_to_date">To Date</label>
            <input type="date" id="filter_to_date" class="form-control form-control-sm" value="<?= esc($filters['date_to'] ?? '') ?>">
          </div>
          <div class="col-md-2 d-flex align-items-end gap-2">
            <button type="button" id="btn-filter" class="btn btn-outline-primary btn-sm">
              <i class="ri-filter-3-line me-1"></i> Filter
            </button>
            <button type="button" id="btn-reset" class="btn btn-outline-secondary btn-sm">
              <i class="ri-refresh-line me-1"></i> Reset
            </button>
          </div>
        </div>

        <!-- DataTable -->
        <div class="table-responsive">
          <table class="table table-bordered table-striped table-hover" id="challansTable" style="width:100%">
            <thead>
              <tr>
                <th>Challan #</th>
                <th>Date</th>

                <th>Customer</th>
                <th>Status</th>
                <th>Weight (g)</th>
                <th>Amount (₹)</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <!-- Loaded via AJAX -->
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="ri-error-warning-line text-danger me-1"></i> Confirm Delete
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete challan <strong id="delete-challan-number"></strong>?</p>
        <p class="text-muted small mb-0">This action cannot be undone. All line items will be permanently removed.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="btn-confirm-delete">
          <i class="ri-delete-bin-line me-1"></i> Delete
        </button>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('page_js') ?>
<script>
  $(document).ready(function() {

    // =========================================================================
    // STATUS & TYPE BADGE HELPERS
    // =========================================================================
    var statusBadgeMap = {
      'Draft': 'bg-label-secondary',
      'Pending': 'bg-label-warning',
      'In Progress': 'bg-label-info',
      'Completed': 'bg-label-success',
      'Invoiced': 'bg-label-primary'
    };

    var typeBadgeMap = {
      'Rhodium': 'bg-label-primary',
      'Meena': 'bg-label-success',
      'Wax': 'bg-label-warning'
    };

    function statusBadge(status) {
      var cls = statusBadgeMap[status] || 'bg-label-secondary';
      return '<span class="badge ' + cls + '">' + (status || '-') + '</span>';
    }

    function typeBadge(type) {
      var cls = typeBadgeMap[type] || 'bg-label-secondary';
      return '<span class="badge ' + cls + '">' + (type || '-') + '</span>';
    }

    function formatCurrency(val) {
      var num = parseFloat(val || 0);
      return '₹ ' + num.toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    }

    function formatWeight(val) {
      var num = parseFloat(val || 0);
      return num.toFixed(3) + ' g';
    }

    function getCustomerName(row) {
      // ChallanModel findAll returns flat row without joins
      // getChallanWithCustomer joins names, but index uses findAll
      // We display whatever is available
      if (row.account_name) return row.account_name;
      if (row.customer_name) return row.customer_name;
      // Fallback: show customer type + ID
      if (row.customer_type === 'Account' && row.account_id) return 'Account #' + row.account_id;
      if (row.customer_type === 'Cash' && row.cash_customer_id) return 'Cash #' + row.cash_customer_id;
      return '-';
    }

    // =========================================================================
    // DATATABLE INITIALIZATION
    // =========================================================================
    var baseUrl = '<?= base_url('challans') ?>';

    var table = $('#challansTable').DataTable({
      processing: true,
      serverSide: false, // Client-side — controller returns all matching results
      ajax: {
        url: baseUrl,
        type: 'GET',
        // Explicitly add header for CodeIgniter isAJAX() check
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        data: function(d) {
          d.account_id = $('#filter_account').val();
          d.challan_status = $('#filter_status').val();
          d.from_date = $('#filter_from_date').val();
          d.to_date = $('#filter_to_date').val();
        },
        error: function(xhr, error, code) {
          console.error('DataTables Error:', error, code, xhr.responseText);
          // Optional: alert('Failed to load challans: ' + code);
        }
      },
      order: [
        [1, 'desc']
      ], // Sort by date descending
      columns: [
        // Challan Number (link)
        {
          data: 'challan_number',
          render: function(data, type, row) {
            return '<a href="' + baseUrl + '/' + row.id + '" class="fw-semibold text-primary">' + data + '</a>';
          }
        },
        // Date
        {
          data: 'challan_date',
          render: function(data) {
            if (!data) return '-';
            var d = new Date(data);
            return d.toLocaleDateString('en-IN', {
              day: '2-digit',
              month: 'short',
              year: 'numeric'
            });
          }
        },

        // Customer Name
        {
          data: null,
          render: function(data, type, row) {
            var name = getCustomerName(row);
            var typeIcon = row.customer_type === 'Account' ?
              '<i class="ri-building-line me-1 text-muted" title="Account"></i>' :
              '<i class="ri-user-line me-1 text-muted" title="Cash"></i>';
            return typeIcon + name;
          }
        },
        // Status badge
        {
          data: 'challan_status',
          render: function(data) {
            return statusBadge(data);
          }
        },
        // Total Weight
        {
          data: 'total_weight',
          className: 'text-end',
          render: function(data) {
            return formatWeight(data);
          }
        },
        // Total Amount
        {
          data: 'total_amount',
          className: 'text-end',
          render: function(data) {
            return formatCurrency(data);
          }
        },
        // Actions
        {
          data: 'id',
          orderable: false,
          searchable: false,
          className: 'text-center',
          render: function(data, type, row) {
            var isInvoiced = row.invoice_generated == 1 || row.challan_status === 'Invoiced';
            var editDisabled = isInvoiced ? 'disabled' : '';
            var deleteDisabled = isInvoiced ? 'disabled' : '';
            var canView = <?= ($action_flags['view'] ?? false) ? 'true' : 'false' ?>;
            var canEdit = <?= ($action_flags['edit'] ?? false) ? 'true' : 'false' ?>;
            var canDelete = <?= ($action_flags['delete'] ?? false) ? 'true' : 'false' ?>;

            var html = '<div class="dropdown">' +
              '<button class="btn p-0" type="button" data-bs-toggle="dropdown"><i class="ri-more-2-fill"></i></button>' +
              '<div class="dropdown-menu">';

            if (canView) {
              html += '  <a class="dropdown-item" href="' + baseUrl + '/' + data + '">' +
                '    <i class="ri-eye-line me-1"></i> View</a>';
            }
            if (canEdit) {
              html += '  <a class="dropdown-item ' + editDisabled + '" href="' + baseUrl + '/' + data + '/edit">' +
                '    <i class="ri-pencil-line me-1"></i> Edit</a>';
            }
            if (canView) { // Print requires view permission
              html += '  <a class="dropdown-item" href="' + baseUrl + '/' + data + '/print" target="_blank">' +
                '    <i class="ri-printer-line me-1"></i> Print</a>';
            }

            if (canDelete) {
              html += '  <div class="dropdown-divider"></div>' +
                '  <a class="dropdown-item text-danger delete-record ' + deleteDisabled + '" href="javascript:void(0);" ' +
                '     data-id="' + data + '" data-number="' + (row.challan_number || '') + '">' +
                '    <i class="ri-delete-bin-line me-1"></i> Delete</a>';
            }
            html += '</div></div>';
            return html;
          }
        }
      ],
      language: {
        emptyTable: 'No challans found. Create your first challan!',
        zeroRecords: 'No challans match your filters.',
        processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div> Loading...'
      },
      drawCallback: function(settings) {
        updateStats(settings);
      }
    });

    // =========================================================================
    // STATS CARDS UPDATE
    // =========================================================================
    function updateStats(settings) {
      var api = new $.fn.dataTable.Api(settings);
      var data = api.rows().data().toArray();
      var total = data.length;
      var draft = 0,
        progress = 0,
        completed = 0;

      data.forEach(function(row) {
        if (row.challan_status === 'Draft') draft++;
        else if (row.challan_status === 'Pending') {
          /* nothing? Pending count? */
        } else if (row.challan_status === 'In Progress') progress++;
        else if (row.challan_status === 'Completed') completed++;
      });

      // Note: The logic above missed 'Pending' and 'Invoiced'. 
      // Assuming user only wants to track specific statuses as per original code.
      // Original code:
      // if (row.challan_status === 'Draft') draft++;
      // else if (row.challan_status === 'In Progress') progress++;
      // else if (row.challan_status === 'Completed') completed++;

      $('#stat-total').text(total);
      $('#stat-draft').text(draft);
      $('#stat-progress').text(progress);
      $('#stat-completed').text(completed);
    }

    // =========================================================================
    // FILTER ACTIONS
    // =========================================================================
    $('#btn-filter').on('click', function() {
      table.ajax.reload();
    });

    $('#btn-reset').on('click', function() {
      $('#filter_account').val('');
      $('#filter_status').val('');
      $('#filter_from_date').val('');
      $('#filter_to_date').val('');
      table.ajax.reload();
    });

    // Pressing Enter in date fields triggers filter
    $('#filter_from_date, #filter_to_date').on('keypress', function(e) {
      if (e.which === 13) table.ajax.reload();
    });

    // =========================================================================
    // ROW CLICK → VIEW DETAIL
    // =========================================================================
    $('#challansTable tbody').on('click', 'tr td:not(:last-child)', function() {
      var row = table.row($(this).closest('tr')).data();
      if (row && row.id) {
        window.location.href = baseUrl + '/' + row.id;
      }
    });

    // Make data rows look clickable
    $('#challansTable tbody').on('mouseenter', 'tr', function() {
      $(this).css('cursor', 'pointer');
    });

    // =========================================================================
    // DELETE
    // =========================================================================
    var deleteId = null;
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

    $(document).on('click', '.delete-record:not(.disabled)', function(e) {
      e.preventDefault();
      e.stopPropagation();
      deleteId = $(this).data('id');
      var challanNumber = $(this).data('number');
      $('#delete-challan-number').text(challanNumber || '#' + deleteId);
      deleteModal.show();
    });

    $('#btn-confirm-delete').on('click', function() {
      if (!deleteId) return;

      var $btn = $(this);
      $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Deleting...');

      $.ajax({
        url: baseUrl + '/' + deleteId,
        type: 'DELETE',
        dataType: 'json',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(res) {
          deleteModal.hide();
          if (res.status === 'success') {
            table.ajax.reload();
          } else {
            alert(res.message || 'Delete failed.');
          }
        },
        error: function(xhr) {
          deleteModal.hide();
          var msg = 'Delete failed.';
          try {
            msg = JSON.parse(xhr.responseText).message || msg;
          } catch (e) {}
          alert(msg);
        },
        complete: function() {
          $btn.prop('disabled', false).html('<i class="ri-delete-bin-line me-1"></i> Delete');
          deleteId = null;
        }
      });
    });

  });
</script>
<?= $this->endSection() ?>
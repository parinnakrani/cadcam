<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Invoices<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">Invoices</li>
  </ol>
</nav>

<!-- Flash Messages -->
<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= session()->getFlashdata('success') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= session()->getFlashdata('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h3 mb-0">Invoices</h1>
  <div class="d-flex gap-2 flex-wrap">
    <?php if ($canCreateAccount ?? false): ?>
      <a href="<?= base_url('account-invoices/create') ?>" class="btn btn-primary">
        <i class="ri-building-line me-1"></i> Account Invoice
      </a>
    <?php endif; ?>
    <?php if ($canCreateCash ?? false): ?>
      <a href="<?= base_url('cash-invoices/create') ?>" class="btn btn-success">
        <i class="ri-bank-card-line me-1"></i> Cash Invoice
      </a>
    <?php endif; ?>
    <?php if ($canCreateWax ?? false): ?>
      <a href="<?= base_url('wax-invoices/create') ?>" class="btn btn-warning">
        <i class="ri-drop-line me-1"></i> Wax Invoice
      </a>
    <?php endif; ?>
  </div>
</div>

<!-- Filters Card -->
<div class="card mb-4">
  <div class="card-header">
    <h5 class="card-title mb-0">
      <i class="ri-filter-3-line"></i> Filters
      <button class="btn btn-sm btn-link float-end" type="button" id="toggleFilters">
        <i class="ri-arrow-down-s-line"></i>
      </button>
    </h5>
  </div>
  <div class="card-body" id="filtersSection">
    <form id="filterForm">
      <div class="row g-3">
        <!-- Invoice Type -->
        <div class="col-md-3">
          <label for="filterInvoiceType" class="form-label">Invoice Type</label>
          <select class="form-select" id="filterInvoiceType" name="invoice_type">
            <option value="">All Types</option>
            <?php foreach ($allowed_types ?? [] as $type): ?>
              <option value="<?= esc($type) ?>"><?= esc($type) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Customer Dropdown (Hidden initially) -->
        <div class="col-md-3" id="filterCustomerContainer" style="display: none;">
          <label for="filterCustomer" class="form-label">Customer</label>
          <select class="form-select" id="filterCustomer" name="customer_id">
            <option value="">All Customers</option>
            <!-- Options populated via JS -->
          </select>
        </div>

        <!-- Payment Status -->
        <div class="col-md-3">
          <label for="filterPaymentStatus" class="form-label">Payment Status</label>
          <select class="form-select" id="filterPaymentStatus" name="payment_status">
            <option value="">All Status</option>
            <option value="Pending">Unpaid</option>
            <option value="Partial Paid">Partially Paid</option>
            <option value="Paid">Paid</option>
          </select>
        </div>

        <!-- Delivery Status -->
        <div class="col-md-3">
          <label for="filterDeliveryStatus" class="form-label">Delivery Status</label>
          <select class="form-select" id="filterDeliveryStatus" name="delivery_status">
            <option value="">All Status</option>
            <option value="Not Delivered">Not Delivered</option>
            <option value="Delivered">Delivered</option>
          </select>
        </div>

        <!-- Date From -->
        <div class="col-md-3">
          <label for="filterDateFrom" class="form-label">Date From</label>
          <input type="date" class="form-control" id="filterDateFrom" name="date_from">
        </div>

        <!-- Date To -->
        <div class="col-md-3">
          <label for="filterDateTo" class="form-label">Date To</label>
          <input type="date" class="form-control" id="filterDateTo" name="date_to">
        </div>

        <!-- Search -->
        <div class="col-md-4">
          <label for="filterSearch" class="form-label">Search</label>
          <input type="text" class="form-control" id="filterSearch" name="search" placeholder="Invoice number...">
        </div>

        <!-- Filter Actions -->
        <div class="col-md-2 d-flex align-items-end">
          <button type="button" class="btn btn-secondary w-100" id="clearFilters">
            <i class="ri-close-circle-line"></i> Clear
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Invoices Table Card -->
<div class="card">
  <div class="card-body">
    <div class="table-responsive">
      <table id="invoicesTable" class="table table-hover table-striped">
        <thead>
          <tr>
            <th>Invoice Number</th>
            <th>Date</th>
            <th>Customer</th>
            <th class="text-end">Grand Total</th>
            <th class="text-end">Amount Paid</th>
            <th class="text-end">Amount Due</th>
            <th>Payment Status</th>
            <th>Delivery Status</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($invoices as $invoice): ?>
            <tr data-invoice-type="<?= esc($invoice['invoice_type']) ?>"
              data-account-id="<?= esc($invoice['account_id'] ?? '') ?>"
              data-cash-customer-id="<?= esc($invoice['cash_customer_id'] ?? '') ?>"
              data-invoice-date="<?= esc($invoice['invoice_date']) ?>"
              data-payment-status="<?= esc($invoice['payment_status']) ?>"
              data-delivery-status="<?= esc($invoice['delivery_status'] ?? '') ?>">
              <!-- Invoice Number -->
              <td>
                <a href="<?= base_url("invoices/{$invoice['id']}") ?>" class="text-decoration-none fw-bold">
                  <?= esc($invoice['invoice_number']) ?>
                </a>
              </td>

              <!-- Date -->
              <td><?= date('d M Y', strtotime($invoice['invoice_date'])) ?></td>

              <!-- Customer Name -->
              <td>
                <?= esc($invoice['customer_name'] ?? 'N/A') ?>
              </td>

              <!-- Grand Total -->
              <td class="text-end fw-bold">₹<?= number_format($invoice['grand_total'], 2) ?></td>

              <!-- Amount Paid -->
              <td class="text-end">₹<?= number_format($invoice['total_paid'], 2) ?></td>

              <!-- Amount Due -->
              <td class="text-end">
                <span class="<?= $invoice['amount_due'] > 0 ? 'text-danger fw-bold' : 'text-success' ?>">
                  ₹<?= number_format($invoice['amount_due'], 2) ?>
                </span>
              </td>

              <!-- Payment Status Badge -->
              <td>
                <?php
                $statusBadge = 'secondary';
                $statusText = $invoice['payment_status'];

                if ($invoice['payment_status'] === 'Pending') {
                  $statusBadge = 'danger';
                  $statusText = 'Unpaid';
                } elseif ($invoice['payment_status'] === 'Partial Paid') {
                  $statusBadge = 'warning';
                } elseif ($invoice['payment_status'] === 'Paid') {
                  $statusBadge = 'success';
                }
                ?>
                <span class="badge bg-<?= $statusBadge ?>">
                  <?= esc($statusText) ?>
                </span>
              </td>

              <!-- Delivery Status -->
              <td>
                <?php
                $deliveryStatus = $invoice['delivery_status'] ?? 'Not Delivered';
                $deliveryBadge = $deliveryStatus === 'Delivered' ? 'success' : 'secondary';
                ?>
                <span class="badge bg-<?= $deliveryBadge ?>">
                  <?= esc($deliveryStatus) ?>
                </span>
              </td>

              <!-- Actions -->
              <td class="text-center">
                <?php $rowFlags = $type_action_flags[$invoice['invoice_type']] ?? []; ?>
                <div class="btn-group btn-group-sm" role="group">
                  <!-- View -->
                  <?php if ($rowFlags['view'] ?? false): ?>
                    <a href="<?= base_url("invoices/{$invoice['id']}") ?>"
                      class="btn btn-outline-primary"
                      title="View">
                      <i class="ri-eye-line"></i>
                    </a>
                  <?php endif; ?>

                  <!-- Edit (only if not paid and not Accounts Invoice) -->
                  <?php if ($invoice['invoice_type'] !== 'Accounts Invoice' && $invoice['total_paid'] == 0 && ($rowFlags['edit'] ?? false)): ?>
                    <?php
                    $editUrl = base_url("invoices/{$invoice['id']}/edit");
                    if ($invoice['invoice_type'] === 'Cash Invoice') {
                      $editUrl = base_url("cash-invoices/{$invoice['id']}/edit");
                    } elseif ($invoice['invoice_type'] === 'Wax Invoice') {
                      $editUrl = base_url("wax-invoices/{$invoice['id']}/edit");
                    }
                    ?>
                    <a href="<?= $editUrl ?>"
                      class="btn btn-outline-secondary"
                      title="Edit">
                      <i class="ri-pencil-line"></i>
                    </a>
                  <?php endif; ?>

                  <!-- Print -->
                  <?php if ($rowFlags['print'] ?? false): ?>
                    <a href="<?= base_url("invoices/{$invoice['id']}/print") ?>"
                      class="btn btn-outline-info"
                      target="_blank"
                      title="Print">
                      <i class="ri-printer-line"></i>
                    </a>
                  <?php endif; ?>

                  <!-- Delete (only if not paid) -->
                  <?php if ($invoice['total_paid'] == 0 && ($rowFlags['delete'] ?? false)): ?>
                    <button type="button"
                      class="btn btn-outline-danger"
                      onclick="deleteInvoice(<?= $invoice['id'] ?>, '<?= esc($invoice['invoice_number']) ?>')"
                      title="Delete">
                      <i class="ri-delete-bin-line"></i>
                    </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>


  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
  // Customer Data Variables
  const accounts = <?= json_encode($accounts ?? []); ?>;
  const cashCustomers = <?= json_encode($cash_customers ?? []); ?>;

  $(document).ready(function() {
    // Custom DataTables logic for filtering
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex, rowData, counter) {
      const row = $(settings.aoData[dataIndex].nTr);

      const invoiceType = $('#filterInvoiceType').val();
      const customerId = $('#filterCustomer').val();
      const paymentStatus = $('#filterPaymentStatus').val();
      const deliveryStatus = $('#filterDeliveryStatus').val();
      const dateFrom = $('#filterDateFrom').val();
      const dateTo = $('#filterDateTo').val();

      const rowInvoiceType = row.data('invoice-type');
      const rowAccountId = row.data('account-id');
      const rowCashCustId = row.data('cash-customer-id');
      const rowDate = row.data('invoice-date');
      const rowPaymentStatus = row.data('payment-status');
      const rowDeliveryStatus = row.data('delivery-status');

      // Invoice Type Check
      if (invoiceType && rowInvoiceType !== invoiceType) return false;

      // Customer ID Check
      if (customerId) {
        if (invoiceType === 'Accounts Invoice' && rowAccountId.toString() !== customerId) return false;
        if (invoiceType === 'Cash Invoice' && rowCashCustId.toString() !== customerId) return false;
      }

      // Payment Status Check
      if (paymentStatus && rowPaymentStatus !== paymentStatus) return false;

      // Delivery Status Check
      // Let's account for "Not Delivered" which may be null or empty in the data attribute
      const normalizedRowDeliveryStatus = rowDeliveryStatus ? rowDeliveryStatus : 'Not Delivered';
      if (deliveryStatus && normalizedRowDeliveryStatus !== deliveryStatus) return false;

      // Date Range Check
      if (dateFrom && rowDate < dateFrom) return false;
      if (dateTo && rowDate > dateTo) return false;

      return true;
    });

    // Initialize DataTable
    const table = $('#invoicesTable').DataTable({
      processing: true,
      serverSide: false,
      pageLength: 20,
      order: [
        [1, 'desc']
      ],
      columnDefs: [{
          orderable: false,
          targets: [8]
        },
        {
          className: 'text-end',
          targets: [3, 4, 5]
        }
      ],
      language: {
        emptyTable: '<i class="ri-inbox-line" style="font-size: 3rem;"></i><p class="mt-2">No invoices found</p>',
        zeroRecords: '<i class="ri-search-line" style="font-size: 3rem;"></i><p class="mt-2">No matching invoices found</p>'
      }
    });

    // Toggle Filters
    $('#toggleFilters').on('click', function() {
      $('#filtersSection').slideToggle();
      const icon = $(this).find('i');
      icon.toggleClass('ri-arrow-down-s-line ri-arrow-up-s-line');
    });

    // Handle Invoice Type change to show correct customers dropdown
    $('#filterInvoiceType').on('change', function() {
      const type = $(this).val();
      const customerContainer = $('#filterCustomerContainer');
      const customerSelect = $('#filterCustomer');

      // Clear current options
      customerSelect.html('<option value="">All Customers</option>');

      if (type === 'Accounts Invoice') {
        accounts.forEach(acc => {
          customerSelect.append(new Option(acc.account_name, acc.id));
        });
        customerContainer.show();
      } else if (type === 'Cash Invoice') {
        cashCustomers.forEach(cust => {
          customerSelect.append(new Option(cust.customer_name, cust.id));
        });
        customerContainer.show();
      } else {
        customerContainer.hide();
      }

      applyFilters();
    });

    // Apply Filters
    $('#filterForm select, #filterForm input').on('change keyup', function() {
      applyFilters();
    });

    // Clear Filters
    $('#clearFilters').on('click', function() {
      $('#filterForm')[0].reset();
      $('#filterCustomerContainer').hide();
      $('#filterCustomer').html('<option value="">All Customers</option>');
      applyFilters();
    });

    // Apply filters function
    function applyFilters() {
      const search = $('#filterSearch').val();

      // DataTables Global Search handles text match, ext.search handles hidden criteria
      table.search(search).draw();
    }

    // Highlight overdue invoices
    highlightOverdueInvoices();
  });

  // Delete Invoice Function
  function deleteInvoice(invoiceId, invoiceNumber) {
    if (confirm(`Are you sure you want to delete invoice ${invoiceNumber}?\n\nThis action cannot be undone.`)) {
      $.ajax({
        url: `<?= base_url('invoices') ?>/${invoiceId}`,
        type: 'DELETE',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
          if (response.success) {
            // Show success message
            showAlert('success', response.message);

            // Reload page after 1 second
            setTimeout(function() {
              window.location.reload();
            }, 1000);
          } else {
            showAlert('danger', response.error || 'Failed to delete invoice');
          }
        },
        error: function(xhr) {
          const response = xhr.responseJSON;
          showAlert('danger', response?.error || 'Failed to delete invoice');
        }
      });
    }
  }

  // Show Alert Function
  function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    // Insert alert at top of page
    $('main').prepend(alertHtml);

    // Auto-dismiss after 5 seconds
    setTimeout(function() {
      $('.alert').fadeOut(function() {
        $(this).remove();
      });
    }, 5000);
  }

  // Highlight Overdue Invoices
  function highlightOverdueInvoices() {
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    $('#invoicesTable tbody tr').each(function() {
      const dateCell = $(this).find('td:eq(1)').text();
      const amountDue = parseFloat($(this).find('td:eq(5)').text().replace('₹', '').replace(',', ''));

      if (dateCell && amountDue > 0) {
        const invoiceDate = new Date(dateCell);
        const daysDiff = Math.floor((today - invoiceDate) / (1000 * 60 * 60 * 24));

        // Highlight if overdue by more than 30 days
        if (daysDiff > 30) {
          $(this).addClass('table-danger');
          $(this).attr('title', `Overdue by ${daysDiff} days`);
        }
      }
    });
  }

  // Export to Excel (optional)
  function exportToExcel() {
    window.location.href = '<?= base_url('invoices/export') ?>?' + $('#filterForm').serialize();
  }

  // Export to PDF (optional)
  function exportToPDF() {
    window.location.href = '<?= base_url('invoices/export-pdf') ?>?' + $('#filterForm').serialize();
  }
</script>

<style>
  /* Custom Styles */
  .table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
  }

  .badge {
    font-size: 0.85rem;
    padding: 0.35em 0.65em;
  }

  .btn-group-sm>.btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
  }

  /* Overdue row highlight */
  .table-danger {
    background-color: rgba(220, 53, 69, 0.1) !important;
  }

  /* Amount due highlight */
  .text-danger.fw-bold {
    font-weight: 600 !important;
  }

  /* Filter section animation */
  #filtersSection {
    transition: all 0.3s ease;
  }

  /* DataTable custom styling */
  .dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 0.25rem 0.5rem;
    margin: 0 0.125rem;
  }

  /* Responsive adjustments */
  @media (max-width: 768px) {
    .btn-group {
      flex-direction: column;
    }

    .btn-group .btn {
      margin-bottom: 0.5rem;
    }
  }
</style>
<?= $this->endSection() ?>
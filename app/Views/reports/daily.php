<?= $this->extend('Layouts/main') ?>

<?= $this->section('title') ?>Daily Invoice Report<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
  <!-- Page Header -->
  <div class="row">
    <div class="col-12">
      <div class="page-title-box d-sm-flex align-items-center justify-content-between">
        <h4 class="mb-sm-0"><i class="ri-calendar-line me-2"></i><?= esc($title) ?></h4>
        <div class="page-title-right">
          <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('reports/outstanding') ?>">Reports</a></li>
            <li class="breadcrumb-item active">Daily Report</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Filters & Actions Bar -->
  <div class="card mb-4">
    <div class="card-body">
      <form method="get" action="<?= current_url() ?>" id="filterForm" class="row gx-3 gy-2 align-items-end">
        <div class="col-sm-3 col-md-2">
          <label for="date" class="form-label fw-semibold">Date</label>
          <input type="date" class="form-control" id="date" name="date" value="<?= esc($date) ?>">
        </div>
        <div class="col-sm-3 col-md-2">
          <label for="per_page" class="form-label fw-semibold">Show</label>
          <select class="form-select" id="per_page" name="per_page">
            <?php foreach ([25, 50, 100, 250, 500, 1000] as $opt): ?>
              <option value="<?= $opt ?>" <?= $perPage == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-primary">
            <i class="ri-filter-3-line me-1"></i> Filter
          </button>
        </div>
        <div class="col-auto ms-auto d-flex gap-2">
          <!-- Print Button -->
          <a href="<?= base_url('reports/daily/print') ?>?date=<?= esc($date) ?>" target="_blank" class="btn btn-outline-secondary">
            <i class="ri-printer-line me-1"></i> Print
          </a>
          <!-- Export Button (permission-based) -->
          <?php if ($canExport): ?>
            <a href="<?= base_url('reports/daily/export') ?>?date=<?= esc($date) ?>" class="btn btn-outline-success">
              <i class="ri-download-2-line me-1"></i> Export CSV
            </a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <!-- Summary Card -->
  <div class="row mb-4">
    <div class="col-xl-3 col-md-6">
      <div class="card card-animate">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-grow-1">
              <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Invoices</p>
            </div>
            <div class="avatar-sm flex-shrink-0">
              <span class="avatar-title bg-label-primary rounded-circle">
                <i class="ri-file-list-3-line ri-24px"></i>
              </span>
            </div>
          </div>
          <div class="mt-3">
            <h4 class="fs-22 fw-semibold ff-secondary mb-0"><?= $totalCount ?></h4>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6">
      <div class="card card-animate">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-grow-1">
              <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Grand Total</p>
            </div>
            <div class="avatar-sm flex-shrink-0">
              <span class="avatar-title bg-label-success rounded-circle">
                <i class="ri-money-rupee-circle-line ri-24px"></i>
              </span>
            </div>
          </div>
          <div class="mt-3">
            <h4 class="fs-22 fw-semibold ff-secondary mb-0">₹ <?= number_format($grandTotal, 2) ?></h4>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6">
      <div class="card card-animate">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-grow-1">
              <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Report Date</p>
            </div>
            <div class="avatar-sm flex-shrink-0">
              <span class="avatar-title bg-label-info rounded-circle">
                <i class="ri-calendar-check-line ri-24px"></i>
              </span>
            </div>
          </div>
          <div class="mt-3">
            <h4 class="fs-22 fw-semibold ff-secondary mb-0"><?= date('d M Y', strtotime($date)) ?></h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Invoice Table -->
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">
            <i class="ri-bill-line me-1"></i> Invoices for <?= date('d M Y', strtotime($date)) ?>
          </h5>
          <span class="badge bg-primary"><?= $totalCount ?> invoice<?= $totalCount !== 1 ? 's' : '' ?></span>
        </div>
        <div class="card-body p-0">
          <?php if (!empty($invoices)): ?>
            <div class="table-responsive">
              <table class="table table-hover table-bordered mb-0" id="dailyReportTable">
                <thead class="table-light">
                  <tr>
                    <th style="width:50px">#</th>
                    <th>Date</th>
                    <th>Invoice Number</th>
                    <th>Invoice Type</th>
                    <th>Customer Name</th>
                    <th class="text-end">Total (₹)</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $offset = ($currentPage - 1) * $perPage;
                  $pageTotal = 0;
                  ?>
                  <?php foreach ($invoices as $i => $inv): ?>
                    <?php $pageTotal += (float)$inv['grand_total']; ?>
                    <tr>
                      <td class="text-center text-muted"><?= $offset + $i + 1 ?></td>
                      <td><?= date('d M Y', strtotime($inv['invoice_date'])) ?></td>
                      <td>
                        <a href="<?= base_url('invoices/' . $inv['id']) ?>" class="fw-semibold text-primary">
                          <?= esc($inv['invoice_number']) ?>
                        </a>
                      </td>
                      <td>
                        <?php
                        $typeBadge = 'secondary';
                        if ($inv['invoice_type'] === 'Accounts Invoice') $typeBadge = 'primary';
                        elseif ($inv['invoice_type'] === 'Cash Invoice') $typeBadge = 'success';
                        elseif ($inv['invoice_type'] === 'Wax Invoice') $typeBadge = 'info';
                        ?>
                        <span class="badge bg-label-<?= $typeBadge ?>"><?= esc($inv['invoice_type']) ?></span>
                      </td>
                      <td><?= esc($inv['customer_name']) ?></td>
                      <td class="text-end fw-semibold">₹ <?= number_format((float)$inv['grand_total'], 2) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                  <tr class="fw-bold">
                    <td colspan="5" class="text-end">Grand Total:</td>
                    <td class="text-end text-primary fs-5">₹ <?= number_format($grandTotal, 2) ?></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          <?php else: ?>
            <div class="p-5 text-center">
              <i class="ri-file-search-line ri-3x text-muted mb-3 d-block"></i>
              <h5 class="text-muted">No invoices found</h5>
              <p class="text-muted mb-0">There are no invoices for <?= date('d M Y', strtotime($date)) ?>.</p>
            </div>
          <?php endif; ?>
        </div>

        <?php if ($totalPages > 1): ?>
          <!-- Pagination -->
          <div class="card-footer d-flex justify-content-between align-items-center">
            <div class="text-muted small">
              Showing <?= (($currentPage - 1) * $perPage) + 1 ?> to <?= min($currentPage * $perPage, $totalCount) ?> of <?= $totalCount ?> entries
            </div>
            <nav aria-label="Report pagination">
              <ul class="pagination pagination-sm mb-0">
                <!-- Previous -->
                <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                  <a class="page-link" href="<?= current_url() ?>?date=<?= esc($date) ?>&per_page=<?= $perPage ?>&page=<?= $currentPage - 1 ?>">
                    <i class="ri-arrow-left-s-line"></i>
                  </a>
                </li>

                <?php
                // Show max 7 page numbers
                $startPage = max(1, $currentPage - 3);
                $endPage   = min($totalPages, $currentPage + 3);
                if ($endPage - $startPage < 6) {
                  if ($startPage === 1) $endPage = min($totalPages, 7);
                  else $startPage = max(1, $endPage - 6);
                }
                ?>

                <?php if ($startPage > 1): ?>
                  <li class="page-item">
                    <a class="page-link" href="<?= current_url() ?>?date=<?= esc($date) ?>&per_page=<?= $perPage ?>&page=1">1</a>
                  </li>
                  <?php if ($startPage > 2): ?>
                    <li class="page-item disabled"><span class="page-link">…</span></li>
                  <?php endif; ?>
                <?php endif; ?>

                <?php for ($p = $startPage; $p <= $endPage; $p++): ?>
                  <li class="page-item <?= $p === $currentPage ? 'active' : '' ?>">
                    <a class="page-link" href="<?= current_url() ?>?date=<?= esc($date) ?>&per_page=<?= $perPage ?>&page=<?= $p ?>"><?= $p ?></a>
                  </li>
                <?php endfor; ?>

                <?php if ($endPage < $totalPages): ?>
                  <?php if ($endPage < $totalPages - 1): ?>
                    <li class="page-item disabled"><span class="page-link">…</span></li>
                  <?php endif; ?>
                  <li class="page-item">
                    <a class="page-link" href="<?= current_url() ?>?date=<?= esc($date) ?>&per_page=<?= $perPage ?>&page=<?= $totalPages ?>"><?= $totalPages ?></a>
                  </li>
                <?php endif; ?>

                <!-- Next -->
                <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                  <a class="page-link" href="<?= current_url() ?>?date=<?= esc($date) ?>&per_page=<?= $perPage ?>&page=<?= $currentPage + 1 ?>">
                    <i class="ri-arrow-right-s-line"></i>
                  </a>
                </li>
              </ul>
            </nav>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('page_js') ?>
<script>
  // Auto-submit form when date or per_page changes
  $(document).ready(function() {
    $('#date, #per_page').on('change', function() {
      $('#filterForm').submit();
    });
  });
</script>
<?= $this->endSection() ?>
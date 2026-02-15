<?= $this->extend('admintheme/layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="page-title-box d-sm-flex align-items-center justify-content-between">
        <h4 class="mb-sm-0">Dashboard</h4>
        <div class="page-title-right">
          <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="javascript: void(0);">Company: <?= session('company_name') ?></a></li>
            <li class="breadcrumb-item active">Dashboard</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Today's Summary -->
  <div class="row">
    <div class="col-xl-3 col-md-6">
      <div class="card card-animate">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-grow-1 overflow-hidden">
              <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Invoices Created (Today)</p>
            </div>
          </div>
          <div class="d-flex align-items-end justify-content-between mt-4">
            <div>
              <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                <span class="counter-value"><?= number_format($todaySummary['invoices_total'] ?? 0, 2) ?></span>
              </h4>
              <span class="badge bg-warning-subtle text-warning fs-12"><?= $todaySummary['invoices_count'] ?? 0 ?> Created</span>
            </div>
            <div class="avatar-sm flex-shrink-0">
              <span class="avatar-title bg-soft-warning rounded fs-3">
                <i class="bx bx-file text-warning"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6">
      <div class="card card-animate">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-grow-1 overflow-hidden">
              <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Payments Received (Today)</p>
            </div>
          </div>
          <div class="d-flex align-items-end justify-content-between mt-4">
            <div>
              <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                <span class="counter-value"><?= number_format($todaySummary['payments_total'] ?? 0, 2) ?></span>
              </h4>
              <span class="badge bg-success-subtle text-success fs-12"><?= $todaySummary['payments_count'] ?? 0 ?> Received</span>
            </div>
            <div class="avatar-sm flex-shrink-0">
              <span class="avatar-title bg-soft-success rounded fs-3">
                <i class="bx bx-money text-success"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6">
      <div class="card card-animate">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-grow-1 overflow-hidden">
              <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Pending Deliveries</p>
            </div>
          </div>
          <div class="d-flex align-items-end justify-content-between mt-4">
            <div>
              <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                <span class="counter-value"><?= $todaySummary['pending_deliveries'] ?? 0 ?></span>
              </h4>
              <a href="<?= base_url('challans') ?>" class="text-decoration-underline text-muted">View Challans</a>
            </div>
            <div class="avatar-sm flex-shrink-0">
              <span class="avatar-title bg-soft-info rounded fs-3">
                <i class="bx bx-package text-info"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6">
      <div class="card card-animate">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-grow-1 overflow-hidden">
              <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Unpaid Invoices</p>
            </div>
          </div>
          <div class="d-flex align-items-end justify-content-between mt-4">
            <div>
              <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                <span class="counter-value"><?= $outstandingSummary['unpaid_invoices'] ?? 0 ?></span>
              </h4>
              <a href="<?= base_url('invoices') ?>" class="text-decoration-underline text-muted">View Invoices</a>
            </div>
            <div class="avatar-sm flex-shrink-0">
              <span class="avatar-title bg-soft-danger rounded fs-3">
                <i class="bx bx-error text-danger"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Outstanding Summary & Top Customers -->
  <div class="row">
    <!-- Outstanding Summary -->
    <div class="col-xl-4">
      <div class="card card-height-100">
        <div class="card-header align-items-center d-flex">
          <h4 class="card-title mb-0 flex-grow-1">Outstanding Summary</h4>
        </div>
        <div class="card-body">
          <div class="p-4 border border-dashed rounded text-center">
            <h5 class="text-muted text-uppercase fs-12 mb-2">Total Receivables</h5>
            <h3 class="fw-bold mb-0 text-danger">
              ₹ <?= number_format($outstandingSummary['total_receivable'] ?? 0, 2) ?>
            </h3>
          </div>

          <div class="mt-4">
            <div id="challan_status_chart"
              data-colors='["--vz-primary", "--vz-success", "--vz-secondary"]'
              class="apex-charts" dir="ltr"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Top Customers -->
    <div class="col-xl-8">
      <div class="card card-height-100">
        <div class="card-header align-items-center d-flex">
          <h4 class="card-title mb-0 flex-grow-1">Top 10 Outstanding Customers</h4>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-borderless table-nowrap align-middle mb-0">
              <thead class="table-light text-muted">
                <tr>
                  <th scope="col">Name</th>
                  <th scope="col">Last Contact</th>
                  <th scope="col" class="text-end">Balance</th>
                  <th scope="col" class="text-end">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($topCustomers)): ?>
                  <tr>
                    <td colspan="4" class="text-center">No outstanding accounts found.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($topCustomers as $customer): ?>
                    <tr>
                      <td>
                        <div class="d-flex align-items-center">
                          <div>
                            <h5 class="fs-14 my-1"><a href="<?= base_url('ledgers/account/' . $customer['id']) ?>" class="text-reset"><?= esc($customer['account_name']) ?></a></h5>
                            <span class="text-muted"><?= esc($customer['mobile_number']) ?></span>
                          </div>
                        </div>
                      </td>
                      <td>
                        <h5 class="fs-14 my-1 fw-normal">-</h5> <!-- Consider implementing Last Interaction Date -->
                      </td>
                      <td class="text-end">
                        <h5 class="fs-14 my-1 fw-normal text-danger">
                          <?= number_format($customer['current_balance'], 2) ?>
                        </h5>
                        <span class="text-muted">Dr</span>
                      </td>
                      <td class="text-end">
                        <a href="<?= base_url('ledgers/account/' . $customer['id']) ?>" class="btn btn-sm btn-soft-primary">View Ledger</a>
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
  <!-- Trend Charts -->
  <div class="row">
    <div class="col-xl-6">
      <div class="card">
        <div class="card-header align-items-center d-flex">
          <h4 class="card-title mb-0 flex-grow-1">Invoice Trends (Last 30 Days)</h4>
        </div>
        <div class="card-body">
          <div id="invoice_trend_chart"
            data-colors='["--vz-primary"]'
            class="apex-charts" dir="ltr"></div>
        </div>
      </div>
    </div>
    <div class="col-xl-6">
      <div class="card">
        <div class="card-header align-items-center d-flex">
          <h4 class="card-title mb-0 flex-grow-1">Payment Trends (Last 30 Days)</h4>
        </div>
        <div class="card-body">
          <div id="payment_trend_chart"
            data-colors='["--vz-success"]'
            class="apex-charts" dir="ltr"></div>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('page_js') ?>
<script src="<?= base_url('admintheme/libs/apexcharts/apexcharts.min.js') ?>"></script>
<script>
  // Helper to get colors
  function getChartColorsArray(chartId) {
    if (document.getElementById(chartId) !== null) {
      var colors = document.getElementById(chartId).getAttribute("data-colors");
      if (colors) {
        colors = JSON.parse(colors);
        return colors.map(function(value) {
          var newValue = value.replace(" ", "");
          if (newValue.indexOf(",") === -1) {
            var color = getComputedStyle(document.documentElement).getPropertyValue(newValue);
            if (color) return color;
            else return newValue;
          } else {
            var val = value.split(",");
            if (val.length == 2) {
              var rgbaColor = getComputedStyle(document.documentElement).getPropertyValue(val[0]);
              rgbaColor = "rgba(" + rgbaColor + "," + val[1] + ")";
              return rgbaColor;
            } else {
              return newValue;
            }
          }
        });
      }
    }
  }

  // --- Challan Status Chart ---
  var challanData = <?= json_encode($challanStatus) ?>;
  var seriesChallan = [];
  var labelsChallan = [];
  for (var key in challanData) {
    if (challanData.hasOwnProperty(key)) {
      labelsChallan.push(key);
      seriesChallan.push(challanData[key]);
    }
  }

  var optionsChallan = {
    series: seriesChallan,
    labels: labelsChallan,
    chart: {
      type: 'donut',
      height: 220
    },
    plotOptions: {
      pie: {
        donut: {
          size: '70%',
          labels: {
            show: true,
            total: {
              show: true,
              label: 'Total',
              formatter: function(w) {
                return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
              }
            }
          }
        }
      }
    },
    dataLabels: {
      enabled: false
    },
    legend: {
      position: 'bottom'
    },
    colors: getChartColorsArray("challan_status_chart") || ['#405189', '#0ab39c', '#f7b84b', '#f06548']
  };
  if (document.querySelector("#challan_status_chart")) {
    new ApexCharts(document.querySelector("#challan_status_chart"), optionsChallan).render();
  }

  // --- Invoice Trend Chart ---
  var invoiceTrend = <?= json_encode($invoiceTrend) ?>;
  var datesInv = Object.keys(invoiceTrend);
  var totalsInv = Object.values(invoiceTrend);

  var optionsInvoice = {
    series: [{
      name: 'Sales',
      data: totalsInv
    }],
    chart: {
      height: 350,
      type: 'bar',
      toolbar: {
        show: false
      }
    },
    plotOptions: {
      bar: {
        borderRadius: 4,
        horizontal: false
      }
    },
    dataLabels: {
      enabled: false
    },
    xaxis: {
      categories: datesInv,
      labels: {
        rotate: -45,
        rotateAlways: false
      }
    },
    colors: getChartColorsArray("invoice_trend_chart") || ['#405189']
  };
  if (document.querySelector("#invoice_trend_chart")) {
    new ApexCharts(document.querySelector("#invoice_trend_chart"), optionsInvoice).render();
  }

  // --- Payment Trend Chart ---
  var paymentTrend = <?= json_encode($paymentTrend) ?>;
  var datesPay = Object.keys(paymentTrend);
  var totalsPay = Object.values(paymentTrend);

  var optionsPayment = {
    series: [{
      name: 'Collections',
      data: totalsPay
    }],
    chart: {
      height: 350,
      type: 'area',
      toolbar: {
        show: false
      }
    },
    dataLabels: {
      enabled: false
    },
    stroke: {
      curve: 'smooth'
    },
    xaxis: {
      categories: datesPay,
      labels: {
        rotate: -45,
        rotateAlways: false
      }
    },
    colors: getChartColorsArray("payment_trend_chart") || ['#0ab39c']
  };
  if (document.querySelector("#payment_trend_chart")) {
    new ApexCharts(document.querySelector("#payment_trend_chart"), optionsPayment).render();
  }
</script>
<?= $this->endSection() ?>
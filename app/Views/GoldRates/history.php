<?= $this->extend('Layouts/main') ?>

<?= $this->section('title') ?>Gold Rate History<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row mb-4">
    <div class="col-12">
      <div class="card">
        <h5 class="card-header">Rate Trends</h5>
        <div class="card-body">
          <canvas id="rateChart" height="100"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter Form -->
  <div class="card mb-4">
    <div class="card-body">
      <form action="" method="GET" class="row gx-3 gy-2 align-items-center">
        <div class="col-md-3">
          <label class="form-label" for="from_date">From Date</label>
          <input type="date" class="form-control" id="from_date" name="from_date" value="<?= esc($fromDate) ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label" for="to_date">To Date</label>
          <input type="date" class="form-control" id="to_date" name="to_date" value="<?= esc($toDate) ?>">
        </div>
        <div class="col-md-3 mt-4">
          <button type="submit" class="btn btn-primary">Filter</button>
          <a href="<?= base_url('masters/gold-rates/history') ?>" class="btn btn-outline-secondary">Reset</a>
        </div>
      </form>
    </div>
  </div>

  <!-- History Table -->
  <div class="card">
    <h5 class="card-header">Detailed History</h5>
    <div class="table-responsive text-nowrap">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Date & Time</th>
            <th>24K Rate (per gram)</th>
            <th>Entered By</th>
          </tr>
        </thead>
        <tbody>
          <?php
          // Filter history to only include 24K and don't group, show all entries
          $filteredHistory = array_filter($history, fn($h) => $h['metal_type'] === '24K');
          ?>
          <?php if (empty($filteredHistory)): ?>
            <tr>
              <td colspan="3" class="text-center">No records found</td>
            </tr>
          <?php else: ?>
            <?php foreach ($filteredHistory as $h): ?>
              <tr>
                <td><?= date('d M Y H:i', strtotime($h['created_at'])) ?></td>
                <td>₹ <?= number_format($h['rate_per_gram'], 2) ?></td>
                <td><?= esc($h['user_name'] ?? 'User #' . $h['created_by']) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/date-fns"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
<script>
  const ctx = document.getElementById('rateChart').getContext('2d');
  const chartData = <?= json_encode($chartData) ?>;

  // Prepare datasets
  const datasets = [];
  const colors = {
    '22K': '#ffc107',
    '24K': '#ff9f43',
    'Silver': '#8592a3'
  };

  Object.keys(chartData).forEach(metal => {
    if (metal === '24K' && chartData[metal].length > 0) {
      datasets.push({
        label: metal,
        data: chartData[metal].map(d => ({
          x: d.date,
          y: d.rate
        })),
        borderColor: colors[metal],
        tension: 0.1,
        fill: false
      });
    }
  });

  new Chart(ctx, {
    type: 'line',
    data: {
      datasets: datasets
    },
    options: {
      responsive: true,
      scales: {
        x: {
          type: 'time',
          time: {
            unit: 'day'
          }
        }
      }
    }
  });
</script>
<?= $this->endSection() ?>
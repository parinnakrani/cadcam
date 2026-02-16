<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= esc($title ?? 'Audit Logs') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
  <div class="row mb-3">
    <div class="col-12">
      <h1 class="h3 text-gray-800"><?= esc($title ?? 'Audit Logs') ?></h1>
    </div>
  </div>

  <div class="card shadow mb-4">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary">Activity Log</h6>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered" id="auditTable" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>Time</th>
              <th>User</th>
              <th>Module</th>
              <th>Action</th>
              <th>Record</th>
              <th>IP Address</th>
              <th>Details</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($logs)): ?>
              <?php foreach ($logs as $log): ?>
                <tr>
                  <td><?= esc($log['created_at']) ?></td>
                  <td>
                    <?= esc($log['full_name'] ?? $log['username'] ?? 'User #' . $log['user_id']) ?>
                  </td>
                  <td><?= esc($log['module']) ?></td>
                  <td>
                    <?php
                    $badgeClass = 'secondary';
                    switch ($log['action_type']) {
                      case 'create':
                        $badgeClass = 'success';
                        break;
                      case 'update':
                        $badgeClass = 'warning';
                        break;
                      case 'delete':
                        $badgeClass = 'danger';
                        break;
                      case 'login':
                        $badgeClass = 'primary';
                        break;
                      case 'export':
                        $badgeClass = 'info';
                        break;
                      default:
                        $badgeClass = 'secondary';
                    }
                    ?>
                    <span class="badge badge-<?= $badgeClass ?>"><?= strtoupper(esc($log['action_type'])) ?></span>
                  </td>
                  <td>
                    <?php if ($log['record_type']): ?>
                      <?= esc($log['record_type']) ?> #<?= esc($log['record_id']) ?>
                    <?php else: ?>
                      -
                    <?php endif; ?>
                  </td>
                  <td><?= esc($log['ip_address']) ?></td>
                  <td>
                    <?php if ($log['before_data'] || $log['after_data']): ?>
                      <button type="button" class="btn btn-sm btn-info btn-details"
                        data-toggle="modal" data-target="#detailsModal"
                        data-before="<?= esc($log['before_data'], 'attr') ?>"
                        data-after="<?= esc($log['after_data'], 'attr') ?>">
                        View
                      </button>
                    <?php else: ?>
                      <span class="text-muted small">No details</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="text-center">No logs found</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailsModalLabel">Log Details</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <h6>Before</h6>
            <pre id="modal-before" class="bg-light p-2 border" style="max-height: 300px; overflow: auto;"></pre>
          </div>
          <div class="col-md-6">
            <h6>After</h6>
            <pre id="modal-after" class="bg-light p-2 border" style="max-height: 300px; overflow: auto;"></pre>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  $(document).ready(function() {
    $('#auditTable').DataTable({
      "order": [
        [0, "desc"]
      ]
    });

    // Use event delegation for dynamically loaded content if needed, though here it's static
    $(document).on('click', '.btn-details', function() {
      var before = $(this).data('before');
      var after = $(this).data('after');

      function prettyPrint(json) {
        if (!json) return 'None';
        try {
          // Try to parse if it's a string, otherwise use as is
          var obj = (typeof json === 'string') ? JSON.parse(json) : json;
          return JSON.stringify(obj, null, 2);
        } catch (e) {
          return json; // Return raw string if parse fails
        }
      }

      $('#modal-before').text(prettyPrint(before));
      $('#modal-after').text(prettyPrint(after));
    });
  });
</script>
<?= $this->endSection() ?>
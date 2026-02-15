<?= $this->extend('Layouts/main') ?>

<?= $this->section('title') ?>Deliveries<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Delivery Management</h5>
        <?php if (has_permission('deliveries.manage')): ?>
          <a href="<?= base_url('deliveries/create') ?>" class="btn btn-primary">
            <i class="ri-add-line me-1"></i> Assign Delivery
          </a>
        <?php endif; ?>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered table-hover" id="deliveriesTable">
            <thead>
              <tr>
                <th>ID</th>
                <th>Invoice</th>
                <th>Customer</th>
                <th>Assigned To</th>
                <th>Expected Date</th>
                <th>Delivered At</th>
                <th>Status</th>
                <th>Proof</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <!-- Data from AJAX -->
            </tbody>
          </table>
        </div>
      </div>
      <!-- Image Modal -->
      <div class="modal fade" id="proofModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Proof of Delivery</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
              <img id="proofImage" src="" class="img-fluid rounded" alt="Proof">
            </div>
          </div>
        </div>
      </div>
      <?= $this->endSection() ?>

      <?= $this->section('page_js') ?>
      <script>
        $(document).ready(function() {
          var table = $('#deliveriesTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
              url: "<?= base_url('deliveries') ?>",
              data: function(d) {
                // optional params
              }
            },
            columns: [{
                data: 'id'
              },
              {
                data: 'invoice_number',
                render: function(data, type, row) {
                  return '<a href="<?= base_url('invoices/') ?>' + row.invoice_id + '">' + data + '</a>';
                }
              },
              {
                data: 'delivery_contact_name',
                defaultContent: 'N/A'
              },
              {
                data: 'assigned_to_name'
              },
              {
                data: 'expected_delivery_date'
              },
              {
                data: 'delivered_timestamp', // New Column
                render: function(data) {
                  return data ? new Date(data).toLocaleString() : '-';
                }
              },
              {
                data: 'delivery_status',
                render: function(data) {
                  var badgeClass = 'bg-label-primary';
                  if (data === 'Assigned') badgeClass = 'bg-label-info';
                  if (data === 'In Transit') badgeClass = 'bg-label-warning';
                  if (data === 'Delivered') badgeClass = 'bg-label-success';
                  if (data === 'Failed') badgeClass = 'bg-label-danger';
                  return '<span class="badge ' + badgeClass + '">' + data + '</span>';
                }
              },
              {
                data: 'delivery_proof_photo',
                render: function(data, type, row) {
                  if (data) {
                    var url = data;
                    if (!url.startsWith('uploads/')) url = 'uploads/delivery_proofs/' + url;
                    var fullUrl = '<?= base_url() ?>' + url;
                    return '<button class="btn btn-xs btn-outline-primary view-proof-btn" data-url="' + fullUrl + '">View</button>';
                  }
                  return '-';
                }
              },
              {
                data: null,
                render: function(data, type, row) {
                  return '<a href="<?= base_url('deliveries/') ?>' + row.id + '" class="btn btn-sm btn-icon btn-text-secondary rounded-pill"><i class="ri-eye-line"></i></a>';
                }
              }
            ],
            order: [
              [0, 'desc']
            ]
          });

          // Handle Proof Modal
          $(document).on('click', '.view-proof-btn', function() {
            var url = $(this).data('url');
            $('#proofImage').attr('src', url);
            $('#proofModal').modal('show');
          });
        });
      </script>
      <?= $this->endSection() ?>
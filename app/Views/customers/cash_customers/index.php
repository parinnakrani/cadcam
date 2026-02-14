<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Cash Customers<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Cash Customers</h5>
                <a href="<?= base_url('customers/cash-customers/create') ?>" class="btn btn-primary">
                    <i class="ri-add-line me-1"></i> Add New Customer
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="cashCustomersTable">
                        <thead>
                            <tr>
                                <th>Customer Name</th>
                                <th>Mobile</th>
                                <th>Email</th>
                                <th>City</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('page_js') ?>
<script>
$(document).ready(function() {
    var table = $('#cashCustomersTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '<?= base_url('customers/cash-customers') ?>',
            type: 'GET'
        },
        columns: [
            { 
                data: 'customer_name',
                render: function(data, type, row) {
                    return `<a href="<?= base_url('customers/cash-customers') ?>/${row.id}" class="fw-bold">${data}</a>`;
                }
            },
            { data: 'mobile' },
            { 
                data: 'email', 
                render: function(data) { return data || '-'; } 
            },
            { 
                data: 'city',
                render: function(data) { return data || '-'; }
            },
            { 
                data: 'is_active',
                render: function(data) {
                    return data == 1 
                        ? '<span class="badge bg-label-success">Active</span>' 
                        : '<span class="badge bg-label-secondary">Inactive</span>';
                }
            },
            {
                data: 'id',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="dropdown">
                            <button class="btn p-0" type="button" data-bs-toggle="dropdown"><i class="ri-more-2-fill"></i></button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="<?= base_url('customers/cash-customers') ?>/${data}"><i class="ri-eye-line me-1"></i> View</a>
                                <a class="dropdown-item" href="<?= base_url('customers/cash-customers') ?>/${data}/edit"><i class="ri-pencil-line me-1"></i> Edit</a>
                                <a class="dropdown-item text-danger delete-record" href="javascript:void(0);" data-id="${data}"><i class="ri-delete-bin-line me-1"></i> Delete</a>
                            </div>
                        </div>
                    `;
                }
            }
        ]
    });

    $(document).on('click', '.delete-record', function() {
        var id = $(this).data('id');
        if(confirm('Are you sure you want to delete this customer?')) {
            $.ajax({
                url: '<?= base_url('customers/cash-customers') ?>/' + id,
                type: 'DELETE',
                dataType: 'json',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                success: function(res) {
                    if(res.status === 'success') {
                        table.ajax.reload();
                    } else {
                        alert(res.message);
                    }
                }
            });
        }
    });
});
</script>
<?= $this->endSection() ?>

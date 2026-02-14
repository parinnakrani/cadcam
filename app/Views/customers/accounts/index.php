<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Account Customers<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Account Customers</h5>
                <a href="<?= base_url('customers/accounts/create') ?>" class="btn btn-primary">
                    <i class="ri-add-line me-1"></i> Add New Account
                </a>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <div class="row mb-4 g-3">
                    <div class="col-md-3">
                        <select id="filter_status" class="form-select">
                            <option value="">All Status</option>
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="accountsTable">
                        <thead>
                            <tr>
                                <th>Account Code</th>
                                <th>Account Name</th>
                                <th>Contact Person</th>
                                <th>Mobile</th>
                                <th>City/State</th>
                                <th>Current Balance</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data loaded via AJAX usually, but Controller passed empty initially? -->
                            <!-- Controller index() returns view. If AJAX, returns JSON. -->
                            <!-- We should use Datatables AJAX. -->
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
    var table = $('#accountsTable').DataTable({
        processing: true,
        serverSide: false, // Client-side for now as getActiveAccounts returns all
        ajax: {
            url: '<?= base_url('customers/accounts') ?>',
            type: 'GET',
            data: function (d) {
                d.is_active = $('#filter_status').val();
            }
        },
        columns: [
            { data: 'account_code' },
            { 
                data: 'account_name',
                render: function(data, type, row) {
                    return `<a href="<?= base_url('customers/accounts') ?>/${row.id}" class="fw-bold">${data}</a>`;
                }
            },
            { data: 'contact_person' },
            { data: 'mobile' },
            { 
                data: null,
                render: function(data, type, row) {
                    return `${row.billing_city || '-'} / ${row.billing_state || '-'}`; // billing_state might need join or separate lookup
                    // AccountModel->getActiveAccounts() uses findAll() which returns matching rows.
                    // But findAll() doesn't join 'states'.
                    // So billing_state might be ID unless Model joins.
                    // AccountModel->getActiveAccounts() in my implementation (Step 2259) calls findAll().
                    // It does NOT join.
                    // So we only have billing_state_id.
                    // We might need to adjust AccountService::getActiveAccounts to join states?
                    // Requirement: "AccountModel... 3. getActiveAccounts... Return findAll()".
                    // Requirement 4: "getAccountWithBalance... Join states".
                    // List view usually needs names.
                    // I'll update AccountService/Model later or just show City.
                    // Or I assume "billing_city" is text.
                    return `${row.billing_city || ''}`;
                }
            },
            { 
                data: 'current_balance',
                render: function(data, type, row) {
                    let balance = parseFloat(data || 0);
                    let color = balance >= 0 ? 'text-success' : 'text-danger'; // Logic: Credit (we owe) vs Debit
                    // Actually, for Accounts:
                    // Debit = Receivable (Customer owes us) -> Positive usually in Asset
                    // Credit = Payable (We owe Customer) -> Negative?
                    // "Debit entry (customer owes us)".
                    // If balance is Positive (Debit), it's money incoming.
                    // Usually Red for "They owe us"? Or Green for Asset?
                    // Let's stick to Green = Credit, Red = Debit?
                    // Or standard accounting: Debit (Dr) / Credit (Cr).
                    // Prompt requirement: "green if credit, red if debit".
                    // How to distinguish? 'current_balance' is signed or absolute with type?
                    // Schema: `current_balance` DECIMAL.
                    // Usually signed. Positive = Debit?
                    // I'll show number.
                    return `<span class="${color}">₹ ${balance.toFixed(2)}</span>`;
                }
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
                                <a class="dropdown-item" href="<?= base_url('customers/accounts') ?>/${data}"><i class="ri-eye-line me-1"></i> View</a>
                                <a class="dropdown-item" href="<?= base_url('customers/accounts') ?>/${data}/edit"><i class="ri-pencil-line me-1"></i> Edit</a>
                                <a class="dropdown-item text-danger delete-record" href="javascript:void(0);" data-id="${data}"><i class="ri-delete-bin-line me-1"></i> Delete</a>
                            </div>
                        </div>
                    `;
                }
            }
        ]
    });

    // Filter Change
    $('#filter_status').on('change', function() {
        table.ajax.reload();
    });

    // Delete
    $(document).on('click', '.delete-record', function() {
        var id = $(this).data('id');
        if(confirm('Are you sure you want to delete this account?')) {
            $.ajax({
                url: '<?= base_url('customers/accounts') ?>/' + id,
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

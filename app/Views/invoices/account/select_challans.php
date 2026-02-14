<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Create Account Invoice<?= $this->endSection() ?>

<?= $this->section('content') ?>

<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Home</a></li>
    <li class="breadcrumb-item"><a href="<?= base_url('invoices') ?>">Invoices</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create Account Invoice</li>
  </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h3 mb-0">Create Account Invoice</h1>
  <a href="<?= base_url('invoices') ?>" class="btn btn-outline-secondary">
    <i class="ri-arrow-left-line"></i> Back to Invoices
  </a>
</div>

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

<div class="card mb-4">
  <div class="card-header">
    <h5 class="card-title mb-0">Select Account & Challans</h5>
  </div>
  <div class="card-body">
    <form method="POST" action="<?= base_url('account-invoices/store-from-challans') ?>" id="selectChallansForm">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-md-6">
          <label for="account_id" class="form-label">Select Account Customer <span class="text-danger">*</span></label>
          <select class="form-select select2" id="account_id" name="account_id" required>
            <option value="">Select Account</option>
            <?php foreach ($accounts as $account): ?>
              <option value="<?= $account['id'] ?>" <?= (isset($selected_account_id) && $selected_account_id == $account['id']) ? 'selected' : '' ?>>
                <?= esc($account['account_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-12">
          <div id="challansContainer">
            <?php if (isset($challans) && !empty($challans)): ?>
              <table class="table table-bordered table-hover mt-3" id="challansTable">
                <thead class="table-light">
                  <tr>
                    <th style="width: 40px;"><input type="checkbox" id="selectAllChallans" class="form-check-input"></th>
                    <th>Challan #</th>
                    <th>Date</th>
                    <th>Notes</th>
                    <th>Total Amt</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($challans as $challan): ?>
                    <tr>
                      <td><input type="checkbox" name="challan_ids[]" value="<?= $challan['id'] ?>" class="form-check-input challan-checkbox"></td>
                      <td><?= esc($challan['challan_number']) ?></td>
                      <td><?= date('d-M-Y', strtotime($challan['challan_date'])) ?></td>
                      <td><?= esc($challan['notes']) ?></td>
                      <td>₹<?= number_format($challan['total_amount'], 2) ?></td>
                      <td><span class="badge bg-success"><?= esc($challan['challan_status']) ?></span></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
              <button type="submit" class="btn btn-primary mt-3">
                Proceed to Create Invoice <i class="ri-arrow-right-line"></i>
              </button>
            <?php elseif (isset($selected_account_id)): ?>
              <div class="alert alert-info mt-3">No pending approved challans found for this account.</div>
            <?php else: ?>
              <div class="alert alert-secondary mt-3">Please select an account to view pending challans.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
  $(document).ready(function() {
    // Auto-submit on account change to fetch challans
    $('#account_id').on('change', function() {
      if ($(this).val()) {
        window.location.href = '<?= base_url('account-invoices/create') ?>?account_id=' + $(this).val();
      }
    });

    $('#selectAllChallans').on('change', function() {
      $('.challan-checkbox').prop('checked', $(this).is(':checked'));
    });
  });
</script>
<?= $this->endSection() ?>
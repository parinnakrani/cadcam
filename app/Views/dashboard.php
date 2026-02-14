<?= $this->extend('Layouts/main') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4 class="card-title">Welcome back, <?= session('full_name') ?>!</h4>
      </div>
      <div class="card-body">
        <p>This is your Gold ERP Dashboard.</p>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

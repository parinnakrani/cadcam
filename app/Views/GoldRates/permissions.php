<?= $this->extend('Layouts/main') ?>

<?= $this->section('title') ?>Gold Rate Permissions<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Manage Permissions</h5>
                    <p class="card-text">
                        Permissions for Gold Rate management (View, Create, Edit) are handled through the global Roles & Permissions system.
                    </p>
                    <p class="card-text">
                        The required permission key for this module is: <code>masters.manage</code>
                    </p>
                    <a href="<?= base_url('roles') ?>" class="btn btn-primary">Go to Roles Management</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

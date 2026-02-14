<!doctype html>

<html
  lang="en"
  class="light-style layout-navbar-fixed layout-menu-fixed layout-compact"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="<?= base_url('admintheme/assets/') ?>"
  data-template="vertical-menu-template"
  data-style="light">
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title><?= $this->renderSection('title') ?> | Gold ERP</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= base_url('admintheme/assets/img/favicon/favicon.ico') ?>" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&ampdisplay=swap"
      rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="<?= base_url('admintheme/assets/vendor/fonts/remixicon/remixicon.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('admintheme/assets/vendor/fonts/flag-icons.css') ?>" />

    <!-- Menu waves for no-customizer fix -->
    <link rel="stylesheet" href="<?= base_url('admintheme/assets/vendor/libs/node-waves/node-waves.css') ?>" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="<?= base_url('admintheme/assets/vendor/css/rtl/core.css') ?>" class="template-customizer-core-css" />
    <link rel="stylesheet" href="<?= base_url('admintheme/assets/vendor/css/rtl/theme-default.css') ?>" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="<?= base_url('admintheme/assets/css/demo.css') ?>" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="<?= base_url('admintheme/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('admintheme/assets/vendor/libs/typeahead-js/typeahead.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('admintheme/assets/vendor/libs/apex-charts/apex-charts.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('admintheme/assets/vendor/libs/swiper/swiper.css') ?>" />
    
    <!-- DataTables (Global) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="<?= base_url('admintheme/assets/vendor/css/pages/cards-statistics.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('admintheme/assets/vendor/css/pages/cards-analytics.css') ?>" />

    <!-- Vendor Styles (per page) -->
    <?= $this->renderSection('vendorStyles') ?>

    <!-- Helpers -->
    <script src="<?= base_url('admintheme/assets/vendor/js/helpers.js') ?>"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
    <!-- <script src="<?= base_url('admintheme/assets/vendor/js/template-customizer.js') ?>"></script> -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="<?= base_url('admintheme/assets/js/config.js') ?>"></script>
    
    <?= $this->renderSection('page_css') ?>
  </head>

  <body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        
        <!-- Menu -->
        <?= $this->include('Partials/sidebar') ?>
        <!-- / Menu -->

        <!-- Layout container -->
        <div class="layout-page">
          
          <!-- Navbar -->
          <?= $this->include('Partials/header') ?>
          <!-- / Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            
            <!-- Content -->
            <div class="container-xxl flex-grow-1 container-p-y">
                <?= $this->renderSection('content') ?>
            </div>
            <!-- / Content -->

            <!-- Footer -->
            <?= $this->include('Partials/footer') ?>
            <!-- / Footer -->

            <div class="content-backdrop fade"></div>
          </div>
          <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
      </div>

      <!-- Overlay -->
      <div class="layout-overlay layout-menu-toggle"></div>

      <!-- Drag Target Area To SlideIn Menu On Small Screens -->
      <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="<?= base_url('admintheme/assets/vendor/libs/jquery/jquery.js') ?>"></script>
    <script src="<?= base_url('admintheme/assets/vendor/libs/popper/popper.js') ?>"></script>
    <script src="<?= base_url('admintheme/assets/vendor/js/bootstrap.js') ?>"></script>
    <script src="<?= base_url('admintheme/assets/vendor/libs/node-waves/node-waves.js') ?>"></script>
    <script src="<?= base_url('admintheme/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') ?>"></script>
    <script src="<?= base_url('admintheme/assets/vendor/libs/hammer/hammer.js') ?>"></script>
    <script src="<?= base_url('admintheme/assets/vendor/libs/i18n/i18n.js') ?>"></script>
    <script src="<?= base_url('admintheme/assets/vendor/libs/typeahead-js/typeahead.js') ?>"></script>
    <script src="<?= base_url('admintheme/assets/vendor/js/menu.js') ?>"></script>

    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="<?= base_url('admintheme/assets/vendor/libs/apex-charts/apexcharts.js') ?>"></script>
    <script src="<?= base_url('admintheme/assets/vendor/libs/swiper/swiper.js') ?>"></script>
    
    <!-- DataTables (Global) -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <!-- Vendor Scripts (per page) -->
    <?= $this->renderSection('vendorScripts') ?>

    <!-- Main JS -->
    <script src="<?= base_url('admintheme/assets/js/main.js') ?>"></script>

    <!-- Page JS -->
    <!-- <script src="<?= base_url('admintheme/assets/js/dashboards-analytics.js') ?>"></script> -->
    
    <!-- Page Scripts (per page) -->
    <?= $this->renderSection('pageScripts') ?>
    <?= $this->renderSection('page_js') ?>
  </body>
</html>

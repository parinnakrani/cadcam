<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->addRedirect('/', 'dashboard');

// Authentication Routes
$routes->group('', ['namespace' => 'App\Controllers\Auth'], function ($routes) {
  $routes->get('login', 'LoginController::showLoginForm');
  $routes->post('login', 'LoginController::authenticate');
  $routes->get('logout', 'LogoutController::logout');
  $routes->post('logout', 'LogoutController::logout');
});

// Protected Dashboard
$routes->get('dashboard', 'Home::dashboard', ['filter' => 'auth']);
$routes->get('switch-company/(:num)', 'Home::switchCompany/$1', ['filter' => 'auth']);

// User Management Routes
$routes->group('users', ['namespace' => 'App\Controllers\Users', 'filter' => 'auth'], function ($routes) {
  $routes->get('/', 'UserController::index');
  $routes->get('create', 'UserController::create');
  $routes->post('create', 'UserController::store');
  $routes->get('(:num)/edit', 'UserController::edit/$1');
  $routes->post('(:num)', 'UserController::update/$1');
  $routes->get('(:num)/delete', 'UserController::delete/$1'); // Ideally POST/DELETE, fallback for GET link
  $routes->delete('(:num)', 'UserController::delete/$1');
  $routes->get('(:num)/password', 'UserController::changePassword/$1');
  $routes->post('(:num)/password', 'UserController::updatePassword/$1');
});

// Role Management Routes
$routes->group('roles', ['namespace' => 'App\Controllers\Users', 'filter' => 'auth'], function ($routes) {
  $routes->get('/', 'RoleController::index');
  $routes->get('create', 'RoleController::create');
  $routes->post('create', 'RoleController::store');
  $routes->get('(:num)/edit', 'RoleController::edit/$1');
  $routes->post('(:num)', 'RoleController::update/$1');
  $routes->get('(:num)/delete', 'RoleController::delete/$1'); // Fallback
  $routes->delete('(:num)', 'RoleController::delete/$1');
  $routes->get('(:num)/permissions', 'RoleController::permissions/$1');
  $routes->post('(:num)/permissions', 'RoleController::updatePermissions/$1');
});

// API Routes for DataTables
$routes->group('api', ['namespace' => 'App\Controllers\Api', 'filter' => 'auth'], function ($routes) {
  $routes->get('users/list', 'UsersApiController::list');
  $routes->get('users/roles', 'UsersApiController::getRoles');
  $routes->get('users/stats', 'UsersApiController::getStats');
});

// Masters Routes (Gold Rates)
$routes->group('masters', ['namespace' => 'App\Controllers\Masters', 'filter' => 'auth'], function ($routes) {
  // Gold Rates
  $routes->group('gold-rates', function ($routes) {
    $routes->get('/', 'GoldRateController::index');
    $routes->get('create', 'GoldRateController::create');
    $routes->post('store', 'GoldRateController::store');
    $routes->get('edit/(:num)', 'GoldRateController::edit/$1');
    $routes->post('update/(:num)', 'GoldRateController::update/$1');
    $routes->get('history', 'GoldRateController::history');
  });
});

// PRODUCT CATEGORY ROUTES
$routes->group('masters', ['filter' => 'auth'], function ($routes) {
  $routes->group('product-categories', ['filter' => 'permission:product_category'], function ($routes) {
    $routes->get('/', 'Masters\ProductCategoryController::index');
    $routes->get('create', 'Masters\ProductCategoryController::create');
    $routes->post('store', 'Masters\ProductCategoryController::store'); // Match View
    $routes->post('/', 'Masters\ProductCategoryController::store');
    $routes->get('(:num)', 'Masters\ProductCategoryController::show/$1');
    $routes->get('(:num)/edit', 'Masters\ProductCategoryController::edit/$1');
    $routes->get('edit/(:num)', 'Masters\ProductCategoryController::edit/$1'); // Match View link
    $routes->post('(:num)', 'Masters\ProductCategoryController::update/$1');
    $routes->delete('(:num)', 'Masters\ProductCategoryController::delete/$1');
    $routes->post('update/(:num)', 'Masters\ProductCategoryController::update/$1');
    $routes->get('delete/(:num)', 'Masters\ProductCategoryController::delete/$1');
  });

  // PRODUCT ROUTES
  $routes->group('products', ['filter' => 'permission:product'], function ($routes) {
    $routes->get('/', 'Masters\ProductController::index');
    $routes->get('create', 'Masters\ProductController::create');
    $routes->post('store', 'Masters\ProductController::store');
    $routes->post('/', 'Masters\ProductController::store');
    $routes->get('search', 'Masters\ProductController::search');
    $routes->get('(:num)', 'Masters\ProductController::show/$1');
    $routes->get('edit/(:num)', 'Masters\ProductController::edit/$1');
    $routes->post('update/(:num)', 'Masters\ProductController::update/$1');
    $routes->delete('delete/(:num)', 'Masters\ProductController::delete/$1');
    $routes->delete('(:num)', 'Masters\ProductController::delete/$1');
  });

  // PROCESS ROUTES
  $routes->group('processes', ['filter' => 'permission:process'], function ($routes) {
    $routes->get('/', 'Masters\ProcessController::index');
    $routes->get('create', 'Masters\ProcessController::create');
    $routes->post('store', 'Masters\ProcessController::store');
    $routes->post('/', 'Masters\ProcessController::store');
    $routes->get('by-type/(:alpha)', 'Masters\ProcessController::getByType/$1');
    $routes->get('(:num)', 'Masters\ProcessController::show/$1');
    $routes->get('edit/(:num)', 'Masters\ProcessController::edit/$1');
    $routes->post('update/(:num)', 'Masters\ProcessController::update/$1');
    $routes->delete('delete/(:num)', 'Masters\ProcessController::delete/$1');
    $routes->delete('(:num)', 'Masters\ProcessController::delete/$1');
  });
});

// CUSTOMER ROUTES
$routes->group('customers', ['filter' => 'auth'], function ($routes) {
  // Accounts
  $routes->group('accounts', ['filter' => 'permission:account'], function ($routes) {
    $routes->get('/', 'Customers\AccountController::index');
    $routes->get('create', 'Customers\AccountController::create');
    $routes->post('/', 'Customers\AccountController::store');
    $routes->get('search', 'Customers\AccountController::search');
    $routes->get('(:num)', 'Customers\AccountController::show/$1');
    $routes->get('(:num)/edit', 'Customers\AccountController::edit/$1');
    $routes->post('(:num)', 'Customers\AccountController::update/$1');
    $routes->delete('(:num)', 'Customers\AccountController::delete/$1');
    $routes->get('(:num)/ledger', 'Customers\AccountController::ledger/$1');
  });

  // Cash Customers
  $routes->group('cash-customers', ['filter' => 'permission:cash_customer'], function ($routes) {
    $routes->get('/', 'Customers\CashCustomerController::index');
    $routes->get('create', 'Customers\CashCustomerController::create');
    $routes->post('/', 'Customers\CashCustomerController::store');
    $routes->post('find-or-create', 'Customers\CashCustomerController::findOrCreate');
    $routes->get('search', 'Customers\CashCustomerController::search');
    $routes->get('(:num)', 'Customers\CashCustomerController::show/$1');
    $routes->get('(:num)/edit', 'Customers\CashCustomerController::edit/$1');
    $routes->post('(:num)', 'Customers\CashCustomerController::update/$1');
    $routes->delete('(:num)', 'Customers\CashCustomerController::delete/$1');
  });
});

// CHALLAN ROUTES
$routes->group('challans', ['filter' => 'auth'], function ($routes) {
  $routes->group('', ['filter' => 'permission:challan'], function ($routes) {
    // List & Search
    $routes->get('/', 'Challans\ChallanController::index');
    $routes->get('search', 'Challans\ChallanController::search');

    // Create
    $routes->get('create', 'Challans\ChallanController::create');
    $routes->post('/', 'Challans\ChallanController::store');

    // AJAX: Calculate line (preview, no save)
    $routes->post('calculate-line', 'Challans\ChallanController::calculateLine');

    // AJAX: Get processes by challan type
    $routes->get('processes', 'Challans\ChallanController::getProcessesByType');

    // AJAX: Delete line (by line ID, not challan ID)
    $routes->delete('lines/(:num)', 'Challans\ChallanController::deleteLine/$1');

    // Show / Edit / Update / Delete (by challan ID)
    $routes->get('(:num)', 'Challans\ChallanController::show/$1');
    $routes->get('(:num)/edit', 'Challans\ChallanController::edit/$1');
    $routes->post('(:num)', 'Challans\ChallanController::update/$1');
    $routes->delete('(:num)', 'Challans\ChallanController::delete/$1');

    // AJAX: Line management
    $routes->post('(:num)/add-line', 'Challans\ChallanController::addLine/$1');
    $routes->post('(:num)/update-line/(:num)', 'Challans\ChallanController::updateLine/$1/$2');

    // AJAX: Status change
    $routes->post('(:num)/change-status', 'Challans\ChallanController::changeStatus/$1');

    // Print
    $routes->get('(:num)/print', 'Challans\ChallanController::print/$1');
  });
});

// INVOICE ROUTES
$routes->group('', ['filter' => 'auth'], function ($routes) {
  // Base Invoice Routes
  $routes->group('invoices', ['namespace' => 'App\Controllers\Invoices', 'filter' => 'permission:invoice'], function ($routes) {
    $routes->get('/', 'InvoiceController::index');

    // Create invoice (standalone)
    $routes->get('create', 'InvoiceController::create');
    $routes->post('/', 'InvoiceController::store');

    // Create invoice from challan
    $routes->get('create-from-challan/(:num)', 'InvoiceController::createFromChallan/$1');
    $routes->post('from-challan', 'InvoiceController::storeFromChallan');

    // Edit invoice
    $routes->get('(:num)/edit', 'InvoiceController::edit/$1');
    $routes->post('(:num)', 'InvoiceController::update/$1');

    // View invoice
    $routes->get('(:num)', 'InvoiceController::show/$1');

    // Delete invoice
    $routes->delete('(:num)', 'InvoiceController::delete/$1');

    // Print/PDF invoice
    $routes->get('(:num)/print', 'InvoiceController::print/$1');
  });

  // Account Invoice Routes
  $routes->group('account-invoices', ['namespace' => 'App\Controllers\Invoices', 'filter' => 'permission:invoice'], function ($routes) {
    $routes->get('/', 'AccountInvoiceController::index');
    $routes->get('create', 'AccountInvoiceController::create');
    $routes->post('/', 'AccountInvoiceController::store');
    $routes->post('store-from-challans', 'AccountInvoiceController::storeFromChallans');

    // Inherited routes
    $routes->get('create-from-challan/(:num)', 'AccountInvoiceController::createFromChallan/$1');
    $routes->post('from-challan', 'AccountInvoiceController::storeFromChallan');
    $routes->get('(:num)/edit', 'AccountInvoiceController::edit/$1');
    $routes->get('(:num)', 'AccountInvoiceController::show/$1');
    $routes->post('(:num)', 'AccountInvoiceController::update/$1');
    $routes->delete('(:num)', 'AccountInvoiceController::delete/$1');
    $routes->get('(:num)/print', 'AccountInvoiceController::print/$1');
  });

  // Cash Invoice Routes
  $routes->group('cash-invoices', ['namespace' => 'App\Controllers\Invoices', 'filter' => 'permission:invoice'], function ($routes) {
    $routes->get('/', 'CashInvoiceController::index');
    $routes->get('create', 'CashInvoiceController::create');
    $routes->post('/', 'CashInvoiceController::store');

    // Inherited routes
    $routes->get('create-from-challan/(:num)', 'CashInvoiceController::createFromChallan/$1');
    $routes->post('from-challan', 'CashInvoiceController::storeFromChallan');
    $routes->get('(:num)/edit', 'CashInvoiceController::edit/$1');
    $routes->get('(:num)', 'CashInvoiceController::show/$1');
    $routes->post('(:num)', 'CashInvoiceController::update/$1');
    $routes->delete('(:num)', 'CashInvoiceController::delete/$1');
    $routes->get('(:num)/print', 'CashInvoiceController::print/$1');
  });

  // Wax Invoice Routes
  $routes->group('wax-invoices', ['namespace' => 'App\Controllers\Invoices', 'filter' => 'permission:invoice'], function ($routes) {
    $routes->get('/', 'WaxInvoiceController::index');
    $routes->get('create', 'WaxInvoiceController::create');
    $routes->post('/', 'WaxInvoiceController::store');

    // Inherited routes
    $routes->get('create-from-challan/(:num)', 'WaxInvoiceController::createFromChallan/$1');
    $routes->post('from-challan', 'WaxInvoiceController::storeFromChallan');
    $routes->get('(:num)/edit', 'WaxInvoiceController::edit/$1');
    $routes->get('(:num)', 'WaxInvoiceController::show/$1');
    $routes->post('(:num)', 'WaxInvoiceController::update/$1');
    $routes->delete('(:num)', 'WaxInvoiceController::delete/$1');
    $routes->get('(:num)/print', 'WaxInvoiceController::print/$1');
  });
});

// PAYMENT ROUTES
$routes->group('payments', ['namespace' => 'App\Controllers\Payments', 'filter' => 'auth'], function ($routes) {
  $routes->get('/', 'PaymentController::index');
  $routes->get('create', 'PaymentController::create');
  $routes->post('/', 'PaymentController::store');
  $routes->get('(:num)', 'PaymentController::show/$1');
  $routes->delete('(:num)', 'PaymentController::delete/$1');
});

// LEDGER & REMINDER ROUTES
$routes->group('ledgers', ['namespace' => 'App\Controllers\Ledgers', 'filter' => 'auth'], function ($routes) {
  // Ledger List (Index)
  $routes->get('accounts', 'LedgerController::accountsLedger');
  $routes->get('cash-customers', 'LedgerController::cashCustomersLedger');

  // Ledger Detail
  $routes->get('account/(:num)', 'LedgerController::accountLedger/$1');
  $routes->get('cash-customer/(:num)', 'LedgerController::cashCustomerLedger/$1');

  // Export
  $routes->get('export/(:alpha)/(:num)', 'LedgerController::exportLedger/$1/$2');

  // Reminders
  $routes->group('reminders', function ($routes) {
    $routes->get('outstanding', 'ReminderController::outstandingInvoices');
    $routes->post('send/(:num)', 'ReminderController::sendReminder/$1');
  });
});

// REPORT ROUTES
$routes->group('reports', ['namespace' => 'App\Controllers\Reports', 'filter' => 'auth'], function ($routes) {
  // Outstanding Report (OutstandingReportController)
  $routes->group('outstanding', function ($routes) {
    $routes->get('/', 'OutstandingReportController::index');
    $routes->get('aging', 'OutstandingReportController::agingReport');
  });

  // Ledger (Note: LedgerController is in Ledgers namespace, alias here for consistency)
  $routes->group('ledger', ['namespace' => 'App\Controllers\Ledgers'], function ($routes) {
    // We link 'reports/receivables' to LedgerController index or new ReceivableReportController
    // Task 8 says ReceivableReportController.
    // Let's assume ReceivableReportController exists or we use LedgerController for now.
    // Existing sidebar points to reports/receivables.
  });

  // Receivables (ReceivableReportController)
  // If ReceivableReportController is not yet made, we might fallback to LedgerController or create it?
  // User Prompt says "Complete Reports Module". I should make sure ReceivableReportController exists if I route to it.
  // I recall LedgerController handles most of this. Let's check if ReceivableReportController exists.
  // I will add the route, assuming I will use LedgerController if Receivable doesn't exist, OR I will create ReceivableReportController.
  // Let's use specific controllers.

  $routes->get('receivables', 'ReceivableReportController::index');
  $routes->get('receivables/monthly', 'ReceivableReportController::monthlySummary');
});


// DELIVERY ROUTES
$routes->group('deliveries', ['filter' => 'auth'], function ($routes) {
  // Admin / Manager Routes
  $routes->get('/', 'Deliveries\DeliveryController::index');
  $routes->get('create', 'Deliveries\DeliveryController::create');
  $routes->post('/', 'Deliveries\DeliveryController::store');

  // Actions
  $routes->get('(:num)', 'Deliveries\DeliveryController::show/$1');
  $routes->post('(:num)/start', 'Deliveries\DeliveryController::start/$1');
  $routes->post('(:num)/complete', 'Deliveries\DeliveryController::complete/$1');
  $routes->post('(:num)/fail', 'Deliveries\DeliveryController::fail/$1');
});

// Delivery Personnel Route
$routes->get('my-deliveries', 'Deliveries\DeliveryController::myDeliveries', ['filter' => 'auth']);

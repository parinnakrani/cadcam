<?php

namespace App\Controllers\Invoices;

use App\Controllers\BaseController;
use App\Services\Invoice\InvoiceService;
use App\Services\Invoice\InvoiceCalculationService;
use App\Services\Tax\TaxCalculationService;
use App\Services\Auth\PermissionService;
use App\Services\Challan\ChallanService;
use App\Services\Challan\ChallanCalculationService;
use App\Services\Audit\AuditService;
use App\Models\AccountModel;
use App\Models\CashCustomerModel;
use App\Models\ProductModel;
use App\Models\ProcessModel;
use App\Models\ChallanModel;
use App\Models\ChallanLineModel;
use App\Models\GoldRateModel;
use App\Models\CompanyModel;
use App\Services\Invoice\InvoiceNotFoundException;
use App\Services\Invoice\InvoiceAlreadyPaidException;
use App\Services\Invoice\ChallanAlreadyInvoicedException;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

/**
 * InvoiceController
 * 
 * Handles all invoice management HTTP requests including:
 * - Invoice listing and filtering
 * - Invoice creation (standalone and from challan)
 * - Invoice viewing and editing
 * - Invoice deletion
 * - PDF generation
 * 
 * Business Rules:
 * - Cannot edit paid invoices
 * - Cannot delete invoices with payments
 * - All actions require appropriate permissions
 * - Challan-to-invoice conversion supported
 */
class InvoiceController extends BaseController
{
  protected InvoiceService $invoiceService;
  protected InvoiceCalculationService $calculationService;
  protected TaxCalculationService $taxService;
  protected ChallanService $challanService;
  protected PermissionService $permissionService;
  protected AccountModel $accountModel;
  protected CashCustomerModel $cashCustomerModel;
  protected ProductModel $productModel;
  protected ProcessModel $processModel;
  protected GoldRateModel $goldRateModel;

  public function __construct()
  {
    $this->invoiceService = new InvoiceService();
    $this->calculationService = new InvoiceCalculationService();
    $this->taxService = new TaxCalculationService();
    $this->permissionService = new PermissionService();
    $this->accountModel = new AccountModel();
    $this->cashCustomerModel = new CashCustomerModel();
    $this->productModel = new ProductModel();
    $this->processModel = new ProcessModel();
    $this->goldRateModel = new GoldRateModel();

    // Instantiate ChallanService with all dependencies
    $this->challanService = new ChallanService(
      new ChallanModel(),
      new ChallanLineModel(),
      new AccountModel(),
      new CashCustomerModel(),
      new CompanyModel(),
      new ChallanCalculationService(
        new ProcessModel(),
        new CompanyModel()
      ),
      new AuditService()
    );
  }

  /**
   * List all invoices with filters
   * 
   * GET /invoices
   * 
   * @return string|ResponseInterface
   */
  public function index()
  {
    // We want the generic invoices list view. If they have ANY invoice list permission, let them in.
    if (!can_any('invoices') || (!can('invoices.all.list') && !can('invoices.account.list') && !can('invoices.cash.list') && !can('invoices.wax.list'))) {
      if ($this->request->isAJAX() || $this->request->is('json')) {
        return $this->response->setStatusCode(403)->setJSON([
          'error' => 'You do not have permission to view invoices'
        ]);
      }
      return redirect()->back()->with('error', 'You do not have permission to view invoices');
    }

    try {
      // Get filters from request
      $filters = [
        'invoice_type'   => $this->request->getGet('invoice_type'),
        'payment_status' => $this->request->getGet('payment_status'),
        'customer_type'  => $this->request->getGet('customer_type'),
        'date_from'      => $this->request->getGet('date_from'),
        'date_to'        => $this->request->getGet('date_to'),
        'search'         => $this->request->getGet('search'),
      ];

      // Get invoices with filters
      $invoiceModel = new \App\Models\InvoiceModel();

      // Initial query config with customer name JOINs
      $invoiceModel->select('invoices.*, COALESCE(accounts.account_name, cash_customers.customer_name) as customer_name')
        ->join('accounts', 'accounts.id = invoices.account_id', 'left')
        ->join('cash_customers', 'cash_customers.id = invoices.cash_customer_id', 'left')
        ->where('invoices.company_id', session()->get('company_id'))
        ->where('invoices.is_deleted', 0)
        ->orderBy('invoices.invoice_date', 'DESC')
        ->orderBy('invoices.id', 'DESC');

      // Apply filters
      if (!empty($filters['invoice_type'])) {
        $invoiceModel->where('invoices.invoice_type', $filters['invoice_type']);
      }

      if (!empty($filters['payment_status'])) {
        $invoiceModel->where('invoices.payment_status', $filters['payment_status']);
      }

      if (!empty($filters['date_from'])) {
        $invoiceModel->where('invoices.invoice_date >=', $filters['date_from']);
      }

      if (!empty($filters['date_to'])) {
        $invoiceModel->where('invoices.invoice_date <=', $filters['date_to']);
      }

      if (!empty($filters['search'])) {
        $invoiceModel->groupStart()
          ->like('invoices.invoice_number', $filters['search'])
          ->orLike('invoices.reference_number', $filters['search'])
          ->groupEnd();
      }

      // Get all invoices (DataTables handles pagination)
      $invoices = $invoiceModel->findAll();

      // Check if AJAX request
      if ($this->request->isAJAX()) {
        return $this->response->setJSON([
          'success' => true,
          'data' => $invoices
        ]);
      }

      // Action flags for the view (we provide the parent 'invoices' and generic sub-module)
      $actionFlags = [];
      if ($this->permissions) {
        $actionFlags = $this->permissions->getActionFlags('invoices', 'all');
      }

      // Per-type create flags for individual buttons
      $canCreateAccount = $this->permissions ? ($this->permissions->canCreate('invoices', 'account') || $this->permissions->canCreate('invoices', 'all')) : false;
      $canCreateCash    = $this->permissions ? ($this->permissions->canCreate('invoices', 'cash')    || $this->permissions->canCreate('invoices', 'all')) : false;
      $canCreateWax     = $this->permissions ? ($this->permissions->canCreate('invoices', 'wax')     || $this->permissions->canCreate('invoices', 'all')) : false;

      // Load view
      return $this->render('invoices/index', [
        'invoices'         => $invoices,
        'filters'          => $filters,
        'action_flags'     => $actionFlags,
        'canCreateAccount' => $canCreateAccount,
        'canCreateCash'    => $canCreateCash,
        'canCreateWax'     => $canCreateWax,
      ]);
    } catch (Exception $e) {
      log_message('error', 'Invoice listing error: ' . $e->getMessage());

      if ($this->request->isAJAX()) {
        return $this->response->setStatusCode(500)->setJSON([
          'error' => 'Failed to load invoices'
        ]);
      }

      return redirect()->back()->with('error', 'Failed to load invoices');
    }
  }

  /**
   * Show invoice creation form
   * 
   * GET /invoices/create
   * 
   * @return string|ResponseInterface
   */
  public function create()
  {
    if (!can('invoices.account.create') && !can('invoices.cash.create') && !can('invoices.wax.create')) {
      return redirect()->back()->with('error', 'You do not have permission to create invoices');
    }

    try {
      // Load dropdowns
      $companyId = session()->get('company_id') ?? 1;

      $data = [
        'accounts' => $this->accountModel->where('company_id', $companyId)
          ->where('is_deleted', 0)
          ->orderBy('account_name', 'ASC')
          ->findAll(),
        'cash_customers' => $this->cashCustomerModel
          ->orderBy('customer_name', 'ASC')
          ->findAll(),
        'products' => $this->productModel->where('company_id', $companyId)
          ->where('is_deleted', 0)
          ->orderBy('product_name', 'ASC')
          ->findAll(),
        'processes' => $this->processModel->where('company_id', $companyId)
          ->where('is_deleted', 0)
          ->orderBy('process_name', 'ASC')
          ->findAll(),
        'default_tax_rate' => $this->taxService->getTaxRate($companyId),
      ];

      return $this->render('invoices/create', $data);
    } catch (Exception $e) {
      log_message('error', 'Invoice create form error: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Failed to load invoice creation form');
    }
  }

  /**
   * Show invoice creation form pre-filled with challan data
   * 
   * GET /invoices/create-from-challan/{challanId}
   * 
   * @param int $challanId Challan ID
   * @return string|ResponseInterface
   */
  public function createFromChallan(int $challanId)
  {
    if (!can('invoices.account.create') && !can('invoices.cash.create') && !can('invoices.wax.create')) {
      return redirect()->back()->with('error', 'You do not have permission to create invoices');
    }

    try {
      // Get challan data
      $challan = $this->challanService->getChallanById($challanId);

      if (!$challan) {
        return redirect()->to('/challans')->with('error', 'Challan not found');
      }

      // Check if already invoiced
      if ($challan['is_invoiced'] == 1) {
        return redirect()->to("/challans/{$challanId}")
          ->with('error', 'This challan has already been invoiced');
      }

      // Check if approved
      if ($challan['challan_status'] !== 'Approved') {
        return redirect()->to("/challans/{$challanId}")
          ->with('error', 'Only approved challans can be invoiced');
      }

      // Load dropdowns
      $companyId = session()->get('company_id') ?? 1;

      $data = [
        'challan' => $challan,
        'accounts' => $this->accountModel->where('company_id', $companyId)
          ->where('is_deleted', 0)
          ->orderBy('account_name', 'ASC')
          ->findAll(),
        'products' => $this->productModel->where('company_id', $companyId)
          ->where('is_deleted', 0)
          ->orderBy('product_name', 'ASC')
          ->findAll(),
        'processes' => $this->processModel->where('company_id', $companyId)
          ->where('is_deleted', 0)
          ->orderBy('process_name', 'ASC')
          ->findAll(),
        'default_tax_rate' => $this->taxService->getTaxRate($companyId),
      ];

      return $this->render('invoices/create_from_challan', $data);
    } catch (Exception $e) {
      log_message('error', 'Create from challan error: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Failed to load challan data');
    }
  }

  /**
   * Store new invoice
   * 
   * POST /invoices
   * 
   * @return ResponseInterface
   */
  public function store()
  {
    $invoiceType = $this->request->getPost('invoice_type');
    $sub = $this->resolveInvoiceSub($invoiceType);

    if (!can("invoices.{$sub}.create")) {
      return $this->response->setStatusCode(403)->setJSON([
        'error' => 'You do not have permission to create this type of invoice'
      ]);
    }

    try {
      // Get POST data
      $invoiceData = [
        'invoice_type'      => $this->request->getPost('invoice_type'),
        'invoice_date'      => $this->request->getPost('invoice_date'),
        'due_date'          => $this->request->getPost('due_date'),
        'account_id'        => $this->request->getPost('account_id'),
        'cash_customer_id'  => $this->request->getPost('cash_customer_id'),
        'billing_address'   => $this->request->getPost('billing_address'),
        'shipping_address'  => $this->request->getPost('shipping_address'),
        'reference_number'  => $this->request->getPost('reference_number'),
        'tax_rate'          => $this->request->getPost('tax_rate') ?? session('company_default_tax_rate') ?? 3.00,
        'notes'             => $this->request->getPost('notes'),
        'terms_conditions'  => $this->request->getPost('terms_conditions'),
        'company_id'        => session()->get('company_id') ?? 1, // Added company_id with default
      ];

      // Get lines data
      $lines = $this->request->getPost('lines') ?? [];

      // Create invoice
      $invoiceId = $this->invoiceService->createInvoice($invoiceData, $lines);

      // Success response
      if ($this->request->isAJAX()) {
        return $this->response->setJSON([
          'success' => true,
          'message' => 'Invoice created successfully',
          'invoice_id' => $invoiceId,
          'redirect' => base_url("invoices/{$invoiceId}")
        ]);
      }

      return redirect()->to("/invoices/{$invoiceId}")
        ->with('success', 'Invoice created successfully');
    } catch (\App\Services\Invoice\ValidationException $e) {
      log_message('error', 'Invoice validation error: ' . $e->getMessage());

      if ($this->request->isAJAX()) {
        return $this->response->setStatusCode(400)->setJSON([
          'error' => $e->getMessage()
        ]);
      }

      return redirect()->back()->withInput()->with('error', $e->getMessage());
    } catch (Exception $e) {
      log_message('error', 'Invoice creation error: ' . $e->getMessage());

      if ($this->request->isAJAX()) {
        return $this->response->setStatusCode(500)->setJSON([
          'error' => $e->getMessage()
        ]);
      }

      return redirect()->back()->withInput()->with('error', 'Failed to create invoice');
    }
  }

  /**
   * Store invoice from challan
   * 
   * POST /invoices/from-challan
   * 
   * @return ResponseInterface
   */
  public function storeFromChallan()
  {
    if (!can('invoices.account.create') && !can('invoices.cash.create') && !can('invoices.wax.create')) {
      return $this->response->setStatusCode(403)->setJSON([
        'status'  => 'error',
        'message' => 'You do not have permission to create an invoice'
      ]);
    }

    try {
      // Get challan ID
      $challanId = (int) $this->request->getPost('challan_id');

      if (!$challanId) {
        throw new Exception('Challan ID is required');
      }

      $companyId = session()->get('company_id') ?? 1; // Used companyId here, though not directly passed to createInvoiceFromChallan

      // Create invoice from challan
      $invoiceId = $this->invoiceService->createInvoiceFromChallan($challanId);

      // Success response
      if ($this->request->isAJAX()) {
        return $this->response->setJSON([
          'success' => true,
          'message' => 'Invoice created from challan successfully',
          'invoice_id' => $invoiceId,
          'redirect' => base_url("invoices/{$invoiceId}")
        ]);
      }

      return redirect()->to("/invoices/{$invoiceId}")
        ->with('success', 'Invoice created from challan successfully');
    } catch (ChallanAlreadyInvoicedException $e) {
      log_message('warning', 'Challan already invoiced: ' . $e->getMessage());

      if ($this->request->isAJAX()) {
        return $this->response->setStatusCode(400)->setJSON([
          'error' => $e->getMessage()
        ]);
      }

      return redirect()->back()->with('error', $e->getMessage());
    } catch (Exception $e) {
      log_message('error', 'Invoice from challan error: ' . $e->getMessage());

      if ($this->request->isAJAX()) {
        return $this->response->setStatusCode(500)->setJSON([
          'error' => 'Failed to create invoice from challan'
        ]);
      }

      return redirect()->back()->with('error', 'Failed to create invoice from challan');
    }
  }

  /**
   * Show invoice details
   * 
   * GET /invoices/{id}
   * 
   * @param int $id Invoice ID
   * @return string|ResponseInterface
   */
  public function show(int $id)
  {
    // Check permission
    if (!can('invoice.view')) {
      return redirect()->back()->with('error', 'You do not have permission to view invoices');
    }

    try {
      // Get invoice with lines
      $invoice = $this->invoiceService->getInvoiceById($id);

      if (!$invoice) {
        return redirect()->to('/invoices')->with('error', 'Invoice not found');
      }

      // Get payment history (if payment module exists)
      // $payments = $this->paymentService->getPaymentsByInvoiceId($id);

      // Check if AJAX request
      if ($this->request->isAJAX()) {
        return $this->response->setJSON([
          'success' => true,
          'data' => $invoice
        ]);
      }

      // Load view
      $data = [
        'invoice' => $invoice,
        // 'payments' => $payments ?? []
      ];
      if ($this->permissions) {
        $data['action_flags'] = $this->permissions->getActionFlags('invoices', $sub ?? 'all');
      }
      return $this->render('invoices/show', $data);
    } catch (Exception $e) {
      log_message('error', 'Invoice show error: ' . $e->getMessage());

      if ($this->request->isAJAX()) {
        return $this->response->setStatusCode(500)->setJSON([
          'error' => 'Failed to load invoice'
        ]);
      }

      return redirect()->to('/invoices')->with('error', 'Failed to load invoice');
    }
  }

  /**
   * Show invoice edit form
   * 
   * GET /invoices/{id}/edit
   * 
   * @param int $id Invoice ID
   * @return string|ResponseInterface
   */
  public function edit(int $id)
  {
    // Permission check will happen after we get the invoice type
    try {
      // Get invoice
      $invoice = $this->invoiceService->getInvoiceById($id);

      if (!$invoice) {
        return redirect()->to('/invoices')->with('error', 'Invoice not found');
      }

      $sub = $this->resolveInvoiceSub($invoice['invoice_type']);
      $this->gate("invoices.{$sub}.edit");

      // Check if paid
      if ($invoice['total_paid'] > 0) {
        return redirect()->to("/invoices/{$id}")
          ->with('error', 'Cannot edit invoice with payment history');
      }

      // Load dropdowns
      $companyId = session()->get('company_id');

      // Get gold rates for all purities
      $goldRates = [];
      foreach (['24K', '22K', '18K', '14K'] as $purity) {
        $rate = $this->goldRateModel->getLatestRate((int)$companyId, $purity);
        if ($rate !== null) {
          $goldRates[$purity] = $rate;
        }
      }

      $data = [
        'invoice' => $invoice,
        'accounts' => $this->accountModel->where('company_id', $companyId)
          ->where('is_deleted', 0)
          ->orderBy('account_name', 'ASC')
          ->findAll(),
        'cash_customers' => $this->cashCustomerModel
          ->orderBy('customer_name', 'ASC')
          ->findAll(),
        'products' => $this->productModel->where('company_id', $companyId)
          ->where('is_deleted', 0)
          ->orderBy('product_name', 'ASC')
          ->findAll(),
        'processes' => $this->processModel->where('company_id', $companyId)
          ->where('is_deleted', 0)
          ->orderBy('process_name', 'ASC')
          ->findAll(),
        'gold_rates' => $goldRates,
      ];

      return $this->render('invoices/edit', $data);
    } catch (Exception $e) {
      log_message('error', 'Invoice edit form error: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Failed to load invoice edit form');
    }
  }

  /**
   * Update invoice
   * 
   * POST /invoices/{id}
   * 
   * @param int $id Invoice ID
   * @return ResponseInterface
   */
  public function update(int $id)
  {
    try {
      // Get invoice first to check its type
      $invoice = $this->invoiceService->getInvoiceById($id);

      if (!$invoice) {
        return $this->response->setStatusCode(404)->setJSON(['error' => 'Invoice not found']);
      }

      $sub = $this->resolveInvoiceSub($invoice['invoice_type']);
      if (!can("invoices.{$sub}.edit")) {
        return $this->response->setStatusCode(403)->setJSON([
          'error' => 'You do not have permission to edit this invoice'
        ]);
      }
      // Get POST data
      $invoiceData = [
        'invoice_date'      => $this->request->getPost('invoice_date'),
        'due_date'          => $this->request->getPost('due_date'),
        'billing_address'   => $this->request->getPost('billing_address'),
        'shipping_address'  => $this->request->getPost('shipping_address'),
        'reference_number'  => $this->request->getPost('reference_number'),
        'notes'             => $this->request->getPost('notes'),
        'terms_conditions'  => $this->request->getPost('terms_conditions'),
      ];

      // Get lines data
      $lines = $this->request->getPost('lines') ?? [];

      // Update invoice
      $success = $this->invoiceService->updateInvoice($id, $invoiceData, $lines);

      // Success response
      if ($this->request->isAJAX()) {
        return $this->response->setJSON([
          'success' => true,
          'message' => 'Invoice updated successfully',
          'redirect' => base_url("invoices/{$id}")
        ]);
      }

      return redirect()->to("/invoices/{$id}")
        ->with('success', 'Invoice updated successfully');
    } catch (InvoiceAlreadyPaidException $e) {
      log_message('warning', 'Cannot edit paid invoice: ' . $e->getMessage());

      if ($this->request->isAJAX()) {
        return $this->response->setStatusCode(400)->setJSON([
          'error' => $e->getMessage()
        ]);
      }

      return redirect()->back()->with('error', $e->getMessage());
    } catch (Exception $e) {
      log_message('error', 'Invoice update error: ' . $e->getMessage());

      if ($this->request->isAJAX()) {
        return $this->response->setStatusCode(500)->setJSON([
          'error' => 'Failed to update invoice'
        ]);
      }

      return redirect()->back()->withInput()->with('error', 'Failed to update invoice');
    }
  }

  /**
   * Delete invoice
   * 
   * DELETE /invoices/{id}
   * 
   * @param int $id Invoice ID
   * @return ResponseInterface
   */
  public function delete(int $id)
  {
    try {
      // Get invoice first to check its type
      $invoice = $this->invoiceService->getInvoiceById($id);

      if (!$invoice) {
        return $this->response->setStatusCode(404)->setJSON(['error' => 'Invoice not found']);
      }

      $sub = $this->resolveInvoiceSub($invoice['invoice_type']);
      if (!can("invoices.{$sub}.delete")) {
        return $this->response->setStatusCode(403)->setJSON([
          'error' => 'You do not have permission to delete this invoice'
        ]);
      }
      // Delete invoice
      $this->invoiceService->deleteInvoice($id);

      return $this->response->setJSON([
        'success' => true,
        'message' => 'Invoice deleted successfully',
        'redirect' => base_url('invoices')
      ]);
    } catch (InvoiceAlreadyPaidException $e) {
      log_message('warning', 'Cannot delete paid invoice: ' . $e->getMessage());

      return $this->response->setStatusCode(400)->setJSON([
        'error' => $e->getMessage()
      ]);
    } catch (InvoiceNotFoundException $e) {
      log_message('warning', 'Invoice not found: ' . $e->getMessage());

      return $this->response->setStatusCode(404)->setJSON([
        'error' => 'Invoice not found'
      ]);
    } catch (Exception $e) {
      log_message('error', 'Invoice deletion error: ' . $e->getMessage());

      return $this->response->setStatusCode(500)->setJSON([
        'error' => 'Failed to delete invoice'
      ]);
    }
  }

  /**
   * Generate and download invoice PDF
   * 
   * GET /invoices/{id}/print
   * 
   * @param int $id Invoice ID
   * @return string|ResponseInterface
   */
  public function print(int $id)
  {
    // Check permission happens after we load the invoice
    try {
      // Get invoice with lines
      $invoice = $this->invoiceService->getInvoiceById($id);

      if (!$invoice) {
        return redirect()->to('/invoices')->with('error', 'Invoice not found');
      }

      $sub = $this->resolveInvoiceSub($invoice['invoice_type']);
      $this->gate("invoices.{$sub}.print");

      // Render printable HTML view
      return $this->render('invoices/print', ['invoice' => $invoice]);
    } catch (Exception $e) {
      log_message('error', 'Invoice print error: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Failed to generate printable invoice: ' . $e->getMessage());
    }
  }
}

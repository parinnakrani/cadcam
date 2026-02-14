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
    // Check permission
    if (!can('invoice.view')) {
      return $this->response->setStatusCode(403)->setJSON([
        'error' => 'You do not have permission to view invoices'
      ]);
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

      // Initial query config
      $invoiceModel->where('company_id', session()->get('company_id'))
        ->where('is_deleted', 0)
        ->orderBy('invoice_date', 'DESC')
        ->orderBy('id', 'DESC');

      // Apply filters
      if (!empty($filters['invoice_type'])) {
        $invoiceModel->where('invoice_type', $filters['invoice_type']);
      }

      if (!empty($filters['payment_status'])) {
        $invoiceModel->where('payment_status', $filters['payment_status']);
      }

      if (!empty($filters['date_from'])) {
        $invoiceModel->where('invoice_date >=', $filters['date_from']);
      }

      if (!empty($filters['date_to'])) {
        $invoiceModel->where('invoice_date <=', $filters['date_to']);
      }

      if (!empty($filters['search'])) {
        $invoiceModel->groupStart()
          ->like('invoice_number', $filters['search'])
          ->orLike('reference_number', $filters['search'])
          ->groupEnd();
      }

      // Pagination
      $perPage = 20;
      $invoices = $invoiceModel->paginate($perPage);
      $pager = $invoiceModel->pager;

      // Check if AJAX request
      if ($this->request->isAJAX()) {
        return $this->response->setJSON([
          'success' => true,
          'data' => $invoices,
          'pager' => $pager->links()
        ]);
      }

      // Load view
      return view('invoices/index', [
        'invoices' => $invoices,
        'pager' => $pager,
        'filters' => $filters
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
    // Check permission
    if (!can('invoice.create')) {
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
        'cash_customers' => $this->cashCustomerModel->where('company_id', $companyId)
          ->where('is_deleted', 0)
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

      return view('invoices/create', $data);
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
    // Check permission
    if (!can('invoice.create')) {
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

      return view('invoices/create_from_challan', $data);
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
    // Check permission
    if (!can('invoice.create')) {
      return $this->response->setStatusCode(403)->setJSON([
        'error' => 'You do not have permission to create invoices'
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
    // Check permission
    if (!can('invoice.create')) {
      return $this->response->setStatusCode(403)->setJSON([
        'error' => 'You do not have permission to create invoices'
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
      return view('invoices/show', [
        'invoice' => $invoice,
        // 'payments' => $payments ?? []
      ]);
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
    // Check permission
    if (!can('invoice.edit')) {
      return redirect()->back()->with('error', 'You do not have permission to edit invoices');
    }

    try {
      // Get invoice
      $invoice = $this->invoiceService->getInvoiceById($id);

      if (!$invoice) {
        return redirect()->to('/invoices')->with('error', 'Invoice not found');
      }

      // Check if paid
      if ($invoice['total_paid'] > 0) {
        return redirect()->to("/invoices/{$id}")
          ->with('error', 'Cannot edit invoice with payment history');
      }

      // Load dropdowns
      $companyId = session()->get('company_id');

      $data = [
        'invoice' => $invoice,
        'accounts' => $this->accountModel->where('company_id', $companyId)
          ->where('is_deleted', 0)
          ->orderBy('account_name', 'ASC')
          ->findAll(),
        'cash_customers' => $this->cashCustomerModel->where('company_id', $companyId)
          ->where('is_deleted', 0)
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
      ];

      return view('invoices/edit', $data);
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
    // Check permission
    if (!can('invoice.edit')) {
      return $this->response->setStatusCode(403)->setJSON([
        'error' => 'You do not have permission to edit invoices'
      ]);
    }

    try {
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
    // Check permission
    if (!can('invoice.delete')) {
      return $this->response->setStatusCode(403)->setJSON([
        'error' => 'You do not have permission to delete invoices'
      ]);
    }

    try {
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
   * @return ResponseInterface
   */
  public function print(int $id)
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

      // Generate PDF
      if (!class_exists('\App\Libraries\InvoicePDF')) {
        // If library doesn't exist, log error and return placeholder or error
        // For development, we might throw exception. For production, graceful fail.
        // We'll throw exception to be explicit during dev.
        throw new Exception('InvoicePDF library not implemented yet');
      }
      // The following comments were part of the instruction, but are not valid code.
      // $pdf = new \App\Libraries\InvoicePDF($invoice); // Check if this library exists
      // For now, assume it's a placeholder or needs to be implemented.
      // If the user didn't provide this library, we can't fix it properly.
      // I'll leave it as is but commented out if it's causing issues, or just ignore.
      // Actually, I'll just check if the file exists first.
      $pdf = new \App\Libraries\InvoicePDF();
      $pdfContent = $pdf->generate($invoice);

      // Determine download or inline
      $action = $this->request->getGet('action') ?? 'download';

      if ($action === 'inline') {
        // Display in browser
        return $this->response
          ->setContentType('application/pdf')
          ->setBody($pdfContent);
      } else {
        // Download
        $filename = "Invoice_{$invoice['invoice_number']}.pdf";
        return $this->response
          ->setContentType('application/pdf')
          ->setHeader('Content-Disposition', "attachment; filename=\"{$filename}\"")
          ->setBody($pdfContent);
      }
    } catch (Exception $e) {
      log_message('error', 'Invoice PDF generation error: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Failed to generate PDF');
    }
  }
}

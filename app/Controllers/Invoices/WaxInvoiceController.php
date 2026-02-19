<?php

namespace App\Controllers\Invoices;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * WaxInvoiceController
 * 
 * Handles Wax Invoice specific operations.
 * Extends InvoiceController with automatic filtering for Wax Invoices.
 * 
 * Features:
 * - Automatic filter: invoice_type = 'Wax Invoice'
 * - Pre-set invoice type in creation form
 * - Shows both Account and Cash customers in dropdowns
 * - All other functionality inherited from InvoiceController
 */
class WaxInvoiceController extends InvoiceController
{
  protected string $invoiceType = 'Wax Invoice';

  /**
   * List Wax Invoices only
   * 
   * GET /wax-invoices
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
        'invoice_type'   => $this->invoiceType, // Force Wax Invoice type
        'payment_status' => $this->request->getGet('payment_status'),
        'customer_type'  => $this->request->getGet('customer_type'),
        'date_from'      => $this->request->getGet('date_from'),
        'date_to'        => $this->request->getGet('date_to'),
        'search'         => $this->request->getGet('search'),
      ];

      // Get invoices with filters
      $invoiceModel = new \App\Models\InvoiceModel();

      // Base query with customer name JOINs
      $invoiceModel->select('invoices.*, COALESCE(accounts.account_name, cash_customers.customer_name) as customer_name')
        ->join('accounts', 'accounts.id = invoices.account_id', 'left')
        ->join('cash_customers', 'cash_customers.id = invoices.cash_customer_id', 'left')
        ->where('invoices.company_id', session()->get('company_id'))
        ->where('invoices.is_deleted', 0)
        ->where('invoices.invoice_type', $this->invoiceType);

      // Apply other filters
      if (!empty($filters['payment_status'])) {
        $invoiceModel->where('invoices.payment_status', $filters['payment_status']);
      }

      if (!empty($filters['customer_type'])) {
        if ($filters['customer_type'] === 'Account') {
          $invoiceModel->where('invoices.account_id IS NOT NULL');
        } elseif ($filters['customer_type'] === 'Cash') {
          $invoiceModel->where('invoices.cash_customer_id IS NOT NULL');
        }
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

      $invoiceModel->orderBy('invoices.invoice_date', 'DESC')
        ->orderBy('invoices.id', 'DESC');

      // Get all invoices (DataTables handles pagination)
      $invoices = $invoiceModel->findAll();

      // Check if AJAX request
      if ($this->request->isAJAX()) {
        return $this->response->setJSON([
          'success' => true,
          'data' => $invoices
        ]);
      }

      // Load view
      return view('invoices/index', [
        'invoices' => $invoices,
        'filters' => $filters,
        'invoice_type' => $this->invoiceType
      ]);
    } catch (\Exception $e) {
      log_message('error', 'Wax invoice listing error: ' . $e->getMessage());

      if ($this->request->isAJAX()) {
        return $this->response->setStatusCode(500)->setJSON([
          'error' => 'Failed to load invoices'
        ]);
      }

      return redirect()->back()->with('error', 'Failed to load invoices');
    }
  }

  /**
   * Show Wax Invoice creation form
   * 
   * GET /wax-invoices/create
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
      // Load dropdowns - both Account and Cash customers for Wax invoices
      $companyId = session()->get('company_id');

      $data = [
        'invoice_type' => $this->invoiceType, // Pre-set invoice type
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

      return view('invoices/create', $data);
    } catch (\Exception $e) {
      log_message('error', 'Wax invoice create form error: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Failed to load invoice creation form: ' . $e->getMessage());
    }
  }

  /**
   * Store new Wax Invoice
   * 
   * POST /wax-invoices
   * 
   * @return ResponseInterface
   */
  public function store()
  {
    // Force invoice type to Wax Invoice
    $_POST['invoice_type'] = $this->invoiceType;

    // Call parent store method
    // Check permission
    if (!can('invoice.create')) {
      return $this->response->setStatusCode(403)->setJSON([
        'error' => 'You do not have permission to create invoices'
      ]);
    }

    try {
      // Get POST data
      $invoiceData = [
        'invoice_type'      => $this->invoiceType,
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
        'company_id'        => session()->get('company_id') ?? 1,
      ];

      // Get lines data
      $lines = $this->request->getPost('lines') ?? [];

      // Create invoice
      $invoiceId = $this->invoiceService->createInvoice($invoiceData, $lines);

      // Success response
      if ($this->request->isAJAX()) {
        return $this->response->setJSON([
          'success' => true,
          'message' => 'Wax Invoice created successfully',
          'invoice_id' => $invoiceId,
          'redirect' => base_url("wax-invoices/{$invoiceId}")
        ]);
      }

      return redirect()->to("/wax-invoices/{$invoiceId}")
        ->with('success', 'Wax Invoice created successfully');
    } catch (\App\Services\Invoice\ValidationException $e) {
      log_message('error', 'Invoice validation error: ' . $e->getMessage());

      if ($this->request->isAJAX()) {
        return $this->response->setStatusCode(400)->setJSON([
          'error' => $e->getMessage()
        ]);
      }

      return redirect()->back()->withInput()->with('error', $e->getMessage());
    } catch (\Exception $e) {
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
   * Show Wax Invoice edit form
   * 
   * GET /wax-invoices/{id}/edit
   * 
   * @param int $id Invoice ID
   * @return string|ResponseInterface
   */
  public function edit(int $id)
  {
    // Check permission
    if (!can('invoice.edit')) {
      return redirect()->to('/wax-invoices')->with('error', 'You do not have permission to edit invoices');
    }

    try {
      // Get invoice
      $invoice = $this->invoiceService->getInvoiceById($id);

      if (!$invoice) {
        return redirect()->to('/wax-invoices')->with('error', 'Invoice not found');
      }

      // Ensure it is a Wax Invoice
      if ($invoice['invoice_type'] !== $this->invoiceType) {
        return redirect()->to('/wax-invoices')->with('error', 'Invalid invoice type');
      }

      // Check if paid
      if ($invoice['total_paid'] > 0) {
        return redirect()->to("/wax-invoices/{$id}")
          ->with('error', 'Cannot edit invoice with payment history');
      }

      // Load dropdowns - both Account and Cash customers
      $companyId = session()->get('company_id');

      $data = [
        'invoice' => $invoice,
        'invoice_type' => $this->invoiceType,
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
      ];

      return view('invoices/edit', $data);
    } catch (\Exception $e) {
      log_message('error', 'Wax invoice edit form error: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Failed to load invoice edit form');
    }
  }

  /**
   * Update Wax Invoice
   * 
   * POST /wax-invoices/{id}
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
          'redirect' => base_url("wax-invoices/{$id}")
        ]);
      }

      return redirect()->to("/wax-invoices/{$id}")
        ->with('success', 'Invoice updated successfully');
    } catch (\App\Services\Invoice\InvoiceAlreadyPaidException $e) {
      log_message('warning', 'Cannot edit paid invoice: ' . $e->getMessage());

      if ($this->request->isAJAX()) {
        return $this->response->setStatusCode(400)->setJSON([
          'error' => $e->getMessage()
        ]);
      }

      return redirect()->back()->with('error', $e->getMessage());
    } catch (\Exception $e) {
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
   * Delete Wax Invoice
   * 
   * DELETE /wax-invoices/{id}
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
        'redirect' => base_url('wax-invoices') // Redirect to Wax Invoices list
      ]);
    } catch (\App\Services\Invoice\InvoiceAlreadyPaidException $e) {
      log_message('warning', 'Cannot delete paid invoice: ' . $e->getMessage());

      return $this->response->setStatusCode(400)->setJSON([
        'error' => $e->getMessage()
      ]);
    } catch (\App\Services\Invoice\InvoiceNotFoundException $e) {
      log_message('warning', 'Invoice not found: ' . $e->getMessage());

      return $this->response->setStatusCode(404)->setJSON([
        'error' => 'Invoice not found'
      ]);
    } catch (\Exception $e) {
      log_message('error', 'Invoice deletion error: ' . $e->getMessage());

      return $this->response->setStatusCode(500)->setJSON([
        'error' => 'Failed to delete invoice'
      ]);
    }
  }

  /**
   * Show Wax Invoice details
   * 
   * GET /wax-invoices/{id}
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
        return redirect()->to('/wax-invoices')->with('error', 'Invoice not found');
      }

      // Ensure it is a Wax Invoice
      if ($invoice['invoice_type'] !== $this->invoiceType) {
        return redirect()->to('/wax-invoices')->with('error', 'Invalid invoice type');
      }

      // Load view
      return view('invoices/show', [
        'invoice' => $invoice,
      ]);
    } catch (\Exception $e) {
      log_message('error', 'Invoice show error: ' . $e->getMessage());
      return redirect()->to('/wax-invoices')->with('error', 'Failed to load invoice');
    }
  }
}

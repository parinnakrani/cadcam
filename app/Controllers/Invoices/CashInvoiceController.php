<?php

namespace App\Controllers\Invoices;

use CodeIgniter\HTTP\ResponseInterface;
use App\Services\FileUploadService;

/**
 * CashInvoiceController
 * 
 * Handles Cash Invoice specific operations.
 * Extends InvoiceController with automatic filtering for Cash Invoices.
 * 
 * Features:
 * - Automatic filter: invoice_type = 'Cash Invoice'
 * - Pre-set invoice type in creation form
 * - Only shows Cash customers in dropdowns
 * - All other functionality inherited from InvoiceController
 */
class CashInvoiceController extends InvoiceController
{
  protected FileUploadService $fileUploadService;
  protected string $invoiceType = 'Cash Invoice';
  protected string $customerType = 'Cash';

  public function __construct()
  {
    parent::__construct();
    $this->fileUploadService = new FileUploadService();
  }

  /**
   * List Cash Invoices only
   * 
   * GET /cash-invoices
   * 
   * @return string|ResponseInterface
   */
  public function index()
  {
    // Check permission
    $this->gate('invoices.cash.list');

    try {
      // Get filters from request
      $filters = [
        'invoice_type'   => $this->invoiceType, // Force Cash Invoice type
        'payment_status' => $this->request->getGet('payment_status'),
        'date_from'      => $this->request->getGet('date_from'),
        'date_to'        => $this->request->getGet('date_to'),
        'search'         => $this->request->getGet('search'),
      ];

      // Get invoices with filters
      $invoiceModel = new \App\Models\InvoiceModel();
      $invoiceModel->select('invoices.*, cash_customers.customer_name as customer_name')
        ->join('cash_customers', 'cash_customers.id = invoices.cash_customer_id', 'left');

      // Base query
      $invoiceModel->where('invoices.company_id', session()->get('company_id'))
        ->where('invoices.is_deleted', 0)
        ->where('invoices.invoice_type', $this->invoiceType);

      // Apply other filters
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
      $data = [
        'invoices' => $invoices,
        'filters' => $filters,
        'invoice_type' => $this->invoiceType
      ];

      if ($this->permissions) {
        $data['action_flags'] = $this->permissions->getActionFlags('invoices', 'cash');
      }

      return $this->render('invoices/index', $data);
    } catch (\Exception $e) {
      log_message('error', 'Cash invoice listing error: ' . $e->getMessage());

      if ($this->request->isAJAX()) {
        return $this->response->setStatusCode(500)->setJSON([
          'error' => 'Failed to load invoices'
        ]);
      }

      return redirect()->back()->with('error', 'Failed to load invoices');
    }
  }

  /**
   * Show Cash Invoice creation form
   * 
   * GET /cash-invoices/create
   * 
   * @return string|ResponseInterface
   */
  public function create()
  {
    // Check permission
    $this->gate('invoices.cash.create');

    try {
      // Load dropdowns - only Cash customers
      $companyId = session()->get('company_id');

      $data = [
        'invoice_type'  => $this->invoiceType, // Pre-set invoice type
        'customer_type' => $this->customerType, // Pre-set customer type
        'form_action'   => base_url('cash-invoices'), // POST to CashInvoiceController::store()
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
    } catch (\Exception $e) {
      log_message('error', 'Cash invoice create form error: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Failed to load invoice creation form');
    }
  }

  /**
   * Store new Cash Invoice
   * 
   * POST /cash-invoices
   * 
   * @return ResponseInterface
   */
  public function store()
  {
    // Force invoice type to Cash Invoice
    $_POST['invoice_type'] = $this->invoiceType;

    // Call parent store method
    // Note: Parent redirects to /invoices/{id}, which is handled by Routes.php logic usually.
    // Wait, create() is custom, store() uses parent. But parent store redirects to /invoices/{id}.
    // This might redirect to GENERIC invoice view.
    // However, Routes.php defines invoices/{id} to InvoiceController::show which loads shared view.
    // The shared view invoices/show now has dynamic breadcrumbs. So it's mostly fine.
    // BUT consistent redirect to /cash-invoices/{id} is better.

    // Let's implement full store to control redirect
    // Check permission
    if (!can('invoices.cash.create')) {
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
        'account_id'        => null,
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
      $lines = $this->parseLinesFromPost();

      // Handle line image uploads
      $lines = $this->handleLineImageUploads($lines);

      // Create invoice
      $invoiceId = $this->invoiceService->createInvoice($invoiceData, $lines);

      // Success response
      if ($this->request->isAJAX()) {
        return $this->response->setJSON([
          'success' => true,
          'message' => 'Cash Invoice created successfully',
          'invoice_id' => $invoiceId,
          'redirect' => base_url("cash-invoices/{$invoiceId}")
        ]);
      }

      return redirect()->to("/cash-invoices/{$invoiceId}")
        ->with('success', 'Cash Invoice created successfully');
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
   * Show Cash Invoice edit form
   * 
   * GET /cash-invoices/{id}/edit
   * 
   * @param int $id Invoice ID
   * @return string|ResponseInterface
   */
  public function edit(int $id)
  {
    try {
      $this->gate('invoices.cash.edit');

      // Get invoice
      $invoice = $this->invoiceService->getInvoiceById($id);

      if (!$invoice) {
        return redirect()->to('/cash-invoices')->with('error', 'Invoice not found');
      }

      // Ensure it is a Cash Invoice
      if ($invoice['invoice_type'] !== $this->invoiceType) {
        return redirect()->to('/cash-invoices')->with('error', 'Invalid invoice type');
      }

      // Check if paid
      if ($invoice['total_paid'] > 0) {
        return redirect()->to("/cash-invoices/{$id}")
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
        'invoice_type' => $this->invoiceType,
        'customer_type' => $this->customerType,
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
    } catch (\Exception $e) {
      log_message('error', 'Cash invoice edit form error: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Failed to load invoice edit form');
    }
  }

  /**
   * Update Cash Invoice
   * 
   * POST /cash-invoices/{id}
   * 
   * @param int $id Invoice ID
   * @return ResponseInterface
   */
  public function update(int $id)
  {
    // Check permission
    try {
      // Get invoice first to check its type
      $invoice = $this->invoiceService->getInvoiceById($id);

      if (!$invoice) {
        return $this->response->setStatusCode(404)->setJSON(['error' => 'Invoice not found']);
      }

      if ($invoice['invoice_type'] !== $this->invoiceType) {
        return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid invoice type']);
      }

      if (!can("invoices.cash.edit")) {
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
      $lines = $this->parseLinesFromPost();

      // Handle line image uploads
      $lines = $this->handleLineImageUploads($lines);

      // Update invoice
      $success = $this->invoiceService->updateInvoice($id, $invoiceData, $lines);

      // Success response
      if ($this->request->isAJAX()) {
        return $this->response->setJSON([
          'success' => true,
          'message' => 'Invoice updated successfully',
          'redirect' => base_url("cash-invoices/{$id}")
        ]);
      }

      return redirect()->to("/cash-invoices/{$id}")
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
   * Delete Cash Invoice
   * 
   * DELETE /cash-invoices/{id}
   * 
   * @param int $id Invoice ID
   * @return ResponseInterface
   */
  public function delete(int $id)
  {
    // Check permission
    try {
      // Get invoice first to check its type
      $invoice = $this->invoiceService->getInvoiceById($id);

      if (!$invoice) {
        return $this->response->setStatusCode(404)->setJSON(['error' => 'Invoice not found']);
      }

      if ($invoice['invoice_type'] !== $this->invoiceType) {
        return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid invoice type']);
      }

      if (!can("invoices.cash.delete")) {
        return $this->response->setStatusCode(403)->setJSON([
          'error' => 'You do not have permission to delete this invoice'
        ]);
      }
      // Delete invoice
      $this->invoiceService->deleteInvoice($id);

      return $this->response->setJSON([
        'success' => true,
        'message' => 'Invoice deleted successfully',
        'redirect' => base_url('cash-invoices') // Redirect to Cash Invoices list
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
   * Show Cash Invoice details
   * 
   * GET /cash-invoices/{id}
   * 
   * @param int $id Invoice ID
   * @return string|ResponseInterface
   */
  public function show(int $id)
  {
    // Check permission
    try {
      // Get invoice with lines
      $invoice = $this->invoiceService->getInvoiceById($id);

      if (!$invoice) {
        return redirect()->to('/cash-invoices')->with('error', 'Invoice not found');
      }

      // Ensure it is a Cash Invoice
      if ($invoice['invoice_type'] !== $this->invoiceType) {
        return redirect()->to('/cash-invoices')->with('error', 'Invalid invoice type');
      }

      $this->gate('invoices.cash.view');

      // Load view
      $data = [
        'invoice' => $invoice,
      ];
      if ($this->permissions) {
        $data['action_flags'] = $this->permissions->getActionFlags('invoices', 'cash');
      }
      return $this->render('invoices/show', $data);
    } catch (\Exception $e) {
      log_message('error', 'Invoice show error: ' . $e->getMessage());
      return redirect()->to('/cash-invoices')->with('error', 'Failed to load invoice');
    }
  }

  /**
   * Parse line items from POST form data.
   * Maps form array notation to structured line data.
   * IMPORTANT: Preserves original form index as array key so file upload
   * indices stay in sync with line_images[N] form field names.
   */
  private function parseLinesFromPost(): array
  {
    $rawLines = $this->request->getPost('lines');

    if (empty($rawLines) || !is_array($rawLines)) {
      return [];
    }

    $lines = [];

    foreach ($rawLines as $index => $rawLine) {
      // Skip completely empty rows (no process AND no rate AND no existing image)
      if (empty($rawLine['processes']) && empty($rawLine['rate']) && empty($rawLine['existing_image'])) {
        continue;
      }

      $line = [
        'products'      => isset($rawLine['products']) ? (array)$rawLine['products'] : [],
        'product_name'  => $rawLine['product_name'] ?? null,
        'processes'     => isset($rawLine['processes']) ? (array)$rawLine['processes'] : [],
        'process_prices' => isset($rawLine['process_prices']) ? json_decode($rawLine['process_prices'], true) : [],
        'quantity'      => (int)($rawLine['quantity'] ?? 1),
        'weight'        => (float)($rawLine['weight'] ?? 0.000),
        'rate'          => (float)($rawLine['rate'] ?? 0.00),
        'amount'        => (float)($rawLine['amount'] ?? 0.00),
        'gold_weight'   => isset($rawLine['gold_weight']) ? (float)$rawLine['gold_weight'] : null,
        'gold_purity'   => $rawLine['gold_purity'] ?? null,
        'current_gold_price'     => isset($rawLine['current_gold_price']) ? (float)$rawLine['current_gold_price'] : null,
        'adjusted_gold_weight'   => isset($rawLine['adjusted_gold_weight']) ? (float)$rawLine['adjusted_gold_weight'] : null,
        'gold_adjustment_amount' => isset($rawLine['gold_adjustment_amount']) ? (float)$rawLine['gold_adjustment_amount'] : null,
        'image_path'    => $rawLine['existing_image'] ?? ($rawLine['image_path'] ?? null),
        'line_notes'    => $rawLine['line_notes'] ?? null,
      ];

      // Recalculate amount from quantity x rate if still zero
      if ($line['amount'] <= 0 && $line['rate'] > 0) {
        $weight = $line['weight'] ?? 0;
        if ($weight > 0) {
          $line['amount'] = round($weight * $line['rate'], 2);
        } else {
          $line['amount'] = round($line['quantity'] * $line['rate'], 2);
        }
      }

      // Use original form index as key to stay in sync with line_images[N]
      $lines[$index] = $line;
    }

    return $lines;
  }

  /**
   * Handle line image uploads from form submission.
   * Processes the line_images[N] file inputs, saves them, and updates the
   * corresponding line data with the saved image_path.
   * Lines array must use original form indices as keys (from parseLinesFromPost).
   */
  private function handleLineImageUploads(array $lines): array
  {
    // Get all files - line_images is an associative/indexed array of UploadedFile objects
    $rawFiles = $this->request->getFiles();
    $uploadedFiles = $rawFiles['line_images'] ?? [];

    // Also try getFileMultiple as fallback
    if (empty($uploadedFiles)) {
      $uploaded = $this->request->getFileMultiple('line_images');
      if (!empty($uploaded)) {
        $uploadedFiles = $uploaded;
      }
    }

    if (empty($uploadedFiles)) {
      // Re-index before returning (InvoiceService expects sequential array)
      return array_values($lines);
    }

    // Map uploaded files to line indices
    foreach ($uploadedFiles as $index => $file) {
      if (!$file instanceof \CodeIgniter\HTTP\Files\UploadedFile) {
        continue;
      }

      // Skip files with no upload (user left file input empty)
      if (!$file->isValid() || $file->getError() !== UPLOAD_ERR_OK) {
        continue;
      }

      if ($file->hasMoved()) {
        continue;
      }

      // Find the corresponding line by original form index
      if (isset($lines[$index])) {
        try {
          $fileName = $this->fileUploadService->uploadFile(
            $file,
            'uploads/invoice_images',
            ['jpg', 'jpeg', 'png', 'gif', 'webp']
          );
          $lines[$index]['image_path'] = 'uploads/invoice_images/' . $fileName;
          log_message('info', "Saved invoice line image at index {$index}: uploads/invoice_images/{$fileName}");
        } catch (\Exception $e) {
          log_message('warning', "Invoice line #{$index} image upload failed: " . $e->getMessage());
        }
      }
    }

    // Re-index to sequential array before passing to InvoiceService
    return array_values($lines);
  }
}

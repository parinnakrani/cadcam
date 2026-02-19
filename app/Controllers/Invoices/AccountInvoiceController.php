<?php

namespace App\Controllers\Invoices;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * AccountInvoiceController
 * 
 * Handles Account Invoice specific operations.
 * Extends InvoiceController with automatic filtering for Account Invoices.
 * 
 * Features:
 * - Automatic filter: invoice_type = 'Accounts Invoice'
 * - Pre-set invoice type in creation form
 * - Only shows Account customers in dropdowns
 * - All other functionality inherited from InvoiceController
 */
class AccountInvoiceController extends InvoiceController
{
  protected string $invoiceType = 'Accounts Invoice';
  protected string $customerType = 'Account';

  /**
   * List Account Invoices only
   * 
   * GET /account-invoices
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
        'invoice_type'   => $this->invoiceType, // Force Account Invoice type
        'payment_status' => $this->request->getGet('payment_status'),
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
      log_message('error', 'Account invoice listing error: ' . $e->getMessage());

      if ($this->request->isAJAX()) {
        return $this->response->setStatusCode(500)->setJSON([
          'error' => 'Failed to load invoices'
        ]);
      }

      return redirect()->back()->with('error', 'Failed to load invoices');
    }
  }

  /**
   * Show Account Invoice creation form
   * 
   * GET /account-invoices/create
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
      $companyId = session()->get('company_id');

      // Check for Account Selection
      $accountId = $this->request->getVar('account_id');
      $pendingChallans = [];

      if (!empty($accountId)) {
        // Fetch pending challans for this account
        $challanModel = new \App\Models\ChallanModel();
        $pendingChallans = $challanModel->where('account_id', $accountId)
          ->where('challan_status', 'Completed')
          ->where('invoice_generated', 0)
          ->where('is_deleted', 0)
          ->findAll();
      }

      // Load Selection View
      return view('invoices/account/select_challans', [
        'accounts' => $this->accountModel->where('company_id', $companyId)->where('is_deleted', 0)->orderBy('account_name', 'ASC')->findAll(),
        'selected_account_id' => $accountId,
        'challans' => $pendingChallans
      ]);
    } catch (\Exception $e) {
      log_message('error', 'Account invoice create form error: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Failed to load invoice creation form: ' . $e->getMessage());
    }
  }

  /**
   * Store new Account Invoice
   * 
   * POST /account-invoices
   * 
   * @return ResponseInterface
   */
  public function store()
  {
    // Force invoice type to Account Invoice
    $_POST['invoice_type'] = $this->invoiceType;

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
        'cash_customer_id'  => null,
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
          'message' => 'Account Invoice created successfully',
          'invoice_id' => $invoiceId,
          'redirect' => base_url("account-invoices/{$invoiceId}")
        ]);
      }

      return redirect()->to("/account-invoices/{$invoiceId}")
        ->with('success', 'Account Invoice created successfully');
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
   * Show Account Invoice edit form
   * 
   * GET /account-invoices/{id}/edit
   * 
   * @param int $id Invoice ID
   * @return string|ResponseInterface
   */
  public function edit(int $id)
  {
    // Check permission
    if (!can('invoice.edit')) {
      return redirect()->to('/account-invoices')->with('error', 'You do not have permission to edit invoices');
    }

    try {
      // Get invoice
      $invoice = $this->invoiceService->getInvoiceById($id);

      if (!$invoice) {
        return redirect()->to('/account-invoices')->with('error', 'Invoice not found');
      }

      // Ensure it is an Account Invoice
      if ($invoice['invoice_type'] !== $this->invoiceType) {
        return redirect()->to('/account-invoices')->with('error', 'Invalid invoice type');
      }

      // Check if paid
      if ($invoice['total_paid'] > 0) {
        return redirect()->to("/account-invoices/{$id}")
          ->with('error', 'Cannot edit invoice with payment history');
      }

      // Load dropdowns - only Account customers
      $companyId = session()->get('company_id');

      $data = [
        'invoice' => $invoice,
        'invoice_type' => $this->invoiceType,
        'customer_type' => $this->customerType,
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
      ];

      return view('invoices/edit', $data);
    } catch (\Exception $e) {
      log_message('error', 'Account invoice edit form error: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Failed to load invoice edit form');
    }
  }

  /**
   * Update Account Invoice
   * 
   * POST /account-invoices/{id}
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
          'redirect' => base_url("account-invoices/{$id}")
        ]);
      }

      return redirect()->to("/account-invoices/{$id}")
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
   * Delete Account Invoice
   * 
   * DELETE /account-invoices/{id}
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
        'redirect' => base_url('account-invoices') // Redirect to Account Invoices list
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
   * Store new Account Invoice directly from Challans (Bulk Action)
   * 
   * POST /account-invoices/store-from-challans
   */
  public function storeFromChallans()
  {
    if (!can('invoice.create')) {
      return redirect()->back()->with('error', 'You do not have permission to create invoices');
    }

    $accountId = $this->request->getPost('account_id');
    $challanIds = $this->request->getPost('challan_ids');

    if (empty($accountId) || empty($challanIds)) {
      return redirect()->back()->with('error', 'Please select an account and at least one challan.');
    }

    try {
      $companyId = session()->get('company_id');
      $account = $this->accountModel->getAccountWithDetails($accountId);

      if (!$account) {
        throw new \Exception('Account not found');
      }

      $challanModel = new \App\Models\ChallanModel();
      $challans = $challanModel->whereIn('id', $challanIds)->findAll();

      if (empty($challans)) {
        throw new \Exception('Selected challans not found');
      }

      // Aggregate Data
      $invoiceResult = $this->aggregateChallansForInvoice($challans, $account);

      // Create Invoice via Service
      $invoiceId = $this->invoiceService->createInvoice($invoiceResult['header'], $invoiceResult['lines']);

      return redirect()->to(base_url('invoices/' . $invoiceId))->with('success', 'Invoice created successfully.');
    } catch (\Exception $e) {
      log_message('error', 'Invoice creation from challans error: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Failed to create invoice: ' . $e->getMessage());
    }
  }

  /**
   * Aggregate Challan Data into Invoice Header and Lines
   */
  protected function aggregateChallansForInvoice(array $challans, array $account)
  {
    $lines = [];
    $grandTotal = 0;
    $subtotal = 0;
    $taxAmount = 0;
    // Tax components - Assuming simple aggregation if available or calculated later
    // The Service calculates tax based on lines.
    // But here lines are abstract "Challan Lines".
    // We need to pass lines with 'amount'. The service will apply tax rate.
    // We should use the Account/Company tax rate?
    // Or if challans already have tax, we might double tax if we are not careful.
    // PRD says: "Tax Calculation Logic ... The system uses tax-inclusive pricing... we need to back-calculate tax."
    // So if we pass 'amount' (which is sum of challan amounts), the service will treat it as tax-inclusive and back-calculate tax.
    // This is consistent.

    $challanModel = new \App\Models\ChallanModel();

    foreach ($challans as $challan) {
      $challanDetails = $challanModel->getChallanWithLines($challan['id']);

      if (!$challanDetails || empty($challanDetails['lines'])) {
        continue;
      }

      // Initialize Aggregates for this Challan (which becomes ONE invoice line)
      $agg = [
        'product_ids' => [],
        'process_ids' => [],
        'challan_line_ids' => [],
        'gold_purity' => [],
        'quantity' => 0,
        'weight' => 0,
        'rate' => 0,
        'amount' => 0,
        'gold_weight' => 0
      ];

      foreach ($challanDetails['lines'] as $line) {
        // Product IDs
        if (!empty($line['product_ids'])) {
          $pIds = is_array($line['product_ids']) ? $line['product_ids'] : json_decode($line['product_ids'], true);
          if (is_array($pIds)) {
            $agg['product_ids'] = array_merge($agg['product_ids'], $pIds);
          } elseif (is_numeric($pIds)) {
            $agg['product_ids'][] = $pIds;
          }
        }

        // Process IDs
        if (!empty($line['process_ids'])) {
          $procIds = is_array($line['process_ids']) ? $line['process_ids'] : json_decode($line['process_ids'], true);
          if (is_array($procIds)) {
            $agg['process_ids'] = array_merge($agg['process_ids'], $procIds);
          } elseif (is_numeric($procIds)) {
            $agg['process_ids'][] = $procIds;
          }
        }

        $agg['challan_line_ids'][] = $line['id'];

        if (!empty($line['gold_purity'])) {
          $agg['gold_purity'][] = $line['gold_purity'];
        }

        $agg['quantity'] += (int)$line['quantity'];
        $agg['weight'] += (float)$line['weight'];
        $agg['rate'] += (float)$line['rate'];
        $agg['amount'] += (float)$line['amount'];
        $agg['gold_weight'] += (float)($line['gold_weight'] ?? 0);
      }

      // Create One Invoice Line representing this Challan
      $sourceLineIds = implode(', ', $agg['challan_line_ids']);

      // Force JSON encoding and NULL for problematic optional field
      $lineData = [
        'source_challan_id' => (int)$challan['id'],
        'source_challan_line_id' => null,
        'product_ids' => json_encode(array_values(array_unique($agg['product_ids']))),
        'process_ids' => json_encode(array_values(array_unique($agg['process_ids']))),
        'quantity' => $agg['quantity'],
        'weight' => $agg['weight'],
        'rate' => $agg['rate'],
        'amount' => $agg['amount'],
        'gold_weight' => $agg['gold_weight'],
        'gold_purity' => implode(',', array_unique($agg['gold_purity'])),
        'line_notes' => "Consolidated from Challan " . $challan['challan_number'] . " (Lines: " . $sourceLineIds . ")",
      ];

      $lines[] = $lineData;
      log_message('error', 'Preparing Invoice Line: ' . json_encode($lineData));
    }

    // Prepare Header
    $billingAddr = ($account['billing_address_line1'] ?? '') . ' ' . ($account['billing_address_line2'] ?? '') . ', ' . ($account['billing_city'] ?? '') . ' - ' . ($account['billing_pincode'] ?? '');
    $shippingAddr = ($account['shipping_address_line1'] ?? '') . ' ' . ($account['shipping_address_line2'] ?? '') . ', ' . ($account['shipping_city'] ?? '') . ' - ' . ($account['shipping_pincode'] ?? '');

    // Get Default Tax Rate
    $taxRate = $this->taxService->getTaxRate(session()->get('company_id'));

    $header = [
      'company_id' => session()->get('company_id'),
      'invoice_type' => 'Accounts Invoice',
      'invoice_date' => date('Y-m-d'),
      'account_id' => $account['id'],
      'billing_address' => trim($billingAddr, ', - '),
      'shipping_address' => trim($shippingAddr, ', - '), // User asked for shipping address too
      'notes' => $account['notes'] ?? '',
      'payment_terms' => $account['payment_terms'] ?? '',
      'challan_ids' => array_column($challans, 'id'),
      'tax_rate' => $taxRate,
    ];

    return ['header' => $header, 'lines' => $lines];
  }

  /**
   * Show Account Invoice details
   * 
   * GET /account-invoices/{id}
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
        return redirect()->to('/account-invoices')->with('error', 'Invoice not found');
      }

      // Ensure it is an Account Invoice
      if ($invoice['invoice_type'] !== $this->invoiceType) {
        return redirect()->to('/account-invoices')->with('error', 'Invalid invoice type');
      }

      // Load view
      return view('invoices/show', [
        'invoice' => $invoice,
      ]);
    } catch (\Exception $e) {
      log_message('error', 'Invoice show error: ' . $e->getMessage());
      return redirect()->to('/account-invoices')->with('error', 'Failed to load invoice');
    }
  }
}

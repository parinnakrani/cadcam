<?php

namespace App\Controllers\Challans;

use App\Controllers\BaseController;
use App\Services\Challan\ChallanService;
use App\Services\Challan\ChallanCalculationService;
use App\Models\AccountModel;
use App\Models\CashCustomerModel;
use App\Models\ProductModel;
use App\Models\ProcessModel;
use App\Models\CompanyModel;
use App\Models\ChallanModel;
use App\Models\ChallanLineModel;
use App\Models\GoldRateModel;
use App\Services\Audit\AuditService;
use App\Services\FileUploadService;
use Exception;

/**
 * ChallanController
 *
 * Handles HTTP requests for challan (job order) management.
 * Supports all three challan types: Rhodium, Meena, Wax.
 *
 * Follows established patterns from AccountController.
 *
 * Permission checks: challan.view, challan.create, challan.edit, challan.delete
 *
 * Routes:
 * - GET  /challans                     → index()
 * - GET  /challans/create              → create()
 * - POST /challans                     → store()
 * - GET  /challans/{id}                → show($id)
 * - GET  /challans/{id}/edit           → edit($id)
 * - POST /challans/{id}                → update($id)
 * - DELETE /challans/{id}              → delete($id)
 * - POST /challans/{id}/add-line       → addLine($id)
 * - POST /challans/{id}/update-line/{lineId} → updateLine($id, $lineId)
 * - DELETE /challans/lines/{lineId}    → deleteLine($lineId)
 * - POST /challans/{id}/change-status  → changeStatus($id)
 * - GET  /challans/{id}/print          → print($id)
 * - GET  /challans/search              → search()
 */
class ChallanController extends BaseController
{
  protected ChallanService $challanService;
  protected ChallanCalculationService $calculationService;
  protected AccountModel $accountModel;
  protected CashCustomerModel $cashCustomerModel;
  protected ProductModel $productModel;
  protected ProcessModel $processModel;
  protected GoldRateModel $goldRateModel;
  protected FileUploadService $fileUploadService;

  public function __construct()
  {
    // Manual DI wrapper — matching existing controller patterns (AccountController, ProcessController)
    $challanModel     = new ChallanModel();
    $challanLineModel = new ChallanLineModel();
    $accountModel     = new AccountModel();
    $cashCustomerModel = new CashCustomerModel();
    $companyModel     = new CompanyModel();
    $processModel     = new ProcessModel();
    $productModel     = new ProductModel();
    $auditService     = new AuditService();
    $goldRateModel    = new GoldRateModel();

    $calculationService = new ChallanCalculationService(
      $processModel,
      $companyModel
    );

    $this->challanService = new ChallanService(
      $challanModel,
      $challanLineModel,
      $accountModel,
      $cashCustomerModel,
      $companyModel,
      $calculationService,
      $auditService
    );

    $this->calculationService = $calculationService;
    $this->accountModel       = $accountModel;
    $this->cashCustomerModel  = $cashCustomerModel;
    $this->productModel       = $productModel;
    $this->processModel       = $processModel;
    $this->goldRateModel      = $goldRateModel;
    $this->fileUploadService  = new FileUploadService();
  }

    // =========================================================================
    // LIST / INDEX
    // =========================================================================

  /**
   * List challans with optional filters.
   *
   * GET /challans
   * Query params: challan_type, challan_status, customer_type, from_date, to_date
   * AJAX: returns JSON; otherwise loads view.
   */
  public function index()
  {
    if (!can('challan.view')) {
      return redirect()->to('/dashboard')->with('error', 'Permission denied');
    }

    $filters = [
      'challan_type'  => $this->request->getGet('challan_type'),
      'status'        => $this->request->getGet('challan_status'),
      'customer_type' => $this->request->getGet('customer_type'),
      'date_from'     => $this->request->getGet('from_date'),
      'date_to'       => $this->request->getGet('to_date'),
      'account_id'    => $this->request->getGet('account_id'),
      'cash_customer_id' => $this->request->getGet('cash_customer_id'),
    ];

    // Remove empty/null filters
    $filters = array_filter($filters, fn($v) => $v !== null && $v !== '');

    if ($this->request->isAJAX()) {
      $challans = $this->challanService->getChallans($filters) ?? [];
      return $this->response->setJSON(['data' => $challans]);
    }

    $challans = $this->challanService->getChallans($filters);

    return view('challans/index', [
      'challans'    => $challans,
      'filters'     => $filters,
      'pageTitle'   => 'Challans',
    ]);
  }

    // =========================================================================
    // CREATE
    // =========================================================================

  /**
   * Show challan creation form.
   *
   * GET /challans/create?type=Rhodium
   */
  public function create()
  {
    if (!can('challan.create')) {
      return redirect()->back()->with('error', 'Permission denied');
    }

    // GET /challans/create?type=Rhodium
    $type = $this->request->getGet('type') ?? 'Rhodium';

    if (!in_array($type, ['Rhodium', 'Meena', 'Wax'])) {
      return redirect()->to('challans')->with('error', 'Invalid challan type.');
    }

    $accountModel      = new AccountModel();
    // Removed CashCustomerModel instantiation as it's no longer used in create view
    $productModel      = new ProductModel();
    $processModel      = new ProcessModel();

    // Get gold rates for all purities
    $companyId = session()->get('company_id');
    $goldRates = [];
    foreach (['24K', '22K', '18K', '14K'] as $purity) {
      $rate = $this->goldRateModel->getLatestRate((int)$companyId, $purity);
      if ($rate !== null) {
        $goldRates[$purity] = $rate;
      }
    }

    $data = [
      'challan_type'   => $type,
      'accounts'       => $accountModel->where('is_active', 1)->findAll(),
      'products'       => $productModel->where('is_active', 1)->findAll(),
      'processes'      => $processModel->where('process_type', $type)->where('is_active', 1)->findAll(),
      'default_tax_rate' => 0.00,
      'gold_rates'     => $goldRates,
      'pageTitle'      => "Create {$type} Challan",
    ];

    return view('challans/create', $data);
  }

  /**
   * Store a new challan.
   *
   * POST /challans
   */
  public function store()
  {
    if (!can('challan.create')) {
      return redirect()->back()->with('error', 'Permission denied');
    }

    // Controller-level validation for required fields
    if (!$this->validate([
      'challan_date'  => 'required|valid_date',
      'challan_type'  => 'required|in_list[Rhodium,Meena,Wax]',
      'customer_type' => 'required|in_list[Account,Cash]',
    ])) {
      return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }

    $data = $this->request->getPost();

    // Parse lines from form input
    $lines = $this->parseLinesFromPost();

    // Handle line image uploads
    $lines = $this->handleLineImageUploads($lines);

    if (!empty($lines)) {
      $data['lines'] = $lines;
    }

    try {
      $challanId = $this->challanService->createChallan($data);

      return redirect()
        ->to("/challans/{$challanId}")
        ->with('message', 'Challan created successfully.');
    } catch (Exception $e) {
      log_message('error', 'ChallanController::store - ' . $e->getMessage());
      return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
  }

    // =========================================================================
    // SHOW / VIEW
    // =========================================================================

  /**
   * View challan details with line items.
   *
   * GET /challans/{id}
   */
  public function show($id)
  {
    if (!can('challan.view')) {
      return redirect()->back()->with('error', 'Permission denied');
    }

    $challan = $this->challanService->getChallanWithLines($id);

    if (!$challan) {
      throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    return view('challans/show', [
      'challan'   => $challan,
      'pageTitle' => "Challan: {$challan['challan_number']}",
    ]);
  }

    // =========================================================================
    // EDIT / UPDATE
    // =========================================================================

  /**
   * Show challan edit form.
   *
   * GET /challans/{id}/edit
   */
  public function edit($id)
  {
    if (!can('challan.edit')) {
      return redirect()->back()->with('error', 'Permission denied');
    }

    $challan = $this->challanService->getChallanWithLines($id);

    if (!$challan) {
      throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    // Check if invoiced
    if (!empty($challan['invoice_generated']) && (int)$challan['invoice_generated'] === 1) {
      return redirect()
        ->to("/challans/{$id}")
        ->with('error', 'Cannot edit an invoiced challan.');
    }

    $challanType = $challan['challan_type'] ?? 'Rhodium';

    // Get gold rates for all purities
    $companyId = session()->get('company_id');
    $goldRates = [];
    foreach (['24K', '22K', '18K', '14K'] as $purity) {
      $rate = $this->goldRateModel->getLatestRate((int)$companyId, $purity);
      if ($rate !== null) {
        $goldRates[$purity] = $rate;
      }
    }

    return view('challans/edit', [
      'challan'        => $challan,
      'accounts'       => $this->accountModel->getActiveAccounts(),
      'cash_customers' => $this->cashCustomerModel->getActiveCashCustomers(),
      'products'       => $this->productModel->getActiveProducts(),
      'processes'      => $this->processModel->getActiveProcesses($challanType),
      'default_tax_rate' => 0.00,
      'gold_rates'     => $goldRates,
      'pageTitle'      => "Edit Challan: {$challan['challan_number']}",
    ]);
  }

  /**
   * Update an existing challan.
   *
   * POST /challans/{id}
   */
  public function update($id)
  {
    if (!can('challan.edit')) {
      return redirect()->back()->with('error', 'Permission denied');
    }

    $data = $this->request->getPost();

    // Parse lines from form
    $lines = $this->parseLinesFromPost();

    // Handle line image uploads
    $lines = $this->handleLineImageUploads($lines);

    try {
      // Update challan header
      $this->challanService->updateChallan($id, $data);

      // Replace lines if provided
      if (!empty($lines)) {
        $this->challanService->replaceLines($id, $lines);
      }

      return redirect()
        ->to("/challans/{$id}")
        ->with('message', 'Challan updated successfully.');
    } catch (Exception $e) {
      log_message('error', 'ChallanController::update - ' . $e->getMessage());
      return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
  }

    // =========================================================================
    // DELETE
    // =========================================================================

  /**
   * Soft delete a challan.
   *
   * DELETE /challans/{id}
   * Returns JSON (called via AJAX).
   */
  public function delete($id)
  {
    if (!can('challan.delete')) {
      return $this->response->setJSON([
        'status'  => 'error',
        'message' => 'Permission denied',
      ]);
    }

    try {
      $this->challanService->deleteChallan($id);

      session()->setFlashdata('message', 'Challan deleted successfully.');

      return $this->response->setJSON(['status' => 'success']);
    } catch (Exception $e) {
      log_message('error', 'ChallanController::delete - ' . $e->getMessage());
      return $this->response->setJSON([
        'status'  => 'error',
        'message' => $e->getMessage(),
      ]);
    }
  }

    // =========================================================================
    // LINE MANAGEMENT (AJAX)
    // =========================================================================

  /**
   * Add a single line item to a challan.
   *
   * POST /challans/{id}/add-line
   * Returns JSON with new line ID and updated totals.
   */
  public function addLine($id)
  {
    if (!can('challan.edit')) {
      return $this->error('Permission denied', 403);
    }

    $lineData = $this->request->getJSON(true) ?? $this->request->getPost();

    if (empty($lineData)) {
      return $this->error('No line data provided.', 400);
    }

    try {
      // Calculate line amounts if process_ids provided
      if (!empty($lineData['process_ids'])) {
        $calculated = $this->calculationService->calculateLineTotal($lineData);
        $lineData = array_merge($lineData, $calculated);
      }

      // Add line via service (validates challan, adds line, recalculates totals)
      $lineId = $this->challanService->addLine($id, $lineData);

      // Get updated totals
      $challan = $this->challanService->getChallanById($id);

      return $this->success('Line added successfully.', [
        'line_id' => $lineId,
        'totals'  => [
          'subtotal_amount' => $challan['subtotal_amount'] ?? 0,
          'tax_amount'      => $challan['tax_amount'] ?? 0,
          'total_amount'    => $challan['total_amount'] ?? 0,
          'total_weight'    => $challan['total_weight'] ?? 0,
        ],
      ]);
    } catch (Exception $e) {
      log_message('error', 'ChallanController::addLine - ' . $e->getMessage());
      return $this->error($e->getMessage(), 400);
    }
  }

  /**
   * Update an existing line item.
   *
   * POST /challans/{id}/update-line/{lineId}
   * Returns JSON with updated totals.
   */
  public function updateLine($id, $lineId)
  {
    if (!can('challan.edit')) {
      return $this->error('Permission denied', 403);
    }

    $lineData = $this->request->getJSON(true) ?? $this->request->getPost();

    if (empty($lineData)) {
      return $this->error('No line data provided.', 400);
    }

    try {
      // Recalculate line amounts if process_ids provided
      if (!empty($lineData['process_ids'])) {
        $calculated = $this->calculationService->calculateLineTotal($lineData);
        $lineData = array_merge($lineData, $calculated);
      }

      $this->challanService->updateLine($id, (int)$lineId, $lineData);

      // Get updated totals
      $challan = $this->challanService->getChallanById($id);

      return $this->success('Line updated successfully.', [
        'totals' => [
          'subtotal_amount' => $challan['subtotal_amount'] ?? 0,
          'tax_amount'      => $challan['tax_amount'] ?? 0,
          'total_amount'    => $challan['total_amount'] ?? 0,
          'total_weight'    => $challan['total_weight'] ?? 0,
        ],
      ]);
    } catch (Exception $e) {
      log_message('error', 'ChallanController::updateLine - ' . $e->getMessage());
      return $this->error($e->getMessage(), 400);
    }
  }

  /**
   * Delete a line item from a challan.
   *
   * DELETE /challans/lines/{lineId}
   * Returns JSON with updated totals.
   */
  public function deleteLine($lineId)
  {
    if (!can('challan.edit')) {
      return $this->error('Permission denied', 403);
    }

    try {
      // Find which challan this line belongs to
      $challanLineModel = new ChallanLineModel();
      $line = $challanLineModel->find($lineId);

      if (!$line) {
        return $this->error('Line not found.', 404);
      }

      $challanId = (int)$line['challan_id'];

      // Delete via service (validates, deletes, resequences, recalculates)
      $this->challanService->deleteLine($challanId, (int)$lineId);

      // Get updated totals
      $challan = $this->challanService->getChallanById($challanId);

      return $this->success('Line deleted successfully.', [
        'totals' => [
          'subtotal_amount' => $challan['subtotal_amount'] ?? 0,
          'tax_amount'      => $challan['tax_amount'] ?? 0,
          'total_amount'    => $challan['total_amount'] ?? 0,
          'total_weight'    => $challan['total_weight'] ?? 0,
        ],
      ]);
    } catch (Exception $e) {
      log_message('error', 'ChallanController::deleteLine - ' . $e->getMessage());
      return $this->error($e->getMessage(), 400);
    }
  }

    // =========================================================================
    // STATUS MANAGEMENT (AJAX)
    // =========================================================================

  /**
   * Change challan status with workflow enforcement.
   *
   * POST /challans/{id}/change-status
   * Request body: { "new_status": "Pending" }
   * Returns JSON.
   */
  public function changeStatus($id)
  {
    if (!can('challan.edit')) {
      return $this->error('Permission denied', 403);
    }

    $newStatus = $this->request->getJSON(true)['new_status']
      ?? $this->request->getPost('new_status')
      ?? null;

    if (empty($newStatus)) {
      return $this->error('New status is required.', 400);
    }

    try {
      $this->challanService->updateChallanStatus($id, $newStatus);

      return $this->success("Status changed to {$newStatus}.", [
        'new_status' => $newStatus,
      ]);
    } catch (Exception $e) {
      log_message('error', 'ChallanController::changeStatus - ' . $e->getMessage());
      return $this->error($e->getMessage(), 400);
    }
  }

    // =========================================================================
    // PRINT
    // =========================================================================

  /**
   * Print a challan (PDF or print-friendly view).
   *
   * GET /challans/{id}/print
   */
  public function print($id)
  {
    if (!can('challan.view')) {
      return redirect()->back()->with('error', 'Permission denied');
    }

    $challan = $this->challanService->getChallanWithLines($id);

    if (!$challan) {
      throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    // Render print-friendly view (no sidebar/header chrome)
    // PDF generation can be added later via a library like Dompdf
    return view('challans/print', [
      'challan'   => $challan,
      'pageTitle' => "Print Challan: {$challan['challan_number']}",
    ]);
  }

    // =========================================================================
    // SEARCH (AJAX)
    // =========================================================================

  /**
   * Search challans by number or notes.
   *
   * GET /challans/search?q=...
   * Returns JSON array.
   */
  public function search()
  {
    if (!can('challan.view')) {
      return $this->response->setJSON([]);
    }

    $query = $this->request->getGet('q');
    $results = $this->challanService->searchChallans((string)$query);

    return $this->response->setJSON($results);
  }

    // =========================================================================
    // API: CALCULATE LINE (AJAX)
    // =========================================================================

  /**
   * Calculate line amounts without saving.
   *
   * POST /challans/calculate-line
   * Used by the front-end to preview amounts before adding a line.
   * Returns JSON with calculated amounts.
   */
  public function calculateLine()
  {
    if (!can('challan.create')) {
      return $this->error('Permission denied', 403);
    }

    $lineData = $this->request->getJSON(true) ?? $this->request->getPost();

    if (empty($lineData)) {
      return $this->error('No line data provided.', 400);
    }

    try {
      // Validate line data
      $this->calculationService->validateLineData($lineData);

      // Calculate
      $result = $this->calculationService->calculateLineTotal($lineData);

      return $this->success('Calculation complete.', $result);
    } catch (Exception $e) {
      return $this->error($e->getMessage(), 400);
    }
  }

    // =========================================================================
    // API: GET PROCESSES BY TYPE (AJAX)
    // =========================================================================

  /**
   * Get active processes filtered by challan type.
   *
   * GET /challans/processes?type=Rhodium
   * Used when user changes challan type in the form.
   */
  public function getProcessesByType()
  {
    if (!can('challan.view')) {
      return $this->response->setJSON([]);
    }

    $type = $this->request->getGet('type');

    if (empty($type)) {
      return $this->response->setJSON([]);
    }

    $processes = $this->processModel->getActiveProcesses($type);

    return $this->response->setJSON($processes);
  }

    // =========================================================================
    // HELPERS
    // =========================================================================

  /**
   * Parse line items from POST form data.
   *
   * Expects form fields in array notation:
   *   lines[0][product_ids] = [1, 2]
   *   lines[0][process_ids] = [3, 4]
   *   lines[0][quantity] = 5
   *   lines[0][rate] = 100.00
   *   etc.
   *
   * @return array Parsed and cleaned line data
   */
  private function parseLinesFromPost(): array
  {
    $rawLines = $this->request->getPost('lines');

    if (empty($rawLines) || !is_array($rawLines)) {
      return [];
    }

    $lines = [];

    foreach ($rawLines as $index => $rawLine) {
      // Skip empty rows (user added but didn't fill)
      if (empty($rawLine['process_ids']) && empty($rawLine['rate'])) {
        continue;
      }

      $line = [
        'product_ids'   => $this->parseJsonOrArray($rawLine['product_ids'] ?? null),
        'product_name'  => $rawLine['product_name'] ?? null,
        'process_ids'   => $this->parseJsonOrArray($rawLine['process_ids'] ?? null),
        'process_prices' => isset($rawLine['process_prices']) ? json_decode($rawLine['process_prices'], true) : [],
        'quantity'      => (int)($rawLine['quantity'] ?? 1),
        'weight'        => (float)($rawLine['weight'] ?? 0.000),
        'rate'          => (float)($rawLine['rate'] ?? 0.00),
        'amount'        => (float)($rawLine['amount'] ?? 0.00),
        'gold_weight'   => isset($rawLine['gold_weight']) ? (float)$rawLine['gold_weight'] : null,
        'gold_purity'   => $rawLine['gold_purity'] ?? null,
        'current_gold_price' => isset($rawLine['current_gold_price']) ? (float)$rawLine['current_gold_price'] : null,
        'adjusted_gold_weight' => isset($rawLine['adjusted_gold_weight']) ? (float)$rawLine['adjusted_gold_weight'] : null,
        'gold_adjustment_amount' => isset($rawLine['gold_adjustment_amount']) ? (float)$rawLine['gold_adjustment_amount'] : null,
        'image_path'    => $rawLine['existing_image'] ?? ($rawLine['image_path'] ?? null),
        'line_notes'    => $rawLine['line_notes'] ?? null,
      ];

      // If amount not pre-calculated, calculate via service
      if ($line['amount'] <= 0 && !empty($line['process_ids'])) {
        try {
          $calculated = $this->calculationService->calculateLineTotal($line);
          $line = array_merge($line, $calculated);
        } catch (Exception $e) {
          log_message('warning', "Line #{$index}: calculation failed - " . $e->getMessage());
          // Skip this line or use zero amounts
        }
      }

      // Recalculate amount from quantity × rate if still zero
      if ($line['amount'] <= 0 && $line['rate'] > 0) {
        $line['amount'] = round($line['quantity'] * $line['rate'], 2);
      }

      $lines[] = $line;
    }

    return $lines;
  }

  /**
   * Parse a value that could be a JSON string or already an array.
   *
   * Form fields may submit JSON strings or PHP array notation.
   *
   * @param mixed $value
   * @return array
   */
  private function parseJsonOrArray($value): array
  {
    if (is_array($value)) {
      return array_filter($value, fn($v) => $v !== '' && $v !== null);
    }

    if (is_string($value) && !empty($value)) {
      $decoded = json_decode($value, true);
      if (is_array($decoded)) {
        return $decoded;
      }
    }

    return [];
  }

  /**
   * Handle line image uploads from form submission.
   *
   * Processes the line_images[] file inputs and stores them,
   * updating the corresponding line data with the saved image_path.
   *
   * @param array $lines Parsed line data
   * @return array Updated line data with image paths
   */
  private function handleLineImageUploads(array $lines): array
  {
    $uploadedFiles = $this->request->getFileMultiple('line_images');

    if (empty($uploadedFiles)) {
      // Try indexed approach: line_images[0], line_images[1], etc.
      $rawFiles = $this->request->getFiles();
      if (isset($rawFiles['line_images']) && is_array($rawFiles['line_images'])) {
        $uploadedFiles = $rawFiles['line_images'];
      }
    }

    if (empty($uploadedFiles)) {
      return $lines;
    }

    // Map uploaded files to line indices
    foreach ($uploadedFiles as $index => $file) {
      if (!$file instanceof \CodeIgniter\HTTP\Files\UploadedFile) {
        continue;
      }

      if (!$file->isValid() || $file->hasMoved()) {
        continue;
      }

      // Find the corresponding line by index
      if (isset($lines[$index])) {
        try {
          $fileName = $this->fileUploadService->uploadFile(
            $file,
            'uploads/challan_images',
            ['jpg', 'jpeg', 'png', 'gif', 'webp']
          );
          $lines[$index]['image_path'] = 'uploads/challan_images/' . $fileName;
        } catch (Exception $e) {
          log_message('warning', "Line #{$index} image upload failed: " . $e->getMessage());
        }
      }
    }

    return $lines;
  }
}

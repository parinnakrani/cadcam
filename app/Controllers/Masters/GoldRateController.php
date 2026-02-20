<?php

namespace App\Controllers\Masters;

use App\Controllers\BaseController;
use App\Services\Master\GoldRateService;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * GoldRateController
 *
 * Controller for managing daily gold rates.
 */
class GoldRateController extends BaseController
{
  protected $goldRateService;

  public function __construct()
  {
    $this->goldRateService = new GoldRateService();
  }

  /**
   * Display rate history and today's status.
   *
   * @return string
   */
  public function index(): string
  {
    if (!$this->hasPermission('masters.manage')) {
      throw new PageNotFoundException("Access Denied");
    }

    // Check if today's rate is entered for standard 22K/24K
    // Usually, we check major metal types.
    $isEntered22K = $this->goldRateService->checkIfTodayRateEntered('22K');
    $isEntered24K = $this->goldRateService->checkIfTodayRateEntered('24K');
    $isEnteredSilver = $this->goldRateService->checkIfTodayRateEntered('Silver');

    $alertMessage = [];
    if (!$isEntered22K) $alertMessage[] = "22K Gold Rate not entered for today.";
    if (!$isEntered24K) $alertMessage[] = "24K Gold Rate not entered for today.";
    if (!$isEnteredSilver) $alertMessage[] = "Silver Rate not entered for today.";

    // Get recent history (last 30 days)
    $today = date('Y-m-d');
    $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
    $history = $this->goldRateService->getRateHistory($thirtyDaysAgo, $today);

    $data = [
      'title'        => 'Gold Rates',
      'alerts'       => $alertMessage,
      'history'      => $history,
      'today'        => $today,
      'isEntered22K' => $isEntered22K,
      'isEntered24K' => $isEntered24K,
      'isEnteredSilver' => $isEnteredSilver
    ];

    return view('GoldRates/index', $data);
  }

  /**
   * Show form to create a new rate.
   *
   * @return string
   */
  public function create(): string
  {
    if (!$this->hasPermission('masters.manage')) {
      throw new PageNotFoundException("Access Denied");
    }

    // Read ?metal= query param to pre-select the dropdown
    // Whitelist to prevent any injection
    $metalParam = $this->request->getGet('metal');
    $validMetals = ['22K', '24K', 'Silver'];
    $selectedMetal = in_array($metalParam, $validMetals, true) ? $metalParam : '22K';

    $data = [
      'title'         => 'Enter Gold Rate',
      'today'         => date('Y-m-d'),
      'selectedMetal' => $selectedMetal,
    ];

    return view('GoldRates/create', $data);
  }

  /**
   * Store a new gold rate.
   *
   * @return \CodeIgniter\HTTP\RedirectResponse
   */
  public function store()
  {
    if (!$this->hasPermission('masters.manage')) {
      return redirect()->back()->with('error', 'Access Denied');
    }

    $rules = [
      'rate_date'     => 'required|valid_date',
      'metal_type'    => 'required|in_list[22K,24K,Silver]',
      'rate_per_gram' => 'required|decimal|greater_than[0]',
    ];

    if (!$this->validate($rules)) {
      return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }

    try {
      $data = $this->request->getPost();
      $this->goldRateService->createRate($data);

      return redirect()->to('masters/gold-rates')->with('message', 'Gold rate entered successfully.');
    } catch (\Exception $e) {
      return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
  }

  /**
   * Show form to edit an existing rate.
   *
   * @param int $id
   * @return string
   */
  public function edit(int $id): string
  {
    if (!$this->hasPermission('masters.manage')) {
      throw new PageNotFoundException("Access Denied");
    }

    // We need to fetch the rate data manually or via model through service?
    // Service doesn't have getRateById extended. 
    // Ideally service should handle retrieval, but for edit form usually we fetch logic.
    // Let's use modeling logic via service or direct model if service permits.
    // Service has updateRate($id, $data).
    // I will add a method to Service to get rate by ID for safety or assume accessing model is okay for read.
    // But Rule 1 says "Only use methods that exist".
    // I'll instantiate model directly here for read, or better, stick to Service pattern.
    // I'll use a direct model instance for reading single record if Service lacks it, 
    // OR I can quickly add getRateById to Service? 
    // I should stick to what's defined or standard. I'll read via Model for strictly view purpose.

    $model = new \App\Models\GoldRateModel(); // Helper usage
    $rate = $model->find($id);

    if (!$rate) {
      throw new PageNotFoundException("Rate not found: $id");
    }

    // Ensure company isolation
    if ($rate['company_id'] != session()->get('company_id')) {
      throw new PageNotFoundException("Access Denied");
    }

    $data = [
      'title' => 'Edit Gold Rate',
      'rate'  => $rate
    ];

    return view('GoldRates/edit', $data);
  }

  /**
   * Update an existing rate.
   *
   * @param int $id
   * @return \CodeIgniter\HTTP\RedirectResponse
   */
  public function update(int $id)
  {
    if (!$this->hasPermission('masters.manage')) {
      return redirect()->back()->with('error', 'Access Denied');
    }

    $rules = [
      'rate_per_gram' => 'required|decimal|greater_than[0]',
      // Date and metal type usually shouldn't change, but validation if they do:
      'rate_date'     => 'permit_empty|valid_date',
      'metal_type'    => 'permit_empty|in_list[22K,24K,Silver]',
    ];

    if (!$this->validate($rules)) {
      return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }

    try {
      $data = $this->request->getPost();
      $this->goldRateService->updateRate($id, $data);

      return redirect()->to('masters/gold-rates')->with('message', 'Gold rate updated successfully.');
    } catch (\Exception $e) {
      return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
  }

  /**
   * View rate history with charts.
   *
   * @return string
   */
  public function history(): string
  {
    if (!$this->hasPermission('masters.manage')) {
      throw new PageNotFoundException("Access Denied");
    }

    $fromDate = $this->request->getGet('from_date') ?? date('Y-m-d', strtotime('-30 days'));
    $toDate = $this->request->getGet('to_date') ?? date('Y-m-d');

    $history = $this->goldRateService->getRateHistory($fromDate, $toDate);

    // Prepare chart data grouping by metal type
    $chartData = [
      '22K' => [],
      '24K' => [],
      'Silver' => []
    ];

    // Populate chart data (dates as keys or parallel arrays)
    foreach ($history as $row) {
      $metal = $row['metal_type'];
      if (isset($chartData[$metal])) {
        $chartData[$metal][] = [
          'date' => $row['rate_date'],
          'rate' => (float)$row['rate_per_gram']
        ];
      }
    }

    $data = [
      'title'     => 'Gold Rate History',
      'history'   => $history,
      'fromDate'  => $fromDate,
      'toDate'    => $toDate,
      'chartData' => $chartData
    ];

    return view('GoldRates/history', $data); // Using separate history view as requested
  }
}

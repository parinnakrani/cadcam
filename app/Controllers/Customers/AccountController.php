<?php

namespace App\Controllers\Customers;

use App\Controllers\BaseController;
use App\Services\Customer\AccountService;
use App\Models\StateModel;
use App\Services\Auth\PermissionService;

class AccountController extends BaseController
{
  protected $accountService;
  protected $stateModel;
  protected $permissionService;

  public function __construct()
  {
    // Inject Dependencies
    // In CI4 simple autowiring via Factories or manual instantiation
    // Assuming global helper or manual new() for now if DI container not fully set up.
    // But prompt says "inject in __construct".
    // CodeIgniter Controllers constructed by framework.
    // We usually instantiate services in construct or use service() helper.
    // I will use service() helper pattern or new() inside construct to simulate injection if DI not configured.
    // Best practice: Use `Config\Services` or Factories.
    // I'll instantiate them manually here as per typical CI4 usage without PHP-DI.

    $this->accountService = new AccountService(
      new \App\Models\AccountModel(),
      new StateModel(),
      new \App\Services\Ledger\LedgerService(),
      new \App\Services\Validation\ValidationService(),
      new \App\Services\Audit\AuditService()
    );

    $this->stateModel = new StateModel();
    // PermissionService usually via Auth helper?
    // Using 'can()' helper in requirements.
    // I'll assume PermissionService is available or just use 'can()' helper if defined.
  }

  public function index()
  {
    if (!can('account.view')) { // Assuming has_permission helper
      return redirect()->to('/dashboard')->with('error', 'Permission denied');
    }

    if ($this->request->isAJAX()) {
      $filters = [
        'is_active' => $this->request->getGet('is_active'), // Can be '0', '1', or null
        'search' => $this->request->getGet('search')['value'] ?? null, // DataTables search
      ];

      // If DataTables sends column specific search? Usually global search is easier for now.

      $accounts = $this->accountService->getAccounts($filters);

      return $this->response->setJSON(['data' => $accounts]);
    }

    return view('customers/accounts/index', [
      'states' => $this->stateModel->where('is_active', 1)->findAll()
    ]);
  }

  public function create()
  {
    if (!can('account.create')) {
      return redirect()->back()->with('error', 'Permission denied');
    }

    return view('customers/accounts/create', [
      'states' => $this->stateModel->where('is_active', 1)->findAll()
    ]);
  }

  public function store()
  {
    if (!can('account.create')) {
      return redirect()->back()->with('error', 'Permission denied');
    }

    if (!$this->validate([
      'account_name' => 'required|min_length[3]',
      'mobile'       => 'required|regex_match[/^[0-9]{10}$/]',
      // Add more validations if needed here or rely on Service
    ])) {
      return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }

    // CSRF handled by CI4 filters usually.

    $data = $this->request->getPost();

    // Handle Checkboxes
    $data['is_active'] = $this->request->getPost('is_active') ? 1 : 0;
    $data['same_as_billing'] = $this->request->getPost('same_as_billing') ? 1 : 0;

    try {
      $this->accountService->createAccount($data);
      return redirect()->to('/customers/accounts')->with('message', 'Account created successfully');
    } catch (\Exception $e) {
      return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
  }

  public function show($id)
  {
    if (!can('account.view')) {
      return redirect()->back()->with('error', 'Permission denied');
    }

    $account = $this->accountService->getAccountById($id);
    if (!$account) {
      throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    $balance = $this->accountService->getLedgerBalance($id);

    return view('customers/accounts/show', [
      'account' => $account,
      'balance' => $balance
    ]);
  }

  public function edit($id)
  {
    if (!can('account.edit')) {
      return redirect()->back()->with('error', 'Permission denied');
    }

    $account = $this->accountService->getAccountById($id);
    if (!$account) {
      throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    return view('customers/accounts/edit', [
      'account' => $account,
      'states'  => $this->stateModel->where('is_active', 1)->findAll()
    ]);
  }

  public function update($id)
  {
    if (!can('account.edit')) {
      return redirect()->back()->with('error', 'Permission denied');
    }

    $data = $this->request->getPost();
    $data['is_active'] = $this->request->getPost('is_active') ? 1 : 0;
    $data['same_as_billing'] = $this->request->getPost('same_as_billing') ? 1 : 0;

    try {
      $this->accountService->updateAccount($id, $data);
      return redirect()->to('/customers/accounts')->with('message', 'Account updated successfully');
    } catch (\Exception $e) {
      return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
  }

  public function delete($id)
  {
    if (!can('account.delete')) {
      return $this->response->setJSON(['status' => 'error', 'message' => 'Permission denied']);
    }

    try {
      $this->accountService->deleteAccount($id);
      session()->setFlashdata('message', 'Account deleted successfully');
      return $this->response->setJSON(['status' => 'success']);
    } catch (\Exception $e) {
      return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
    }
  }

  public function search()
  {
    if (!can('account.view')) {
      return $this->response->setJSON([]);
    }

    $query = $this->request->getGet('q');
    $results = $this->accountService->searchAccounts((string)$query);

    return $this->response->setJSON($results);
  }

  public function ledger($id)
  {
    if (!can('account.view')) {
      return redirect()->back()->with('error', 'Permission denied');
    }

    return redirect()->to('/reports/ledger/account/' . $id);
  }
}

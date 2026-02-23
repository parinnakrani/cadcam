<?php

namespace App\Controllers\Customers;

use App\Controllers\BaseController;
use App\Services\Customer\CashCustomerService;
use App\Models\StateModel;
use App\Services\Auth\PermissionService;

class CashCustomerController extends BaseController
{
  protected $cashCustomerService;
  protected $stateModel;

  public function __construct()
  {
    // Inject Dependencies
    $this->cashCustomerService = new CashCustomerService(
      new \App\Models\CashCustomerModel(),
      new StateModel(),
      new \App\Services\Validation\ValidationService(),
      new \App\Services\Audit\AuditService()
    );

    $this->stateModel = new StateModel();
  }

  public function index()
  {
    $this->gate('customers.cash_customers.all.list');

    if ($this->request->isAJAX()) {
      $customers = $this->cashCustomerService->getActiveCashCustomers();
      return $this->response->setJSON(['data' => $customers]);
    }

    $data = [
      'states' => $this->stateModel->where('is_active', 1)->findAll()
    ];

    if ($this->permissions) {
      $data['action_flags'] = $this->permissions->getActionFlags('customers', 'cash_customers.all');
    }

    return $this->render('customers/cash_customers/index', $data);
  }

  public function create()
  {
    $this->gate('customers.cash_customers.all.create');

    return $this->render('customers/cash_customers/create', [
      'states' => $this->stateModel->where('is_active', 1)->findAll()
    ]);
  }

  public function store()
  {
    $this->gate('customers.cash_customers.all.create');

    if (!$this->validate([
      'customer_name' => 'required|min_length[3]',
      'mobile'        => 'required|regex_match[/^[0-9]{10}$/]',
    ])) {
      return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }

    // CSRF Auto-checked

    $data = $this->request->getPost();
    $data['is_active'] = $this->request->getPost('is_active') ? 1 : 0;

    try {
      $this->cashCustomerService->createCashCustomer($data);
      return redirect()->to('/customers/cash-customers')->with('message', 'Cash customer created successfully');
    } catch (\Exception $e) {
      // Check duplicate message
      if (strpos($e->getMessage(), 'already exists') !== false) {
        return redirect()->back()->withInput()->with('error', $e->getMessage());
      }
      return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
  }

  public function findOrCreate()
  {
    if (!can('customers.cash_customers.all.create') && !can('invoices.cash.create') && !can('challans.all.create')) {
      // Find or create can be called from invoice/challan pages too
      return $this->response->setJSON(['success' => false, 'message' => 'Permission denied']);
    }

    $name = $this->request->getPost('customer_name');
    $mobile = $this->request->getPost('mobile_number');

    if (empty($name) || empty($mobile)) {
      return $this->response->setJSON(['success' => false, 'message' => 'Name and Mobile required']);
    }

    try {
      $customerId = $this->cashCustomerService->findOrCreate($name, $mobile);
      return $this->response->setJSON(['success' => true, 'customer_id' => $customerId, 'message' => 'Customer added']);
    } catch (\Exception $e) {
      return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
    }
  }

  public function show($id)
  {
    $this->gate('customers.cash_customers.all.view');

    $customer = $this->cashCustomerService->getCashCustomerById($id);
    if (!$customer) {
      throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    $stateName = '';
    if (!empty($customer['state_id'])) {
      $state = $this->stateModel->find($customer['state_id']);
      $stateName = $state['state_name'] ?? '';
    }

    return $this->render('customers/cash_customers/show', [
      'customer' => $customer,
      'state_name' => $stateName
    ]);
  }

  public function edit($id)
  {
    $this->gate('customers.cash_customers.all.edit');

    $customer = $this->cashCustomerService->getCashCustomerById($id);
    if (!$customer) {
      throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    return $this->render('customers/cash_customers/edit', [
      'customer' => $customer,
      'states'   => $this->stateModel->where('is_active', 1)->findAll()
    ]);
  }

  public function update($id)
  {
    $this->gate('customers.cash_customers.all.edit');

    $data = $this->request->getPost();
    $data['is_active'] = $this->request->getPost('is_active') ? 1 : 0;

    try {
      $this->cashCustomerService->updateCashCustomer($id, $data);
      return redirect()->to('/customers/cash-customers')->with('message', 'Cash customer updated successfully');
    } catch (\Exception $e) {
      return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
  }

  public function delete($id)
  {
    if (!can('customers.cash_customers.all.delete')) {
      return $this->response->setJSON(['status' => 'error', 'message' => 'Permission denied']);
    }

    try {
      $this->cashCustomerService->deleteCashCustomer($id);
      session()->setFlashdata('message', 'Cash customer deleted successfully');
      return $this->response->setJSON(['status' => 'success']);
    } catch (\Exception $e) {
      return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
    }
  }

  public function search()
  {
    if (!can('customers.cash_customers.all.view')) {
      return $this->response->setJSON([]);
    }

    $query = $this->request->getGet('q');
    $results = $this->cashCustomerService->searchCashCustomers((string)$query);

    return $this->response->setJSON($results);
  }
}

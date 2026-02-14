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
    if (!has_permission('cash_customer.view')) {
      return redirect()->to('/dashboard')->with('error', 'Permission denied');
    }

    if ($this->request->isAJAX()) {
      $customers = $this->cashCustomerService->getActiveCashCustomers();
      return $this->response->setJSON(['data' => $customers]);
    }

    return view('customers/cash_customers/index', [
      'states' => $this->stateModel->where('is_active', 1)->findAll()
    ]);
  }

  public function create()
  {
    if (!has_permission('cash_customer.create')) {
      return redirect()->back()->with('error', 'Permission denied');
    }

    return view('customers/cash_customers/create', [
      'states' => $this->stateModel->where('is_active', 1)->findAll()
    ]);
  }

  public function store()
  {
    if (!has_permission('cash_customer.create')) {
      return redirect()->back()->with('error', 'Permission denied');
    }

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
    if (!has_permission('cash_customer.create')) {
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
    if (!has_permission('cash_customer.view')) {
      return redirect()->back()->with('error', 'Permission denied');
    }

    $customer = $this->cashCustomerService->getCashCustomerById($id);
    if (!$customer) {
      throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    $stateName = '';
    if (!empty($customer['state_id'])) {
      $state = $this->stateModel->find($customer['state_id']);
      $stateName = $state['state_name'] ?? '';
    }

    return view('customers/cash_customers/show', [
      'customer' => $customer,
      'state_name' => $stateName
    ]);
  }

  public function edit($id)
  {
    if (!has_permission('cash_customer.edit')) {
      return redirect()->back()->with('error', 'Permission denied');
    }

    $customer = $this->cashCustomerService->getCashCustomerById($id);
    if (!$customer) {
      throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    return view('customers/cash_customers/edit', [
      'customer' => $customer,
      'states'   => $this->stateModel->where('is_active', 1)->findAll()
    ]);
  }

  public function update($id)
  {
    if (!has_permission('cash_customer.edit')) {
      return redirect()->back()->with('error', 'Permission denied');
    }

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
    if (!has_permission('cash_customer.delete')) {
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
    if (!has_permission('cash_customer.view')) {
      return $this->response->setJSON([]);
    }

    $query = $this->request->getGet('q');
    $results = $this->cashCustomerService->searchCashCustomers((string)$query);

    return $this->response->setJSON($results);
  }
}

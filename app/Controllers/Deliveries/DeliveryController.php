<?php

namespace App\Controllers\Deliveries;

use App\Controllers\BaseController;
use App\Services\Delivery\DeliveryService;
use App\Models\DeliveryModel;
use App\Models\InvoiceModel;
use CodeIgniter\API\ResponseTrait;

class DeliveryController extends BaseController
{
  use ResponseTrait;

  protected $deliveryService;
  protected $deliveryModel;
  protected $invoiceModel;
  protected $db;

  public function __construct()
  {
    $this->deliveryService = new DeliveryService();
    $this->deliveryModel = new DeliveryModel();
    $this->invoiceModel = new InvoiceModel();
    $this->db = \Config\Database::connect();
  }

  /**
   * List all deliveries (Admin)
   */
  public function index()
  {
    if (!has_permission('deliveries.view')) {
      return redirect()->back()->with('error', 'Permission denied');
    }

    if ($this->request->isAJAX()) {
      $status = $this->request->getGet('status');
      $start = $this->request->getGet('start_date');
      $end = $this->request->getGet('end_date');

      $builder = $this->deliveryModel->builder()
        ->select('deliveries.*, invoices.invoice_number, users.full_name as assigned_to_name')
        ->join('invoices', 'invoices.id = deliveries.invoice_id')
        ->join('users', 'users.id = deliveries.assigned_to')
        ->where('deliveries.company_id', session()->get('company_id'))
        ->where('deliveries.is_deleted', 0);

      if ($status) {
        $builder->where('deliveries.delivery_status', $status);
      }
      if ($start && $end) {
        $builder->where('deliveries.expected_delivery_date >=', $start)
          ->where('deliveries.expected_delivery_date <=', $end);
      }

      return $this->response->setJSON(['data' => $builder->get()->getResultArray()]);
    }

    return view('deliveries/index', [
      'title' => 'Deliveries'
    ]);
  }

  /**
   * List my deliveries (Delivery Personnel)
   */
  public function myDeliveries()
  {
    // Simple permission check implies role
    // Or check specifically

    $userId = session()->get('user_id');
    $dashboard = $this->deliveryService->getMyDashboard($userId);

    // Fetch active deliveries for display
    $activeDeliveries = $this->deliveryModel->getMyDeliveries($userId);

    // Fetch history (Delivered/Failed)
    $history = $this->deliveryModel->where('assigned_to', $userId)
      ->whereIn('delivery_status', ['Delivered', 'Failed'])
      ->orderBy('updated_at', 'DESC')
      ->limit(50)
      ->findAll();

    return view('deliveries/my_deliveries', [
      'title' => 'My Deliveries',
      'dashboard' => $dashboard,
      'active' => $activeDeliveries,
      'history' => $history
    ]);
  }

  /**
   * Show create assignment form
   */
  public function create()
  {
    if (!has_permission('deliveries.manage')) {
      return redirect()->back()->with('error', 'Permission denied');
    }

    $companyId = session()->get('company_id');

    // Get deliverable invoices
    $invoices = $this->deliveryService->getDeliverableInvoices($companyId);

    // Get delivery personnel
    $deliveryUsers = $this->db->table('users')
      ->select('users.id, users.full_name')
      ->join('user_roles', 'user_roles.user_id = users.id')
      ->join('roles', 'roles.id = user_roles.role_id')
      ->where('roles.role_name', 'Delivery Personnel') // Ensure exact Role Name match from Seed
      ->where('users.company_id', $companyId)
      ->where('users.is_deleted', 0)
      ->get()->getResultArray();

    return view('deliveries/create', [
      'title' => 'Assign Delivery',
      'invoices' => $invoices,
      'users' => $deliveryUsers
    ]);
  }

  /**
   * Store new assignment
   */
  public function store()
  {
    if (!has_permission('deliveries.manage')) {
      return $this->failForbidden();
    }

    $rules = [
      'invoice_id' => 'required|integer',
      'assigned_to' => 'required|integer',
      'expected_delivery_date' => 'required|valid_date'
    ];

    if (!$this->validate($rules)) {
      return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }

    try {
      $this->deliveryService->assignDelivery(
        $this->request->getPost('invoice_id'),
        $this->request->getPost('assigned_to'),
        session()->get('user_id'),
        $this->request->getPost('expected_delivery_date'),
        $this->request->getPost('notes')
      );

      return redirect()->to('/deliveries')->with('message', 'Delivery assigned successfully');
    } catch (\Exception $e) {
      return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
  }

  /**
   * Start delivery
   */
  public function start($id)
  {
    try {
      $delivery = $this->deliveryModel->find($id);
      if (!$delivery) throw new \Exception('Delivery not found');

      // Security check
      $userId = session()->get('user_id');
      if ($delivery['assigned_to'] != $userId && !has_permission('deliveries.manage')) {
        throw new \Exception('You are not authorized to start this delivery');
      }

      $this->deliveryService->startDelivery($id);
      return $this->response->setJSON(['status' => 'success', 'message' => 'Delivery started']);
    } catch (\Exception $e) {
      return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
    }
  }

  /**
   * Complete delivery
   */
  public function complete($id)
  {
    $file = $this->request->getFile('proof_photo');

    if (!$file || !$file->isValid()) {
      return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid proof photo']);
    }

    try {
      $delivery = $this->deliveryModel->find($id);
      if (!$delivery) throw new \Exception('Delivery not found');

      // Security check
      $userId = session()->get('user_id');
      if ($delivery['assigned_to'] != $userId && !has_permission('deliveries.manage')) {
        throw new \Exception('You are not authorized to complete this delivery');
      }

      $this->deliveryService->markDelivered($id, $file);
      return $this->response->setJSON(['status' => 'success', 'message' => 'Delivery marked as delivered']);
    } catch (\Exception $e) {
      return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
    }
  }

  /**
   * Fail delivery
   */
  public function fail($id)
  {
    $reason = $this->request->getPost('reason');

    if (empty($reason)) {
      return $this->response->setJSON(['status' => 'error', 'message' => 'Reason is required']);
    }

    try {
      $delivery = $this->deliveryModel->find($id);
      if (!$delivery) throw new \Exception('Delivery not found');

      // Security check
      $userId = session()->get('user_id');
      if ($delivery['assigned_to'] != $userId && !has_permission('deliveries.manage')) {
        throw new \Exception('You are not authorized to fail this delivery');
      }

      $this->deliveryService->markFailed($id, $reason);
      return $this->response->setJSON(['status' => 'success', 'message' => 'Delivery marked as failed']);
    } catch (\Exception $e) {
      return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
    }
  }

  /**
   * Show delivery details
   */
  public function show($id)
  {
    // Permission check: 'deliveries.view' OR 'deliveries.view_assigned' if assigned to user
    $delivery = $this->deliveryModel->find($id);

    if (!$delivery) {
      throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    // Details with invoice
    $invoice = $this->invoiceModel->getInvoiceWithLines($delivery['invoice_id']);

    $assignedUser = $this->db->table('users')->select('full_name')->where('id', $delivery['assigned_to'])->get()->getRow();

    return view('deliveries/show', [
      'title' => 'Delivery Details',
      'delivery' => $delivery,
      'invoice' => $invoice,
      'assigned_user_name' => $assignedUser ? $assignedUser->full_name : 'Unknown'
    ]);
  }
}

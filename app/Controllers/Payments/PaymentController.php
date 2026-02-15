<?php

namespace App\Controllers\Payments;

use App\Controllers\BaseController;
use App\Services\Payment\PaymentService;
use App\Models\InvoiceModel;
use App\Models\PaymentModel;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

class PaymentController extends BaseController
{
  protected $paymentService;
  protected $invoiceModel;
  protected $paymentModel;

  public function __construct()
  {
    $this->paymentService = new PaymentService();
    $this->invoiceModel = new InvoiceModel();
    $this->paymentModel = new PaymentModel();
  }

  /**
   * List all payments
   * 
   * GET /payments
   */
  public function index()
  {
    if (!can('payment.view')) {
      return redirect()->to('/dashboard')->with('error', 'Permission denied');
    }

    try {
      // Get filters
      $filters = [
        'date_from'     => $this->request->getGet('date_from'),
        'date_to'       => $this->request->getGet('date_to'),
        'customer_type' => $this->request->getGet('customer_type'),
        'payment_mode'  => $this->request->getGet('payment_mode'),
        'search'        => $this->request->getGet('search')
      ];

      // Build query using Model for pagination support
      $this->paymentModel->where('is_deleted', 0);

      // Apply Company Filter (handled by Model's findAll but we need it for query builder too if using paginate)
      // Model's applyCompanyFilter is protected and called in findAll/find.
      // But for paginate, we might need to manually ensure it if the model doesn't apply it in builder.
      // Let's check PaymentModel... it modifies findAll.
      // CI4 paginate uses findAll internally? No, it uses standard builder.
      // So we MUST manually apply company filter here or use a scope.

      $companyId = session()->get('company_id');
      if ($companyId) {
        $this->paymentModel->where('company_id', $companyId);
      }

      // Apply Filters
      if (!empty($filters['date_from'])) {
        $this->paymentModel->where('payment_date >=', $filters['date_from']);
      }
      if (!empty($filters['date_to'])) {
        $this->paymentModel->where('payment_date <=', $filters['date_to']);
      }
      if (!empty($filters['customer_type'])) {
        $this->paymentModel->where('customer_type', $filters['customer_type']);
      }
      if (!empty($filters['payment_mode'])) {
        $this->paymentModel->where('payment_mode', $filters['payment_mode']);
      }
      if (!empty($filters['search'])) {
        $this->paymentModel->groupStart()
          ->like('payment_number', $filters['search'])
          ->orLike('transaction_reference', $filters['search'])
          ->orLike('cheque_number', $filters['search'])
          ->groupEnd();
      }

      $this->paymentModel->orderBy('payment_date', 'DESC');

      $data = [
        'payments' => $this->paymentModel->paginate(20),
        'pager'    => $this->paymentModel->pager,
        'filters'  => $filters
      ];

      return view('payments/index', $data);
    } catch (Exception $e) {
      log_message('error', '[PaymentController::index] ' . $e->getMessage());
      return redirect()->back()->with('error', 'Failed to load payments.');
    }
  }

  /**
   * Show create payment form
   * 
   * GET /payments/create
   */
  public function create()
  {
    if (!can('payment.create')) {
      return redirect()->back()->with('error', 'Permission denied');
    }

    try {
      $invoiceId = $this->request->getGet('invoice_id');
      $companyId = session()->get('company_id');

      // Get outstanding invoices for dropdown
      // We need invoices where amount_due > 0 AND invoice_status != 'Draft'
      // And company_id matches
      $invoices = $this->invoiceModel
        ->where('company_id', $companyId)
        ->where('is_deleted', 0)
        ->where('amount_due >', 0)
        ->where('invoice_status !=', 'Draft')
        ->orderBy('invoice_date', 'DESC') // Most recent first
        ->findAll();

      return view('payments/create', [
        'invoices' => $invoices,
        'selected_invoice_id' => $invoiceId,
        'payment_modes' => ['Cash', 'Cheque', 'Bank Transfer', 'UPI', 'Card', 'Other']
      ]);
    } catch (Exception $e) {
      log_message('error', '[PaymentController::create] ' . $e->getMessage());
      return redirect()->back()->with('error', 'Failed to load create form.');
    }
  }

  /**
   * Store new payment
   * 
   * POST /payments
   */
  public function store()
  {
    if (!can('payment.create')) {
      return redirect()->back()->with('error', 'Permission denied');
    }

    if (!$this->validate([
      'invoice_id' => 'required|integer',
      'payment_date' => 'required|valid_date',
      'payment_amount' => 'required|decimal|greater_than[0]',
      'payment_mode' => 'required|in_list[Cash,Cheque,Bank Transfer,UPI,Card,Other]'
    ])) {
      return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }

    try {
      $data = $this->request->getPost();

      $paymentId = $this->paymentService->createPayment($data);

      return redirect()->to('/payments/' . $paymentId)->with('success', 'Payment recorded successfully.');
    } catch (Exception $e) {
      log_message('error', '[PaymentController::store] ' . $e->getMessage());
      return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
  }

  /**
   * Show payment details
   * 
   * GET /payments/{id}
   */
  public function show($id)
  {
    if (!can('payment.view')) {
      return redirect()->back()->with('error', 'Permission denied');
    }

    try {
      $payment = $this->paymentService->getPaymentById($id);

      if (!$payment) {
        return redirect()->to('/payments')->with('error', 'Payment not found.');
      }

      return view('payments/show', ['payment' => $payment]);
    } catch (Exception $e) {
      log_message('error', '[PaymentController::show] ' . $e->getMessage());
      return redirect()->to('/payments')->with('error', 'Failed to load payment details.');
    }
  }

  /**
   * Delete payment
   * 
   * DELETE /payments/{id}
   */
  public function delete($id)
  {
    if (!can('payment.delete')) {
      return $this->response->setJSON(['status' => 'error', 'message' => 'Permission denied'])->setStatusCode(403);
    }

    try {
      $this->paymentService->deletePayment($id);

      return $this->response->setJSON(['status' => 'success', 'message' => 'Payment deleted successfully.']);
    } catch (Exception $e) {
      log_message('error', '[PaymentController::delete] ' . $e->getMessage());
      return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()])->setStatusCode(500);
    }
  }
}

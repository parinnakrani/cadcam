<?php

namespace App\Controllers\Reports;

use App\Controllers\BaseController;
use App\Models\PaymentModel;
use App\Models\AccountModel;
use App\Models\CashCustomerModel;

class PaymentReportController extends BaseController
{
  protected $paymentModel;
  protected $accountModel;
  protected $cashCustomerModel;

  public function __construct()
  {
    $this->paymentModel      = new PaymentModel();
    $this->accountModel      = new AccountModel();
    $this->cashCustomerModel = new CashCustomerModel();
  }

  public function index()
  {
    if (!$this->hasPermission('reports.view')) {
      return redirect()->back()->with('error', 'Permission denied.');
    }

    $fromDate = $this->request->getGet('from_date') ?? date('Y-m-01');
    $toDate   = $this->request->getGet('to_date') ?? date('Y-m-d');

    $currentCompanyId = session()->get('company_id');

    // Query Payments
    $builder = $this->paymentModel->builder();
    $builder->select('payments.*, accounts.account_name, cash_customers.customer_name as cash_customer_name');

    // Joins to get customer names
    // Note: Payment has account_id OR cash_customer_id
    $builder->join('accounts', 'accounts.id = payments.account_id', 'left');
    $builder->join('cash_customers', 'cash_customers.id = payments.cash_customer_id', 'left');

    if (!session()->get('is_super_admin') && $currentCompanyId) {
      $builder->where('payments.company_id', $currentCompanyId);
    }

    $builder->where('payments.is_deleted', 0);
    $builder->where('payment_date >=', $fromDate);
    $builder->where('payment_date <=', $toDate);
    $builder->orderBy('payment_date', 'DESC');
    $builder->orderBy('payments.id', 'DESC');

    $payments = $builder->get()->getResultArray();

    // Calculate Summaries
    $totalCollected = 0.00;
    $modeBreakdown  = [];

    foreach ($payments as $p) {
      $amount = (float)$p['payment_amount'];
      $mode   = $p['payment_mode'];

      $totalCollected += $amount;

      if (!isset($modeBreakdown[$mode])) {
        $modeBreakdown[$mode] = 0.00;
      }
      $modeBreakdown[$mode] += $amount;
    }

    return view('reports/payment_collection', [
      'payments'       => $payments,
      'fromDate'       => $fromDate,
      'toDate'         => $toDate,
      'totalCollected' => $totalCollected,
      'modeBreakdown'  => $modeBreakdown,
      'title'          => 'Payment Collection Summary'
    ]);
  }
}

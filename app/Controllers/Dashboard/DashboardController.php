<?php

namespace App\Controllers\Dashboard;

use App\Controllers\BaseController; // Use BaseController from app/Controllers/BaseController
use App\Models\InvoiceModel;
use App\Models\PaymentModel;
use App\Models\ChallanModel;
use App\Models\AccountModel;
use App\Models\CashCustomerModel;

class DashboardController extends BaseController
{
  protected $invoiceModel;
  protected $paymentModel;
  protected $challanModel;
  protected $accountModel;
  protected $cashCustomerModel;

  public function __construct()
  {
    $this->invoiceModel      = new InvoiceModel();
    $this->paymentModel      = new PaymentModel();
    $this->challanModel      = new ChallanModel();
    $this->accountModel      = new AccountModel();
    $this->cashCustomerModel = new CashCustomerModel();
  }

  public function index()
  {
    $data = [
      'todaySummary'       => $this->getTodaySummary(),
      'outstandingSummary' => $this->getOutstandingSummary(),
      'topCustomers'       => $this->getTopCustomers(),
      'challanStatus'      => $this->getChallanStatus(),
      'paymentTrend'       => $this->getPaymentCollectionTrend(),
      'invoiceTrend'       => $this->getInvoiceTrend(),
      'title'              => 'Dashboard'
    ];

    return view('dashboard/index', $data);
  }

  private function getTodaySummary()
  {
    $today = date('Y-m-d');
    // Invoices Created Today
    $invResult = $this->invoiceModel->selectCount('id')->selectSum('grand_total')
      ->where('invoice_date', $today)
      ->first();

    // Payments Received Today
    $payResult = $this->paymentModel->selectCount('id')->selectSum('payment_amount')
      ->where('payment_date', $today)
      ->first();

    // Pending Deliveries (Challans Approved but not Delivered/Invoiced)
    // Assuming 'Approved' status means ready/pending delivery.
    $pendingDeliveries = $this->challanModel->where('status', 'Approved')->countAllResults();

    return [
      'invoices_count' => $invResult['id'] ?? 0,
      'invoices_total' => $invResult['grand_total'] ?? 0,
      'payments_count' => $payResult['id'] ?? 0,
      'payments_total' => $payResult['payment_amount'] ?? 0,
      'pending_deliveries' => $pendingDeliveries
    ];
  }

  private function getOutstandingSummary()
  {
    // Total Receivables = Sum of positive current_balance from Accounts
    // (Cash customers usually settle immediately but we check them too)

    $accRec = $this->accountModel->selectSum('current_balance')
      ->where('current_balance >', 0)
      ->first();

    $cashRec = $this->cashCustomerModel->selectSum('current_balance')
      ->where('current_balance >', 0)
      ->first();

    $totalReceivable = ($accRec['current_balance'] ?? 0) + ($cashRec['current_balance'] ?? 0);

    // Count Unpaid Invoices
    $unpaidCount = $this->invoiceModel->where('payment_status !=', 'Paid')->countAllResults();

    return [
      'total_receivable' => $totalReceivable,
      'unpaid_invoices'  => $unpaidCount
    ];
  }

  private function getTopCustomers()
  {
    return $this->accountModel->orderBy('current_balance', 'DESC')
      ->limit(10)
      ->find();
  }

  private function getChallanStatus()
  {
    // Group by status
    $stats = $this->challanModel->select('status, count(*) as count')
      ->groupBy('status')
      ->findAll();

    $data = ['Draft' => 0, 'Approved' => 0, 'Completed' => 0];

    foreach ($stats as $row) {
      $data[$row['status']] = $row['count'];
    }

    return $data;
  }

  private function getPaymentCollectionTrend(int $days = 30)
  {
    $fromDate = date('Y-m-d', strtotime("-{$days} days"));

    $results = $this->paymentModel->select('payment_date, SUM(payment_amount) as total')
      ->where('payment_date >=', $fromDate)
      ->groupBy('payment_date')
      ->orderBy('payment_date', 'ASC')
      ->findAll();

    $data = [];
    // Fill missing dates
    for ($i = $days; $i >= 0; $i--) {
      $date = date('Y-m-d', strtotime("-{$i} days"));
      $data[$date] = 0;
    }

    foreach ($results as $row) {
      $data[$row['payment_date']] = (float)$row['total'];
    }

    return $data;
  }

  private function getInvoiceTrend(int $days = 30)
  {
    $fromDate = date('Y-m-d', strtotime("-{$days} days"));

    $results = $this->invoiceModel->select('invoice_date, SUM(grand_total) as total')
      ->where('invoice_date >=', $fromDate)
      ->groupBy('invoice_date')
      ->orderBy('invoice_date', 'ASC')
      ->findAll();

    $data = [];
    // Fill missing dates
    for ($i = $days; $i >= 0; $i--) {
      $date = date('Y-m-d', strtotime("-{$i} days"));
      $data[$date] = 0;
    }

    foreach ($results as $row) {
      $data[$row['invoice_date']] = (float)$row['total'];
    }

    return $data;
  }
}

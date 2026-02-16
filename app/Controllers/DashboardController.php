<?php

namespace App\Controllers;

use App\Models\InvoiceModel;
use App\Models\PaymentModel;
use App\Models\ChallanModel;
use App\Models\AccountModel;
use App\Models\CashCustomerModel;
use App\Models\LedgerEntryModel;

class DashboardController extends BaseController
{
  protected $invoiceModel;
  protected $paymentModel;
  protected $challanModel;
  protected $accountModel;
  protected $cashCustomerModel;
  protected $ledgerEntryModel;

  public function __construct()
  {
    $this->invoiceModel = new InvoiceModel();
    $this->paymentModel = new PaymentModel();
    $this->challanModel = new ChallanModel();
    $this->accountModel = new AccountModel();
    $this->cashCustomerModel = new CashCustomerModel();
    $this->ledgerEntryModel = new LedgerEntryModel();
  }

  public function index()
  {
    $todaySummary = $this->getTodaySummary();
    $outstandingSummary = $this->getOutstandingSummary();
    $topCustomers = $this->getTopCustomers();
    $challanStatus = $this->getChallanStatus();
    $invoiceTrend = $this->getInvoiceTrend();
    $paymentTrend = $this->getPaymentTrend();

    return view('dashboard/index', [
      'todaySummary' => $todaySummary,
      'outstandingSummary' => $outstandingSummary,
      'topCustomers' => $topCustomers,
      'challanStatus' => $challanStatus,
      'invoiceTrend' => $invoiceTrend,
      'paymentTrend' => $paymentTrend,
    ]);
  }

  private function getTodaySummary()
  {
    $today = date('Y-m-d');
    $currentCompanyId = session()->get('company_id');

    // Prepare query constraints
    $invBuilder = $this->invoiceModel->builder();
    $payBuilder = $this->paymentModel->builder();
    $chalBuilder = $this->challanModel->builder();

    if (!session()->get('is_super_admin') && $currentCompanyId) {
      $invBuilder->where('company_id', $currentCompanyId);
      $payBuilder->where('company_id', $currentCompanyId);
      $chalBuilder->where('company_id', $currentCompanyId);
    }

    // Invoices Created
    $invResult = $invBuilder->selectCount('id', 'count')
      ->selectSum('grand_total', 'total')
      ->where('invoice_date', $today)
      ->where('is_deleted', 0)
      ->get()->getRowArray();

    // Payments Received
    $payResult = $payBuilder->selectCount('id', 'count')
      ->selectSum('payment_amount', 'total')
      ->where('payment_date', $today) // Assuming payment_date exists
      ->where('is_deleted', 0)
      ->get()->getRowArray();

    // Pending Deliveries (Challans approved but not invoiced?)
    // Or just approved challans created today? Prompt suggests "Pending Deliveries".
    // Let's count challans with status 'Approved'.
    $pendingDeliveries = $chalBuilder->where('challan_status', 'Approved')->countAllResults();

    return [
      'invoices_count' => $invResult['count'] ?? 0,
      'invoices_total' => $invResult['total'] ?? 0,
      'payments_count' => $payResult['count'] ?? 0,
      'payments_total' => $payResult['total'] ?? 0,
      'pending_deliveries' => $pendingDeliveries
    ];
  }

  private function getOutstandingSummary()
  {
    // 1. Calculate Total Receivables using Ledger Aggregation
    $builder = $this->ledgerEntryModel->builder();
    $builder->selectSum('debit_amount', 'debit');
    $builder->selectSum('credit_amount', 'credit');

    $currentCompanyId = session()->get('company_id');
    if (!session()->get('is_super_admin') && $currentCompanyId) {
      $builder->where('company_id', $currentCompanyId);
    }

    // Summing all ledger entries gives Net Receivables effectively
    // Assuming strictly Asset accounts (Customers).
    // If we mix Vendors, this would be net.
    // But for now, let's assume all ledger entries are customer related.
    $result = $builder->get()->getRowArray();

    $totalReceivable = ($result['debit'] ?? 0) - ($result['credit'] ?? 0);

    // 2. Count Unpaid Invoices
    // Since we lack 'payment_status' column potentially on invoices table, we skip detailed count 
    // or assume 'status' column exists. If 'payment_status' missing, fallback to count of all non-deleted invoices.
    // Let's assume 'payment_status' exists as it's standard.
    $invBuilder = $this->invoiceModel->builder();
    if (!session()->get('is_super_admin') && $currentCompanyId) {
      $invBuilder->where('company_id', $currentCompanyId);
    }
    $unpaidCount = $invBuilder->where('is_deleted', 0)
      ->where('payment_status !=', 'Paid')
      ->countAllResults();

    return [
      'total_receivable' => $totalReceivable,
      'unpaid_invoices' => $unpaidCount
    ];
  }

  private function getTopCustomers()
  {
    // Use Ledger Aggregation to find top balances
    $builder = $this->ledgerEntryModel->builder();
    $builder->select('account_id, SUM(debit_amount) - SUM(credit_amount) as balance');

    $currentCompanyId = session()->get('company_id');
    if (!session()->get('is_super_admin') && $currentCompanyId) {
      $builder->where('company_id', $currentCompanyId);
    }

    $builder->where('account_id IS NOT NULL'); // Only Accounts for simplicity in "Top Customers" list
    $builder->groupBy('account_id');
    $builder->having('balance >', 0);
    $builder->orderBy('balance', 'DESC');
    $builder->limit(10);

    $balances = $builder->get()->getResultArray();

    if (empty($balances)) {
      return [];
    }

    $accountIds = array_column($balances, 'account_id');
    $balanceMap = array_column($balances, 'balance', 'account_id');

    // Fetch details
    $accounts = $this->accountModel->whereIn('id', $accountIds)->findAll();

    // Merge balance
    foreach ($accounts as &$acc) {
      $acc['current_balance'] = $balanceMap[$acc['id']] ?? 0;
    }

    // Re-sort because database return order is distinct
    usort($accounts, function ($a, $b) {
      return $b['current_balance'] <=> $a['current_balance'];
    });

    return $accounts;
  }

  private function getRecentInvoices()
  {
    $currentCompanyId = session()->get('company_id');
    $builder = $this->invoiceModel->builder();
    if (!session()->get('is_super_admin') && $currentCompanyId) {
      $builder->where('invoices.company_id', $currentCompanyId);
    }

    $builder->select('invoices.*, accounts.account_name, cash_customers.customer_name as cash_customer_name');
    $builder->join('accounts', 'accounts.id = invoices.account_id', 'left');
    $builder->join('cash_customers', 'cash_customers.id = invoices.cash_customer_id', 'left');
    $builder->where('invoices.is_deleted', 0);
    $builder->orderBy('invoices.created_at', 'DESC');
    $builder->limit(10); // Recent 10

    return $builder->get()->getResultArray();
  }

  private function getRecentPayments()
  {
    $currentCompanyId = session()->get('company_id');
    $builder = $this->paymentModel->builder();
    if (!session()->get('is_super_admin') && $currentCompanyId) {
      $builder->where('payments.company_id', $currentCompanyId);
    }

    $builder->select('payments.*, accounts.account_name, cash_customers.customer_name as cash_customer_name');
    $builder->join('accounts', 'accounts.id = payments.account_id', 'left');
    $builder->join('cash_customers', 'cash_customers.id = payments.cash_customer_id', 'left');
    $builder->where('payments.is_deleted', 0);
    $builder->orderBy('payments.created_at', 'DESC');
    $builder->limit(10); // Recent 10

    return $builder->get()->getResultArray();
  }

  private function getChallanStatus()
  {
    $builder = $this->challanModel->builder();
    $currentCompanyId = session()->get('company_id');
    if (!session()->get('is_super_admin') && $currentCompanyId) {
      $builder->where('company_id', $currentCompanyId);
    }

    $stats = $builder->select('challan_status, count(id) as count')
      ->groupBy('challan_status')
      ->get()->getResultArray();

    $data = ['Draft' => 0, 'Approved' => 0, 'Invoiced' => 0];
    foreach ($stats as $row) {
      if (isset($data[$row['challan_status']])) {
        $data[$row['challan_status']] = $row['count'];
      }
    }
    return $data;
  }

  private function getInvoiceTrend()
  {
    $currentCompanyId = session()->get('company_id');
    $builder = $this->invoiceModel->builder();

    if (!session()->get('is_super_admin') && $currentCompanyId) {
      $builder->where('company_id', $currentCompanyId);
    }

    // Last 30 days
    $startDate = date('Y-m-d', strtotime('-30 days'));

    $builder->select('DATE(invoice_date) as date, SUM(grand_total) as total');
    $builder->where('invoice_date >=', $startDate);
    $builder->where('is_deleted', 0);
    $builder->groupBy('DATE(invoice_date)');
    $builder->orderBy('date', 'ASC');

    $results = $builder->get()->getResultArray();

    $trend = [];
    for ($i = 29; $i >= 0; $i--) {
      $trend[date('Y-m-d', strtotime("-$i days"))] = 0;
    }

    foreach ($results as $row) {
      $trend[$row['date']] = (float)$row['total'];
    }

    return $trend;
  }

  private function getPaymentTrend()
  {
    $currentCompanyId = session()->get('company_id');
    $builder = $this->paymentModel->builder();

    if (!session()->get('is_super_admin') && $currentCompanyId) {
      $builder->where('company_id', $currentCompanyId);
    }

    // Last 30 days
    $startDate = date('Y-m-d', strtotime('-30 days'));

    $builder->select('DATE(payment_date) as date, SUM(payment_amount) as total');
    $builder->where('payment_date >=', $startDate);
    $builder->where('is_deleted', 0);
    $builder->groupBy('DATE(payment_date)');
    $builder->orderBy('date', 'ASC');

    $results = $builder->get()->getResultArray();

    $trend = [];
    for ($i = 29; $i >= 0; $i--) {
      $trend[date('Y-m-d', strtotime("-$i days"))] = 0;
    }

    foreach ($results as $row) {
      $trend[$row['date']] = (float)$row['total'];
    }

    return $trend;
  }
}

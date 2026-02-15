<?php

namespace App\Controllers\Reports;

use App\Controllers\BaseController;
use App\Models\InvoiceModel;
use App\Models\AccountModel;
use App\Models\CashCustomerModel;

class OutstandingReportController extends BaseController
{
  protected $invoiceModel;
  protected $accountModel;
  protected $cashCustomerModel;

  public function __construct()
  {
    $this->invoiceModel      = new InvoiceModel();
    $this->accountModel      = new AccountModel();
    $this->cashCustomerModel = new CashCustomerModel();
  }

  public function index()
  {
    if (!$this->hasPermission('reports.view')) {
      return redirect()->back()->with('error', 'Permission denied.');
    }
    return redirect()->to(base_url('ledgers/reminders/outstanding'));
  }

  public function agingReport()
  {
    if (!$this->hasPermission('reports.view')) {
      return redirect()->back()->with('error', 'Permission denied.');
    }

    $toDate = $this->request->getGet('to_date') ?? date('Y-m-d');

    // Fetch all outstanding invoices. getOutstandingInvoices filters by amount_due > 0 and !Paid
    $allInvoices = $this->invoiceModel->getOutstandingInvoices(null, null);

    // Preload Customers to avoid N+1 queries
    $accountIds = [];
    $cashIds    = [];
    foreach ($allInvoices as $inv) {
      if ($inv['account_id']) $accountIds[] = $inv['account_id'];
      if ($inv['cash_customer_id']) $cashIds[] = $inv['cash_customer_id'];
    }
    $accountIds = array_unique($accountIds);
    $cashIds    = array_unique($cashIds);

    $accounts = [];
    if (!empty($accountIds)) {
      $accountRows = $this->accountModel->whereIn('id', $accountIds)->findAll();
      foreach ($accountRows as $row) $accounts[$row['id']] = $row['account_name'];
    }

    $cashCustomers = [];
    if (!empty($cashIds)) {
      $cashRows = $this->cashCustomerModel->whereIn('id', $cashIds)->findAll();
      foreach ($cashRows as $row) $cashCustomers[$row['id']] = $row['customer_name'];
    }

    // Initialize Buckets
    $buckets = [
      '0-30'   => ['label' => '0-30 Days', 'count' => 0, 'amount' => 0.00, 'invoices' => []],
      '31-60'  => ['label' => '31-60 Days', 'count' => 0, 'amount' => 0.00, 'invoices' => []],
      '61-90'  => ['label' => '61-90 Days', 'count' => 0, 'amount' => 0.00, 'invoices' => []],
      '90+'    => ['label' => '90+ Days', 'count' => 0, 'amount' => 0.00, 'invoices' => []]
    ];

    $todayObj = new \DateTime($toDate);

    foreach ($allInvoices as $inv) {
      $amountDue = (float)$inv['amount_due'];

      // Determine Due Date: If 'due_date' column is empty/zeros, fallback to 'invoice_date'
      $dueDateStr = ($inv['due_date'] && $inv['due_date'] != '0000-00-00') ? $inv['due_date'] : $inv['invoice_date'];

      try {
        $dueDateObj = new \DateTime($dueDateStr);
      } catch (\Exception $e) {
        // Fallback if date format error
        $dueDateObj = new \DateTime($inv['invoice_date']);
        $dueDateStr = $inv['invoice_date'];
      }

      // Calculate Days Overdue: Today - DueDate
      $daysOverdue = 0;
      if ($todayObj > $dueDateObj) {
        $daysOverdue = $todayObj->diff($dueDateObj)->days;
      } else {
        // Not overdue yet (Current) - Consider as 0 days overdue
        $daysOverdue = 0;
      }

      // Bucket Logic
      if ($daysOverdue <= 30) {
        $bucketKey = '0-30';
      } elseif ($daysOverdue <= 60) {
        $bucketKey = '31-60';
      } elseif ($daysOverdue <= 90) {
        $bucketKey = '61-90';
      } else {
        $bucketKey = '90+';
      }

      // Resolve Customer Name
      $customerName = 'Unknown';
      if ($inv['account_id']) {
        $customerName = $accounts[$inv['account_id']] ?? 'Account #' . $inv['account_id'];
      } elseif ($inv['cash_customer_id']) {
        $customerName = $cashCustomers[$inv['cash_customer_id']] ?? 'Cash Customer #' . $inv['cash_customer_id'];
      }

      // Add to bucket
      $buckets[$bucketKey]['count']++;
      $buckets[$bucketKey]['amount'] += $amountDue;
      $buckets[$bucketKey]['invoices'][] = [
        'id'             => $inv['id'],
        'invoice_number' => $inv['invoice_number'],
        'customer_name'  => $customerName,
        'due_date'       => $dueDateStr,
        'days_overdue'   => $daysOverdue,
        'amount_due'     => $amountDue
      ];
    }

    return view('reports/outstanding/aging', [
      'buckets' => $buckets,
      'toDate'  => $toDate,
      'title'   => 'Aging Report of Outstanding Invoices'
    ]);
  }
}

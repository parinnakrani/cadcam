<?php

namespace App\Controllers\Reports;

use App\Controllers\BaseController;
use App\Models\AccountModel;
use App\Models\CashCustomerModel;
use App\Models\LedgerEntryModel;

class ReceivableReportController extends BaseController
{
  protected $accountModel;
  protected $cashCustomerModel;
  protected $ledgerEntryModel;

  public function __construct()
  {
    $this->accountModel      = new AccountModel();
    $this->cashCustomerModel = new CashCustomerModel();
    $this->ledgerEntryModel  = new LedgerEntryModel();
  }

  public function index()
  {
    if (!$this->hasPermission('reports.view')) {
      return redirect()->back()->with('error', 'Permission denied.');
    }

    // Fetch All Customers (Cannot filter by current_balance since column might be missing)
    $accounts = $this->accountModel->findAll();
    $cashCustomers = $this->cashCustomerModel->findAll();

    // Calculate Balances efficiently using Aggregation
    // Account Balances
    $accBuilder = $this->ledgerEntryModel->builder();
    $accBuilder->select('account_id, SUM(debit_amount) - SUM(credit_amount) as balance');
    $currentCompanyId = session()->get('company_id');
    if (!session()->get('is_super_admin') && $currentCompanyId) {
      $accBuilder->where('company_id', $currentCompanyId);
    }
    $accBuilder->where('account_id IS NOT NULL');
    $accBuilder->groupBy('account_id');
    $accBalancesRaw = $accBuilder->get()->getResultArray();

    $accBalanceMap = [];
    foreach ($accBalancesRaw as $row) {
      $accBalanceMap[$row['account_id']] = (float)$row['balance'];
    }

    // Cash Customer Balances
    $cashBuilder = $this->ledgerEntryModel->builder();
    $cashBuilder->select('cash_customer_id, SUM(debit_amount) - SUM(credit_amount) as balance');
    if (!session()->get('is_super_admin') && $currentCompanyId) {
      $cashBuilder->where('company_id', $currentCompanyId);
    }
    $cashBuilder->where('cash_customer_id IS NOT NULL');
    $cashBuilder->groupBy('cash_customer_id');
    $cashBalancesRaw = $cashBuilder->get()->getResultArray();

    $cashBalanceMap = [];
    foreach ($cashBalancesRaw as $row) {
      $cashBalanceMap[$row['cash_customer_id']] = (float)$row['balance'];
    }

    $customers = [];
    foreach ($accounts as $acc) {
      $balance = $accBalanceMap[$acc['id']] ?? 0.00;
      // Only add if non-zero balance
      if (abs($balance) > 0.001) {
        $customers[] = [
          'id' => $acc['id'],
          'type' => 'Account',
          'name' => $acc['account_name'],
          'mobile' => $acc['mobile_number'],
          'balance' => $balance
        ];
      }
    }
    foreach ($cashCustomers as $cc) {
      $balance = $cashBalanceMap[$cc['id']] ?? 0.00;
      if (abs($balance) > 0.001) {
        $customers[] = [
          'id' => $cc['id'],
          'type' => 'Cash',
          'name' => $cc['customer_name'],
          'mobile' => $cc['mobile_number'],
          'balance' => $balance
        ];
      }
    }

    // Sort by balance desc
    usort($customers, function ($a, $b) {
      return $b['balance'] <=> $a['balance'];
    });

    return view('reports/receivables/index', [
      'customers' => $customers,
      'title'     => 'Customer Ledger Balances'
    ]);
  }

  public function monthlySummary()
  {
    if (!$this->hasPermission('reports.view')) {
      return redirect()->back()->with('error', 'Permission denied.');
    }

    $startMonth = $this->request->getGet('start_month') ?? date('Y-m');
    $endMonth   = $this->request->getGet('end_month') ?? date('Y-m');

    $currentCompanyId = session()->get('company_id');

    // Logic:
    // We need to iterate month by month from Start to End.
    // For each customer, we need Opening Balance before Start Month.
    // Then for each month, sum Debit and Credit.

    // 1. Get List of Customers (Accounts + Cash)
    // Optimization: Fetch only active customers or those with transactions?
    // For 'Receivable Summary', we usually want everyone with a balance.
    // But iterating everyone might be slow if thousands. 
    // Let's fetch all for now, assuming manageable dataset < 1000 active.

    $accounts      = $this->accountModel->findAll();
    $cashCustomers = $this->cashCustomerModel->findAll();

    // Combine into a standard structure
    $customers = [];
    foreach ($accounts as $acc) {
      $customers[] = [
        'id' => $acc['id'],
        'type' => 'Account',
        'name' => $acc['account_name'],
        'mobile' => $acc['mobile_number'],

      ];
    }
    foreach ($cashCustomers as $cc) {
      $customers[] = [
        'id' => $cc['id'],
        'type' => 'Cash',
        'name' => $cc['customer_name'],
        'mobile' => $cc['mobile_number'],

      ];
    }

    // 2. Prepare Date Range
    $start = new \DateTime($startMonth . '-01');
    $end   = new \DateTime($endMonth . '-01');
    $end->modify('last day of this month');

    $months = [];
    // Re-do logic to ensure inclusive months
    $curr = clone $start;
    $maxDate = clone $end;
    // Safety Break
    $safety = 0;
    while ($curr <= $maxDate && $safety < 24) { // Limit to 24 months
      $months[] = $curr->format('Y-m');
      $curr->modify('+1 month');
      $safety++;
    }

    // 3. Process Data
    $reportData = [];

    $startDateStr = $startMonth . '-01';
    $endDateStr   = $end->format('Y-m-d');

    // Helper to get aggregated monthly data
    $builder = $this->ledgerEntryModel->builder();
    $builder->select("
            account_id, 
            cash_customer_id, 
            DATE_FORMAT(entry_date, '%Y-%m') as month_key,
            SUM(debit_amount) as total_debit,
            SUM(credit_amount) as total_credit
        ");

    // Apply Company Filter manually since we are using builder
    if (!session()->get('is_super_admin') && $currentCompanyId) {
      $builder->where('company_id', $currentCompanyId);
    }

    $builder->where('entry_date >=', $startDateStr);
    $builder->where('entry_date <=', $endDateStr);
    $builder->groupBy('account_id, cash_customer_id, month_key');

    $transactions = $builder->get()->getResultArray();

    // Map transactions to fast lookup: [type][id][month] => [debit, credit]
    $transMap = [];
    foreach ($transactions as $t) {
      $type = $t['account_id'] ? 'Account' : 'Cash';
      $id   = $t['account_id'] ?? $t['cash_customer_id'];
      $m    = $t['month_key'];

      $transMap[$type][$id][$m] = [
        'debit'  => (float)$t['total_debit'],
        'credit' => (float)$t['total_credit']
      ];
    }

    // Calculate Opening Balances (Efficiently)
    // A. Filter only Obs where date < StartDate

    $obsBuilder = $this->ledgerEntryModel->builder();
    $obsBuilder->select("
            account_id,
            cash_customer_id,
            SUM(debit_amount) as total_debit,
            SUM(credit_amount) as total_credit
        ");
    if (!session()->get('is_super_admin') && $currentCompanyId) {
      $obsBuilder->where('company_id', $currentCompanyId);
    }
    $obsBuilder->where('entry_date <', $startDateStr);
    $obsBuilder->groupBy('account_id, cash_customer_id');
    $obsResults = $obsBuilder->get()->getResultArray();

    $obMap = [];
    foreach ($obsResults as $row) {
      $type = $row['account_id'] ? 'Account' : 'Cash';
      $id   = $row['account_id'] ?? $row['cash_customer_id'];
      $obMap[$type][$id] = (float)$row['total_debit'] - (float)$row['total_credit'];
    }

    // Build Final Report Array
    foreach ($customers as $cust) {
      $type = $cust['type'];
      $id   = $cust['id'];

      // Get Opening Balance
      $openingBal = $obMap[$type][$id] ?? 0.00;

      // Start Running Balance
      $runningBal = $openingBal;

      $row = [
        'name'   => $cust['name'],
        'mobile' => $cust['mobile'],
        'opening_balance' => $openingBal,
        'months' => []
      ];

      foreach ($months as $m) {
        // Get month trans
        $debit  = $transMap[$type][$id][$m]['debit'] ?? 0.00;
        $credit = $transMap[$type][$id][$m]['credit'] ?? 0.00;

        $runningBal = $runningBal + $debit - $credit;

        $row['months'][$m] = [
          'debit'   => $debit,
          'credit'  => $credit,
          'balance' => $runningBal
        ];
      }

      $row['closing_balance'] = $runningBal;

      // Filter logic: Show if Non-Zero Opening OR Non-Zero Closing OR Any Activity
      $hasActivity = false;
      if (abs($openingBal) > 0.01) $hasActivity = true;
      if (abs($runningBal) > 0.01) $hasActivity = true;
      foreach ($row['months'] as $mData) {
        if ($mData['debit'] > 0 || $mData['credit'] > 0) $hasActivity = true;
      }

      if ($hasActivity) {
        $reportData[] = $row;
      }
    }

    return view('reports/receivables/monthly', [
      'reportData' => $reportData,
      'months'     => $months,
      'startMonth' => $startMonth,
      'endMonth'   => $endMonth,
      'title'      => 'Monthly Receivable Summary'
    ]);
  }
}

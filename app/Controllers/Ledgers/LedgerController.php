<?php

namespace App\Controllers\Ledgers;

use App\Controllers\BaseController;
use App\Models\LedgerEntryModel;
use App\Models\AccountModel;
use App\Models\CashCustomerModel;

class LedgerController extends BaseController
{
  protected $ledgerEntryModel;
  protected $accountModel;
  protected $cashCustomerModel;

  public function __construct()
  {
    $this->ledgerEntryModel  = new LedgerEntryModel();
    $this->accountModel      = new AccountModel();
    $this->cashCustomerModel = new CashCustomerModel();
  }

  /**
   * List all accounts with current balance.
   * GET /ledgers/accounts
   */
  public function accountsLedger()
  {
    if (!$this->hasPermission('reports.ledger')) {
      return redirect()->back()->with('error', 'Permission denied.');
    }

    $accounts = $this->accountModel->findAll();

    return view('ledgers/accounts_list', [
      'accounts' => $accounts,
      'title'    => 'Account Ledgers'
    ]);
  }

  /**
   * List all cash customers with current balance.
   * GET /ledgers/cash-customers
   */
  public function cashCustomersLedger()
  {
    if (!$this->hasPermission('reports.ledger')) {
      return redirect()->back()->with('error', 'Permission denied.');
    }

    $cashCustomers = $this->cashCustomerModel->findAll();

    return view('ledgers/cash_customers_list', [
      'cashCustomers' => $cashCustomers,
      'title'         => 'Cash Customer Ledgers'
    ]);
  }

  /**
   * Detailed ledger for a specific account.
   * GET /ledgers/account/{id}
   */
  public function accountLedger(int $id)
  {
    if (!$this->hasPermission('reports.ledger')) {
      return redirect()->back()->with('error', 'Permission denied.');
    }

    $account = $this->accountModel->find($id);
    if (!$account) {
      return redirect()->to('/ledgers/accounts')->with('error', 'Account not found.');
    }

    $fromDate = $this->request->getGet('from_date');
    $toDate   = $this->request->getGet('to_date');

    $entries = $this->ledgerEntryModel->getLedgerForAccount($id, $fromDate, $toDate);

    $openingBalance = 0.00;
    if ($fromDate) {
      $openingBalance = $this->ledgerEntryModel->getOpeningBalance($id, 'Account', $fromDate);
    }

    return view('ledgers/account_ledger', [
      'account'        => $account,
      'entries'        => $entries,
      'fromDate'       => $fromDate,
      'toDate'         => $toDate,
      'openingBalance' => $openingBalance,
      'title'          => 'Ledger: ' . $account['account_name']
    ]);
  }

  /**
   * Detailed ledger for a specific cash customer.
   * GET /ledgers/cash-customer/{id}
   */
  public function cashCustomerLedger(int $id)
  {
    if (!$this->hasPermission('reports.ledger')) {
      return redirect()->back()->with('error', 'Permission denied.');
    }

    $cashCustomer = $this->cashCustomerModel->find($id);
    if (!$cashCustomer) {
      return redirect()->to('/ledgers/cash-customers')->with('error', 'Cash Customer not found.');
    }

    $fromDate = $this->request->getGet('from_date');
    $toDate   = $this->request->getGet('to_date');

    $entries = $this->ledgerEntryModel->getLedgerForCashCustomer($id, $fromDate, $toDate);

    $openingBalance = 0.00;
    if ($fromDate) {
      $openingBalance = $this->ledgerEntryModel->getOpeningBalance($id, 'Cash', $fromDate);
    }

    return view('ledgers/cash_customer_ledger', [
      'cashCustomer'   => $cashCustomer,
      'entries'        => $entries,
      'fromDate'       => $fromDate,
      'toDate'         => $toDate,
      'openingBalance' => $openingBalance,
      'title'          => 'Ledger: ' . $cashCustomer['customer_name']
    ]);
  }

  /**
   * Export Ledger (Excel/PDF)
   * GET /ledgers/export/{type}/{id}
   * type: 'account' or 'cash-customer'
   * Params: format=csv|pdf, from_date, to_date
   */
  public function exportLedger($type, $id)
  {
    if (!$this->hasPermission('reports.ledger')) {
      return redirect()->back()->with('error', 'Permission denied.');
    }

    $format = $this->request->getGet('format') ?? 'csv';
    $fromDate = $this->request->getGet('from_date');
    $toDate   = $this->request->getGet('to_date');

    $name = '';
    $entries = [];
    $openingBalance = 0.00;

    if ($type === 'account') {
      $account = $this->accountModel->find($id);
      if (!$account) return redirect()->back()->with('error', 'Account not found.');
      $name = $account['account_name'];
      $entries = $this->ledgerEntryModel->getLedgerForAccount($id, $fromDate, $toDate);
      if ($fromDate) {
        $openingBalance = $this->ledgerEntryModel->getOpeningBalance($id, 'Account', $fromDate);
      }
    } elseif ($type === 'cash-customer') {
      $customer = $this->cashCustomerModel->find($id);
      if (!$customer) return redirect()->back()->with('error', 'Customer not found.');
      $name = $customer['customer_name'];
      $entries = $this->ledgerEntryModel->getLedgerForCashCustomer($id, $fromDate, $toDate);
      if ($fromDate) {
        $openingBalance = $this->ledgerEntryModel->getOpeningBalance($id, 'Cash', $fromDate);
      }
    } else {
      return redirect()->back()->with('error', 'Invalid type.');
    }

    if ($format === 'pdf') {
      // Render Print View
      return view('ledgers/print_ledger', [
        'name'           => $name,
        'entries'        => $entries,
        'fromDate'       => $fromDate,
        'toDate'         => $toDate,
        'openingBalance' => $openingBalance,
        'type'           => ucfirst(str_replace('-', ' ', $type))
      ]);
    } else {
      // CSV Export
      $filename = 'Ledger_' . preg_replace('/[^A-Za-z0-9]/', '_', $name) . '_' . date('Ymd') . '.csv';

      header('Content-Type: text/csv');
      header('Content-Disposition: attachment; filename="' . $filename . '"');

      $fp = fopen('php://output', 'w');

      // Header Info
      fputcsv($fp, ['Ledger Statement']);
      fputcsv($fp, ['Name', $name]);
      fputcsv($fp, ['From Date', $fromDate ?: 'Start']);
      fputcsv($fp, ['To Date', $toDate ?: 'Total']);
      fputcsv($fp, []); // Empty line

      // Columns
      fputcsv($fp, ['Date', 'Reference', 'Type', 'Description', 'Debit', 'Credit', 'Balance']);

      $runningBalance = $openingBalance;

      // Opening Balance Row
      if ($fromDate) {
        fputcsv($fp, [
          date('d-M-Y', strtotime($fromDate)),
          '-',
          'OPENING',
          'Opening Balance b/f',
          ($openingBalance > 0) ? $openingBalance : '',
          ($openingBalance < 0) ? abs($openingBalance) : '',
          $runningBalance . (($runningBalance >= 0) ? ' Dr' : ' Cr')
        ]);
      }

      foreach ($entries as $entry) {
        $debit  = (float)$entry['debit_amount'];
        $credit = (float)$entry['credit_amount'];
        $runningBalance = $runningBalance + $debit - $credit;

        fputcsv($fp, [
          date('d-M-Y', strtotime($entry['entry_date'])),
          $entry['reference_number'],
          ucfirst($entry['reference_type']),
          $entry['description'],
          ($debit > 0) ? $debit : '',
          ($credit > 0) ? $credit : '',
          abs($runningBalance) . (($runningBalance >= 0) ? ' Dr' : ' Cr')
        ]);
      }

      // Closing Row
      fputcsv($fp, []);
      fputcsv($fp, ['', '', '', 'Closing Balance', '', '', abs($runningBalance) . (($runningBalance >= 0) ? ' Dr' : ' Cr')]);

      fclose($fp);
      exit;
    }
  }
}

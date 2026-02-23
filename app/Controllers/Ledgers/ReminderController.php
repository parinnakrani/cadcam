<?php

namespace App\Controllers\Ledgers;

use App\Controllers\BaseController;
use App\Models\InvoiceModel;
use App\Models\AccountModel;
use App\Models\CashCustomerModel;

class ReminderController extends BaseController
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

  public function outstandingInvoices()
  {
    $this->gate('ledgers.reminders.all.list');

    $toDate = $this->request->getGet('to_date') ?? date('Y-m-d');
    $currentCompanyId = session()->get('company_id');

    // Build Query
    $builder = $this->invoiceModel->builder();
    $builder->select('invoices.*, accounts.account_name, cash_customers.customer_name as cash_customer_name, accounts.mobile_number as acc_mobile, cash_customers.mobile_number as cash_mobile, accounts.payment_terms as credit_limit_days');

    $builder->join('accounts', 'accounts.id = invoices.account_id', 'left');
    $builder->join('cash_customers', 'cash_customers.id = invoices.cash_customer_id', 'left');

    // Filter by company
    if (!session()->get('is_super_admin') && $currentCompanyId) {
      $builder->where('invoices.company_id', $currentCompanyId);
    }

    $builder->where('invoices.is_deleted', 0);
    $builder->where('invoices.payment_status !=', 'Paid'); // Assuming 'Paid', 'Partial', 'Pending'

    $builder->orderBy('invoices.invoice_date', 'ASC');

    $invoices = $builder->get()->getResultArray();

    $today = new \DateTime($toDate);

    $processedInvoices = [];
    foreach ($invoices as $inv) {
      $invDate = new \DateTime($inv['invoice_date']);
      $creditDays = $inv['credit_limit_days'] ?? 0;

      // Calculate Due Date
      $dueDate = (clone $invDate);
      if ($inv['account_id'] && $creditDays > 0) {
        $dueDate->modify("+{$creditDays} days");
      }

      // Calculate Overdue Days
      $daysOverdue = 0;
      if ($today > $dueDate) {
        $daysOverdue = $today->diff($dueDate)->days;
      }

      // Determine Customer Name & Mobile
      if ($inv['account_id']) {
        $customerName = $inv['account_name'];
        $mobile       = $inv['acc_mobile'];
        $type         = 'Account';
      } else {
        $customerName = $inv['cash_customer_name'];
        $mobile       = $inv['cash_mobile'];
        $type         = 'Cash';
      }

      $processedInvoices[] = [
        'id'             => $inv['id'],
        'invoice_number' => $inv['invoice_number'],
        'date'           => $inv['invoice_date'],
        'due_date'       => $dueDate->format('Y-m-d'),
        'customer_name'  => $customerName,
        'mobile'         => $mobile,
        'amount'         => $inv['grand_total'],
        'status'         => $inv['payment_status'] ?? 'Pending',
        'days_overdue'   => $daysOverdue,
        'type'           => $type
      ];
    }

    return $this->render('reports/outstanding_invoices', [
      'invoices' => $processedInvoices,
      'toDate'   => $toDate,
      'title'    => 'Outstanding Invoices'
    ]);
  }

  public function sendReminder($invoiceId)
  {
    if (!can('ledgers.reminders.all.send')) {
      return $this->error('Permission denied.');
    }

    $invoice = $this->invoiceModel->find($invoiceId);

    if (!$invoice) {
      return $this->error('Invoice not found.');
    }

    // Logic to send reminder (SMS/Email) would go here
    // For now, return success

    return $this->success('Reminder sent successfully!');
  }
}

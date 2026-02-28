<?php

namespace App\Controllers\Reports;

use App\Controllers\BaseController;
use App\Models\InvoiceModel;
use App\Models\AccountModel;
use App\Models\CashCustomerModel;

/**
 * DailyReportController
 *
 * Handles daily invoice report with date filtering, pagination, export and print.
 *
 * Routes:
 * - GET /reports/daily           → index()         [permission: reports.daily.list]
 * - GET /reports/daily/export    → exportCsv()      [permission: reports.daily.export]
 * - GET /reports/daily/print     → printReport()    [permission: reports.daily.list]
 */
class DailyReportController extends BaseController
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

  /**
   * Show daily invoice report.
   * By default shows today's invoices.
   */
  public function index()
  {
    $this->gate('reports.daily.list');

    // Get filter parameters
    $date    = $this->request->getGet('date') ?? date('Y-m-d');
    $perPage = (int)($this->request->getGet('per_page') ?? 100);

    // Validate per_page
    $validPerPage = [25, 50, 100, 250, 500, 1000];
    if (!in_array($perPage, $validPerPage)) {
      $perPage = 100;
    }

    // Current page
    $page = max(1, (int)($this->request->getGet('page') ?? 1));

    // Get invoices for the selected date
    $result = $this->getInvoicesForDate($date, $perPage, $page);

    return $this->render('reports/daily', [
      'title'       => 'Daily Invoice Report',
      'invoices'    => $result['invoices'],
      'grandTotal'  => $result['grandTotal'],
      'totalCount'  => $result['totalCount'],
      'date'        => $date,
      'perPage'     => $perPage,
      'currentPage' => $page,
      'totalPages'  => $result['totalPages'],
      'canExport'   => can('reports.daily.export'),
    ]);
  }

  /**
   * Export daily report as CSV.
   */
  public function exportCsv()
  {
    $this->gate('reports.daily.export');

    $date = $this->request->getGet('date') ?? date('Y-m-d');

    // Get ALL invoices for the date (no pagination for export)
    $result = $this->getInvoicesForDate($date, 999999, 1);
    $invoices = $result['invoices'];

    // Build CSV
    $filename = 'daily_invoice_report_' . $date . '.csv';

    // Use output buffering to build CSV content
    ob_start();
    $output = fopen('php://output', 'w');

    // Header row
    fputcsv($output, ['Date', 'Invoice Number', 'Invoice Type', 'Customer Name', 'Total']);

    foreach ($invoices as $inv) {
      fputcsv($output, [
        date('d M Y', strtotime($inv['invoice_date'])),
        $inv['invoice_number'],
        $inv['invoice_type'],
        $inv['customer_name'],
        number_format((float)$inv['grand_total'], 2, '.', ''),
      ]);
    }

    // Total row
    fputcsv($output, ['', '', '', 'TOTAL', number_format((float)$result['grandTotal'], 2, '.', '')]);

    fclose($output);
    $csvContent = ob_get_clean();

    return $this->response
      ->setHeader('Content-Type', 'text/csv')
      ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
      ->setBody($csvContent);
  }

  /**
   * Print-friendly view of the daily report.
   */
  public function printReport()
  {
    $this->gate('reports.daily.list');

    $date = $this->request->getGet('date') ?? date('Y-m-d');

    // Get ALL invoices for the date (no pagination for print)
    $result = $this->getInvoicesForDate($date, 999999, 1);

    return $this->render('reports/daily_print', [
      'title'      => 'Daily Invoice Report',
      'invoices'   => $result['invoices'],
      'grandTotal' => $result['grandTotal'],
      'totalCount' => $result['totalCount'],
      'date'       => $date,
    ]);
  }

  // =========================================================================
  // PRIVATE HELPERS
  // =========================================================================

  /**
   * Get invoices for a specific date with customer names resolved.
   *
   * @param string $date      Date in Y-m-d format
   * @param int    $perPage   Items per page
   * @param int    $page      Current page
   * @return array
   */
  private function getInvoicesForDate(string $date, int $perPage, int $page): array
  {
    $db = \Config\Database::connect();
    $session = session();
    $companyId = $session->get('company_id');

    // Get total count for pagination
    $builderCount = $db->table('invoices')
      ->where('invoice_date', $date)
      ->where('is_deleted', 0);
    if ($companyId) {
      $builderCount->where('company_id', $companyId);
    }
    $totalCount = $builderCount->countAllResults();

    // Get grand total (sum of all matching invoices)
    $builderSum = $db->table('invoices')
      ->selectSum('grand_total', 'total_sum')
      ->where('invoice_date', $date)
      ->where('is_deleted', 0);
    if ($companyId) {
      $builderSum->where('company_id', $companyId);
    }
    $sumResult = $builderSum->get()->getRowArray();
    $grandTotal = (float)($sumResult['total_sum'] ?? 0);

    // Pagination bounds
    $totalPages = max(1, (int)ceil($totalCount / $perPage));
    $offset     = ($page - 1) * $perPage;

    // Fetch paginated results
    $builderList = $db->table('invoices')
      ->select('id, invoice_number, invoice_type, invoice_date, grand_total, account_id, cash_customer_id')
      ->where('invoice_date', $date)
      ->where('is_deleted', 0);
    if ($companyId) {
      $builderList->where('company_id', $companyId);
    }

    $invoices = $builderList->orderBy('invoice_number', 'ASC')
      ->limit($perPage, $offset)
      ->get()
      ->getResultArray();

    // Preload customer names
    $accountIds = [];
    $cashIds    = [];
    foreach ($invoices as $inv) {
      if (!empty($inv['account_id'])) $accountIds[] = $inv['account_id'];
      if (!empty($inv['cash_customer_id'])) $cashIds[] = $inv['cash_customer_id'];
    }
    $accountIds = array_unique($accountIds);
    $cashIds    = array_unique($cashIds);

    $accounts = [];
    if (!empty($accountIds)) {
      $rows = $this->accountModel->whereIn('id', $accountIds)->findAll();
      foreach ($rows as $row) $accounts[$row['id']] = $row['account_name'];
    }

    $cashCustomers = [];
    if (!empty($cashIds)) {
      $rows = $this->cashCustomerModel->whereIn('id', $cashIds)->findAll();
      foreach ($rows as $row) $cashCustomers[$row['id']] = $row['customer_name'];
    }

    // Resolve customer names
    foreach ($invoices as &$inv) {
      if (!empty($inv['account_id'])) {
        $inv['customer_name'] = $accounts[$inv['account_id']] ?? 'Account #' . $inv['account_id'];
      } elseif (!empty($inv['cash_customer_id'])) {
        $inv['customer_name'] = $cashCustomers[$inv['cash_customer_id']] ?? 'Cash Customer #' . $inv['cash_customer_id'];
      } else {
        $inv['customer_name'] = '—';
      }
    }
    unset($inv);

    return [
      'invoices'   => $invoices,
      'grandTotal' => $grandTotal,
      'totalCount' => $totalCount,
      'totalPages' => $totalPages,
    ];
  }
}

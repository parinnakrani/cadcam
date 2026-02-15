<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\AccountModel;
use App\Models\CashCustomerModel;
use App\Services\Ledger\LedgerService;

class RecalculateBalances extends BaseCommand
{
  /**
   * The Command's Group
   *
   * @var string
   */
  protected $group = 'Ledger';

  /**
   * The Command's Name
   *
   * @var string
   */
  protected $name = 'ledger:recalculate';

  /**
   * The Command's Description
   *
   * @var string
   */
  protected $description = 'Recalculates running balances for all accounts and cash customers.';

  /**
   * The Command's Usage
   *
   * @var string
   */
  protected $usage = 'ledger:recalculate';

  /**
   * The Command's Arguments
   *
   * @var array
   */
  protected $arguments = [];

  /**
   * The Command's Options
   *
   * @var array
   */
  protected $options = [];

  /**
   * Actually execute a command.
   *
   * @param array $params
   */
  public function run(array $params)
  {
    CLI::write('Starting Ledger Balance Recalculation...', 'yellow');

    $ledgerService     = new LedgerService();
    $accountModel      = new AccountModel();
    $cashCustomerModel = new CashCustomerModel();

    // 1. Recalculate Accounts
    $accounts = $accountModel->findAll(); // Service usually applies filter, but CLI might need to handle all companies or specific.
    // NOTE: AccountModel applies company filter based on session. CLI has no session.
    // We need to bypass filter or iterate all companies.
    // Models usually return everything if no filter applied? 
    // Our BaseModel applies filter if NOT Super Admin. CLI session is empty, so it might default to no filter or fail?
    // Let's check BaseModel logic.
    // "Apply filter if NOT super admin and company_id is present"
    // In CLI, company_id is null. So it returns ALL records across ALL companies.
    // This is correct for a system-wide maintenance command.

    $totalAccounts = count($accounts);
    CLI::write("Found {$totalAccounts} Accounts.", 'green');

    if ($totalAccounts > 0) {
      $steps = $totalAccounts;
      $curr  = 0;

      foreach ($accounts as $account) {
        $curr++;
        CLI::showProgress($curr, $steps);

        try {
          // We need to ensure we don't mix companies query-wise if service relies on session
          // LedgerService doesn't rely on session for recalculation, it takes ID.
          // BUT LedgerEntryModel applies filter! 
          // If session is empty, LedgerEntryModel might return all entries mixed? 
          // No, "Apply filter if ... company_id is present". If not present, it doesn't filter.
          // So getLedgerForAccount($id) will return entries for that account ID. 
          // Since IDs are unique (AI), this is safe even without company filter.

          $ledgerService->recalculateRunningBalance($account['id'], 'Account');
        } catch (\Exception $e) {
          CLI::write("Error processing Account ID {$account['id']}: " . $e->getMessage(), 'red');
        }
      }
      CLI::showProgress(false); // End progress bar
    }

    CLI::newLine();

    // 2. Recalculate Cash Customers
    $cashCustomers = $cashCustomerModel->findAll();
    $totalCash     = count($cashCustomers);
    CLI::write("Found {$totalCash} Cash Customers.", 'green');

    if ($totalCash > 0) {
      $steps = $totalCash;
      $curr  = 0;

      foreach ($cashCustomers as $customer) {
        $curr++;
        CLI::showProgress($curr, $steps);

        try {
          $ledgerService->recalculateRunningBalance($customer['id'], 'Cash');
        } catch (\Exception $e) {
          CLI::write("Error processing Cash Customer ID {$customer['id']}: " . $e->getMessage(), 'red');
        }
      }
      CLI::showProgress(false);
    }

    CLI::newLine();
    CLI::write('Balance Recalculation Completed!', 'green');
  }
}

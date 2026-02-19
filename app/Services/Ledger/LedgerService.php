<?php

namespace App\Services\Ledger;

use App\Models\LedgerEntryModel;
use App\Models\AccountModel;
use App\Models\CashCustomerModel;
use CodeIgniter\I18n\Time;
use Exception;

class LedgerService
{
  protected $ledgerEntryModel;
  protected $accountModel;
  protected $cashCustomerModel;
  protected $db;

  public function __construct()
  {
    $this->ledgerEntryModel  = new LedgerEntryModel();
    $this->accountModel      = new AccountModel();
    $this->cashCustomerModel = new CashCustomerModel();
    $this->db                = \Config\Database::connect();
  }

  /**
   * Create a ledger entry for an invoice.
   * 
   * @param int $invoiceId
   * @param array $invoiceData
   * @return int Entry ID
   * @throws Exception
   */
  public function createInvoiceLedgerEntry(int $invoiceId, array $invoiceData): int
  {
    try {
      $companyId   = $invoiceData['company_id'];
      $invoiceDate = $invoiceData['invoice_date'];
      $grandTotal  = (float)$invoiceData['grand_total'];
      $invoiceNum  = $invoiceData['invoice_number'] ?? 'INV-' . $invoiceId;

      // Identify Customer
      $accountId      = $invoiceData['account_id'] ?? null;
      $cashCustomerId = $invoiceData['cash_customer_id'] ?? null;
      $customerType   = $accountId ? 'Account' : ($cashCustomerId ? 'Cash' : null);

      if (!$customerType) {
        throw new Exception("Invoice must be linked to an Account or Cash Customer.");
      }

      // Calculate new balance
      $currentBalance = 0.00;
      if ($customerType === 'Account') {
        $account = $this->accountModel->find($accountId);
        if (!$account) throw new Exception("Account not found: $accountId");
        $currentBalance = (float)$account['current_balance'];
      } else {
        $customer = $this->cashCustomerModel->find($cashCustomerId);
        // Cash customers might not have current_balance if new, assuming 0
        $currentBalance = (float)($customer['current_balance'] ?? 0.00);
      }

      // Update Balance: Debit increases balance (Receivable)
      // Balance = Previous + Debit - Credit
      $newBalance = $currentBalance + $grandTotal;

      // Prepare Ledger Data
      $ledgerData = [
        'company_id'       => $companyId,
        'account_id'       => $accountId,
        'cash_customer_id' => $cashCustomerId,
        'entry_date'       => $invoiceDate,
        'reference_type'   => 'invoice',
        'reference_id'     => $invoiceId,
        'reference_number' => $invoiceNum,
        'description'      => "Invoice Generated: $invoiceNum",
        'debit_amount'     => $grandTotal,
        'credit_amount'    => 0.00,
        'balance_after'    => $newBalance,
        'created_at'       => date('Y-m-d H:i:s')
      ];

      // Insert Ledger Entry
      if (!$this->ledgerEntryModel->insert($ledgerData)) {
        $errors = implode(', ', $this->ledgerEntryModel->errors());
        throw new Exception("Failed to insert ledger entry: $errors");
      }
      $entryId = $this->ledgerEntryModel->getInsertID();

      // Update Customer Current Balance
      if ($customerType === 'Account') {
        $this->accountModel->update($accountId, ['current_balance' => $newBalance]);
      } else {
        $this->cashCustomerModel->update($cashCustomerId, ['current_balance' => $newBalance]);
      }

      return $entryId;
    } catch (Exception $e) {
      log_message('error', '[LedgerService::createInvoiceLedgerEntry] ' . $e->getMessage());
      throw $e;
    }
  }

  /**
   * Create a ledger entry for a payment.
   * 
   * @param int $paymentId
   * @param array $paymentData
   * @return int Entry ID
   * @throws Exception
   */
  public function createPaymentLedgerEntry(int $paymentId, array $paymentData): int
  {
    $this->db->transStart();

    try {
      $companyId     = $paymentData['company_id'];
      $paymentDate   = $paymentData['payment_date'];
      $paymentAmount = (float)$paymentData['payment_amount'];
      $paymentNum    = $paymentData['payment_number'] ?? 'PAY-' . $paymentId;

      // Identify Customer
      $accountId      = $paymentData['account_id'] ?? null;
      $cashCustomerId = $paymentData['cash_customer_id'] ?? null;
      $customerType   = $paymentData['customer_type'] ?? ($accountId ? 'Account' : 'Cash');

      if (!$accountId && !$cashCustomerId) {
        // Try to fallback if not explicitly provided but implied
        // (Ideally validation should catch this upstream)
        throw new Exception("Payment must be linked to an Account or Cash Customer.");
      }

      // Calculate new balance
      $currentBalance = 0.00;
      if ($customerType === 'Account') {
        $account = $this->accountModel->find($accountId);
        if (!$account) throw new Exception("Account not found: $accountId");
        $currentBalance = (float)$account['current_balance'];
      } else {
        $customer = $this->cashCustomerModel->find($cashCustomerId);
        $currentBalance = (float)($customer['current_balance'] ?? 0.00);
      }

      // Update Balance: Credit decreases balance (Payment Received)
      // Balance = Previous + Debit - Credit
      $newBalance = $currentBalance - $paymentAmount;

      // Prepare Ledger Data
      $ledgerData = [
        'company_id'       => $companyId,
        'account_id'       => $accountId,
        'cash_customer_id' => $cashCustomerId,
        'entry_date'       => $paymentDate,
        'reference_type'   => 'payment',
        'reference_id'     => $paymentId,
        'reference_number' => $paymentNum,
        'description'      => "Payment Received: $paymentNum",
        'debit_amount'     => 0.00,
        'credit_amount'    => $paymentAmount,
        'balance_after'    => $newBalance,
        'created_at'       => date('Y-m-d H:i:s')
      ];

      // Insert Ledger Entry
      if (!$this->ledgerEntryModel->insert($ledgerData)) {
        $errors = implode(', ', $this->ledgerEntryModel->errors());
        throw new Exception("Failed to insert payment ledger entry: $errors");
      }
      $entryId = $this->ledgerEntryModel->getInsertID();

      // Update Customer Current Balance
      if ($customerType === 'Account') {
        $this->accountModel->update($accountId, ['current_balance' => $newBalance]);
      } else {
        $this->cashCustomerModel->update($cashCustomerId, ['current_balance' => $newBalance]);
      }

      $this->db->transComplete();

      if ($this->db->transStatus() === false) {
        throw new Exception("Transaction failed while creating payment ledger entry.");
      }

      return $entryId;
    } catch (Exception $e) {
      $this->db->transRollback();
      log_message('error', '[LedgerService::createPaymentLedgerEntry] ' . $e->getMessage());
      throw $e;
    }
  }

  /**
   * Create an opening balance ledger entry.
   * 
   * @param int $customerId
   * @param string $customerType 'Account' or 'Cash'
   * @param float $amount
   * @param string $type 'Debit' or 'Credit'
   * @return int Entry ID
   * @throws Exception
   */
  public function createOpeningBalanceLedgerEntry(int $customerId, string $customerType, float $amount, string $type): int
  {
    $this->db->transStart();

    try {
      // Get Company ID from session context or user context?
      // Since this is usually an admin action, we take from session
      $companyId = session()->get('company_id');
      if (!$companyId) {
        throw new Exception("Company context missing for opening balance.");
      }

      $debit  = ($type === 'Debit') ? $amount : 0.00;
      $credit = ($type === 'Credit') ? $amount : 0.00;

      // Since it's opening balance, it sets the initial state or adjusts it. 
      // BUT, if we are creating it later, it affects running balance.
      // Assumption: This is created when creating the customer.
      // Balance = Debit - Credit.

      // However, to be safe and consistent with "running balance" logic, we should probably 
      // fetch current balance if customer exists? 
      // Usually Opening Balance is the FIRST entry.
      // Let's assume balance_after is simply (Debit - Credit) for the first entry.

      // But if we add opening balance to existing customer?
      // We should treat it like a transaction that updates the balance.

      $currentBalance = 0.00;
      // If strictly opening balance, we might overwrite? No, let's treat as additive to be safe against data corruption.
      // But logic says: "Opening Balance calculated as balance before from date".
      // Here we are inserting a record.

      // Let's fetch current record to be sure
      if ($customerType === 'Account') {
        $customer = $this->accountModel->find($customerId);
        $currentBalance = (float)$customer['current_balance'];
        $accountId = $customerId;
        $cashCustomerId = null;
      } else {
        $customer = $this->cashCustomerModel->find($customerId);
        $currentBalance = (float)($customer['current_balance'] ?? 0.00);
        $accountId = null;
        $cashCustomerId = $customerId;
      }

      // If Type is Debit, customer owes us -> Balance increases (Positive)
      // If Type is Credit, we owe customer -> Balance decreases (Negative, or less Positive)
      // Wait, Standard Accounting: 
      // Receivable (Asset): Debit increases, Credit decreases.
      // So if Opening Balance is Debit $1000, Balance = +1000.
      // If Credit $500, Balance = -500.

      // Logic: Balance = Previous + Debit - Credit
      $newBalance = $currentBalance + $debit - $credit;

      $ledgerData = [
        'company_id'       => $companyId,
        'account_id'       => $accountId,
        'cash_customer_id' => $cashCustomerId,
        'entry_date'       => date('Y-m-d'), // Today or specified? Usually today for entry, but effectively effective 'start'.
        'reference_type'   => 'opening_balance',
        'reference_id'     => null, // Self-referential or null
        'reference_number' => 'OP-BAL',
        'description'      => "Opening Balance ($type)",
        'debit_amount'     => $debit,
        'credit_amount'    => $credit,
        'balance_after'    => $newBalance,
        'created_at'       => date('Y-m-d H:i:s')
      ];

      if (!$this->ledgerEntryModel->insert($ledgerData)) {
        $errors = implode(', ', $this->ledgerEntryModel->errors());
        throw new Exception("Failed to insert opening balance entry: $errors");
      }
      $entryId = $this->ledgerEntryModel->getInsertID();

      // Update Customer
      if ($customerType === 'Account') {
        $this->accountModel->update($accountId, ['current_balance' => $newBalance]);
      } else {
        $this->cashCustomerModel->update($cashCustomerId, ['current_balance' => $newBalance]);
      }

      $this->db->transComplete();

      if ($this->db->transStatus() === false) {
        throw new Exception("Transaction failed for opening balance.");
      }

      return $entryId;
    } catch (Exception $e) {
      $this->db->transRollback();
      log_message('error', '[LedgerService::createOpeningBalanceLedgerEntry] ' . $e->getMessage());
      throw $e;
    }
  }

  /**
   * Recalculate running balance for a customer.
   * Useful for data consistency checks or backdated entry insertions.
   * 
   * @param int $customerId
   * @param string $customerType 'Account' or 'Cash'
   * @return void
   * @throws Exception
   */
  public function recalculateRunningBalance(int $customerId, string $customerType): void
  {
    $this->db->transStart();

    try {
      // 1. Fetch all entries ordered by date ASC, id ASC
      $entries = [];
      if ($customerType === 'Account') {
        $entries = $this->ledgerEntryModel->getLedgerForAccount($customerId);
      } else {
        $entries = $this->ledgerEntryModel->getLedgerForCashCustomer($customerId);
      }

      $runningBalance = 0.00;

      // 2. Iterate and update
      foreach ($entries as $entry) {
        $debit  = (float)$entry['debit_amount'];
        $credit = (float)$entry['credit_amount'];

        // Balance logic: Previous + Debit - Credit
        $runningBalance = $runningBalance + $debit - $credit;

        // Update row ONLY if balance is different (to save queries? or just update all)
        // To avoid floating point comparison issues, assume update needed.
        // We use builder update to bypass model overhead if needed, but model is fine here for safety.
        // WE MUST NOT change created_at or other fields.

        $this->ledgerEntryModel->update($entry['id'], [
          'balance_after' => $runningBalance
        ]);
      }

      // 3. Update Parent Customer Record
      if ($customerType === 'Account') {
        $this->accountModel->update($customerId, ['current_balance' => $runningBalance]);
      } else {
        // Ensure column exists for cash customers
        $this->cashCustomerModel->update($customerId, ['current_balance' => $runningBalance]);
      }

      $this->db->transComplete();

      if ($this->db->transStatus() === false) {
        throw new Exception("Transaction failed during balance recalculation.");
      }
    } catch (Exception $e) {
      $this->db->transRollback();
      log_message('error', '[LedgerService::recalculateRunningBalance] ' . $e->getMessage());
      throw $e;
    }
  }
}

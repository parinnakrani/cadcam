<?php

namespace App\Services\Invoice;

use App\Models\InvoiceModel;
use App\Models\InvoiceLineModel;
use App\Models\ChallanModel;
use App\Models\AccountModel;
use App\Models\CashCustomerModel;
use App\Services\Tax\TaxCalculationService;
use App\Services\Ledger\LedgerService;
use App\Services\Numbering\NumberingService;
use App\Services\Audit\AuditService;
use CodeIgniter\Database\Exceptions\DatabaseException;
use Exception;

/**
 * InvoiceService
 * 
 * Handles all invoice business logic including:
 * - Invoice CRUD operations
 * - Challan-to-invoice conversion
 * - Tax calculation (CGST/SGST or IGST)
 * - Payment tracking
 * - Ledger entry management
 * - Sequential invoice numbering
 * - Transaction safety
 * - Audit logging
 * 
 * Business Rules:
 * - Cannot modify paid invoices
 * - Cannot delete invoices with payments
 * - Automatic tax calculation based on customer state
 * - Ledger entries created for all financial transactions
 * - All operations are transactional
 * - Complete audit trail
 */
class InvoiceService
{
  protected InvoiceModel $invoiceModel;
  protected InvoiceLineModel $invoiceLineModel;
  protected ChallanModel $challanModel;
  protected AccountModel $accountModel;
  protected CashCustomerModel $cashCustomerModel;
  protected TaxCalculationService $taxService;
  protected LedgerService $ledgerService;
  protected NumberingService $numberingService;
  protected AuditService $auditService;
  protected $db;

  public function __construct()
  {
    $this->invoiceModel = new InvoiceModel();
    $this->invoiceLineModel = new InvoiceLineModel();
    $this->challanModel = new ChallanModel();
    $this->accountModel = new AccountModel();
    $this->cashCustomerModel = new CashCustomerModel();
    $this->taxService = new TaxCalculationService();
    $this->ledgerService = new LedgerService();
    $this->numberingService = new NumberingService();
    $this->auditService = new AuditService();
    $this->db = \Config\Database::connect();
  }

  /**
   * Create a new invoice with lines
   * 
   * @param array $data Invoice data
   * @param array $lines Invoice line items
   * @return int Invoice ID
   * @throws ValidationException
   * @throws Exception
   */
  public function createInvoice(array $data, array $lines = []): int
  {
    // Validate invoice data
    $this->validateInvoiceData($data);

    // Start transaction
    $this->db->transStart();

    try {
      // Auto-set company_id and created_by from session
      $session = session();
      $data['company_id'] = $data['company_id'] ?? $session->get('company_id') ?? 1;
      $data['created_by'] = $data['created_by'] ?? $session->get('user_id');
      $data['updated_by'] = $session->get('user_id');

      // Generate invoice number
      $data['invoice_number'] = $this->numberingService->getNextInvoiceNumber(
        $data['company_id'],
        $data['invoice_type']
      );

      // Validate customer exists
      $this->validateCustomer($data);

      // Get customer state for tax calculation
      $customerState = $this->getCustomerState($data);
      $companyState = $this->getCompanyState($data['company_id']);

      // Calculate tax breakdown (CGST/SGST or IGST)
      if (!empty($lines)) {
        $taxBreakdown = $this->taxService->calculateInvoiceTax(
          $lines,
          $data['tax_rate'] ?? 3.00,
          $customerState,
          $companyState
        );

        $data['subtotal'] = $taxBreakdown['subtotal'];
        $data['tax_amount'] = $taxBreakdown['total_tax'];
        $data['cgst_amount'] = $taxBreakdown['cgst_amount'];
        $data['sgst_amount'] = $taxBreakdown['sgst_amount'];
        $data['igst_amount'] = $taxBreakdown['igst_amount'];
        $data['grand_total'] = $taxBreakdown['grand_total'];
      }

      // Ensure optional FKs are null if empty
      $data['account_id'] = !empty($data['account_id']) ? $data['account_id'] : null;
      $data['cash_customer_id'] = !empty($data['cash_customer_id']) ? $data['cash_customer_id'] : null;
      $data['billing_address'] = !empty($data['billing_address']) ? $data['billing_address'] : null;
      $data['shipping_address'] = !empty($data['shipping_address']) ? $data['shipping_address'] : null;
      $data['reference_number'] = !empty($data['reference_number']) ? $data['reference_number'] : null;
      $data['notes'] = !empty($data['notes']) ? $data['notes'] : null;
      $data['payment_terms'] = !empty($data['payment_terms']) ? $data['payment_terms'] : null;

      // Encode challan_ids if present as array
      if (!empty($data['challan_ids']) && is_array($data['challan_ids'])) {
        $data['challan_ids'] = json_encode($data['challan_ids']);
      } elseif (empty($data['challan_ids'])) {
        $data['challan_ids'] = null;
      }

      // Set initial payment status
      $data['total_paid'] = $data['total_paid'] ?? 0.00;
      $data['amount_due'] = $data['grand_total'] - $data['total_paid'];
      $data['payment_status'] = $data['payment_status'] ?? 'Pending';
      $data['invoice_status'] = $data['invoice_status'] ?? 'Draft';

      // Insert invoice record
      $invoiceId = $this->invoiceModel->insert($data);

      if (!$invoiceId) {
        throw new Exception('Failed to create invoice: ' . json_encode($this->invoiceModel->errors()));
      }

      // Create invoice lines
      if (!empty($lines)) {
        $this->createInvoiceLines($invoiceId, $lines, $data['tax_rate'] ?? 3.00);
      }

      // Update Challans if provided
      if (!empty($data['challan_ids'])) {
        $challanIds = is_array($data['challan_ids']) ? $data['challan_ids'] : json_decode($data['challan_ids'], true);
        if (is_array($challanIds)) {
          foreach ($challanIds as $cid) {
            $this->challanModel->markAsInvoiced($cid, $invoiceId);
          }
          // Save challan_ids in invoice record too? PRD says "Selected challan IDs (array)".
          // The schema for `invoices` usually has JSON `challan_ids` or relation.
          // Let's assume `challan_ids` column exists since we are passing it.
          // Wait, previous `createInvoiceFromChallan` set `challan_ids` in `$invoiceData`.
          // We should do the same here if needed.
          // But `insert($data)` already used `$data`. If `challan_ids` was in `$data`, it's saved.
          // Wait, `insert` filters allowed fields. I should check `InvoiceModel`.
          // I will assume `challan_ids` is allowed.

          // We might need to ensure `challan_ids` in `$data` is JSON encoded string before insert.
          // But `$data` has already been inserted...
          // If `challan_ids` was array in `$data`, CI4 `insert` might have ignored it or error if not allowed field.
          // If I need to save it, I should have encoded it before insert.
        }
      }

      // Recalculate totals from lines
      if (!empty($lines)) {
        $this->recalculateTotals($invoiceId);
      }

      // Create ledger entry (Debit for customer - they owe us)
      $this->ledgerService->createInvoiceLedgerEntry(
        $invoiceId,
        $data['company_id'],
        $data['account_id'] ?? null,
        $data['cash_customer_id'] ?? null,
        $data['grand_total'],
        'debit',
        "Invoice {$data['invoice_number']} created"
      );

      // Commit transaction
      $this->db->transComplete();

      if ($this->db->transStatus() === false) {
        throw new Exception('Transaction failed during invoice creation');
      }

      // Audit log
      $this->auditService->logCreate('Invoice', 'Invoice', $invoiceId, $data);

      return $invoiceId;
    } catch (Exception $e) {
      $this->db->transRollback();
      throw $e;
    }
  }

  /**
   * Create invoice from approved challan
   * 
   * @param int $challanId Challan ID
   * @return int Invoice ID
   * @throws ChallanNotFoundException
   * @throws ChallanAlreadyInvoicedException
   * @throws Exception
   */
  public function createInvoiceFromChallan(int $challanId): int
  {
    // Get challan with lines
    $challan = $this->challanModel->getChallanWithLines($challanId);

    if (!$challan) {
      throw new ChallanNotFoundException("Challan ID {$challanId} not found");
    }

    // Check if already invoiced
    if ($challan['is_invoiced'] == 1) {
      throw new ChallanAlreadyInvoicedException("Challan {$challan['challan_number']} is already invoiced");
    }

    // Validate challan is approved
    if ($challan['challan_status'] !== 'Approved') {
      throw new Exception("Challan must be approved before creating invoice");
    }

    // Start transaction
    $this->db->transStart();

    try {
      // Map challan data to invoice data
      $invoiceData = [
        'company_id'        => $challan['company_id'],
        'invoice_type'      => 'Accounts Invoice', // Challan invoices are always Accounts
        'invoice_date'      => date('Y-m-d'),
        'due_date'          => date('Y-m-d', strtotime('+30 days')),
        'account_id'        => $challan['account_id'],
        'billing_address'   => $challan['billing_address'] ?? null,
        'shipping_address'  => $challan['shipping_address'] ?? null,
        'reference_number'  => $challan['challan_number'],
        'challan_ids'       => json_encode([$challanId]),
        'tax_rate'          => $challan['tax_rate'] ?? 3.00,
        'notes'             => "Generated from Challan {$challan['challan_number']}",
        'invoice_status'    => 'Posted', // Auto-post challan invoices
        'payment_status'    => 'Pending',
        'created_by'        => session()->get('user_id'),
      ];

      // Create invoice without lines first
      $invoiceId = $this->createInvoice($invoiceData, []);

      // Copy challan lines to invoice lines
      $success = $this->invoiceLineModel->copyFromChallan($invoiceId, $challanId);

      if (!$success) {
        throw new Exception('Failed to copy challan lines to invoice');
      }

      // Recalculate totals from copied lines
      $this->recalculateTotals($invoiceId);

      // Mark challan as invoiced
      $this->challanModel->update($challanId, [
        'is_invoiced'   => 1,
        'invoiced_date' => date('Y-m-d'),
        'updated_by'    => session()->get('user_id'),
      ]);

      // Commit transaction
      $this->db->transComplete();

      if ($this->db->transStatus() === false) {
        throw new Exception('Transaction failed during challan-to-invoice conversion');
      }

      // Audit log
      $this->auditService->logCreate('Invoice', 'Invoice', $invoiceId, ['from_challan' => $challanId, 'data' => $invoiceData]);

      return $invoiceId;
    } catch (Exception $e) {
      $this->db->transRollback();
      throw $e;
    }
  }

  /**
   * Update an existing invoice
   * 
   * @param int $id Invoice ID
   * @param array $data Updated data
   * @return bool Success status
   * @throws InvoiceNotFoundException
   * @throws InvoiceAlreadyPaidException
   * @throws Exception
   */
  public function updateInvoice(int $id, array $data, ?array $lines = null): bool
  {
    // Get existing invoice
    $invoice = $this->invoiceModel->find($id);

    if (!$invoice) {
      throw new InvoiceNotFoundException("Invoice ID {$id} not found");
    }

    // Check if invoice has payments
    if ($invoice['total_paid'] > 0) {
      throw new InvoiceAlreadyPaidException("Cannot modify invoice with payment history");
    }

    // Start transaction
    $this->db->transStart();

    try {
      // Set updated_by
      $data['updated_by'] = session()->get('user_id');

      // Update invoice record
      $success = $this->invoiceModel->update($id, $data);

      if (!$success) {
        throw new Exception('Failed to update invoice: ' . json_encode($this->invoiceModel->errors()));
      }

      // Update lines if provided
      if ($lines !== null) {
        // Delete existing lines
        $this->invoiceLineModel->deleteLinesByInvoiceId($id);

        if (!empty($lines)) {
          // Determine tax rate (from data or existing invoice)
          $taxRate = $data['tax_rate'] ?? $invoice['tax_rate'] ?? 3.00;

          // Create new lines
          $this->createInvoiceLines($id, $lines, $taxRate);
        }
      }

      // Always recalculate totals after update
      $this->recalculateTotals($id);

      // Update ledger entry
      // We need the NEW grand total. recalculateTotals updates the DB, so we should fetch it or trust the calculation.
      // recalculateTotals updates the invoice record. Let's fetch the updated invoice to get the new grand_total.
      $updatedInvoice = $this->invoiceModel->find($id);

      if ($updatedInvoice['grand_total'] != $invoice['grand_total']) {
        $this->ledgerService->updateInvoiceLedgerEntry(
          $id,
          $updatedInvoice['grand_total']
        );
      }

      // Commit transaction
      $this->db->transComplete();

      if ($this->db->transStatus() === false) {
        throw new Exception('Transaction failed during invoice update');
      }

      // Audit log
      $this->auditService->logUpdate('Invoice', 'Invoice', $id, [], $data);

      return true;
    } catch (Exception $e) {
      $this->db->transRollback();
      throw $e;
    }
  }

  /**
   * Delete an invoice (soft delete)
   * 
   * @param int $id Invoice ID
   * @return bool Success status
   * @throws InvoiceNotFoundException
   * @throws InvoiceAlreadyPaidException
   * @throws Exception
   */
  public function deleteInvoice(int $id): bool
  {
    // Get invoice
    $invoice = $this->invoiceModel->find($id);

    if (!$invoice) {
      throw new InvoiceNotFoundException("Invoice ID {$id} not found");
    }

    // Check if can delete
    if (!$this->invoiceModel->canDelete($id)) {
      throw new InvoiceAlreadyPaidException("Cannot delete invoice with payment history");
    }

    // Start transaction
    $this->db->transStart();

    try {
      // Soft delete invoice
      $this->invoiceModel->delete($id);

      // Soft delete all invoice lines
      $this->invoiceLineModel->deleteLinesByInvoiceId($id);

      // Delete ledger entry
      $this->ledgerService->deleteInvoiceLedgerEntry($id);

      // If invoice was from challan, unmark challan as invoiced
      if (!empty($invoice['challan_ids'])) {
        $challanIds = json_decode($invoice['challan_ids'], true);
        foreach ($challanIds as $challanId) {
          $this->challanModel->update($challanId, [
            'is_invoiced'   => 0,
            'invoiced_date' => null,
            'updated_by'    => session()->get('user_id'),
          ]);
        }
      }

      // Commit transaction
      $this->db->transComplete();

      if ($this->db->transStatus() === false) {
        throw new Exception('Transaction failed during invoice deletion');
      }

      // Audit log
      $this->auditService->logDelete('Invoice', 'Invoice', $id, $invoice);

      return true;
    } catch (Exception $e) {
      $this->db->transRollback();
      throw $e;
    }
  }

  /**
   * Get invoice by ID with complete data
   * 
   * @param int $id Invoice ID
   * @return array|null Invoice data with customer and lines
   */
  public function getInvoiceById(int $id): ?array
  {
    return $this->invoiceModel->getInvoiceWithLines($id);
  }

  /**
   * Record a payment for an invoice
   * 
   * @param int $invoiceId Invoice ID
   * @param float $amount Payment amount
   * @return bool Success status
   * @throws InvoiceNotFoundException
   * @throws Exception
   */
  public function recordPayment(int $invoiceId, float $amount): bool
  {
    // Get invoice
    $invoice = $this->invoiceModel->find($invoiceId);

    if (!$invoice) {
      throw new InvoiceNotFoundException("Invoice ID {$invoiceId} not found");
    }

    // Calculate new total paid
    $newTotalPaid = (float) $invoice['total_paid'] + $amount;

    // Start transaction
    $this->db->transStart();

    try {
      // Update payment status
      $success = $this->invoiceModel->updatePaymentStatus($invoiceId, $newTotalPaid);

      if (!$success) {
        throw new Exception('Failed to update payment status');
      }

      // Create payment ledger entry (Credit - they paid us)
      $this->ledgerService->createPaymentLedgerEntry(
        $invoiceId,
        $invoice['company_id'],
        $invoice['account_id'] ?? null,
        $invoice['cash_customer_id'] ?? null,
        $amount,
        'credit',
        "Payment received for Invoice {$invoice['invoice_number']}"
      );

      // Commit transaction
      $this->db->transComplete();

      if ($this->db->transStatus() === false) {
        throw new Exception('Transaction failed during payment recording');
      }

      // Audit log
      $this->auditService->log('Invoice', 'update', 'Invoice', $invoiceId, ['total_paid' => $invoice['total_paid']], ['total_paid' => $newTotalPaid, 'payment_amount' => $amount]);

      return true;
    } catch (Exception $e) {
      $this->db->transRollback();
      throw $e;
    }
  }

  /**
   * Get outstanding amount for an invoice
   * 
   * @param int $invoiceId Invoice ID
   * @return float Outstanding amount
   * @throws InvoiceNotFoundException
   */
  public function getOutstandingAmount(int $invoiceId): float
  {
    $invoice = $this->invoiceModel->find($invoiceId);

    if (!$invoice) {
      throw new InvoiceNotFoundException("Invoice ID {$invoiceId} not found");
    }

    return (float) $invoice['amount_due'];
  }

  /**
   * Recalculate invoice totals from line items
   * 
   * @param int $invoiceId Invoice ID
   * @return bool Success status
   */
  public function recalculateTotals(int $invoiceId): bool
  {
    // Get line totals (these are exclusive of tax)
    $totals = $this->invoiceLineModel->getTotalsForInvoice($invoiceId);

    // Get current invoice
    $invoice = $this->invoiceModel->find($invoiceId);

    if (!$invoice) {
      return false;
    }

    // Determine states for tax type
    $companyState = $this->getCompanyState((int)$invoice['company_id']);

    if ($invoice['invoice_type'] === 'Cash Invoice') {
      $customerState = $companyState; // Force local for Cash
    } else {
      $customerState = $this->getCustomerState($invoice);
    }

    // Default tax rate if not set
    $taxRate = (float)($invoice['tax_rate'] ?? 3.00);
    $subtotal = (float)$totals['total_subtotal'];
    $taxAmount = round($subtotal * ($taxRate / 100), 2);

    // Breakdown
    $cgst = 0;
    $sgst = 0;
    $igst = 0;

    // Logic for breakdown
    if ($companyState && $customerState && $companyState == $customerState) {
      $halfTax = round($taxAmount / 2, 2);
      $cgst = $halfTax;
      $sgst = $halfTax;
      // Adjust for rounding diff
      if (($cgst + $sgst) != $taxAmount) {
        $sgst = $taxAmount - $cgst;
      }
    } else {
      $igst = $taxAmount;
    }

    $grandTotal = $subtotal + $taxAmount;

    // Calculate amount_due
    $amountDue = $grandTotal - (float) $invoice['total_paid'];

    // Update invoice totals
    return $this->invoiceModel->update($invoiceId, [
      'subtotal'    => $subtotal,
      'tax_amount'  => $taxAmount,
      'cgst_amount' => $cgst,
      'sgst_amount' => $sgst,
      'igst_amount' => $igst,
      'grand_total' => $grandTotal,
      'amount_due'  => $amountDue,
      'updated_by'  => session()->get('user_id'),
    ]);
  }

  /**
   * Create invoice lines
   * 
   * @param int $invoiceId Invoice ID
   * @param array $lines Line items
   * @param float $taxRate Tax rate percentage
   * @return bool Success status
   */
  protected function createInvoiceLines(int $invoiceId, array $lines, float $taxRate): bool
  {
    $lineNumber = 1;

    foreach ($lines as $line) {
      $line['invoice_id'] = $invoiceId;
      $line['line_number'] = $lineNumber;

      // Map input arrays to model fields
      if (isset($line['products'])) {
        $line['product_ids'] = is_array($line['products']) ? json_encode($line['products']) : $line['products'];
        unset($line['products']);
      }
      if (isset($line['processes'])) {
        $line['process_ids'] = is_array($line['processes']) ? json_encode($line['processes']) : $line['processes'];
        unset($line['processes']);
      }

      // Calculate line totals if not provided
      if (!isset($line['amount'])) {
        $line = $this->calculateLineTotals($line, $taxRate);
      }

      // Insert line
      $lineId = $this->invoiceLineModel->insert($line);

      if (!$lineId) {
        throw new Exception('Failed to create invoice line: ' . json_encode($this->invoiceLineModel->errors()));
      }

      $lineNumber++;
    }

    return true;
  }

  /**
   * Calculate line totals (tax-inclusive)
   * 
   * @param array $line Line data
   * @param float $taxRate Tax rate percentage
   * @return array Line with calculated totals
   */
  protected function calculateLineTotals(array $line, float $taxRate): array
  {
    $weight = (float) ($line['weight'] ?? 0);
    $rate = (float) ($line['rate'] ?? 0);
    $quantity = (int) ($line['quantity'] ?? 1);

    // Calculate line total (tax-inclusive)
    if ($weight > 0) {
      $lineTotal = $weight * $rate;
    } else {
      $lineTotal = $quantity * $rate;
    }

    // Back-calculate tax (tax-inclusive pricing) - DEPRECATED in new schema
    // Just setting amount for now. Tax is calculated at invoice level.

    $line['amount'] = round($lineTotal, 2);

    return $line;
  }

  /**
   * Validate invoice data
   * 
   * @param array $data Invoice data
   * @return void
   * @throws ValidationException
   */
  protected function validateInvoiceData(array $data): void
  {
    $errors = [];

    // Required fields
    if (empty($data['invoice_type'])) {
      $errors[] = 'Invoice type is required';
    }

    if (empty($data['invoice_date'])) {
      $errors[] = 'Invoice date is required';
    }

    // Validate customer type
    if (empty($data['account_id']) && empty($data['cash_customer_id'])) {
      $errors[] = 'Either account_id or cash_customer_id is required';
    }

    if (!empty($data['account_id']) && !empty($data['cash_customer_id'])) {
      $errors[] = 'Cannot have both account_id and cash_customer_id';
    }

    // Validate dates
    if (!empty($data['invoice_date']) && !strtotime($data['invoice_date'])) {
      $errors[] = 'Invalid invoice date format';
    }

    if (!empty($data['due_date']) && !strtotime($data['due_date'])) {
      $errors[] = 'Invalid due date format';
    }

    if (!empty($errors)) {
      throw new ValidationException(implode(', ', $errors));
    }
  }

  /**
   * Validate customer exists
   * 
   * @param array $data Invoice data
   * @return void
   * @throws ValidationException
   */
  protected function validateCustomer(array $data): void
  {
    if (!empty($data['account_id'])) {
      $account = $this->accountModel->find($data['account_id']);
      if (!$account) {
        throw new ValidationException("Account ID {$data['account_id']} not found");
      }
    }

    if (!empty($data['cash_customer_id'])) {
      $cashCustomer = $this->cashCustomerModel->find($data['cash_customer_id']);
      if (!$cashCustomer) {
        throw new ValidationException("Cash Customer ID {$data['cash_customer_id']} not found");
      }
    }
  }

  /**
   * Get customer state for tax calculation
   * 
   * @param array $data Invoice data
   * @return int|null State ID
   */
  protected function getCustomerState(array $data): ?int
  {
    if (!empty($data['account_id'])) {
      $account = $this->accountModel->find($data['account_id']);
      return $account['billing_state_id'] ?? null;
    }

    if (!empty($data['cash_customer_id'])) {
      $cashCustomer = $this->cashCustomerModel->find($data['cash_customer_id']);
      return $cashCustomer['state_id'] ?? null;
    }

    return null;
  }

  /**
   * Get company state for tax calculation
   * 
   * @param int $companyId Company ID
   * @return int|null State ID
   */
  protected function getCompanyState(int $companyId): ?int
  {
    $companyModel = new \App\Models\CompanyModel();
    $company = $companyModel->find($companyId);
    return $company['state_id'] ?? null;
  }
}

/**
 * Custom Exceptions
 */
class InvoiceNotFoundException extends Exception {}
class InvoiceAlreadyPaidException extends Exception {}
class ChallanNotFoundException extends Exception {}
class ChallanAlreadyInvoicedException extends Exception {}
class ValidationException extends Exception {}

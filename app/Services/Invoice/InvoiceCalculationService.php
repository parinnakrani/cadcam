<?php

namespace App\Services\Invoice;

use App\Services\Tax\TaxCalculationService;
use App\Models\InvoiceLineModel;
use App\Models\InvoiceModel;
use Exception;

/**
 * InvoiceCalculationService
 * 
 * Handles all invoice amount calculations including:
 * - Invoice totals from line items
 * - Line-level calculations
 * - Tax integration
 * - Payment balance calculations
 * - Discount handling (future)
 * 
 * Business Rules:
 * - Subtotal = Sum of all line subtotals
 * - Tax calculated via TaxCalculationService
 * - Grand Total = Subtotal + Tax
 * - Amount Due = Grand Total - Total Paid
 * - All amounts rounded to 2 decimal places
 */
class InvoiceCalculationService
{
  protected TaxCalculationService $taxService;
  protected InvoiceLineModel $invoiceLineModel;
  protected InvoiceModel $invoiceModel;

  public function __construct()
  {
    $this->taxService = new TaxCalculationService();
    $this->invoiceLineModel = new InvoiceLineModel();
    $this->invoiceModel = new InvoiceModel();
  }

  /**
   * Calculate invoice totals from line items
   * 
   * @param int $invoiceId Invoice ID
   * @param array $invoiceData Invoice data (for tax calculation)
   * @return array Complete totals breakdown
   */
  public function calculateInvoiceTotals(int $invoiceId, array $invoiceData): array
  {
    // Get all invoice lines
    $lines = $this->invoiceLineModel->getLinesByInvoiceId($invoiceId);

    if (empty($lines)) {
      return [
        'subtotal'      => 0.00,
        'tax_amount'    => 0.00,
        'cgst_amount'   => 0.00,
        'sgst_amount'   => 0.00,
        'igst_amount'   => 0.00,
        'discount'      => 0.00,
        'grand_total'   => 0.00,
        'total_paid'    => 0.00,
        'amount_due'    => 0.00,
      ];
    }

    // Get line totals
    $lineTotals = $this->invoiceLineModel->getTotalsForInvoice($invoiceId);

    // Get tax rate
    $taxRate = $invoiceData['tax_rate'] ?? $this->taxService->getTaxRate();

    // Get customer and company states for tax calculation
    $customerStateId = $this->getCustomerStateFromInvoiceData($invoiceData);
    $companyStateId = $this->getCompanyStateFromInvoiceData($invoiceData);

    // Calculate tax breakdown
    $taxBreakdown = $this->taxService->calculateInvoiceTax(
      $lines,
      $taxRate,
      $customerStateId,
      $companyStateId
    );

    // Calculate totals
    $subtotal = $lineTotals['total_subtotal'];
    $taxAmount = $taxBreakdown['total_tax'];
    $discount = $invoiceData['discount'] ?? 0.00;
    $grandTotal = $subtotal + $taxAmount - $discount;
    $totalPaid = $invoiceData['total_paid'] ?? 0.00;
    $amountDue = $grandTotal - $totalPaid;

    // Ensure amount_due is not negative
    if ($amountDue < 0) {
      $amountDue = 0.00;
    }

    return [
      'subtotal'      => round($subtotal, 2),
      'tax_type'      => $taxBreakdown['tax_type'],
      'tax_rate'      => $taxRate,
      'tax_amount'    => round($taxAmount, 2),
      'cgst_amount'   => round($taxBreakdown['cgst_amount'], 2),
      'sgst_amount'   => round($taxBreakdown['sgst_amount'], 2),
      'igst_amount'   => round($taxBreakdown['igst_amount'], 2),
      'discount'      => round($discount, 2),
      'grand_total'   => round($grandTotal, 2),
      'total_paid'    => round($totalPaid, 2),
      'amount_due'    => round($amountDue, 2),
    ];
  }

  /**
   * Calculate line total from line data
   * 
   * @param array $lineData Line item data
   * @param string $taxType Tax type: 'CGST_SGST' or 'IGST'
   * @param float $taxRate Tax rate percentage
   * @return array Line totals with tax breakdown
   */
  public function calculateLineTotal(array $lineData, string $taxType, float $taxRate): array
  {
    // Calculate line subtotal from processes
    $lineSubtotal = $this->calculateLineSubtotal($lineData);

    // Calculate tax for this line
    $lineTax = $this->taxService->calculateLineTax($lineSubtotal, $taxType, $taxRate);

    return [
      'line_subtotal'   => round($lineSubtotal, 2),
      'tax_type'        => $taxType,
      'cgst_amount'     => round($lineTax['cgst_amount'], 2),
      'sgst_amount'     => round($lineTax['sgst_amount'], 2),
      'igst_amount'     => round($lineTax['igst_amount'], 2),
      'line_tax_amount' => round($lineTax['line_tax_amount'], 2),
      'line_total'      => round($lineTax['line_total'], 2),
    ];
  }

  /**
   * Recalculate amount due after new payment
   * 
   * @param int $invoiceId Invoice ID
   * @param float $newPayment New payment amount
   * @return float New amount due
   */
  public function recalculateAmountDue(int $invoiceId, float $newPayment): float
  {
    // Get current invoice
    $invoice = $this->invoiceModel->find($invoiceId);

    if (!$invoice) {
      throw new Exception("Invoice ID {$invoiceId} not found");
    }

    $grandTotal = (float) $invoice['grand_total'];
    $currentPaid = (float) $invoice['total_paid'];
    $newTotalPaid = $currentPaid + $newPayment;

    // Calculate new amount due
    $amountDue = $grandTotal - $newTotalPaid;

    // Ensure amount_due is not negative
    if ($amountDue < 0) {
      $amountDue = 0.00;
    }

    return round($amountDue, 2);
  }

  /**
   * Calculate line subtotal from processes
   * 
   * Supports multiple calculation methods:
   * 1. Direct line_subtotal (if provided)
   * 2. Weight-based: weight × rate
   * 3. Quantity-based: quantity × unit_price
   * 4. Process-based: sum of process prices
   * 
   * @param array $lineData Line item data
   * @return float Line subtotal
   */
  protected function calculateLineSubtotal(array $lineData): float
  {
    // If line_subtotal is already provided, use it
    if (isset($lineData['line_subtotal']) && $lineData['line_subtotal'] > 0) {
      return (float) $lineData['line_subtotal'];
    }

    // Method 1: Weight-based calculation
    if (isset($lineData['weight']) && $lineData['weight'] > 0 && isset($lineData['rate'])) {
      $weight = (float) $lineData['weight'];
      $rate = (float) $lineData['rate'];
      return $weight * $rate;
    }

    // Method 2: Quantity-based calculation
    if (isset($lineData['quantity']) && isset($lineData['unit_price'])) {
      $quantity = (int) $lineData['quantity'];
      $unitPrice = (float) $lineData['unit_price'];
      return $quantity * $unitPrice;
    }

    // Method 3: Process-based calculation
    if (isset($lineData['process_prices']) && is_array($lineData['process_prices'])) {
      $total = 0.00;
      foreach ($lineData['process_prices'] as $process) {
        if (isset($process['rate'])) {
          $total += (float) $process['rate'];
        }
      }
      return $total;
    }

    // Default to 0 if no calculation method available
    return 0.00;
  }

  /**
   * Calculate payment balance
   * 
   * @param float $grandTotal Grand total amount
   * @param float $totalPaid Total amount paid
   * @return array Payment balance details
   */
  public function calculatePaymentBalance(float $grandTotal, float $totalPaid): array
  {
    $amountDue = $grandTotal - $totalPaid;

    // Ensure amount_due is not negative
    if ($amountDue < 0) {
      $amountDue = 0.00;
    }

    // Determine payment status
    if ($amountDue == 0) {
      $paymentStatus = 'Paid';
      $paymentPercentage = 100.00;
    } elseif ($totalPaid > 0) {
      $paymentStatus = 'Partial Paid';
      $paymentPercentage = ($totalPaid / $grandTotal) * 100;
    } else {
      $paymentStatus = 'Pending';
      $paymentPercentage = 0.00;
    }

    return [
      'grand_total'        => round($grandTotal, 2),
      'total_paid'         => round($totalPaid, 2),
      'amount_due'         => round($amountDue, 2),
      'payment_status'     => $paymentStatus,
      'payment_percentage' => round($paymentPercentage, 2),
    ];
  }

  /**
   * Calculate discount amount
   * 
   * Supports both percentage and fixed amount discounts
   * 
   * @param float $subtotal Subtotal amount
   * @param float $discountValue Discount value (percentage or amount)
   * @param string $discountType Discount type: 'percentage' or 'fixed'
   * @return float Discount amount
   */
  public function calculateDiscount(float $subtotal, float $discountValue, string $discountType = 'percentage'): float
  {
    if ($discountType === 'percentage') {
      // Percentage discount
      $discount = $subtotal * ($discountValue / 100);
    } else {
      // Fixed amount discount
      $discount = $discountValue;
    }

    // Ensure discount doesn't exceed subtotal
    if ($discount > $subtotal) {
      $discount = $subtotal;
    }

    return round($discount, 2);
  }

  /**
   * Calculate gold adjustment amount
   * 
   * @param float $originalGoldWeight Original gold weight in grams
   * @param float $adjustedGoldWeight Adjusted gold weight in grams
   * @param float $goldRatePerGram Gold rate per gram
   * @return array Gold adjustment details
   */
  public function calculateGoldAdjustment(
    float $originalGoldWeight,
    float $adjustedGoldWeight,
    float $goldRatePerGram
  ): array {
    $goldDifference = $adjustedGoldWeight - $originalGoldWeight;
    $adjustmentAmount = $goldDifference * $goldRatePerGram;

    return [
      'original_gold_weight'    => round($originalGoldWeight, 3),
      'adjusted_gold_weight'    => round($adjustedGoldWeight, 3),
      'gold_difference'         => round($goldDifference, 3),
      'gold_rate_per_gram'      => round($goldRatePerGram, 2),
      'gold_adjustment_amount'  => round($adjustmentAmount, 2),
    ];
  }

  /**
   * Validate invoice totals
   * 
   * Ensures all calculations are accurate
   * 
   * @param array $totals Invoice totals
   * @return bool True if valid
   * @throws Exception If validation fails
   */
  public function validateInvoiceTotals(array $totals): bool
  {
    $errors = [];

    // Check required fields
    $requiredFields = ['subtotal', 'tax_amount', 'grand_total', 'total_paid', 'amount_due'];
    foreach ($requiredFields as $field) {
      if (!isset($totals[$field])) {
        $errors[] = "Missing required field: {$field}";
      }
    }

    if (!empty($errors)) {
      throw new Exception('Validation failed: ' . implode(', ', $errors));
    }

    $subtotal = (float) $totals['subtotal'];
    $taxAmount = (float) $totals['tax_amount'];
    $discount = (float) ($totals['discount'] ?? 0.00);
    $grandTotal = (float) $totals['grand_total'];
    $totalPaid = (float) $totals['total_paid'];
    $amountDue = (float) $totals['amount_due'];

    // Validate grand total calculation
    $calculatedGrandTotal = $subtotal + $taxAmount - $discount;
    if (abs($grandTotal - $calculatedGrandTotal) > 0.02) {
      $errors[] = "Grand total mismatch: expected {$calculatedGrandTotal}, got {$grandTotal}";
    }

    // Validate amount due calculation
    $calculatedAmountDue = max(0, $grandTotal - $totalPaid);
    if (abs($amountDue - $calculatedAmountDue) > 0.02) {
      $errors[] = "Amount due mismatch: expected {$calculatedAmountDue}, got {$amountDue}";
    }

    // Validate non-negative amounts
    if ($subtotal < 0 || $taxAmount < 0 || $grandTotal < 0 || $totalPaid < 0 || $amountDue < 0) {
      $errors[] = "Amounts cannot be negative";
    }

    if (!empty($errors)) {
      throw new Exception('Validation failed: ' . implode(', ', $errors));
    }

    return true;
  }

  /**
   * Get customer state from invoice data
   * 
   * @param array $invoiceData Invoice data
   * @return int|null Customer state ID
   */
  protected function getCustomerStateFromInvoiceData(array $invoiceData): ?int
  {
    // Try to get from invoice data directly
    if (isset($invoiceData['customer_state_id'])) {
      return (int) $invoiceData['customer_state_id'];
    }

    // Get from customer record
    if (isset($invoiceData['account_id'])) {
      $accountModel = new \App\Models\AccountModel();
      $account = $accountModel->find($invoiceData['account_id']);
      return $account['billing_state_id'] ?? null;
    }

    if (isset($invoiceData['cash_customer_id'])) {
      $cashCustomerModel = new \App\Models\CashCustomerModel();
      $cashCustomer = $cashCustomerModel->find($invoiceData['cash_customer_id']);
      return $cashCustomer['state_id'] ?? null;
    }

    return null;
  }

  /**
   * Get company state from invoice data
   * 
   * @param array $invoiceData Invoice data
   * @return int|null Company state ID
   */
  protected function getCompanyStateFromInvoiceData(array $invoiceData): ?int
  {
    // Try to get from invoice data directly
    if (isset($invoiceData['company_state_id'])) {
      return (int) $invoiceData['company_state_id'];
    }

    // Get from company record
    if (isset($invoiceData['company_id'])) {
      $companyModel = new \App\Models\CompanyModel();
      $company = $companyModel->find($invoiceData['company_id']);
      return $company['state_id'] ?? null;
    }

    return null;
  }

  /**
   * Calculate invoice summary for multiple invoices
   * 
   * @param array $invoices Array of invoices
   * @return array Summary totals
   */
  public function calculateInvoiceSummary(array $invoices): array
  {
    $summary = [
      'total_invoices'    => count($invoices),
      'total_subtotal'    => 0.00,
      'total_tax'         => 0.00,
      'total_discount'    => 0.00,
      'total_grand_total' => 0.00,
      'total_paid'        => 0.00,
      'total_outstanding' => 0.00,
    ];

    foreach ($invoices as $invoice) {
      $summary['total_subtotal'] += (float) ($invoice['subtotal'] ?? 0);
      $summary['total_tax'] += (float) ($invoice['tax_amount'] ?? 0);
      $summary['total_discount'] += (float) ($invoice['discount'] ?? 0);
      $summary['total_grand_total'] += (float) ($invoice['grand_total'] ?? 0);
      $summary['total_paid'] += (float) ($invoice['total_paid'] ?? 0);
      $summary['total_outstanding'] += (float) ($invoice['amount_due'] ?? 0);
    }

    // Round all totals
    foreach ($summary as $key => $value) {
      if ($key !== 'total_invoices') {
        $summary[$key] = round($value, 2);
      }
    }

    return $summary;
  }
}

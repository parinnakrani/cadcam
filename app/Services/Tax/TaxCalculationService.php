<?php

namespace App\Services\Tax;

use App\Models\CompanyModel;
use App\Models\StateModel;
use App\Models\AccountModel;
use App\Models\CashCustomerModel;
use Exception;

/**
 * TaxCalculationService
 * 
 * Handles GST tax calculations for invoices and challans.
 * 
 * Business Rules:
 * - Same state transaction: CGST + SGST (tax split equally)
 * - Different state transaction: IGST (full tax)
 * - Tax rate retrieved from company settings
 * - Supports both invoice-level and line-level calculations
 * 
 * GST Types:
 * - CGST (Central GST): 50% of tax rate for intra-state
 * - SGST (State GST): 50% of tax rate for intra-state
 * - IGST (Integrated GST): 100% of tax rate for inter-state
 */
class TaxCalculationService
{
  protected CompanyModel $companyModel;
  protected StateModel $stateModel;
  protected AccountModel $accountModel;
  protected CashCustomerModel $cashCustomerModel;

  public function __construct()
  {
    $this->companyModel = new CompanyModel();
    $this->stateModel = new StateModel();
    $this->accountModel = new AccountModel();
    $this->cashCustomerModel = new CashCustomerModel();
  }

  /**
   * Calculate invoice-level tax
   * 
   * @param array $lines Invoice line items with line_subtotal
   * @param float $taxRate Tax rate percentage (e.g., 3.00 for 3%)
   * @param int|null $customerStateId Customer state ID
   * @param int|null $companyStateId Company state ID
   * @return array Tax breakdown with all amounts
   */
  public function calculateInvoiceTax(
    array $lines,
    float $taxRate,
    ?int $customerStateId = null,
    ?int $companyStateId = null
  ): array {
    // Calculate subtotal from lines
    $subtotal = 0.00;
    foreach ($lines as $line) {
      // Support both weight-based and quantity-based pricing
      if (isset($line['amount'])) {
        $subtotal += (float) $line['amount'];
      } elseif (isset($line['line_subtotal'])) {
        // Legacy fallback
        $subtotal += (float) $line['line_subtotal'];
      } else {
        // Calculate from weight/rate or quantity/rate
        $weight = (float) ($line['weight'] ?? 0);
        $rate = (float) ($line['rate'] ?? 0);
        $quantity = (int) ($line['quantity'] ?? 1);

        if ($weight > 0) {
          $lineTotal = $weight * $rate;
        } else {
          $lineTotal = $quantity * $rate;
        }

        // Back-calculate subtotal from tax-inclusive total
        $lineTax = $lineTotal * $taxRate / (100 + $taxRate);
        $lineSubtotal = $lineTotal - $lineTax;
        $subtotal += $lineSubtotal;
      }
    }

    // Determine tax type based on states
    $taxType = $this->determineTaxTypeByStates($companyStateId, $customerStateId);

    // Calculate tax amounts
    if ($taxType === 'CGST_SGST') {
      // Intra-state: Split tax equally between CGST and SGST
      $cgstRate = $taxRate / 2;
      $sgstRate = $taxRate / 2;
      $cgstAmount = round($subtotal * $cgstRate / 100, 2);
      $sgstAmount = round($subtotal * $sgstRate / 100, 2);
      $igstRate = 0.00;
      $igstAmount = 0.00;
      $totalTax = $cgstAmount + $sgstAmount;
    } else {
      // Inter-state: Full tax as IGST
      $cgstRate = 0.00;
      $sgstRate = 0.00;
      $cgstAmount = 0.00;
      $sgstAmount = 0.00;
      $igstRate = $taxRate;
      $igstAmount = round($subtotal * $igstRate / 100, 2);
      $totalTax = $igstAmount;
    }

    $grandTotal = $subtotal + $totalTax;

    return [
      'tax_type'         => $taxType,
      'subtotal'         => round($subtotal, 2),
      'tax_rate'         => $taxRate,
      'cgst_rate'        => $cgstRate,
      'cgst_amount'      => $cgstAmount,
      'sgst_rate'        => $sgstRate,
      'sgst_amount'      => $sgstAmount,
      'igst_rate'        => $igstRate,
      'igst_amount'      => $igstAmount,
      'total_tax'        => round($totalTax, 2),
      'grand_total'      => round($grandTotal, 2),
    ];
  }

  /**
   * Calculate line-level tax
   * 
   * @param float $lineSubtotal Line subtotal (before tax)
   * @param string $taxType Tax type: 'CGST_SGST' or 'IGST'
   * @param float $taxRate Tax rate percentage
   * @return array Line tax breakdown
   */
  public function calculateLineTax(float $lineSubtotal, string $taxType, float $taxRate): array
  {
    if ($taxType === 'CGST_SGST') {
      // Intra-state: Split tax equally
      $cgstRate = $taxRate / 2;
      $sgstRate = $taxRate / 2;
      $cgstAmount = round($lineSubtotal * $cgstRate / 100, 2);
      $sgstAmount = round($lineSubtotal * $sgstRate / 100, 2);
      $igstRate = 0.00;
      $igstAmount = 0.00;
      $lineTax = $cgstAmount + $sgstAmount;
    } else {
      // Inter-state: Full tax as IGST
      $cgstRate = 0.00;
      $sgstRate = 0.00;
      $cgstAmount = 0.00;
      $sgstAmount = 0.00;
      $igstRate = $taxRate;
      $igstAmount = round($lineSubtotal * $igstRate / 100, 2);
      $lineTax = $igstAmount;
    }

    $lineTotal = $lineSubtotal + $lineTax;

    return [
      'line_subtotal'   => round($lineSubtotal, 2),
      'tax_type'        => $taxType,
      'cgst_rate'       => $cgstRate,
      'cgst_amount'     => $cgstAmount,
      'sgst_rate'       => $sgstRate,
      'sgst_amount'     => $sgstAmount,
      'igst_rate'       => $igstRate,
      'igst_amount'     => $igstAmount,
      'line_tax_amount' => round($lineTax, 2),
      'line_total'      => round($lineTotal, 2),
    ];
  }

  /**
   * Determine tax type based on company and customer
   * 
   * @param int $companyId Company ID
   * @param int $customerId Customer ID (account_id or cash_customer_id)
   * @param string $customerType Customer type: 'Account' or 'Cash'
   * @return string Tax type: 'CGST_SGST' or 'IGST'
   * @throws StateNotFoundException
   */
  public function determineTaxType(int $companyId, int $customerId, string $customerType): string
  {
    // Get company state
    $company = $this->companyModel->find($companyId);
    if (!$company) {
      throw new StateNotFoundException("Company ID {$companyId} not found");
    }
    $companyStateId = $company['state_id'] ?? null;

    // Get customer state
    $customerStateId = $this->getCustomerState($customerId, $customerType);

    // Determine tax type
    return $this->determineTaxTypeByStates($companyStateId, $customerStateId);
  }

  /**
   * Determine tax type by comparing state IDs
   * 
   * @param int|null $companyStateId Company state ID
   * @param int|null $customerStateId Customer state ID
   * @return string Tax type: 'CGST_SGST' or 'IGST'
   */
  public function determineTaxTypeByStates(?int $companyStateId, ?int $customerStateId): string
  {
    // If states are not available, default to IGST (safer for compliance)
    if (!$companyStateId || !$customerStateId) {
      return 'IGST';
    }

    // Same state: CGST + SGST
    if ($companyStateId === $customerStateId) {
      return 'CGST_SGST';
    }

    // Different state: IGST
    return 'IGST';
  }

  /**
   * Get tax rate from company settings
   * 
   * @param int|null $companyId Company ID (optional, uses session if not provided)
   * @return float Tax rate percentage (e.g., 3.00)
   */
  public function getTaxRate(?int $companyId = null): float
  {
    if (!$companyId) {
      $companyId = session()->get('company_id');
    }

    $company = $this->companyModel->find($companyId);

    if (!$company) {
      // Default to 3% if company not found
      return 3.00;
    }

    return (float) ($company['default_tax_rate'] ?? 3.00);
  }

  /**
   * Validate tax calculation
   * 
   * Ensures:
   * - Tax amounts add up correctly
   * - Only CGST+SGST OR IGST is non-zero (not both)
   * - Totals are accurate
   * 
   * @param array $taxData Tax calculation data
   * @return bool True if valid
   * @throws TaxCalculationException If validation fails
   */
  public function validateTaxCalculation(array $taxData): bool
  {
    $errors = [];

    // Check required fields
    $requiredFields = [
      'subtotal',
      'cgst_amount',
      'sgst_amount',
      'igst_amount',
      'total_tax',
      'grand_total'
    ];

    foreach ($requiredFields as $field) {
      if (!isset($taxData[$field])) {
        $errors[] = "Missing required field: {$field}";
      }
    }

    if (!empty($errors)) {
      throw new TaxCalculationException(implode(', ', $errors));
    }

    $subtotal = (float) $taxData['subtotal'];
    $cgstAmount = (float) $taxData['cgst_amount'];
    $sgstAmount = (float) $taxData['sgst_amount'];
    $igstAmount = (float) $taxData['igst_amount'];
    $totalTax = (float) $taxData['total_tax'];
    $grandTotal = (float) $taxData['grand_total'];

    // Validate: Only CGST+SGST OR IGST should be non-zero
    if ($cgstAmount > 0 || $sgstAmount > 0) {
      // CGST+SGST mode
      if ($igstAmount > 0) {
        $errors[] = 'Cannot have both CGST/SGST and IGST';
      }

      // CGST and SGST should be equal (or very close due to rounding)
      if (abs($cgstAmount - $sgstAmount) > 0.02) {
        $errors[] = 'CGST and SGST amounts must be equal';
      }

      // Total tax should equal CGST + SGST
      $calculatedTax = $cgstAmount + $sgstAmount;
      if (abs($totalTax - $calculatedTax) > 0.02) {
        $errors[] = "Total tax mismatch: expected {$calculatedTax}, got {$totalTax}";
      }
    } elseif ($igstAmount > 0) {
      // IGST mode
      if ($cgstAmount > 0 || $sgstAmount > 0) {
        $errors[] = 'Cannot have both IGST and CGST/SGST';
      }

      // Total tax should equal IGST
      if (abs($totalTax - $igstAmount) > 0.02) {
        $errors[] = "Total tax mismatch: expected {$igstAmount}, got {$totalTax}";
      }
    } else {
      // No tax (should be rare, but valid for 0% tax rate)
      if ($totalTax > 0.02) {
        $errors[] = 'Total tax should be zero when no CGST/SGST/IGST';
      }
    }

    // Validate grand total
    $calculatedGrandTotal = $subtotal + $totalTax;
    if (abs($grandTotal - $calculatedGrandTotal) > 0.02) {
      $errors[] = "Grand total mismatch: expected {$calculatedGrandTotal}, got {$grandTotal}";
    }

    if (!empty($errors)) {
      throw new TaxCalculationException('Tax validation failed: ' . implode(', ', $errors));
    }

    return true;
  }

  /**
   * Get customer state ID
   * 
   * @param int $customerId Customer ID
   * @param string $customerType Customer type: 'Account' or 'Cash'
   * @return int|null State ID
   */
  protected function getCustomerState(int $customerId, string $customerType): ?int
  {
    if ($customerType === 'Account') {
      $account = $this->accountModel->find($customerId);
      return $account['billing_state_id'] ?? null;
    } elseif ($customerType === 'Cash') {
      $cashCustomer = $this->cashCustomerModel->find($customerId);
      return $cashCustomer['state_id'] ?? null;
    }

    return null;
  }

  /**
   * Calculate tax from tax-inclusive amount
   * 
   * Used when you have the final amount (including tax) and need to
   * extract the tax component.
   * 
   * @param float $taxInclusiveAmount Amount including tax
   * @param float $taxRate Tax rate percentage
   * @return array Tax breakdown
   */
  public function extractTaxFromInclusive(float $taxInclusiveAmount, float $taxRate): array
  {
    // Formula: tax = amount × rate / (100 + rate)
    $taxAmount = round($taxInclusiveAmount * $taxRate / (100 + $taxRate), 2);
    $subtotal = round($taxInclusiveAmount - $taxAmount, 2);

    return [
      'tax_inclusive_amount' => round($taxInclusiveAmount, 2),
      'subtotal'             => $subtotal,
      'tax_amount'           => $taxAmount,
      'tax_rate'             => $taxRate,
    ];
  }

  /**
   * Calculate tax-inclusive amount from subtotal
   * 
   * @param float $subtotal Subtotal (before tax)
   * @param float $taxRate Tax rate percentage
   * @return array Tax breakdown
   */
  public function addTaxToSubtotal(float $subtotal, float $taxRate): array
  {
    $taxAmount = round($subtotal * $taxRate / 100, 2);
    $taxInclusiveAmount = round($subtotal + $taxAmount, 2);

    return [
      'subtotal'             => round($subtotal, 2),
      'tax_amount'           => $taxAmount,
      'tax_inclusive_amount' => $taxInclusiveAmount,
      'tax_rate'             => $taxRate,
    ];
  }

  /**
   * Get tax summary for reporting
   * 
   * @param array $invoices Array of invoices
   * @return array Tax summary with totals
   */
  public function getTaxSummary(array $invoices): array
  {
    $summary = [
      'total_invoices'   => count($invoices),
      'total_subtotal'   => 0.00,
      'total_cgst'       => 0.00,
      'total_sgst'       => 0.00,
      'total_igst'       => 0.00,
      'total_tax'        => 0.00,
      'total_grand_total' => 0.00,
      'cgst_sgst_count'  => 0,
      'igst_count'       => 0,
    ];

    foreach ($invoices as $invoice) {
      $summary['total_subtotal'] += (float) ($invoice['subtotal'] ?? 0);
      $summary['total_cgst'] += (float) ($invoice['cgst_amount'] ?? 0);
      $summary['total_sgst'] += (float) ($invoice['sgst_amount'] ?? 0);
      $summary['total_igst'] += (float) ($invoice['igst_amount'] ?? 0);
      $summary['total_tax'] += (float) ($invoice['tax_amount'] ?? 0);
      $summary['total_grand_total'] += (float) ($invoice['grand_total'] ?? 0);

      // Count tax types
      if (($invoice['cgst_amount'] ?? 0) > 0 || ($invoice['sgst_amount'] ?? 0) > 0) {
        $summary['cgst_sgst_count']++;
      }
      if (($invoice['igst_amount'] ?? 0) > 0) {
        $summary['igst_count']++;
      }
    }

    // Round all totals
    $summary['total_subtotal'] = round($summary['total_subtotal'], 2);
    $summary['total_cgst'] = round($summary['total_cgst'], 2);
    $summary['total_sgst'] = round($summary['total_sgst'], 2);
    $summary['total_igst'] = round($summary['total_igst'], 2);
    $summary['total_tax'] = round($summary['total_tax'], 2);
    $summary['total_grand_total'] = round($summary['total_grand_total'], 2);

    return $summary;
  }
}

/**
 * Custom Exceptions
 */
class TaxCalculationException extends Exception {}
class StateNotFoundException extends Exception {}

<?php

namespace App\Services\Challan;

use App\Models\ProcessModel;
use App\Models\CompanyModel;
use Exception;

/**
 * ChallanCalculationService
 *
 * Handles all amount calculation logic for challans and their line items.
 * Separated from ChallanService for testability and single responsibility.
 *
 * Responsibilities:
 * - Calculate individual line item amounts from processes
 * - Calculate tax amounts based on company default_tax_rate
 * - Aggregate line totals into challan-level totals
 * - Handle gold weight calculations
 * - Validate line data before calculation
 * - Snapshot process rates at creation time
 *
 * Actual DB column references:
 * - processes.rate_per_unit       → current rate for a process
 * - companies.default_tax_rate    → company-level tax rate (e.g., 3.00 for 3%)
 * - challan_lines.amount          → calculated line amount
 * - challan_lines.weight          → weight per line
 * - challan_lines.rate            → rate applied
 * - challan_lines.quantity        → quantity of items
 * - challan_lines.gold_weight     → gold weight in grams
 * - challan_lines.process_prices  → JSON snapshot of process prices at creation
 */
class ChallanCalculationService
{
  protected ProcessModel $processModel;
  protected CompanyModel $companyModel;

  public function __construct(
    ProcessModel $processModel,
    CompanyModel $companyModel
  ) {
    $this->processModel = $processModel;
    $this->companyModel = $companyModel;
  }

    // =========================================================================
    // LINE-LEVEL CALCULATIONS
    // =========================================================================

  /**
   * Calculate amounts for a single challan line.
   *
   * Calculation logic:
   * 1. For each process: amount = quantity × rate
   * 2. line_subtotal = sum of all process amounts
   * 3. line_tax = line_subtotal × (tax_rate / 100)
   * 4. line_total = line_subtotal + line_tax
   *
   * If process rates are not provided in the data, they are fetched
   * from the ProcessModel (current rates). Fetched rates are also
   * returned as `process_prices` for snapshot storage on the line.
   *
   * @param array $lineData Must contain:
   *   - process_ids (array): array of process IDs
   *   - quantity (int): item quantity (default 1)
   *   - weight (float, optional): item weight in grams
   *   - gold_weight (float, optional): gold weight in grams
   *   - rate (float, optional): if provided, used directly instead of process rates
   *   - process_prices (array, optional): pre-set process rate overrides [{process_id, rate, amount}]
   * @return array Calculated values:
   *   - amount: total line amount (subtotal, no tax at line level)
   *   - rate: effective rate used
   *   - process_prices: snapshot of process rates used
   *   - gold_weight: gold weight (pass-through)
   * @throws Exception
   */
  public function calculateLineTotal(array $lineData): array
  {
    $quantity     = (int)($lineData['quantity'] ?? 1);
    $weight       = (float)($lineData['weight'] ?? 0.000);
    $goldWeight   = isset($lineData['gold_weight']) ? (float)$lineData['gold_weight'] : null;
    $processIds   = $lineData['process_ids'] ?? [];
    $providedRate = isset($lineData['rate']) ? (float)$lineData['rate'] : null;

    // Ensure at least quantity of 1
    if ($quantity < 1) {
      $quantity = 1;
    }

    // ------------------------------------------------------------------
    // Calculate from processes
    // ------------------------------------------------------------------
    $processPrices = [];
    $totalProcessAmount = 0.00;

    if (!empty($processIds) && is_array($processIds)) {
      // Check if process_prices already provided (pre-set / snapshot)
      $existingPrices = [];
      if (!empty($lineData['process_prices']) && is_array($lineData['process_prices'])) {
        foreach ($lineData['process_prices'] as $pp) {
          if (isset($pp['process_id'])) {
            $existingPrices[(int)$pp['process_id']] = $pp;
          }
        }
      }

      foreach ($processIds as $processId) {
        $processId = (int)$processId;

        // Try to use existing price, otherwise fetch current rate
        if (isset($existingPrices[$processId]) && isset($existingPrices[$processId]['rate'])) {
          $rate = (float)$existingPrices[$processId]['rate'];
          $processName = $existingPrices[$processId]['process_name'] ?? '';
        } else {
          $rate = $this->getProcessRate($processId);
          $processName = $this->getProcessName($processId);
        }

        // Calculate: amount = quantity × rate
        $processAmount = round($quantity * $rate, 2);

        $processPrices[] = [
          'process_id'   => $processId,
          'process_name' => $processName,
          'rate'         => $rate,
          'quantity'     => $quantity,
          'amount'       => $processAmount,
        ];

        $totalProcessAmount += $processAmount;
      }
    }

    // ------------------------------------------------------------------
    // Determine effective rate and amount
    // ------------------------------------------------------------------
    // If a direct rate was provided (e.g., for Wax challans with custom pricing),
    // use it instead of summing process rates
    if ($providedRate !== null && $providedRate > 0) {
      $lineAmount = round($quantity * $providedRate, 2);
      $effectiveRate = $providedRate;
    } elseif ($totalProcessAmount > 0) {
      $lineAmount = round($totalProcessAmount, 2);
      // Effective rate = total / quantity (for display purposes)
      $effectiveRate = ($quantity > 0) ? round($lineAmount / $quantity, 2) : 0.00;
    } else {
      // No processes and no rate — amount stays 0
      $lineAmount = 0.00;
      $effectiveRate = 0.00;
    }

    return [
      'quantity'       => $quantity,
      'weight'         => $weight,
      'rate'           => $effectiveRate,
      'amount'         => $lineAmount,
      'gold_weight'    => $goldWeight,
      'process_prices' => $processPrices,
    ];
  }

  /**
   * Recalculate process amounts from a list of process entries.
   *
   * Used when updating a line: re-fetches current rates for any process
   * that doesn't have a rate override, and recalculates amounts.
   *
   * @param array $processes Array of [{process_id, quantity, rate (optional)}]
   * @return array Updated processes with amounts calculated
   * @throws Exception
   */
  public function recalculateProcessAmounts(array $processes): array
  {
    $result = [];

    foreach ($processes as $process) {
      $processId = (int)($process['process_id'] ?? 0);
      $quantity  = (int)($process['quantity'] ?? 1);

      if ($processId <= 0) {
        continue; // Skip invalid entries
      }

      // Use provided rate or fetch current
      if (isset($process['rate']) && (float)$process['rate'] > 0) {
        $rate = (float)$process['rate'];
      } else {
        $rate = $this->getProcessRate($processId);
      }

      $amount = round($quantity * $rate, 2);

      $result[] = [
        'process_id'   => $processId,
        'process_name' => $process['process_name'] ?? $this->getProcessName($processId),
        'quantity'     => $quantity,
        'rate'         => $rate,
        'amount'       => $amount,
      ];
    }

    return $result;
  }

    // =========================================================================
    // CHALLAN-LEVEL CALCULATIONS
    // =========================================================================

  /**
   * Calculate challan-level totals from an array of line items.
   *
   * Aggregates:
   * - subtotal_amount  = sum of all line amounts
   * - tax_amount       = subtotal × (company tax rate / 100)
   * - total_amount     = subtotal + tax
   * - total_weight     = sum of all gold_weight values
   *
   * @param array $lines Array of line data arrays (each containing amounts)
   * @param float|null $taxRate Optional tax rate override. If null, fetches company default.
   * @return array ['subtotal_amount', 'tax_amount', 'total_amount', 'total_weight']
   * @throws Exception
   */
  public function calculateChallanTotals(array $lines, ?float $taxRate = null): array
  {
    $subtotal    = 0.00;
    $totalWeight = 0.000;

    foreach ($lines as $line) {
      // If lines already have calculated amounts, use them
      if (isset($line['amount'])) {
        $subtotal += (float)$line['amount'];
      }

      if (isset($line['gold_weight']) && $line['gold_weight'] !== null) {
        $totalWeight += (float)$line['gold_weight'];
      }
    }

    $subtotal    = round($subtotal, 2);
    $totalWeight = round($totalWeight, 3);

    // Tax calculation
    // Use provided rate, or fetch default if null
    $appliedTaxRate = ($taxRate !== null) ? $taxRate : $this->getTaxRate();

    $taxAmount = round($subtotal * ($appliedTaxRate / 100), 2);
    $total     = round($subtotal + $taxAmount, 2);

    return [
      'subtotal_amount' => $subtotal,
      'tax_amount'      => $taxAmount,
      'total_amount'    => $total,
      'total_weight'    => $totalWeight,
      'tax_percent'     => $appliedTaxRate, // Return the rate used
    ];
  }

  /**
   * Calculate and prepare line totals with challan summaries.
   *
   * Convenience method that calculates each line's amounts and then
   * aggregates into challan totals.
   *
   * @param array $rawLines Array of raw line data (process_ids, quantity, etc.)
   * @param float|null $taxRate Optional tax rate override.
   * @return array ['lines' => [...calculated lines], 'totals' => [...challan totals]]
   * @throws Exception
   */
  public function calculateAll(array $rawLines, ?float $taxRate = null): array
  {
    $calculatedLines = [];

    foreach ($rawLines as $line) {
      $calculated = $this->calculateLineTotal($line);

      // Merge calculated values back into line data
      $mergedLine = array_merge($line, $calculated);
      $calculatedLines[] = $mergedLine;
    }

    $totals = $this->calculateChallanTotals($calculatedLines, $taxRate);

    return [
      'lines'  => $calculatedLines,
      'totals' => $totals,
    ];
  }

    // =========================================================================
    // TAX RATE
    // =========================================================================

  /**
   * Get the company's default tax rate.
   *
   * Fetches from the companies table using the session company_id.
   * Column: companies.default_tax_rate (DECIMAL 5,2, default 3.00)
   *
   * @return float Tax rate as percentage (e.g., 3.00 for 3%)
   */
  public function getTaxRate(): float
  {
    $session   = session();
    $companyId = $session->get('company_id');

    if (empty($companyId)) {
      return 0.00; // Safeguard — should never happen in normal flow
    }

    $company = $this->companyModel->find($companyId);

    if (!$company || !isset($company['default_tax_rate'])) {
      return 0.00;
    }

    return (float)$company['default_tax_rate'];
  }

    // =========================================================================
    // VALIDATION
    // =========================================================================

  /**
   * Validate line data before calculation.
   *
   * Checks:
   * - process_ids is a non-empty array (or rate is directly provided)
   * - quantity > 0
   * - gold_weight >= 0 (if provided)
   *
   * @param array $lineData
   * @return bool TRUE if valid
   * @throws Exception with descriptive message if invalid
   */
  public function validateLineData(array $lineData): bool
  {
    // Must have either processes or a direct rate
    $hasProcesses = !empty($lineData['process_ids']) && is_array($lineData['process_ids']);
    $hasDirectRate = isset($lineData['rate']) && (float)$lineData['rate'] > 0;

    if (!$hasProcesses && !$hasDirectRate) {
      throw new Exception(
        'Line must have at least one process or a direct rate specified.'
      );
    }

    // Validate quantity
    $quantity = $lineData['quantity'] ?? 0;
    if ((int)$quantity < 1) {
      throw new Exception(
        'Line quantity must be at least 1.'
      );
    }

    // Validate gold_weight if provided
    if (isset($lineData['gold_weight']) && $lineData['gold_weight'] !== null) {
      if ((float)$lineData['gold_weight'] < 0) {
        throw new Exception(
          'Gold weight cannot be negative.'
        );
      }
    }

    // Validate weight if provided
    if (isset($lineData['weight']) && $lineData['weight'] !== null) {
      if ((float)$lineData['weight'] < 0) {
        throw new Exception(
          'Weight cannot be negative.'
        );
      }
    }

    // Validate each process_id is a positive integer
    if ($hasProcesses) {
      foreach ($lineData['process_ids'] as $pid) {
        if (!is_numeric($pid) || (int)$pid <= 0) {
          throw new Exception(
            "Invalid process ID: '{$pid}'. Process IDs must be positive integers."
          );
        }
      }
    }

    return true;
  }

  /**
   * Validate multiple lines at once.
   *
   * @param array $lines Array of line data
   * @return bool TRUE if all valid
   * @throws Exception on first invalid line (with line number context)
   */
  public function validateAllLines(array $lines): bool
  {
    if (empty($lines)) {
      throw new Exception('At least one line item is required.');
    }

    foreach ($lines as $index => $line) {
      try {
        $this->validateLineData($line);
      } catch (Exception $e) {
        $lineNum = $index + 1;
        throw new Exception("Line #{$lineNum}: " . $e->getMessage());
      }
    }

    return true;
  }

    // =========================================================================
    // PROCESS RATE HELPERS
    // =========================================================================

  /**
   * Get the current rate for a process.
   *
   * Uses ProcessModel::getCurrentRate() which enforces company isolation.
   *
   * @param int $processId
   * @return float
   * @throws Exception if process not found or rate is null
   */
  private function getProcessRate(int $processId): float
  {
    $rate = $this->processModel->getCurrentRate($processId);

    if ($rate === null) {
      throw new Exception(
        "Process rate not found for process ID: {$processId}. "
          . 'The process may not exist or may belong to a different company.'
      );
    }

    return $rate;
  }

  /**
   * Get the name of a process by its ID.
   *
   * @param int $processId
   * @return string Process name or empty string
   */
  private function getProcessName(int $processId): string
  {
    $process = $this->processModel->find($processId);

    if ($process && isset($process['process_name'])) {
      return $process['process_name'];
    }

    return '';
  }

    // =========================================================================
    // SNAPSHOT HELPERS
    // =========================================================================

  /**
   * Create a process price snapshot for a line.
   *
   * Fetches current rates for all processes and creates a snapshot array
   * for storage in challan_lines.process_prices JSON column.
   *
   * @param array $processIds Array of process IDs
   * @param int   $quantity   Quantity applied
   * @return array Array of [{process_id, process_name, rate, quantity, amount}]
   * @throws Exception
   */
  public function createProcessPriceSnapshot(array $processIds, int $quantity = 1): array
  {
    $snapshot = [];

    foreach ($processIds as $processId) {
      $processId = (int)$processId;
      $rate      = $this->getProcessRate($processId);
      $name      = $this->getProcessName($processId);
      $amount    = round($quantity * $rate, 2);

      $snapshot[] = [
        'process_id'   => $processId,
        'process_name' => $name,
        'rate'         => $rate,
        'quantity'     => $quantity,
        'amount'       => $amount,
      ];
    }

    return $snapshot;
  }
}

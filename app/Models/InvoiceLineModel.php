<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * InvoiceLineModel
 * 
 * Handles invoice line item data operations with automatic JSON casting,
 * totals calculation, and line number management.
 * 
 * Business Rules:
 * - Lines belong to invoices (invoice_id)
 * - Optional reference to challan lines (source_challan_line_id)
 * - JSON fields auto-cast for products and processes
 * - Line numbers sequential within invoice
 * - Soft delete support
 */
class InvoiceLineModel extends Model
{
  protected $table            = 'invoice_lines';
  protected $primaryKey       = 'id';
  protected $useAutoIncrement = true;
  protected $returnType       = 'array';
  protected $useSoftDeletes   = false; // We handle soft deletes manually
  protected $protectFields    = true;

  protected $allowedFields = [
    'invoice_id',
    'line_number',
    'source_challan_id',
    'source_challan_line_id',
    'product_ids',
    'product_name',
    'process_ids',
    'process_prices',
    'quantity',
    'weight',
    'rate',
    'amount',
    'gold_weight',
    'gold_fine_weight',
    'gold_purity',
    'original_gold_weight',
    'adjusted_gold_weight',
    'gold_adjustment_amount',
    'line_notes',
  ];

  protected $useTimestamps = true;
  protected $createdField  = 'created_at';
  protected $updatedField  = 'updated_at';

  // Automatic JSON casting for JSON fields
  // Note: $casts only works on Entity objects in CI4, not plain array models.
  // JSON decoding is handled manually in getLinesByInvoiceId() consumers.
  protected $casts = [
    'product_ids'     => 'json',
    'process_ids'     => 'json',
    'process_prices'  => 'json',
    'quantity'        => 'integer',
    'line_number'     => 'integer',
  ];

  protected $validationRules = [
    'invoice_id'      => 'required|integer',
    'line_number'     => 'required|integer',
    'quantity'        => 'required|integer',
    'weight'          => 'permit_empty|decimal',
    'amount'          => 'required|decimal',
  ];

  protected $validationMessages = [
    'invoice_id' => [
      'required' => 'Invoice ID is required',
      'integer'  => 'Invoice ID must be an integer',
    ],
    'line_number' => [
      'required' => 'Line number is required',
      'integer'  => 'Line number must be an integer',
    ],
    'quantity' => [
      'required' => 'Quantity is required',
      'integer'  => 'Quantity must be an integer',
    ],
  ];

  protected $skipValidation = false;
  protected $cleanValidationRules = true;

  // Callbacks
  protected $allowCallbacks = true;
  protected $beforeInsert   = ['setDefaultValues'];
  protected $beforeUpdate   = ['setDefaultValues'];

  /**
   * Set default values for fields before insert/update
   * 
   * @param array $data
   * @return array
   */
  protected function setDefaultValues(array $data): array
  {
    if (isset($data['data'])) {
      // Ensure numeric fields have defaults
      if (!isset($data['data']['quantity'])) {
        $data['data']['quantity'] = 1;
      }
      if (!isset($data['data']['weight'])) {
        $data['data']['weight'] = 0.000;
      }
      if (!isset($data['data']['gold_weight'])) {
        $data['data']['gold_weight'] = 0.000;
      }
      if (!isset($data['data']['original_gold_weight'])) {
        $data['data']['original_gold_weight'] = 0.000;
      }
      if (!isset($data['data']['adjusted_gold_weight'])) {
        $data['data']['adjusted_gold_weight'] = 0.000;
      }
      if (!isset($data['data']['gold_adjustment_amount'])) {
        $data['data']['gold_adjustment_amount'] = 0.00;
      }
      if (!isset($data['data']['amount'])) {
        $data['data']['amount'] = 0.00;
      }
    }

    return $data;
  }

  /**
   * Get all lines for a specific invoice
   * 
   * @param int $invoiceId Invoice ID
   * @return array List of invoice lines
   */
  public function getLinesByInvoiceId(int $invoiceId): array
  {
    $builder = $this->builder();
    $builder->where('invoice_id', $invoiceId);
    $builder->orderBy('line_number', 'ASC');

    return $builder->get()->getResultArray();
  }

  /**
   * Calculate totals for all lines in an invoice
   * 
   * Returns:
   * - total_quantity: Sum of all quantities
   * - total_weight: Sum of all weights
   * - total_gold_weight: Sum of all gold weights
   * - total_subtotal: Sum of all line_subtotal
   * - total_tax: Sum of all line_tax_amount
   * - total_amount: Sum of all line_total
   * - total_gold_adjustment: Sum of all gold_adjustment_amount
   * 
   * @param int $invoiceId Invoice ID
   * @return array Totals array
   */
  public function getTotalsForInvoice(int $invoiceId): array
  {
    $builder = $this->db->table($this->table);
    $builder->select('
            SUM(quantity) as total_quantity,
            SUM(weight) as total_weight,
            SUM(gold_weight) as total_gold_weight,
            SUM(amount) as total_subtotal,
            SUM(amount) as total_amount,
            SUM(gold_adjustment_amount) as total_gold_adjustment,
            COUNT(*) as line_count
        ');
    $builder->where('invoice_id', $invoiceId);

    $result = $builder->get()->getRowArray();

    // Ensure numeric values (handle NULL from empty result)
    return [
      'total_quantity'         => (int) ($result['total_quantity'] ?? 0),
      'total_weight'           => (float) ($result['total_weight'] ?? 0.000),
      'total_gold_weight'      => (float) ($result['total_gold_weight'] ?? 0.000),
      'total_subtotal'         => (float) ($result['total_subtotal'] ?? 0.00),
      'total_tax'              => 0.00,
      'total_amount'           => (float) ($result['total_amount'] ?? 0.00),
      'total_gold_adjustment'  => (float) ($result['total_gold_adjustment'] ?? 0.00),
      'line_count'             => (int) ($result['line_count'] ?? 0),
    ];
  }

  /**
   * Get the next available line number for an invoice
   * 
   * @param int $invoiceId Invoice ID
   * @return int Next line number (starts at 1)
   */
  public function getNextLineNumber(int $invoiceId): int
  {
    // invoice_lines has NO is_deleted column — no soft-delete filter needed
    $builder = $this->db->table($this->table);
    $builder->selectMax('line_number', 'max_line_number');
    $builder->where('invoice_id', $invoiceId);

    $result = $builder->get()->getRowArray();
    $maxLineNumber = (int) ($result['max_line_number'] ?? 0);

    return $maxLineNumber + 1;
  }

  /**
   * Get line with product and process details
   * Enriches line data with product and process names from master tables
   * 
   * @param int $lineId Line ID
   * @return array|null Line with enriched data
   */
  public function getLineWithDetails(int $lineId): ?array
  {
    $line = $this->find($lineId);

    if (!$line) {
      return null;
    }

    // Get product details if product_ids exist
    if (!empty($line['product_ids']) && is_array($line['product_ids'])) {
      $productModel = new \App\Models\ProductModel();
      $products = $productModel->whereIn('id', $line['product_ids'])->findAll();
      $line['products'] = $products;
    }

    // Get process details if process_ids exist
    if (!empty($line['process_ids']) && is_array($line['process_ids'])) {
      $processModel = new \App\Models\ProcessModel();
      $processes = $processModel->whereIn('id', $line['process_ids'])->findAll();
      $line['processes'] = $processes;
    }

    return $line;
  }

  /**
   * Get lines with enriched product and process details
   * 
   * @param int $invoiceId Invoice ID
   * @return array List of lines with enriched data
   */
  public function getLinesWithDetails(int $invoiceId): array
  {
    $lines = $this->getLinesByInvoiceId($invoiceId);

    foreach ($lines as &$line) {
      // Get product details if product_ids exist
      if (!empty($line['product_ids']) && is_array($line['product_ids'])) {
        $productModel = new \App\Models\ProductModel();
        $products = $productModel->whereIn('id', $line['product_ids'])->findAll();
        $line['products'] = $products;
      }

      // Get process details if process_ids exist
      if (!empty($line['process_ids']) && is_array($line['process_ids'])) {
        $processModel = new \App\Models\ProcessModel();
        $processes = $processModel->whereIn('id', $line['process_ids'])->findAll();
        $line['processes'] = $processes;
      }
    }

    return $lines;
  }

  /**
   * Delete all lines for an invoice (hard delete)
   * 
   * @param int $invoiceId Invoice ID
   * @return bool Success status
   */
  public function deleteLinesByInvoiceId(int $invoiceId): bool
  {
    $builder = $this->builder();
    $builder->where('invoice_id', $invoiceId);

    return $builder->delete();
  }

  /**
   * Copy lines from challan to invoice
   * Used when creating invoice from challan
   * 
   * @param int $invoiceId Target invoice ID
   * @param int $challanId Source challan ID
   * @return bool Success status
   */
  public function copyFromChallan(int $invoiceId, int $challanId): bool
  {
    // Get challan lines
    $challanLineModel = new \App\Models\ChallanLineModel();
    $challanLines = $challanLineModel->getLinesByChallanId($challanId);

    if (empty($challanLines)) {
      return false;
    }

    $lineNumber = 1;
    foreach ($challanLines as $challanLine) {
      $invoiceLineData = [
        'invoice_id'             => $invoiceId,
        'line_number'            => $lineNumber,
        'source_challan_id'      => $challanId,
        'source_challan_line_id' => $challanLine['id'],
        // Note: 'challan_line_id' does NOT exist in invoice_lines table — removed
        'product_ids'            => $challanLine['product_ids'],
        'product_name'           => $challanLine['product_name'],
        'process_ids'            => $challanLine['process_ids'],
        'process_prices'         => $challanLine['process_prices'],
        'quantity'               => $challanLine['quantity'],
        'weight'                 => $challanLine['weight'],
        'rate'                   => $challanLine['rate'],
        'amount'                 => $challanLine['amount'],
        'gold_weight'            => $challanLine['gold_weight'],
        'gold_fine_weight'       => $challanLine['gold_fine_weight'],
        'gold_purity'            => $challanLine['gold_purity'],
        'line_notes'             => $challanLine['line_notes'],
        'original_gold_weight'   => $challanLine['gold_weight'] ?? 0.000,
        'adjusted_gold_weight'   => $challanLine['gold_weight'] ?? 0.000,
      ];

      if (!$this->insert($invoiceLineData)) {
        return false;
      }

      $lineNumber++;
    }

    return true;
  }

  /**
   * Update gold adjustment for a line
   * 
   * @param int $lineId Line ID
   * @param float $adjustedGoldWeight Adjusted gold weight in grams
   * @param float $goldRatePerGram Gold rate per gram
   * @return bool Success status
   */
  public function updateGoldAdjustment(int $lineId, float $adjustedGoldWeight, float $goldRatePerGram): bool
  {
    $line = $this->find($lineId);

    if (!$line) {
      return false;
    }

    $originalGoldWeight = (float) $line['original_gold_weight'];
    $goldDifference = $adjustedGoldWeight - $originalGoldWeight;
    $goldAdjustmentAmount = $goldDifference * $goldRatePerGram;

    // Update line total
    $newLineTotal = (float) $line['amount'] + $goldAdjustmentAmount;

    $updateData = [
      'adjusted_gold_weight'    => $adjustedGoldWeight,
      'gold_adjustment_amount'  => $goldAdjustmentAmount,
      'amount'                  => $newLineTotal,
    ];

    return $this->update($lineId, $updateData);
  }

  /**
   * Recalculate line totals based on weight and rate
   * 
   * @param int $lineId Line ID
   * @param float $taxRate Tax rate percentage (e.g., 3.00 for 3%)
   * @return bool Success status
   */
  public function recalculateLineTotals(int $lineId, float $taxRate): bool
  {
    $line = $this->find($lineId);

    if (!$line) {
      return false;
    }

    $weight = (float) $line['weight'];
    $rate = (float) $line['rate'];
    $quantity = (int) $line['quantity'];

    // Calculate line total (tax-inclusive)
    if ($weight > 0) {
      $lineTotal = $weight * $rate;
    } else {
      $lineTotal = $quantity * $rate;
    }

    // Back-calculate tax (tax-inclusive pricing)
    // Back-calculate tax (tax-inclusive pricing) - DEPRECATED
    // Tax is calculated at invoice level. Just assume amount is inclusive.

    // $lineTaxAmount = $lineTotal * $taxRate / (100 + $taxRate);
    // $lineSubtotal = $lineTotal - $lineTaxAmount;

    $updateData = [
      'amount'          => $lineTotal,
    ];

    return $this->update($lineId, $updateData);
  }

  /**
   * Get lines by source challan
   * Useful for tracking which invoice lines came from which challan
   * 
   * @param int $challanId Challan ID
   * @return array List of invoice lines
   */
  public function getLinesByChallanId(int $challanId): array
  {
    $builder = $this->builder();
    $builder->where('source_challan_id', $challanId);
    $builder->orderBy('invoice_id', 'ASC');
    $builder->orderBy('line_number', 'ASC');

    return $builder->get()->getResultArray();
  }

  /**
   * Check if challan line is already used in an invoice
   * 
   * @param int $challanLineId Challan line ID
   * @return bool True if already used, false otherwise
   */
  public function isChallanLineUsed(int $challanLineId): bool
  {
    $builder = $this->builder();
    $builder->where('source_challan_line_id', $challanLineId);

    $count = $builder->countAllResults();

    return $count > 0;
  }
}

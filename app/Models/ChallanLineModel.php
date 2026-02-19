<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * ChallanLineModel
 *
 * Model for managing individual line items within a challan.
 * Extends CodeIgniter\Model directly (NOT BaseModel) because challan_lines
 * does not have company_id or is_deleted columns — tenant isolation
 * is handled via the parent challan record.
 *
 * Business Context:
 * - Each challan has one or more line items.
 * - Lines store product IDs and process IDs as JSON arrays.
 * - Process prices are snapshot at creation time (JSON) to preserve historical pricing.
 * - Line amounts: quantity × weight × rate, or quantity × rate (if weight-based vs piece-based).
 * - Gold weight tracking per line for gold adjustment at invoice time.
 * - Line numbers are sequential within a challan (1, 2, 3...).
 *
 * Actual DB Columns:
 *   id, challan_id, line_number,
 *   product_ids (JSON), product_name (VARCHAR — for Wax file upload),
 *   process_ids (JSON), process_prices (JSON — snapshot at creation),
 *   quantity (INT), weight (DECIMAL 10,3), rate (DECIMAL 10,2), amount (DECIMAL 15,2),
 *   image_path (VARCHAR),
 *   gold_weight (DECIMAL 10,3), gold_fine_weight (DECIMAL 10,3), gold_purity (VARCHAR),
 *   line_notes (TEXT),
 *   created_at, updated_at
 */
class ChallanLineModel extends Model
{
  protected $table         = 'challan_lines';
  protected $primaryKey    = 'id';
  protected $useTimestamps = true;
  protected $returnType    = 'array';

  protected $allowedFields = [
    'challan_id',
    'line_number',
    'product_ids',
    'product_name',
    'process_ids',
    'process_prices',
    'quantity',
    'weight',
    'rate',
    'amount',
    'image_path',
    'gold_weight',
    'gold_fine_weight',
    'gold_purity',
    'current_gold_price',
    'adjusted_gold_weight',
    'gold_adjustment_amount',
    'line_notes',
    // created_at, updated_at handled automatically by CI4
  ];

  protected $validationRules = [
    'challan_id'  => 'required|integer',
    'line_number' => 'required|integer|greater_than[0]',
    'quantity'    => 'required|integer|greater_than[0]',
    'weight'      => 'permit_empty|decimal',
    'rate'        => 'required|decimal',
    'amount'      => 'required|decimal',
  ];

  // =========================================================================
  // CI4 MODEL CALLBACKS — JSON Encoding/Decoding
  // =========================================================================
  // CI4 Model does not support $casts on the Model class (only on Entity).
  // We use callbacks to transparently handle JSON encode/decode.

  protected $allowCallbacks = true;

  protected $beforeInsert = ['encodeJsonFields'];
  protected $beforeUpdate = ['encodeJsonFields'];
  protected $afterFind    = ['decodeJsonFields'];

  /**
   * JSON field names that require encoding/decoding.
   */
  protected array $jsonFields = ['product_ids', 'process_ids', 'process_prices'];

  /**
   * Encode JSON fields before insert/update.
   *
   * Converts PHP arrays to JSON strings for storage.
   * If the value is already a string (manually pre-encoded), it is left as-is.
   *
   * @param array $eventData CI4 callback event data
   * @return array
   */
  protected function encodeJsonFields(array $eventData): array
  {
    if (isset($eventData['data'])) {
      foreach ($this->jsonFields as $field) {
        if (array_key_exists($field, $eventData['data'])) {
          $value = $eventData['data'][$field];
          if (is_array($value)) {
            $eventData['data'][$field] = json_encode($value, JSON_UNESCAPED_UNICODE);
          }
          // If null, leave as null. If already string, leave as-is.
        }
      }
    }

    return $eventData;
  }

  /**
   * Decode JSON fields after find.
   *
   * Converts JSON strings to PHP arrays for consumption.
   * Handles both single-result and multi-result (findAll) queries.
   *
   * @param array $eventData CI4 callback event data
   * @return array
   */
  protected function decodeJsonFields(array $eventData): array
  {
    // $eventData['data'] contains the result(s)
    // $eventData['method'] tells us: 'find', 'first', 'findAll', etc.

    if (!isset($eventData['data']) || empty($eventData['data'])) {
      return $eventData;
    }

    $data = $eventData['data'];

    // Determine if this is a single row or multiple rows
    // findAll() returns array of arrays, find(id) returns single array
    if (isset($eventData['id']) && $eventData['id'] !== null) {
      // Single result (find by ID)
      $eventData['data'] = $this->decodeRow($data);
    } elseif (is_array($data) && !empty($data)) {
      // Check if it's a list of rows (array of arrays) or a single row
      if (isset($data[0]) && is_array($data[0])) {
        // Multiple rows (findAll, etc.)
        foreach ($data as $key => $row) {
          $data[$key] = $this->decodeRow($row);
        }
        $eventData['data'] = $data;
      } elseif (array_key_exists('id', $data)) {
        // Single row (first(), etc.)
        $eventData['data'] = $this->decodeRow($data);
      }
    }

    return $eventData;
  }

  /**
   * Decode JSON fields in a single row.
   *
   * @param array $row
   * @return array
   */
  private function decodeRow(array $row): array
  {
    foreach ($this->jsonFields as $field) {
      if (array_key_exists($field, $row) && is_string($row[$field]) && !empty($row[$field])) {
        $decoded = json_decode($row[$field], true);
        $row[$field] = is_array($decoded) ? $decoded : [];
      } elseif (array_key_exists($field, $row) && ($row[$field] === null || $row[$field] === '')) {
        $row[$field] = [];
      }
    }

    return $row;
  }

    // =========================================================================
    // LINE RETRIEVAL METHODS
    // =========================================================================

  /**
   * Get all active lines for a given challan, ordered by line_number.
   *
   * JSON fields are automatically decoded to PHP arrays via afterFind callback.
   *
   * @param int $challanId
   * @return array Array of line item arrays
   */
  public function getLinesByChallanId(int $challanId): array
  {
    $this->where('challan_id', $challanId);
    $this->orderBy('line_number', 'ASC');

    return $this->findAll();
  }

  /**
   * Get a single line item with decoded JSON fields.
   *
   * @param int $lineId
   * @return array|null Line data with decoded JSON, or null if not found
   */
  public function getLineWithDetails(int $lineId): ?array
  {
    return $this->find($lineId);
  }

  /**
   * Get lines with product and process names resolved.
   *
   * Fetches lines for a challan, then resolves product_ids and process_ids
   * to their actual names from the products and processes tables.
   *
   * @param int $challanId
   * @return array Lines with 'product_names' and 'process_names' keys added
   */
  public function getLinesWithNames(int $challanId): array
  {
    $lines = $this->getLinesByChallanId($challanId);

    if (empty($lines)) {
      return [];
    }

    // Collect all product IDs and process IDs across all lines
    $allProductIds = [];
    $allProcessIds = [];

    foreach ($lines as $line) {
      if (!empty($line['product_ids']) && is_array($line['product_ids'])) {
        $allProductIds = array_merge($allProductIds, $line['product_ids']);
      }
      if (!empty($line['process_ids']) && is_array($line['process_ids'])) {
        $allProcessIds = array_merge($allProcessIds, $line['process_ids']);
      }
    }

    $allProductIds = array_unique(array_filter($allProductIds));
    $allProcessIds = array_unique(array_filter($allProcessIds));

    // Batch-fetch product names
    $productMap = [];
    if (!empty($allProductIds)) {
      $products = $this->db->table('products')
        ->select('id, product_name, product_code')
        ->whereIn('id', $allProductIds)
        ->get()
        ->getResultArray();

      foreach ($products as $p) {
        $productMap[(int)$p['id']] = $p['product_name'] . ' (' . $p['product_code'] . ')';
      }
    }

    // Batch-fetch process names
    $processMap = [];
    if (!empty($allProcessIds)) {
      $processes = $this->db->table('processes')
        ->select('id, process_name, process_code')
        ->whereIn('id', $allProcessIds)
        ->get()
        ->getResultArray();

      foreach ($processes as $p) {
        $processMap[(int)$p['id']] = $p['process_name'] . ' (' . $p['process_code'] . ')';
      }
    }

    // Enrich each line with resolved names
    foreach ($lines as &$line) {
      $line['product_names'] = [];
      if (!empty($line['product_ids']) && is_array($line['product_ids'])) {
        foreach ($line['product_ids'] as $pid) {
          $line['product_names'][] = $productMap[(int)$pid] ?? "Product #{$pid}";
        }
      }

      $line['process_names'] = [];
      if (!empty($line['process_ids']) && is_array($line['process_ids'])) {
        foreach ($line['process_ids'] as $pid) {
          $line['process_names'][] = $processMap[(int)$pid] ?? "Process #{$pid}";
        }
      }
    }
    unset($line); // break reference

    return $lines;
  }

    // =========================================================================
    // LINE NUMBER MANAGEMENT
    // =========================================================================

  /**
   * Get the next sequential line number for a challan.
   *
   * @param int $challanId
   * @return int Next line number (1-based)
   */
  public function getNextLineNumber(int $challanId): int
  {
    $result = $this->selectMax('line_number', 'max_line')
      ->where('challan_id', $challanId)
      ->first();

    $maxLine = (int)($result['max_line'] ?? 0);

    return $maxLine + 1;
  }

  /**
   * Resequence line numbers for a challan after a line is deleted.
   *
   * Ensures line numbers remain sequential: 1, 2, 3...
   *
   * @param int $challanId
   * @return bool
   */
  public function resequenceLines(int $challanId): bool
  {
    $lines = $this->db->table($this->table)
      ->select('id')
      ->where('challan_id', $challanId)
      ->orderBy('line_number', 'ASC')
      ->get()
      ->getResultArray();

    $lineNumber = 1;
    foreach ($lines as $line) {
      $this->db->table($this->table)
        ->where('id', $line['id'])
        ->update([
          'line_number' => $lineNumber,
          'updated_at'  => date('Y-m-d H:i:s'),
        ]);
      $lineNumber++;
    }

    return true;
  }

    // =========================================================================
    // LINE DELETION
    // =========================================================================

  /**
   * Delete a challan line (hard delete).
   *
   * Note: challan_lines table does NOT have an is_deleted column.
   * Lines are hard-deleted. Tenant isolation is enforced via the parent
   * challan record (which has company_id and is_deleted).
   *
   * @param int $lineId
   * @return bool
   */
  public function deleteLine(int $lineId): bool
  {
    return $this->delete($lineId);
  }

  /**
   * Delete all lines for a given challan (hard delete).
   *
   * Used when a challan is being recreated or its lines are fully replaced.
   *
   * @param int $challanId
   * @return bool
   */
  public function deleteLinesByChallanId(int $challanId): bool
  {
    return $this->db->table($this->table)
      ->where('challan_id', $challanId)
      ->delete();
  }

    // =========================================================================
    // TOTALS & AGGREGATION
    // =========================================================================

  /**
   * Get total gold weight for a challan (sum of all lines).
   *
   * @param int $challanId
   * @return float Total gold weight in grams
   */
  public function getTotalWeightForChallan(int $challanId): float
  {
    $result = $this->db->table($this->table)
      ->selectSum('gold_weight', 'total_gold_weight')
      ->where('challan_id', $challanId)
      ->get()
      ->getRowArray();

    return (float)($result['total_gold_weight'] ?? 0.000);
  }

  /**
   * Get aggregated totals for a challan's lines.
   *
   * Returns sum of amounts, weight, quantity, and gold weight.
   *
   * @param int $challanId
   * @return array ['total_amount', 'total_weight', 'total_gold_weight', 'total_quantity']
   */
  public function getTotalsForChallan(int $challanId): array
  {
    $result = $this->db->table($this->table)
      ->selectSum('amount', 'total_amount')
      ->selectSum('weight', 'total_weight')
      ->selectSum('gold_weight', 'total_gold_weight')
      ->selectSum('quantity', 'total_quantity')
      ->where('challan_id', $challanId)
      ->get()
      ->getRowArray();

    return [
      'total_amount'      => (float)($result['total_amount'] ?? 0.00),
      'total_weight'      => (float)($result['total_weight'] ?? 0.000),
      'total_gold_weight' => (float)($result['total_gold_weight'] ?? 0.000),
      'total_quantity'    => (int)($result['total_quantity'] ?? 0),
    ];
  }

  /**
   * Get the line count for a challan.
   *
   * @param int $challanId
   * @return int
   */
  public function getLineCount(int $challanId): int
  {
    return (int)$this->db->table($this->table)
      ->where('challan_id', $challanId)
      ->countAllResults();
  }

    // =========================================================================
    // BULK OPERATIONS
    // =========================================================================

  /**
   * Insert multiple lines for a challan in bulk.
   *
   * Encodes JSON fields before insertion. Assigns sequential line numbers
   * starting from the next available number.
   *
   * @param int   $challanId
   * @param array $lines Array of line data arrays
   * @return bool
   */
  public function insertBulkLines(int $challanId, array $lines): bool
  {
    if (empty($lines)) {
      return true;
    }

    $nextLineNumber = $this->getNextLineNumber($challanId);

    foreach ($lines as $line) {
      $lineData = [
        'challan_id'     => $challanId,
        'line_number'    => $nextLineNumber,
        'product_ids'    => isset($line['product_ids']) && is_array($line['product_ids'])
          ? json_encode($line['product_ids'], JSON_UNESCAPED_UNICODE)
          : ($line['product_ids'] ?? null),
        'product_name'   => $line['product_name'] ?? null,
        'process_ids'    => isset($line['process_ids']) && is_array($line['process_ids'])
          ? json_encode($line['process_ids'], JSON_UNESCAPED_UNICODE)
          : ($line['process_ids'] ?? null),
        'process_prices' => isset($line['process_prices']) && is_array($line['process_prices'])
          ? json_encode($line['process_prices'], JSON_UNESCAPED_UNICODE)
          : ($line['process_prices'] ?? null),
        'quantity'       => (int)($line['quantity'] ?? 1),
        'weight'         => (float)($line['weight'] ?? 0.000),
        'rate'           => (float)($line['rate'] ?? 0.00),
        'amount'         => (float)($line['amount'] ?? 0.00),
        'image_path'     => $line['image_path'] ?? null,
        'gold_weight'    => isset($line['gold_weight']) ? (float)$line['gold_weight'] : null,
        'gold_fine_weight' => isset($line['gold_fine_weight']) ? (float)$line['gold_fine_weight'] : null,
        'gold_purity'    => $line['gold_purity'] ?? null,
        'current_gold_price' => isset($line['current_gold_price']) ? (float)$line['current_gold_price'] : null,
        'adjusted_gold_weight' => isset($line['adjusted_gold_weight']) ? (float)$line['adjusted_gold_weight'] : null,
        'gold_adjustment_amount' => isset($line['gold_adjustment_amount']) ? (float)$line['gold_adjustment_amount'] : null,
        'line_notes'     => $line['line_notes'] ?? null,
      ];

      // Use DB builder directly to avoid model callback double-encoding
      $this->db->table($this->table)->insert($lineData);

      $nextLineNumber++;
    }

    return true;
  }

  /**
   * Replace all lines for a challan (delete existing, insert new).
   *
   * Used during challan update when lines are fully replaced.
   * Wraps in a transaction for atomicity.
   *
   * @param int   $challanId
   * @param array $lines Array of new line data arrays
   * @return bool
   */
  public function replaceAllLines(int $challanId, array $lines): bool
  {
    $db = \Config\Database::connect();
    $db->transStart();

    // 1. Delete all existing lines
    $this->deleteLinesByChallanId($challanId);

    // 2. Insert new lines
    $this->insertBulkLines($challanId, $lines);

    $db->transComplete();

    return $db->transStatus() !== false;
  }
}

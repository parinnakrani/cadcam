<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * DeliveryModel
 * 
 * Handles deliveries table operations.
 * Note: 'is_deleted' is a boolean flag (0/1), NOT a timestamp.
 * Standard CI4 SoftDeletes expect a timestamp/null.
 * So we disable useSoftDeletes and handle 'is_deleted' manually.
 */
class DeliveryModel extends Model
{
  protected $table            = 'deliveries';
  protected $primaryKey       = 'id';
  protected $useAutoIncrement = true;
  protected $returnType       = 'array';

  // Disable built-in soft deletes to avoid "is_deleted IS NULL" queries
  protected $useSoftDeletes   = false;

  protected $protectFields    = true;
  protected $allowedFields    = [
    'company_id',
    'invoice_id',
    'assigned_to',
    'assigned_by',
    'assigned_date',
    'expected_delivery_date',
    'actual_delivery_date',
    'delivery_status', // 'Assigned', 'In Transit', 'Delivered', 'Failed'
    'delivery_address',
    'customer_contact_mobile',
    'delivery_contact_name',
    'delivery_notes',
    'failed_reason',
    'delivery_proof_photo',
    'delivered_timestamp',
    'is_deleted',
    'created_at',
    'updated_at'
  ];

  // Dates
  protected $useTimestamps = true;
  protected $dateFormat    = 'datetime';
  protected $createdField  = 'created_at';
  protected $updatedField  = 'updated_at';
  // deletedField is not used by CI4 logic since useSoftDeletes is false

  // Validation
  protected $validationRules      = [
    'company_id'              => 'required|integer',
    'invoice_id'              => 'required|integer',
    'assigned_to'             => 'required|integer',
    'expected_delivery_date'  => 'required|valid_date',
    'delivery_status'         => 'required|in_list[Assigned,In Transit,Delivered,Failed]',
    'delivery_address'        => 'required',
    // 'customer_contact_mobile' => 'required', // Optional as per schema nullable? No, schema says VARCHAR(20) DEFAULT NULL? Let's check. 
    // SQL dump: `customer_contact_mobile` VARCHAR(20) DEFAULT NULL.
  ];
  protected $validationMessages   = [];
  protected $skipValidation       = false;
  protected $cleanValidationRules = true;

  /**
   * Override findAll to always apply is_deleted check
   */
  public function findAll(int $limit = 0, int $offset = 0)
  {
    $this->where('is_deleted', 0);
    return parent::findAll($limit, $offset);
  }

  /**
   * Override first to always apply is_deleted check
   */
  public function first()
  {
    $this->where('is_deleted', 0);
    return parent::first();
  }

  /**
   * Override find to always apply is_deleted check
   * Note: find() calls first() internally in many cases or uses builder.
   * CI4 Model find() uses keys.
   */
  public function find($id = null)
  {
    $this->where('is_deleted', 0);
    return parent::find($id);
  }

  /**
   * Get deliveries by invoice ID
   */
  public function getDeliveriesByInvoice(int $invoiceId): array
  {
    return $this->where('invoice_id', $invoiceId)
      ->where('is_deleted', 0)
      ->findAll();
  }

  /**
   * Get deliveries assigned to a specific user (Active only)
   * Active = Not Delivered, Not Failed (so Assigned or In Transit)
   */
  public function getMyDeliveries(int $userId): array
  {
    return $this->where('assigned_to', $userId)
      ->where('is_deleted', 0)
      ->groupStart()
      ->where('delivery_status', 'Assigned')
      ->orWhere('delivery_status', 'In Transit')
      ->groupEnd()
      ->orderBy('expected_delivery_date', 'ASC')
      ->findAll();
  }

  /**
   * Get all pending deliveries (Assigned or In Transit) for admin/manager
   */
  public function getPendingDeliveries(): array
  {
    return $this->groupStart()
      ->where('delivery_status', 'Assigned')
      ->orWhere('delivery_status', 'In Transit')
      ->groupEnd()
      ->where('is_deleted', 0)
      ->orderBy('expected_delivery_date', 'ASC')
      ->findAll();
  }

  /**
   * Mark delivery as delivered
   */
  public function markAsDelivered(int $deliveryId, string $proofPhotoPath, string $actualDate): bool
  {
    // DB timestamp format
    $timestamp = date('Y-m-d H:i:s');

    return $this->update($deliveryId, [
      'delivery_status'      => 'Delivered',
      'delivery_proof_photo' => $proofPhotoPath,
      'actual_delivery_date' => $actualDate,
      'delivered_timestamp'  => $timestamp,
      'updated_at'           => $timestamp
    ]);
  }

  /**
   * Mark delivery as failed
   */
  public function markAsFailed(int $deliveryId, string $reason): bool
  {
    return $this->update($deliveryId, [
      'delivery_status' => 'Failed',
      'failed_reason'   => $reason,
      'updated_at'      => date('Y-m-d H:i:s')
    ]);
  }
}

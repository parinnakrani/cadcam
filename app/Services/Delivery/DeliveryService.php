<?php

namespace App\Services\Delivery;

use App\Models\DeliveryModel;
use App\Models\InvoiceModel;
use App\Services\FileUploadService;
use App\Services\Audit\AuditService;
use Exception;

/**
 * DeliveryService
 * 
 * Handles delivery management logic including assignment, execution, and tracking.
 */
class DeliveryService
{
  protected DeliveryModel $deliveryModel;
  protected InvoiceModel $invoiceModel;
  protected FileUploadService $fileUploadService;
  protected AuditService $auditService;

  public function __construct()
  {
    $this->deliveryModel = new DeliveryModel();
    $this->invoiceModel = new InvoiceModel();
    $this->fileUploadService = new FileUploadService();
    $this->auditService = new AuditService();
  }

  /**
   * Assign an invoice for delivery
   * 
   * @param int $invoiceId
   * @param int $assignedToUserId
   * @param int $assignedByUserId
   * @param string $expectedDate (Y-m-d)
   * @param string|null $notes
   * @return int Delivery ID
   * @throws Exception
   */
  public function assignDelivery(int $invoiceId, int $assignedToUserId, int $assignedByUserId, string $expectedDate, ?string $notes = null): int
  {
    // 1. Validate Invoice
    $invoice = $this->invoiceModel->find($invoiceId);
    if (!$invoice) {
      throw new Exception("Invoice ID {$invoiceId} not found.");
    }

    if ($invoice['payment_status'] !== 'Paid') {
      throw new Exception("Invoice {$invoice['invoice_number']} is not fully paid (Status: {$invoice['payment_status']}). Cannot assign delivery.");
    }

    // 2. Check if already assigned (active delivery)
    $existing = $this->deliveryModel->where('invoice_id', $invoiceId)
      ->where('is_deleted', 0)
      ->whereNotIn('delivery_status', ['Failed']) // Allow re-assign if previous failed? 
      // Actually, if failed, we might want to re-assign. 
      // So we only block if there is an active (Assigned/In Transit) or Completed delivery.
      ->whereNotIn('delivery_status', ['Failed', 'Cancelled']) // Assuming Cancelled might exist in future, but distinct from Failed
      // For now, prompt says "One invoice can have only one delivery assignment". 
      // But logic suggests we might retry. Let's block if 'Assigned', 'In Transit', 'Delivered'.
      // If 'Failed', we allow re-assignment (new record).
      ->whereIn('delivery_status', ['Assigned', 'In Transit', 'Delivered'])
      ->first();

    if ($existing) {
      throw new Exception("Invoice {$invoice['invoice_number']} is already assigned for delivery (Status: {$existing['delivery_status']}).");
    }

    // 3. Prepare Data
    // Get customer contact info from invoice
    // We need 'customer_contact_mobile' and 'delivery_address'
    // These are in 'invoice' table: 'shipping_address' (or billing if empty). 
    // Mobile is in relation.

    $customerMobile = '';
    $customerName = '';

    // Fetch full invoice with customer to get mobile
    $invoiceFull = $this->invoiceModel->getInvoiceWithCustomer($invoiceId);
    if ($invoiceFull && isset($invoiceFull['customer'])) {
      $customerMobile = $invoiceFull['customer']['mobile'] ?? $invoiceFull['customer']['mobile_number'] ?? ''; // Accounts use 'mobile', Cash use 'mobile_number'
      $customerName = $invoiceFull['customer']['customer_name'] ?? $invoiceFull['customer']['account_name'] ?? '';
    }

    $address = $invoice['shipping_address'] ?: $invoice['billing_address'];

    $data = [
      'company_id'              => $invoice['company_id'],
      'invoice_id'              => $invoiceId,
      'assigned_to'             => $assignedToUserId,
      'assigned_by'             => $assignedByUserId, // This is the 'created_by' equivalent for this table
      'assigned_date'           => date('Y-m-d H:i:s'),
      'expected_delivery_date'  => $expectedDate,
      'delivery_status'         => 'Assigned',
      'delivery_address'        => $address ?: 'Address Not Available',
      'customer_contact_mobile' => $customerMobile ?: 'N/A',
      'delivery_contact_name'   => $customerName ?: 'N/A',
      'delivery_notes'          => $notes,
      'is_deleted'              => 0
    ];

    // 4. Insert
    $deliveryId = $this->deliveryModel->insert($data);
    if (!$deliveryId) {
      throw new Exception("Failed to create delivery record: " . json_encode($this->deliveryModel->errors()));
    }

    // 5. Audit Log
    $this->auditService->log('Delivery', 'create', 'Delivery', $deliveryId, null, ['invoice_id' => $invoiceId, 'assigned_to' => $assignedToUserId]);

    return $deliveryId;
  }

  /**
   * Start delivery (mark as In Transit)
   * 
   * @param int $deliveryId
   * @return bool
   * @throws Exception
   */
  public function startDelivery(int $deliveryId): bool
  {
    $delivery = $this->deliveryModel->find($deliveryId);
    if (!$delivery) {
      throw new Exception("Delivery ID {$deliveryId} not found.");
    }

    if ($delivery['delivery_status'] !== 'Assigned') {
      throw new Exception("Cannot start delivery. Current status: {$delivery['delivery_status']}");
    }

    $this->deliveryModel->update($deliveryId, [
      'delivery_status' => 'In Transit'
    ]);

    $this->auditService->log('Delivery', 'update', 'Delivery', $deliveryId, ['delivery_status' => 'Assigned'], ['delivery_status' => 'In Transit']);

    return true;
  }

  /**
   * Mark delivery as delivered with proof
   * 
   * @param int $deliveryId
   * @param \CodeIgniter\HTTP\Files\UploadedFile $proofPhotoFile
   * @return bool
   * @throws Exception
   */
  public function markDelivered(int $deliveryId, $proofPhotoFile): bool
  {
    $delivery = $this->deliveryModel->find($deliveryId);
    if (!$delivery) {
      throw new Exception("Delivery ID {$deliveryId} not found.");
    }

    // Upload Photo
    $fileName = $this->fileUploadService->uploadFile($proofPhotoFile, 'uploads/delivery_proofs', ['jpg', 'jpeg', 'png']);
    $photoPath = 'uploads/delivery_proofs/' . $fileName;

    $db = \Config\Database::connect();
    $db->transStart();

    try {
      // Update Delivery
      $this->deliveryModel->markAsDelivered($deliveryId, $photoPath, date('Y-m-d'));

      // Update Invoice Status
      // Note: InvoiceModel has markAsDelivered method
      $this->invoiceModel->markAsDelivered($delivery['invoice_id']);

      $db->transComplete();

      if ($db->transStatus() === false) {
        throw new Exception("Transaction failed while marking delivery as complete.");
      }

      $this->auditService->log('Delivery', 'update', 'Delivery', $deliveryId, ['delivery_status' => 'In Transit'], ['delivery_status' => 'Delivered']);

      return true;
    } catch (Exception $e) {
      $db->transRollback();
      // Try to cleanup uploaded file if db failed?
      // $this->fileUploadService->deleteFile($photoPath);
      throw $e;
    }
  }

  /**
   * Mark delivery as failed
   * 
   * @param int $deliveryId
   * @param string $reason
   * @return bool
   * @throws Exception
   */
  public function markFailed(int $deliveryId, string $reason): bool
  {
    $delivery = $this->deliveryModel->find($deliveryId);
    if (!$delivery) {
      throw new Exception("Delivery ID {$deliveryId} not found.");
    }

    $this->deliveryModel->markAsFailed($deliveryId, $reason);

    $this->auditService->log('Delivery', 'update', 'Delivery', $deliveryId, ['delivery_status' => 'In Transit'], ['delivery_status' => 'Failed', 'failed_reason' => $reason]);

    return true;
  }

  /**
   * Get dashboard stats for a delivery user
   * 
   * @param int $userId
   * @return array
   */
  public function getMyDashboard(int $userId): array
  {
    $assigned = $this->deliveryModel->where('assigned_to', $userId)
      ->where('delivery_status', 'Assigned')
      ->where('is_deleted', 0)
      ->countAllResults();

    $inTransit = $this->deliveryModel->where('assigned_to', $userId)
      ->where('delivery_status', 'In Transit')
      ->where('is_deleted', 0)
      ->countAllResults();

    $deliveredToday = $this->deliveryModel->where('assigned_to', $userId)
      ->where('delivery_status', 'Delivered')
      ->where('actual_delivery_date', date('Y-m-d'))
      ->where('is_deleted', 0)
      ->countAllResults();

    return [
      'assigned' => $assigned,
      'in_transit' => $inTransit,
      'delivered_today' => $deliveredToday
    ];
  }

  /**
   * Get list of invoices that are eligible for delivery assignment
   * Rule: Paid status, Not already Assigned/In Transit/Delivered
   * 
   * @param int $companyId
   * @return array
   */
  public function getDeliverableInvoices(int $companyId): array
  {
    // Get all Paid invoices
    $invoices = $this->invoiceModel->where('company_id', $companyId)
      ->where('payment_status', 'Paid')
      ->where('invoice_status !=', 'Delivered') // Optimization
      ->where('is_deleted', 0) // Explicitly check soft delete for invoices if model uses flag!
      // InvoiceModel step 30 says it sets where('is_deleted', 0) in findAll.
      // But we are calling findAll() here, so InvoiceModel's findAll override (if present) will be used.
      // Let's assume standard Model methods are safe.
      ->findAll();

    if (empty($invoices)) {
      return [];
    }

    // Get IDs of active deliveries
    $activeDeliveryInvoiceIds = $this->deliveryModel->where('company_id', $companyId)
      ->whereIn('delivery_status', ['Assigned', 'In Transit', 'Delivered'])
      ->where('is_deleted', 0)
      ->findColumn('invoice_id');

    $activeDeliveryInvoiceIds = $activeDeliveryInvoiceIds ?? [];

    // Filter invoices
    $deliverable = [];
    foreach ($invoices as $inv) {
      if (!in_array($inv['id'], $activeDeliveryInvoiceIds)) {
        $deliverable[] = $inv;
      }
    }

    // Optional: formatting for dropdown (id => number)
    return $deliverable;
  }
}

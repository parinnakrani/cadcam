<?php

namespace App\Services\Numbering;

use App\Models\CompanyModel;
use App\Models\InvoiceModel;
use Exception;

/**
 * NumberingService
 * 
 * Handles sequential number generation for invoices and other documents.
 * Ensures uniqueness and gapless numbering where possible using DB locks.
 */
class NumberingService
{
  protected CompanyModel $companyModel;
  protected InvoiceModel $invoiceModel;
  protected $db;

  public function __construct()
  {
    $this->companyModel = new CompanyModel();
    $this->invoiceModel = new InvoiceModel();
    $this->db = \Config\Database::connect();
  }

  /**
   * Get the next invoice number for a company.
   * 
   * Uses CompanyModel's thread-safe numbering mechanism.
   * Formats the number with the company's invoice prefix.
   * 
   * @param int $companyId
   * @param string $invoiceType (Optional) Type of invoice, could affect prefix in future
   * @return string Formatted invoice number (e.g., INV-0001)
   * @throws Exception
   */
  public function getNextInvoiceNumber(int $companyId, string $invoiceType = ''): string
  {
    // Get next number from company model (handles locking and incrementing)
    $nextNumber = $this->companyModel->getNextInvoiceNumber($companyId);

    // Get prefix
    $company = $this->companyModel->find($companyId);
    if (!$company) {
      throw new Exception("Company ID {$companyId} not found");
    }

    $prefix = $company['invoice_prefix'] ?? 'INV-';

    // Pad with zeros (e.g., 0001)
    $paddedNumber = str_pad((string)$nextNumber, 4, '0', STR_PAD_LEFT);

    $invoiceNumber = $prefix . $paddedNumber;

    // Final safety check for uniqueness
    if ($this->invoiceModel->where('invoice_number', $invoiceNumber)
      ->where('company_id', $companyId)
      ->countAllResults() > 0
    ) {
      // This is a critical failure - the sequence is out of sync with actual records
      // In a real scenario, we might retry or throw an alert
      throw new Exception("Generated invoice number {$invoiceNumber} already exists. Sequence mismatch detected.");
    }

    return $invoiceNumber;
  }

  /**
   * Get the next challan number for a company.
   * 
   * @param int $companyId
   * @return string Formatted challan number
   * @throws Exception
   */
  public function getNextChallanNumber(int $companyId): string
  {
    $nextNumber = $this->companyModel->getNextChallanNumber($companyId);

    $company = $this->companyModel->find($companyId);
    if (!$company) {
      throw new Exception("Company ID {$companyId} not found");
    }

    $prefix = $company['challan_prefix'] ?? 'CH-';

    $paddedNumber = str_pad((string)$nextNumber, 4, '0', STR_PAD_LEFT);

    return $prefix . $paddedNumber;
  }
}

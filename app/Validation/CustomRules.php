<?php

namespace App\Validation;

use App\Services\Common\ValidationService;

class CustomRules
{
  /**
   * @var ValidationService
   */
  protected $service;

  public function __construct()
  {
    $this->service = new ValidationService();
  }

  /**
   * Rule: gst_number
   */
  public function gst_number(string $str, string &$error = null): bool
  {
    if (empty($str)) return true; // Let required rule check empty if needed
    if (!$this->service->validateGST($str)) {
      $error = 'The {field} must be a valid GST Number (e.g. 22AAAAA0000A1Z5).';
      return false;
    }
    return true;
  }

  /**
   * Rule: pan_number
   */
  public function pan_number(string $str, string &$error = null): bool
  {
    if (empty($str)) return true;
    if (!$this->service->validatePAN($str)) {
      $error = 'The {field} must be a valid PAN Number (e.g. AAAAA0000A).';
      return false;
    }
    return true;
  }

  /**
   * Rule: indian_mobile
   */
  public function indian_mobile(string $str, string &$error = null): bool
  {
    if (empty($str)) return true;
    if (!$this->service->validateMobile($str)) {
      $error = 'The {field} must be a valid 10-digit Indian mobile number.';
      return false;
    }
    return true;
  }

  /**
   * Rule: positive_amount
   */
  public function positive_amount(string $str, string &$error = null): bool
  {
    if (!is_numeric($str) || $str <= 0) {
      $error = 'The {field} must be a positive amount greater than zero.';
      return false;
    }
    return true;
  }

  /**
   * Rule: not_future_date
   * Ensures date is today or in the past
   */
  public function not_future_date(string $str, string &$error = null): bool
  {
    if (empty($str)) return true;
    // validateDate in service checks format and "not future" logic
    if (!$this->service->validateDate($str)) {
      $error = 'The {field} cannot be in the future.';
      return false;
    }
    return true;
  }

  /**
   * Rule: future_date
   * Ensures date is in the future (if needed, despite prompt ambiguity, implemented strictly as "must be future")
   * But prompt said "future_date (date not in future)" -> contradicting. 
   * I implemented logic in `not_future_date` above to satisfy the prompt's description.
   */
}

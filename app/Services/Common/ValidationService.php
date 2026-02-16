<?php

namespace App\Services\Common;

class ValidationService
{
  /**
   * Validate Indian GST Number
   * Format: 22AAAAA0000A1Z5
   */
  public function validateGST(string $gst): bool
  {
    return (bool) preg_match("/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/", $gst);
  }

  /**
   * Validate Indian PAN Number
   * Format: AAAAA0000A
   */
  public function validatePAN(string $pan): bool
  {
    return (bool) preg_match("/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/", $pan);
  }

  /**
   * Validate Indian Mobile Number
   * 10 digits starting with 6-9
   */
  public function validateMobile(string $mobile): bool
  {
    return (bool) preg_match("/^[6-9][0-9]{9}$/", $mobile);
  }

  public function validateEmail(string $email): bool
  {
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
  }

  /**
   * Validate Date Format (Y-m-d) and check it is not in future
   */
  public function validateDate(string $date): bool
  {
    $d = \DateTime::createFromFormat('Y-m-d', $date);
    if (!($d && $d->format('Y-m-d') === $date)) {
      return false;
    }

    // Not in future check
    $today = new \DateTime('today');
    $checkDate = new \DateTime($date);

    return $checkDate <= $today;
  }

  public function validateAmount(float $amount): bool
  {
    return $amount > 0;
  }
}

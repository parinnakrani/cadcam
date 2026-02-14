<?php

namespace App\Services\Validation;

/**
 * Placeholder ValidationService
 */
class ValidationService
{
    /**
     * Validate GST format.
     * India Format: 2 digits + 5 letters + 4 digits + 1 letter + 1 digit + 'Z' + 1 char.
     * Regex: /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/
     * 
     * @param string $gst
     * @return bool
     */
    public function isValidGST(string $gst): bool
    {
        return preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', $gst) === 1;
    }

    /**
     * Validate PAN format.
     * India Format: 5 letters + 4 digits + 1 letter.
     * Regex: /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/
     * 
     * @param string $pan
     * @return bool
     */
    public function isValidPAN(string $pan): bool
    {
        return preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $pan) === 1;
    }
}

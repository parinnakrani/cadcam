<?php

if (!function_exists('formatCurrency')) {
  function formatCurrency(float $amount = null): string
  {
    if ($amount === null) return '₹ 0.00';
    return '₹ ' . number_format($amount, 2);
  }
}

if (!function_exists('formatDate')) {
  function formatDate($date, string $format = 'd-M-Y'): string
  {
    if (!$date || $date == '0000-00-00') return '-';
    return date($format, strtotime($date));
  }
}

if (!function_exists('formatWeight')) {
  function formatWeight(float $grams = null, int $decimals = 3): string
  {
    if ($grams === null) return '0.000 g';
    return number_format($grams, $decimals) . ' g';
  }
}

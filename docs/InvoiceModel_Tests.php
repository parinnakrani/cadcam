<?php

/**
 * InvoiceModel Test Cases
 * 
 * Manual test scenarios to verify InvoiceModel functionality
 * Run these in a controller or test environment
 */

// Test 1: Create Invoice
function testCreateInvoice()
{
  $invoiceModel = new \App\Models\InvoiceModel();

  $data = [
    'invoice_number'  => 'INV-TEST-001',
    'invoice_type'    => 'Cash Invoice',
    'invoice_date'    => date('Y-m-d'),
    'cash_customer_id' => 1,
    'subtotal'        => 10000.00,
    'tax_rate'        => 3.00,
    'tax_amount'      => 300.00,
    'igst_amount'     => 300.00,
    'cgst_amount'     => 0.00,
    'sgst_amount'     => 0.00,
    'grand_total'     => 10300.00,
    'total_paid'      => 0.00,
    'amount_due'      => 10300.00,
    'invoice_status'  => 'Draft',
    'payment_status'  => 'Pending',
    'created_by'      => session()->get('user_id'),
  ];

  $invoiceId = $invoiceModel->insert($data);

  if ($invoiceId) {
    echo "✅ Invoice created successfully: ID = $invoiceId\n";
    return $invoiceId;
  } else {
    echo "❌ Failed to create invoice\n";
    print_r($invoiceModel->errors());
    return null;
  }
}

// Test 2: Get Invoice with Customer
function testGetInvoiceWithCustomer($invoiceId)
{
  $invoiceModel = new \App\Models\InvoiceModel();
  $invoice = $invoiceModel->getInvoiceWithCustomer($invoiceId);

  if ($invoice && isset($invoice['customer'])) {
    echo "✅ Invoice with customer retrieved successfully\n";
    echo "   Invoice: {$invoice['invoice_number']}\n";
    echo "   Customer: {$invoice['customer']['customer_name']}\n";
    return true;
  } else {
    echo "❌ Failed to get invoice with customer\n";
    return false;
  }
}

// Test 3: Update Payment Status - Partial Payment
function testPartialPayment($invoiceId)
{
  $invoiceModel = new \App\Models\InvoiceModel();

  // Pay 50% of invoice
  $success = $invoiceModel->updatePaymentStatus($invoiceId, 5150.00);

  if ($success) {
    $invoice = $invoiceModel->find($invoiceId);
    echo "✅ Partial payment recorded\n";
    echo "   Total Paid: ₹{$invoice['total_paid']}\n";
    echo "   Amount Due: ₹{$invoice['amount_due']}\n";
    echo "   Payment Status: {$invoice['payment_status']}\n";

    // Verify calculations
    if ($invoice['payment_status'] === 'Partial Paid' && $invoice['amount_due'] == 5150.00) {
      echo "✅ Payment status calculation correct\n";
      return true;
    } else {
      echo "❌ Payment status calculation incorrect\n";
      return false;
    }
  } else {
    echo "❌ Failed to update payment status\n";
    return false;
  }
}

// Test 4: Update Payment Status - Full Payment
function testFullPayment($invoiceId)
{
  $invoiceModel = new \App\Models\InvoiceModel();

  // Pay remaining amount
  $success = $invoiceModel->updatePaymentStatus($invoiceId, 10300.00);

  if ($success) {
    $invoice = $invoiceModel->find($invoiceId);
    echo "✅ Full payment recorded\n";
    echo "   Total Paid: ₹{$invoice['total_paid']}\n";
    echo "   Amount Due: ₹{$invoice['amount_due']}\n";
    echo "   Payment Status: {$invoice['payment_status']}\n";
    echo "   Invoice Status: {$invoice['invoice_status']}\n";

    // Verify auto-update to Paid status
    if ($invoice['payment_status'] === 'Paid' && $invoice['invoice_status'] === 'Paid') {
      echo "✅ Auto-update to Paid status correct\n";
      return true;
    } else {
      echo "❌ Auto-update to Paid status failed\n";
      return false;
    }
  } else {
    echo "❌ Failed to update payment status\n";
    return false;
  }
}

// Test 5: Get Outstanding Invoices
function testGetOutstandingInvoices()
{
  $invoiceModel = new \App\Models\InvoiceModel();
  $outstanding = $invoiceModel->getOutstandingInvoices();

  echo "✅ Outstanding invoices retrieved: " . count($outstanding) . " invoices\n";

  foreach ($outstanding as $invoice) {
    echo "   - {$invoice['invoice_number']}: ₹{$invoice['amount_due']} due\n";
  }

  return true;
}

// Test 6: Can Delete - Should Fail (has payments)
function testCannotDeletePaidInvoice($invoiceId)
{
  $invoiceModel = new \App\Models\InvoiceModel();

  if ($invoiceModel->canDelete($invoiceId)) {
    echo "❌ canDelete returned true for paid invoice (should be false)\n";
    return false;
  } else {
    echo "✅ canDelete correctly prevents deletion of paid invoice\n";
    return true;
  }
}

// Test 7: Mark as Delivered
function testMarkAsDelivered($invoiceId)
{
  $invoiceModel = new \App\Models\InvoiceModel();

  $success = $invoiceModel->markAsDelivered($invoiceId);

  if ($success) {
    $invoice = $invoiceModel->find($invoiceId);
    if ($invoice['invoice_status'] === 'Delivered') {
      echo "✅ Invoice marked as delivered\n";
      return true;
    } else {
      echo "❌ Invoice status not updated to Delivered\n";
      return false;
    }
  } else {
    echo "❌ Failed to mark invoice as delivered\n";
    return false;
  }
}

// Test 8: Get Total Sales
function testGetTotalSales()
{
  $invoiceModel = new \App\Models\InvoiceModel();

  $startDate = date('Y-m-01'); // First day of current month
  $endDate = date('Y-m-d');    // Today

  $totalSales = $invoiceModel->getTotalSales($startDate, $endDate);

  echo "✅ Total sales for current month: ₹" . number_format($totalSales, 2) . "\n";
  return true;
}

// Test 9: Get Total Outstanding
function testGetTotalOutstanding()
{
  $invoiceModel = new \App\Models\InvoiceModel();

  $totalOutstanding = $invoiceModel->getTotalOutstanding();

  echo "✅ Total outstanding: ₹" . number_format($totalOutstanding, 2) . "\n";
  return true;
}

// Run all tests
function runAllInvoiceModelTests()
{
  echo "\n=== InvoiceModel Test Suite ===\n\n";

  // Test 1: Create Invoice
  echo "Test 1: Create Invoice\n";
  $invoiceId = testCreateInvoice();
  if (!$invoiceId) return;
  echo "\n";

  // Test 2: Get Invoice with Customer
  echo "Test 2: Get Invoice with Customer\n";
  testGetInvoiceWithCustomer($invoiceId);
  echo "\n";

  // Test 3: Partial Payment
  echo "Test 3: Partial Payment\n";
  testPartialPayment($invoiceId);
  echo "\n";

  // Test 4: Full Payment
  echo "Test 4: Full Payment\n";
  testFullPayment($invoiceId);
  echo "\n";

  // Test 5: Get Outstanding Invoices
  echo "Test 5: Get Outstanding Invoices\n";
  testGetOutstandingInvoices();
  echo "\n";

  // Test 6: Cannot Delete Paid Invoice
  echo "Test 6: Cannot Delete Paid Invoice\n";
  testCannotDeletePaidInvoice($invoiceId);
  echo "\n";

  // Test 7: Mark as Delivered
  echo "Test 7: Mark as Delivered\n";
  testMarkAsDelivered($invoiceId);
  echo "\n";

  // Test 8: Get Total Sales
  echo "Test 8: Get Total Sales\n";
  testGetTotalSales();
  echo "\n";

  // Test 9: Get Total Outstanding
  echo "Test 9: Get Total Outstanding\n";
  testGetTotalOutstanding();
  echo "\n";

  echo "=== All Tests Complete ===\n\n";
}

// Usage in controller:
// runAllInvoiceModelTests();

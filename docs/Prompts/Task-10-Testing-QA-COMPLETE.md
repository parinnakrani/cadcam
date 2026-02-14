# AI CODING PROMPTS - TASK 10
## Testing & Quality Assurance

**Version:** 1.0  
**Phase:** 10 - Testing (Ongoing)  
**Generated:** February 10, 2026

---

## SUBTASKS: All Testing Tasks (10.1-10.5)

---

## 🎯 TASK 10.1: UNIT TESTS

### Subtask 10.1.1: Setup PHPUnit Configuration

```
[PASTE .antigravity RULES FIRST]

FILE: phpunit.xml

Configure PHPUnit for CodeIgniter 4:
- Test directory: tests/
- Bootstrap: vendor/autoload.php
- Code coverage: app/
- Test suffix: Test.php

DELIVERABLES: phpunit.xml configuration

ACCEPTANCE CRITERIA: PHPUnit runs successfully
```

---

### Subtask 10.1.2-10.1.6: Create Unit Tests for Services

```
[PASTE .antigravity RULES FIRST]

Create unit tests for all services:

FILE 1: tests/unit/Services/TaxCalculationServiceTest.php
- testDetermineTaxType_SameState_ReturnsCGST_SGST()
- testDetermineTaxType_DifferentState_ReturnsIGST()
- testCalculateInvoiceTax_CGST_SGST()
- testCalculateInvoiceTax_IGST()
- testCalculateTax_ZeroAmount()

FILE 2: tests/unit/Services/GoldAdjustmentServiceTest.php
- testCalculateAdjustment_RateIncreased()
- testCalculateAdjustment_RateDecreased()
- testCalculateAdjustment_NoChange()
- testCalculateAdjustment_NegativeFinalAmount()

FILE 3: tests/unit/Services/InvoiceCalculationServiceTest.php
- testCalculateLineTotal()
- testCalculateInvoiceTotals()
- testRecalculateAmountDue()

FILE 4: tests/unit/Services/ChallanCalculationServiceTest.php
- testCalculateLineTotal()
- testCalculateChallanTotals()
- testRecalculateProcessAmounts()

FILE 5: tests/unit/Services/LedgerServiceTest.php
- testCreateInvoiceLedgerEntry()
- testCreatePaymentLedgerEntry()
- testCalculateRunningBalance()
- testGetAccountBalance()

FILE 6: tests/unit/Services/PaymentValidationServiceTest.php
- testValidatePaymentAmount()
- testValidatePaymentMode()
- testValidateInvoiceOutstanding()

DELIVERABLES: 6+ unit test files with 30+ tests

ACCEPTANCE CRITERIA: All tests pass, code coverage > 80%
```

---

## 🎯 TASK 10.2: INTEGRATION TESTS

### Subtask 10.2.1-10.2.5: Create Integration Tests

```
[PASTE .antigravity RULES FIRST]

FILE 1: tests/integration/ChallanToInvoiceTest.php
- testCreateChallanAndConvertToInvoice()
- testInvoiceAmountsMatchChallanAmounts()
- testChallanMarkedAsInvoiced()
- testCannotConvertInvoicedChallanAgain()

FILE 2: tests/integration/PaymentFlowTest.php
- testRecordPayment_UpdatesInvoiceStatus()
- testRecordPayment_CreatesLedgerEntry()
- testRecordPayment_UpdatesCustomerBalance()
- testPartialPayment_InvoicePartiallyPaid()
- testFullPayment_InvoiceMarkedPaid()

FILE 3: tests/integration/LedgerBalanceTest.php
- testOpeningBalance_CreatesLedgerEntry()
- testInvoice_IncreasesDebitBalance()
- testPayment_DecreasesCreditBalance()
- testBalanceCalculation_Accurate()

FILE 4: tests/integration/GoldAdjustmentFlowTest.php
- testPaymentWithGoldAdjustment_CalculatesCorrectly()
- testPaymentWithoutGoldAdjustment_UsesOriginalAmount()
- testGoldAdjustment_UpdatesLedger()

FILE 5: tests/integration/DeliveryFlowTest.php
- testAssignDelivery_UpdatesInvoiceStatus()
- testMarkDelivered_UploadsProof()
- testMarkDelivered_UpdatesInvoiceStatus()

DELIVERABLES: 5 integration test files with 20+ tests

ACCEPTANCE CRITERIA: All integration tests pass
```

---

## 🎯 TASK 10.3: SERVICE LAYER TESTS

### Subtask 10.3.1-10.3.3: Test All Services

```
[PASTE .antigravity RULES FIRST]

FILE 1: tests/unit/Services/InvoiceServiceTest.php
- testCreateInvoice_Success()
- testCreateInvoiceFromChallan_Success()
- testUpdateInvoice_PaidInvoice_ThrowsException()
- testDeleteInvoice_PaidInvoice_ThrowsException()
- testRecordPayment_UpdatesStatus()

FILE 2: tests/unit/Services/ChallanServiceTest.php
- testCreateChallan_Success()
- testUpdateChallan_InvoicedChallan_ThrowsException()
- testDeleteChallan_InvoicedChallan_ThrowsException()
- testUpdateChallanStatus_ValidTransition()
- testUpdateChallanStatus_InvalidTransition_ThrowsException()

FILE 3: tests/unit/Services/PaymentServiceTest.php
- testCreatePayment_Success()
- testCreatePayment_OverpayInvoice_ThrowsException()
- testCreatePayment_WithGoldAdjustment()
- testDeletePayment_UpdatesInvoiceStatus()

DELIVERABLES: Service test files

ACCEPTANCE CRITERIA: All service tests pass
```

---

## 🎯 TASK 10.4: API ENDPOINT TESTS

### Subtask 10.4.1-10.4.4: Test All API Endpoints

```
[PASTE .antigravity RULES FIRST]

FILE 1: tests/api/InvoiceAPITest.php
- testCreateInvoice_ValidData_ReturnsSuccess()
- testCreateInvoice_InvalidData_ReturnsError()
- testUpdateInvoice_PaidInvoice_ReturnsError()
- testDeleteInvoice_PaidInvoice_ReturnsError()

FILE 2: tests/api/PaymentAPITest.php
- testRecordPayment_ValidData_ReturnsSuccess()
- testCalculateGoldAdjustment_ReturnsCorrectAmount()

FILE 3: tests/api/ChallanAPITest.php
- testAddChallanLine_ReturnsSuccess()
- testDeleteChallanLine_UpdatesTotals()
- testChangeStatus_ValidTransition_ReturnsSuccess()

FILE 4: tests/api/SearchAPITest.php
- testSearchProducts_ReturnsResults()
- testSearchCustomers_ReturnsResults()

DELIVERABLES: API test files

ACCEPTANCE CRITERIA: All API endpoints tested
```

---

## 🎯 TASK 10.5: END-TO-END TESTS

### Subtask 10.5.1-10.5.4: Create E2E Test Scenarios

```
[PASTE .antigravity RULES FIRST]

FILE 1: tests/e2e/CompleteInvoiceFlowTest.php
Complete flow test:
1. Create account customer
2. Create products and processes
3. Create challan with lines
4. Convert challan to invoice
5. Record payment
6. Verify ledger entry
7. Verify customer balance updated

FILE 2: tests/e2e/CashCustomerFlowTest.php
1. Create cash customer
2. Create cash invoice
3. Record immediate payment
4. Verify payment status

FILE 3: tests/e2e/DeliveryFlowTest.php
1. Create invoice
2. Record payment
3. Assign delivery
4. Upload proof of delivery
5. Verify invoice status = Delivered

FILE 4: tests/e2e/ReportGenerationTest.php
1. Create multiple invoices and payments
2. Generate ledger report
3. Verify report data accuracy
4. Export to PDF/Excel
5. Verify export file exists

DELIVERABLES: 4 E2E test scenarios

ACCEPTANCE CRITERIA: All E2E tests pass, complete workflows verified
```

---

## TESTING BEST PRACTICES

```
1. Use database transactions in tests (rollback after each test)
2. Use factories for test data generation
3. Mock external dependencies (email, SMS, etc.)
4. Test both success and failure scenarios
5. Test edge cases (null values, empty strings, boundary values)
6. Test validation rules
7. Test permission checks
8. Test SQL injection prevention
9. Test CSRF protection
10. Achieve minimum 80% code coverage

CONTINUOUS INTEGRATION:
- Setup GitHub Actions or GitLab CI
- Run tests on every commit
- Run tests before merge to main branch
- Generate code coverage reports
```

---

**END OF TASK-10 COMPLETE**

---

## 🎉 ALL TASKS COMPLETE!

**Total Subtasks Covered:** 100+  
**Total AI Prompts Generated:** 150+  
**Estimated Implementation Time:** 20 weeks  
**Code Coverage Target:** 80%+

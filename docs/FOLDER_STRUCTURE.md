# CODEIGNITER 4 FOLDER STRUCTURE
## Gold Manufacturing & Billing ERP System

```
gold-erp/
│
├── app/
│   ├── Config/
│   │   ├── Routes.php
│   │   ├── Database.php
│   │   ├── Filters.php
│   │   ├── Validation.php
│   │   └── Constants.php
│   │
│   ├── Controllers/
│   │   ├── BaseController.php
│   │   ├── Auth/
│   │   │   ├── LoginController.php
│   │   │   ├── LogoutController.php
│   │   │   └── PasswordController.php
│   │   ├── Companies/
│   │   │   ├── CompanyController.php
│   │   │   └── CompanySettingsController.php
│   │   ├── Users/
│   │   │   ├── UserController.php
│   │   │   └── RoleController.php
│   │   ├── Masters/
│   │   │   ├── GoldRateController.php
│   │   │   ├── ProductController.php
│   │   │   ├── ProcessController.php
│   │   │   ├── ProductCategoryController.php
│   │   │   └── StateController.php
│   │   ├── Customers/
│   │   │   ├── AccountController.php
│   │   │   └── CashCustomerController.php
│   │   ├── Challans/
│   │   │   ├── ChallanController.php
│   │   │   ├── RhodiumChallanController.php
│   │   │   ├── MeenaChallanController.php
│   │   │   └── WaxChallanController.php
│   │   ├── Invoices/
│   │   │   ├── InvoiceController.php
│   │   │   ├── AccountInvoiceController.php
│   │   │   ├── CashInvoiceController.php
│   │   │   └── WaxInvoiceController.php
│   │   ├── Payments/
│   │   │   └── PaymentController.php
│   │   ├── Deliveries/
│   │   │   └── DeliveryController.php
│   │   ├── Reports/
│   │   │   ├── LedgerReportController.php
│   │   │   ├── ReceivableReportController.php
│   │   │   ├── OutstandingReportController.php
│   │   │   ├── PaymentReportController.php
│   │   │   ├── TaxReportController.php
│   │   │   └── DashboardController.php
│   │   └── Audit/
│   │       └── AuditLogController.php
│   │
│   ├── Models/
│   │   ├── CompanyModel.php
│   │   ├── StateModel.php
│   │   ├── RoleModel.php
│   │   ├── UserModel.php
│   │   ├── UserRoleModel.php
│   │   ├── GoldRateModel.php
│   │   ├── ProductCategoryModel.php
│   │   ├── ProductModel.php
│   │   ├── ProcessModel.php
│   │   ├── AccountModel.php
│   │   ├── CashCustomerModel.php
│   │   ├── ChallanModel.php
│   │   ├── ChallanLineModel.php
│   │   ├── InvoiceModel.php
│   │   ├── InvoiceLineModel.php
│   │   ├── PaymentModel.php
│   │   ├── LedgerEntryModel.php
│   │   ├── DeliveryModel.php
│   │   ├── AuditLogModel.php
│   │   └── CompanySettingModel.php
│   │
│   ├── Services/
│   │   ├── Auth/
│   │   │   ├── AuthService.php
│   │   │   └── PermissionService.php
│   │   ├── Company/
│   │   │   └── CompanyService.php
│   │   ├── User/
│   │   │   ├── UserService.php
│   │   │   └── RoleService.php
│   │   ├── Master/
│   │   │   ├── GoldRateService.php
│   │   │   ├── ProductService.php
│   │   │   └── ProcessService.php
│   │   ├── Customer/
│   │   │   ├── AccountService.php
│   │   │   └── CashCustomerService.php
│   │   ├── Challan/
│   │   │   ├── ChallanService.php
│   │   │   ├── ChallanValidationService.php
│   │   │   └── ChallanCalculationService.php
│   │   ├── Invoice/
│   │   │   ├── InvoiceService.php
│   │   │   ├── InvoiceValidationService.php
│   │   │   ├── InvoiceCalculationService.php
│   │   │   └── TaxCalculationService.php
│   │   ├── Payment/
│   │   │   ├── PaymentService.php
│   │   │   ├── PaymentValidationService.php
│   │   │   └── GoldAdjustmentService.php
│   │   ├── Delivery/
│   │   │   └── DeliveryService.php
│   │   ├── Ledger/
│   │   │   └── LedgerService.php
│   │   ├── Report/
│   │   │   ├── LedgerReportService.php
│   │   │   ├── ReceivableReportService.php
│   │   │   ├── OutstandingReportService.php
│   │   │   └── DashboardService.php
│   │   ├── Audit/
│   │   │   └── AuditService.php
│   │   └── Common/
│   │       ├── NumberingService.php
│   │       ├── ValidationService.php
│   │       └── FileUploadService.php
│   │
│   ├── Filters/
│   │   ├── AuthFilter.php
│   │   ├── PermissionFilter.php
│   │   ├── CompanyFilter.php
│   │   └── RateLimitFilter.php
│   │
│   ├── Entities/
│   │   ├── Company.php
│   │   ├── User.php
│   │   ├── Role.php
│   │   ├── Account.php
│   │   ├── CashCustomer.php
│   │   ├── Challan.php
│   │   ├── Invoice.php
│   │   ├── Payment.php
│   │   ├── LedgerEntry.php
│   │   └── Delivery.php
│   │
│   ├── Validation/
│   │   ├── ChallanRules.php
│   │   ├── InvoiceRules.php
│   │   ├── PaymentRules.php
│   │   ├── AccountRules.php
│   │   └── UserRules.php
│   │
│   ├── Libraries/
│   │   ├── PDF/
│   │   │   ├── InvoicePDF.php
│   │   │   ├── ChallanPDF.php
│   │   │   └── ReportPDF.php
│   │   └── Excel/
│   │       └── ReportExcel.php
│   │
│   ├── Helpers/
│   │   ├── permission_helper.php
│   │   ├── format_helper.php
│   │   ├── date_helper.php
│   │   └── number_helper.php
│   │
│   ├── Database/
│   │   ├── Migrations/
│   │   │   ├── 2026-01-01-000001_create_companies_table.php
│   │   │   ├── 2026-01-01-000002_create_states_table.php
│   │   │   ├── 2026-01-01-000003_create_roles_table.php
│   │   │   ├── 2026-01-01-000004_create_users_table.php
│   │   │   ├── 2026-01-01-000005_create_user_roles_table.php
│   │   │   ├── 2026-01-01-000006_create_gold_rates_table.php
│   │   │   ├── 2026-01-01-000007_create_product_categories_table.php
│   │   │   ├── 2026-01-01-000008_create_products_table.php
│   │   │   ├── 2026-01-01-000009_create_processes_table.php
│   │   │   ├── 2026-01-01-000010_create_accounts_table.php
│   │   │   ├── 2026-01-01-000011_create_cash_customers_table.php
│   │   │   ├── 2026-01-01-000012_create_challans_table.php
│   │   │   ├── 2026-01-01-000013_create_challan_lines_table.php
│   │   │   ├── 2026-01-01-000014_create_invoices_table.php
│   │   │   ├── 2026-01-01-000015_create_invoice_lines_table.php
│   │   │   ├── 2026-01-01-000016_create_payments_table.php
│   │   │   ├── 2026-01-01-000017_create_ledger_entries_table.php
│   │   │   ├── 2026-01-01-000018_create_deliveries_table.php
│   │   │   ├── 2026-01-01-000019_create_audit_logs_table.php
│   │   │   └── 2026-01-01-000020_create_company_settings_table.php
│   │   │
│   │   └── Seeds/
│   │       ├── StateSeeder.php
│   │       ├── RoleSeeder.php
│   │       ├── SuperAdminSeeder.php
│   │       ├── ProductCategorySeeder.php
│   │       ├── ProcessTypeSeeder.php
│   │       └── DemoDataSeeder.php
│   │
│   ├── Views/
│   │   ├── layouts/
│   │   │   ├── main.php
│   │   │   ├── auth.php
│   │   │   └── error.php
│   │   ├── auth/
│   │   │   ├── login.php
│   │   │   └── forgot_password.php
│   │   ├── dashboard/
│   │   │   └── index.php
│   │   ├── companies/
│   │   ├── users/
│   │   ├── challans/
│   │   ├── invoices/
│   │   ├── payments/
│   │   ├── deliveries/
│   │   └── reports/
│   │
│   └── Language/
│       └── en/
│           ├── Validation.php
│           └── Messages.php
│
├── public/
│   ├── index.php
│   ├── .htaccess
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   ├── images/
│   │   └── uploads/
│   │       ├── products/
│   │       ├── challans/
│   │       ├── deliveries/
│   │       └── company_logos/
│   │
├── writable/
│   ├── cache/
│   ├── logs/
│   ├── session/
│   └── uploads/
│
├── tests/
│   ├── unit/
│   │   ├── Services/
│   │   │   ├── InvoiceServiceTest.php
│   │   │   ├── PaymentServiceTest.php
│   │   │   ├── GoldAdjustmentServiceTest.php
│   │   │   ├── TaxCalculationServiceTest.php
│   │   │   └── LedgerServiceTest.php
│   │   └── Models/
│   └── integration/
│       ├── ChallanToInvoiceTest.php
│       ├── PaymentFlowTest.php
│       └── LedgerBalanceTest.php
│
├── vendor/
├── .env
├── .env.example
├── composer.json
├── phpunit.xml
└── README.md
```

## Module to File Mapping

### Authentication & Authorization
- **Controllers:** LoginController, LogoutController, PasswordController
- **Services:** AuthService, PermissionService
- **Filters:** AuthFilter, PermissionFilter
- **Models:** UserModel, RoleModel, UserRoleModel

### Company Management
- **Controllers:** CompanyController, CompanySettingsController
- **Services:** CompanyService
- **Models:** CompanyModel, CompanySettingModel
- **Filters:** CompanyFilter (Multi-tenant isolation)

### Master Data
- **Controllers:** GoldRateController, ProductController, ProcessController
- **Services:** GoldRateService, ProductService, ProcessService
- **Models:** GoldRateModel, ProductModel, ProcessModel, ProductCategoryModel

### Customer Management
- **Controllers:** AccountController, CashCustomerController
- **Services:** AccountService, CashCustomerService
- **Models:** AccountModel, CashCustomerModel

### Challan Management
- **Controllers:** ChallanController (+ type-specific)
- **Services:** ChallanService, ChallanValidationService, ChallanCalculationService
- **Models:** ChallanModel, ChallanLineModel

### Invoice Management
- **Controllers:** InvoiceController (+ type-specific)
- **Services:** InvoiceService, InvoiceValidationService, InvoiceCalculationService, TaxCalculationService
- **Models:** InvoiceModel, InvoiceLineModel

### Payment Management
- **Controllers:** PaymentController
- **Services:** PaymentService, PaymentValidationService, GoldAdjustmentService
- **Models:** PaymentModel

### Delivery Management
- **Controllers:** DeliveryController
- **Services:** DeliveryService
- **Models:** DeliveryModel

### Reporting
- **Controllers:** LedgerReportController, ReceivableReportController, etc.
- **Services:** LedgerReportService, ReceivableReportService, DashboardService
- **Models:** LedgerEntryModel (read-only queries)

### Audit & Logging
- **Controllers:** AuditLogController
- **Services:** AuditService
- **Models:** AuditLogModel

## Key Design Patterns

1. **MVC + Service Layer:** Controllers handle HTTP, Services handle business logic, Models handle DB
2. **Repository Pattern:** Models act as repositories (Query Builder only)
3. **Filter Pattern:** RBAC and multi-tenant enforcement via Filters
4. **Transaction Safety:** All financial operations wrapped in DB transactions
5. **Soft Delete:** All deletions are soft (is_deleted flag)
6. **Audit Everything:** AuditService called after every critical operation

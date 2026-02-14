# Product Requirements Document (PRD)
## Gold Manufacturing & Billing ERP System

**Document Version:** 1.0  
**Last Updated:** February 8, 2026  
**Prepared By:** Senior Product Manager & ERP Domain Expert  
**Status:** Final Draft

## Technical Constraints

- Backend framework: CodeIgniter 4 (PHP 8+)
- Database: MySQL
- Architecture: MVC + Service Layer
- Schema managed via CI4 Migrations
- RBAC via Filters

---

## 1. Executive Summary

This document outlines the comprehensive business requirements for a **multi-tenant, cloud-based Gold Manufacturing and Billing ERP System** designed specifically for gold jewelry manufacturing businesses, rhodium and meena processing units, and wax manufacturing operations.

The system will serve as a complete end-to-end solution managing the entire business lifecycle from customer onboarding, process job management via challans, invoicing (both account-based and cash customers), payment collection with gold weight adjustments, delivery tracking, and comprehensive financial reporting with full accounting ledger capabilities.

**Key Business Drivers:**
- Eliminate manual challan and invoice processing errors
- Automate gold rate adjustments in real-time during payment collection
- Maintain complete audit trail for financial compliance
- Support both account-based customers and walk-in cash customers
- Enable multi-company operations with data isolation
- Provide real-time visibility into receivables and outstanding balances
- Generate comprehensive ledger reports for accounts and cash customers

**Target Users:**
- Gold jewelry manufacturing businesses
- Rhodium/Meena processing units
- Wax manufacturing units
- Businesses managing both account customers and walk-in cash customers

---

## 2. Objectives

### 2.1 Primary Business Objectives

1. **Streamline Operations:** Reduce manual data entry and paperwork by 80% through digital challan and invoice management
2. **Financial Accuracy:** Eliminate calculation errors in tax computations, gold adjustments, and payment reconciliation
3. **Real-time Gold Price Management:** Automatically adjust outstanding amounts based on current gold rates during payment collection
4. **Customer Management:** Unified system for managing both account-based customers and cash walk-in customers
5. **Compliance & Audit:** Maintain immutable financial records with complete audit trail for tax compliance
6. **Multi-tenant Support:** Enable the software vendor to serve multiple independent client companies from single deployment
7. **Scalability:** Support business growth from 5 to 500+ users per company
8. **Reporting & Analytics:** Provide real-time visibility into receivables, payables, and business performance

### 2.2 Success Metrics

- 95% reduction in invoice/challan generation time
- 100% accuracy in tax calculations (CGST/SGST/IGST)
- Zero financial record deletion (soft delete only)
- Complete audit trail for all financial transactions
- Real-time gold price adjustments with zero manual calculations
- Sub-second response time for ledger report generation
- 99.9% system uptime

---

## 3. Scope

### 3.1 In Scope

**Core Business Modules:**
- Multi-company/Multi-tenant management
- User and role-based access control (RBAC)
- Daily gold rate management
- Product and process catalog management
- Customer account management (Account and Cash customers)
- Challan management (Rhodium, Meena, Wax)
- Invoice generation (Account, Cash, Wax)
- Payment collection with gold adjustment logic
- Delivery management with proof of delivery
- Comprehensive ledger and financial reporting
- Audit logging and activity tracking
- Company settings and configuration

**Key Business Capabilities:**
- Generate challans with multiple line items (products, processes, weights, rates)
- Convert challans to invoices automatically
- Handle partial payments with payment history
- Adjust invoice amounts based on gold weight changes during payment
- Support tax-inclusive pricing with automatic CGST/SGST/IGST calculation
- Maintain running balance ledgers for all customers
- Generate account-wise and cash customer-wise ledger reports
- Monthly receivable summary reports
- Role-based permissions for all operations
- Soft delete with data retention
- Document/image upload for challans and deliveries

### 3.2 Out of Scope (Future Phases)

- Manufacturing floor management (production scheduling, machine management)
- Raw material procurement and vendor management
- Employee payroll and HR management
- CRM and marketing automation
- E-commerce customer portal
- Mobile application (native iOS/Android)
- Barcode/RFID scanning for inventory
- Integration with third-party accounting software
- Multi-currency support (only INR in Phase 1)
- Credit limit management for accounts
- Automated SMS/Email notifications (future enhancement)

---

## 4. User Roles & Personas

### 4.1 Super Administrator
**Description:** System owner managing the entire multi-tenant platform  
**Business Needs:**
- Create and manage multiple company accounts
- View system-wide analytics and usage
- Manage global system settings
- Access all companies for support purposes

**Key Permissions:**
- Full system access across all companies
- Company creation and deactivation
- Global user management
- System configuration

### 4.2 Company Administrator
**Description:** Business owner or manager of a specific company  
**Business Needs:**
- Manage company settings (gold rates, tax rates, numbering prefixes)
- Create and manage users within their company
- Assign roles and permissions
- View all business reports and analytics
- Configure products and processes

**Key Permissions:**
- Full access within their company only
- User and role management
- Settings and configuration
- All module access (create/edit/delete/view)
- Report access

### 4.3 Billing Manager
**Description:** Staff member responsible for challan and invoice creation  
**Business Needs:**
- Create challans for customer orders
- Generate invoices from completed challans
- Handle cash customer billing
- View outstanding challans and invoices
- Print invoices and challans

**Key Permissions:**
- challan.create, challan.view, challan.edit
- invoice.create, invoice.view, invoice.edit
- invoice.print
- account.view
- cash_customer.create, cash_customer.view

### 4.4 Accounts/Finance Manager
**Description:** Staff managing payments and financial reconciliation  
**Business Needs:**
- Record customer payments (full or partial)
- Apply gold weight adjustments during payment
- View payment history
- Generate ledger reports
- Reconcile accounts

**Key Permissions:**
- payment.create, payment.view
- payment.apply_gold_adjustment
- invoice.view
- reports.ledger_view
- reports.receivable_summary

### 4.5 Delivery Personnel
**Description:** Staff responsible for delivering finished goods to customers  
**Business Needs:**
- View assigned deliveries
- Upload delivery proof (photos)
- Mark deliveries as completed
- View delivery address and contact information

**Key Permissions:**
- delivery.view_assigned
- delivery.mark_complete
- delivery.upload_proof
- invoice.view (read-only for assigned deliveries)

### 4.6 Report Viewer
**Description:** Management or auditor needing read-only access to reports  
**Business Needs:**
- View all reports without editing capabilities
- Export reports to PDF/Excel
- View ledgers and outstanding summaries

**Key Permissions:**
- reports.view_all (read-only)
- reports.export
- dashboard.view

---

## 5. Modules Overview

### 5.1 Company Management
Multi-tenant company setup and configuration with data isolation

### 5.2 User & Role Management
User accounts, role definition, and permission assignment

### 5.3 Gold Rate Management
Daily gold rate entry and historical tracking

### 5.4 Product & Process Catalog
Master data for products (designs) and manufacturing processes

### 5.5 Account Management
Customer account creation and profile management (account-based customers)

### 5.6 Cash Customer Management
Walk-in customer tracking with name and mobile number

### 5.7 Challan Management
Job order creation with products, processes, weights, and rates

### 5.8 Invoice Management
Invoice generation from challans or standalone cash invoices

### 5.9 Payment Management
Payment collection with gold adjustment capabilities

### 5.10 Delivery Management
Delivery assignment, tracking, and proof of delivery

### 5.11 Reporting & Analytics
Ledger reports, receivable summaries, and business analytics

### 5.12 Audit & Activity Logging
Complete audit trail of all system actions

### 5.13 Settings & Configuration
Company-level settings, tax rates, prefixes, and business rules

---

## 6. Functional Requirements

### 6.1 Module: Company Management

#### 6.1.1 Company Registration
**Business Requirement:** Enable super admin to onboard new client companies into the system

**Functional Details:**
- Super admin can create new company accounts
- Each company must have unique company name
- Mandatory fields: Company name, business type, address, state, contact details
- Company is assigned a unique company_id (auto-generated)
- On creation, company status is set to "Active"

**Data Captured:**
- Company name
- Business legal name
- Business type (Gold Manufacturing, Rhodium Processing, Meena Processing, Wax Manufacturing)
- Billing address (line1, line2, city, state, pincode)
- State (for tax calculation - same state = CGST+SGST, different state = IGST)
- Contact person name
- Contact email
- Contact phone
- GST number
- PAN number
- Company logo (optional)
- Invoice number prefix (e.g., "INV-", "GST-")
- Challan number prefix (e.g., "CH-", "JOB-")
- Default tax rate (e.g., 3% for jewelry, 18% for services)
- Minimum wax price (applicable for wax manufacturing)
- Financial year start month
- Date format preference
- Timezone

**Business Rules:**
- Company name must be unique across the system
- State is mandatory for tax calculation logic
- GST number must be valid format
- Invoice and challan prefixes must be unique per company
- Tax rate must be between 0% and 28% (GST compliance)
- Minimum wax price must be greater than zero
- Only super admin can create/deactivate companies
- Deactivated companies cannot perform transactions but data remains accessible

**Validation Rules:**
- GST number: 15 characters, alphanumeric, format: 22AAAAA0000A1Z5
- PAN number: 10 characters, format: AAAAA9999A
- Email: Valid email format
- Phone: 10 digits, numeric
- Pincode: 6 digits, numeric
- Tax rate: 0 to 28, up to 2 decimal places

**NEW FEATURE:** Company-Level Gold Purity Standards
- Allow companies to define standard gold purity levels (e.g., 22K, 24K, 18K)
- Store purity percentage for calculation purposes
- This enables future purity-based pricing and reporting

#### 6.1.2 Company Settings Management
**Business Requirement:** Allow company administrators to configure business-specific settings

**Functional Details:**
- Company admin can update company profile information
- Can change tax rates (affects future transactions only)
- Can update invoice/challan number prefixes
- Can update minimum wax price
- Can toggle modules on/off (e.g., disable Wax if not applicable)

**Business Rules:**
- Historical transactions are not affected by settings changes
- Invoice/challan number changes apply only to new documents
- Tax rate changes apply to new invoices created after the change
- Cannot change company_id or creation date
- Cannot change state once transactions exist (tax implication)

#### 6.1.3 Multi-Tenant Data Isolation
**Business Requirement:** Ensure complete data separation between companies

**Functional Details:**
- Every database record includes company_id
- All queries automatically filter by logged-in user's company_id
- Users see only data belonging to their company
- Exception: Super admin can switch company context for support
- Company switching must be logged in audit trail

**Business Rules:**
- Users cannot access data from other companies
- Shared reference data (states, countries) have company_id = 0 (global)
- Super admin access to other companies must be logged
- API responses include only current company's data
- Cross-company reporting is not allowed

### 6.2 Module: User & Role Management

#### 6.2.1 User Account Creation
**Business Requirement:** Allow company admin to create user accounts for staff

**Functional Details:**
- Company admin creates user accounts
- Each user belongs to one or more companies (future: multi-company users)
- User credentials: username, email, password, full name
- User assigned to one or more roles
- User status: Active, Inactive, Suspended
- User can be assigned to multiple roles for combined permissions

**Data Captured:**
- Full name
- Username (unique across system)
- Email (unique across system)
- Password (hashed, minimum 8 characters, must include uppercase, lowercase, number, special character)
- Mobile number
- Role(s) assignment
- Company assignment
- Employment status
- Adhar Card Number (optional)
- Date of joining (optional)
- Profile photo (optional)
- Is active flag
- Is deleted flag (soft delete)

**Business Rules:**
- Username must be unique system-wide
- Email must be unique system-wide
- Users must be assigned at least one role
- Users can belong to multiple companies (future feature)
- Deactivated users cannot log in but records remain
- Password must be hashed using industry-standard algorithm
- Failed login attempts tracked (5 attempts = temporary lock)

**Validation Rules:**
- Username: 3-30 characters, alphanumeric, underscore, hyphen only
- Email: Valid email format
- Password: Minimum 8 characters, at least 1 uppercase, 1 lowercase, 1 number, 1 special character
- Mobile: 10 digits, numeric
- Full name: Required, 2-100 characters

#### 6.2.2 Role Management
**Business Requirement:** Define roles with specific permission sets

**Functional Details:**
- System comes with predefined roles (Super Admin, Company Admin, Billing Manager, Accounts Manager, Delivery Personnel, Report Viewer)
- Company admin can create custom roles
- Each role has a set of permissions
- Permissions follow format: {module}.{action}
  - Example: invoice.create, invoice.edit, invoice.delete, invoice.view, invoice.print
- Roles can be cloned to create similar roles
- Multiple roles can be assigned to a user (permissions are additive)

**Data Captured:**
- Role name
- Role description
- Permission list (array of permission codes)
- Is system role (true for predefined roles, cannot be deleted)
- Is active

**Predefined Roles and Permissions:**

**Super Administrator:**
- ALL permissions across ALL modules
- Can access all companies
- system.manage_companies
- system.manage_global_settings
- system.access_all_companies

**Company Administrator:**
- All permissions within their company
- company.manage_settings
- user.create, user.edit, user.delete, user.view
- role.create, role.edit, role.delete, role.view
- All module permissions (challan, invoice, payment, delivery, reports, etc.)

**Billing Manager:**
- challan.create, challan.edit, challan.view, challan.print
- invoice.create, invoice.edit, invoice.view, invoice.print
- account.view
- cash_customer.create, cash_customer.view
- product.view, process.view
- gold_rate.view

**Accounts/Finance Manager:**
- payment.create, payment.view
- payment.apply_gold_adjustment
- invoice.view
- challan.view
- account.view, account.edit
- cash_customer.view
- reports.ledger_view, reports.receivable_view
- reports.export

**Delivery Personnel:**
- delivery.view_assigned
- delivery.mark_complete
- delivery.upload_proof
- invoice.view (limited to assigned deliveries)

**Report Viewer:**
- reports.view_all
- reports.export
- dashboard.view
- All modules: .view permission only (no create/edit/delete)

**Business Rules:**
- Users inherit all permissions from all assigned roles (additive)
- If a user has multiple roles, they get union of all permissions
- System roles (predefined) cannot be deleted or renamed
- Custom roles can be created only by Company Admin
- Permission changes to a role affect all users with that role immediately
- At least one active Company Admin must exist per company at all times

#### 6.2.3 Permission-Based Access Control
**Business Requirement:** Restrict system access based on user permissions

**Functional Details:**
- Every API endpoint/screen checks user's permissions before allowing access
- If user lacks permission, show "Access Denied" message
- Permissions checked in real-time (not cached for long durations)
- UI dynamically shows/hides menu items based on permissions
- Buttons (Edit, Delete, Create) visible only if user has corresponding permission

**Business Rules:**
- Permission check happens on every request
- No client-side permission checks only (must validate server-side)
- Attempting unauthorized action is logged in audit trail
- Super admin bypasses permission checks but actions are still logged

### 6.3 Module: Gold Rate Management

#### 6.3.1 Daily Gold Rate Entry
**Business Requirement:** Maintain daily gold rate for gold weight adjustment calculations

**Functional Details:**
- Company admin or authorized user enters today's gold rate
- Gold rate is per gram (INR per gram)
- One entry per day per company
- System uses the latest available rate if today's rate not entered
- Historical rates stored for audit purposes
- Rate can be updated multiple times in a day (latest value used)

**Data Captured:**
- Date (YYYY-MM-DD)
- Gold rate per gram (INR)
- Metal type (22K, 24K, 18K) - NEW FEATURE
- Entered by (user_id)
- Entry timestamp
- Updated timestamp (if modified)

**Business Rules:**
- Only users with gold_rate.create permission can enter rates
- One active rate per day per company per metal type
- If rate updated, previous value retained with timestamp (rate history)
- System shows alert if today's rate not entered
- Payment with gold adjustment uses the latest available rate
- Cannot delete historical rates (soft delete only)
- Cannot enter future-dated rates (date <= today only)

**Validation Rules:**
- Gold rate: Must be greater than zero, up to 2 decimal places
- Rate must be between 1000 and 100,000 (sanity check)
- Date cannot be future date

**NEW FEATURE:** Gold Rate Alerts
- System alerts users if rate not entered by 10 AM
- Can configure alert recipients
- Show warning banner on dashboard if today's rate missing

#### 6.3.2 Gold Rate History
**Business Requirement:** View historical gold rates for reporting and audit

**Functional Details:**
- Users can view historical gold rate entries
- Display as table: Date, Rate, Metal Type, Entered By, Timestamp
- Filter by date range
- Export to Excel/PDF
- Show rate trend graph (line chart)

**Business Rules:**
- Historical rates cannot be edited after next day
- Rates can only be soft-deleted (never permanently removed)
- Rate history visible to all users (transparency)

### 6.4 Module: Product & Process Catalog

#### 6.4.1 Product Management
**Business Requirement:** Maintain catalog of jewelry designs/products

**Functional Details:**
- Company admin creates product master records
- Products represent jewelry designs (rings, bangles, necklaces, earrings, etc.)
- Each product has name, code, description, image
- Products used in challan line items
- Multiple products can be selected per challan line

**Data Captured:**
- Product code (unique per company - if company_id=0 then global for all companies)
- Product name
- Product description
- Product category (Ring, Bangle, Necklace, Earring, Pendant, Bracelet, Chain, etc.)
- Product image (optional)
- HSN code (for GST)
- Is active flag

**Business Rules:**
- Product code must be unique per company - if company_id=0 then global for all companies
- Cannot delete products used in any challan/invoice (soft delete only)
- Inactive products not shown in dropdowns but remain in historical records
- Product selection is multi-select in challan lines

**Validation Rules:**
- Product code: 3-20 characters, alphanumeric
- Product name: Required, 2-100 characters
- HSN code: 4-8 digits

**NEW FEATURE:** Product Categories
- Organize products by categories for easier selection
- Filter products by category in challan entry screens

#### 6.4.2 Process Management
**Business Requirement:** Maintain catalog of manufacturing processes with pricing

**Functional Details:**
- Company admin creates process master records
- Processes represent manufacturing steps (Rhodium plating, Meena work, Polishing, Stone setting, etc.)
- Each process has name, code, price per unit
- Processes used in challan line items
- Multiple processes can be selected per challan line
- Process price (rate) is per unit of work (can be per gram, per piece, etc.)

**Data Captured:**
- Process code (unique per company - if company_id=0 then global for all companies)
- Process name
- Process description
- Process type (Rhodium, Meena, Polishing, Stone Setting, Casting, Other)
- Price per unit (INR)
- Unit type (Per Gram, Per Piece, Per Job)
- Is active flag

**Business Rules:**
- Process code must be unique per company - if company_id=0 then global for all companies
- Cannot delete processes used in any challan/invoice (soft delete only)
- Inactive processes not shown in dropdowns but remain in historical records
- Process selection is multi-select in challan lines
- When multiple processes selected, line rate = SUM of all selected process prices

**Validation Rules:**
- Process code: 3-20 characters, alphanumeric
- Process name: Required, 2-100 characters
- Price: Must be greater than or equal to zero, up to 2 decimal places

**NEW FEATURE:** Process Sequences
- Define typical process sequences (e.g., Casting → Polishing → Rhodium)
- Quick-select process sequence templates in challans

### 6.5 Module: Account Management

#### 6.5.1 Account Customer Creation
**Business Requirement:** Manage regular business customers with account ledgers

**Functional Details:**
- Company admin or billing manager creates account customer records
- Account customers are businesses or individuals with ongoing credit relationship
- Account customers have running ledger balances
- Used for "Accounts Challan" and "Accounts Invoice" types

**Data Captured:**
- Account code (unique per company, auto-generated or manual - if company_id=0 then global for all companies)
- Account name (business/individual name)
- Contact person name
- Mobile number
- Email address
- Billing address (line1, line2, city, state, pincode)
- Shipping address (optional, can be same as billing)
- GST number (optional, for B2B customers)
- PAN number (optional)
- Opening balance (starting ledger balance, can be debit or credit)
- Opening balance date
- Credit limit (optional, future feature)
- Payment terms (e.g., Net 30 days, Net 60 days)
- Is active flag

**Business Rules:**
- Account code must be unique per company - if company_id=0 then global for all companies
- Account name need not be unique (multiple accounts can have same name but different codes)
- Cannot delete accounts with transaction history (soft delete only)
- Opening balance can be positive (receivable/debit) or negative (payable/credit)
- Opening balance is a one-time entry during account creation
- GST number required if customer is registered business
- State is mandatory for tax calculation

**Validation Rules:**
- Account name: Required, 2-200 characters
- Mobile: 10 digits, numeric
- Email: Valid email format (optional)
- GST number: 15 characters if provided
- PAN number: 10 characters if provided
- Pincode: 6 digits
- Opening balance: Can be positive or negative, up to 2 decimal places
- State: Must be selected from predefined state list

**NEW FEATURE:** Account Groups
- Organize accounts into groups (Wholesalers, Retailers, Manufacturers, etc.)
- Filter and report by account groups

#### 6.5.2 Account Ledger View
**Business Requirement:** View running ledger balance for each account customer

**Functional Details:**
- Users can view detailed ledger for any account customer
- Ledger shows all financial transactions: invoices, payments, adjustments
- Running balance calculated transaction by transaction
- Opening balance shown as first entry
- Displays: Date, Transaction Type, Reference No, Description, Debit, Credit, Balance

**Business Rules:**
- Ledger entries are append-only (immutable)
- Balance calculated as: Opening Balance + Sum(Debits) - Sum(Credits)
- Debit = Invoice amount (money owed by customer)
- Credit = Payment received (money paid by customer)
- Positive balance = Customer owes money (receivable)
- Negative balance = Customer has advance payment (credit balance)

### 6.6 Module: Cash Customer Management

#### 6.6.1 Cash Customer Capture
**Business Requirement:** Track walk-in customers for cash invoices without creating full account profiles

**Functional Details:**
- Cash customers are captured at the time of creating Cash Invoice or Cash Challan
- Minimal information captured: Name and Mobile number
- System checks if customer with same name+mobile already exists
- If exists, reuses existing record (prevents duplicates)
- If not exists, creates new cash customer record
- Cash customers do NOT have opening balance or credit terms
- Each cash invoice is typically paid immediately (but can have partial payments)

**Data Captured:**
- Customer name (first name + last name or full name)
- Mobile number
- Unique constraint on combination: (name + mobile + company_id - if company_id=0 then global for all companies)

**Business Rules:**
- Cash customer identified by unique combination of name + mobile per company - if company_id=0 then global for all companies
- Same name allowed with different mobile numbers (different customers)
- Same mobile allowed with different names (different customers)
- Exact match of name+mobile = same customer (reuse existing record)
- Cash customers appear in ledger reports just like account customers
- Cash customers have running balance calculated from cash invoices and payments
- Cannot convert cash customer to account customer (business decision - can be added in future)

**Validation Rules:**
- Name: Required, 2-500 characters, alphabets and spaces only
- Mobile: Required, exactly 10 digits, numeric only

**NEW FEATURE:** Cash Customer Search and Autocomplete
- As user types name or mobile in invoice form, show matching existing cash customers
- Select from autocomplete to reuse existing customer record
- Show message: "Existing customer found" when match detected
- Show customer's previous invoice history when selected (last 5 invoices)

#### 6.6.2 Cash Customer Deduplication Logic
**Business Requirement:** Prevent duplicate cash customer records

**Functional Details:**
- Before creating new cash customer, check if record exists with same name + mobile
- Match is case-insensitive for name
- Mobile number must match exactly (no country code handling in Phase 1)
- If match found: Return existing customer ID, do not create new record
- If no match: Create new record

**Business Rules:**
- Matching logic: LOWER(name) + mobile + company_id
- Name trimming: Leading/trailing spaces removed before matching
- Multiple spaces within name normalized to single space
- Mobile number: No spaces or special characters allowed
- Duplicate check is mandatory before every cash invoice/challan creation

**NEW FEATURE:** Cash Customer Merge
- If duplicate cash customers created by mistake, admin can merge them
- Select primary customer record (retained)
- Select secondary customer record (merged into primary)
- All invoices/challans from secondary customer reassigned to primary customer
- Secondary customer record soft-deleted with audit log entry

### 6.7 Module: Challan Management

#### 6.7.1 Challan Types
**Business Requirement:** Support three types of challans based on business operation type

**Challan Types:**
1. **Rhodium Accounts Challan:** For rhodium plating jobs for account customers
2. **Meena Accounts Challan:** For meena (enamel) work jobs for account customers
3. **Wax Challan:** For wax model manufacturing jobs

**Business Rules:**
- Challan type determined at creation time
- Challan type cannot be changed after creation
- Each type may have different rate calculation logic
- All three types follow same base challan structure with type-specific variations

#### 6.7.2 Challan Creation (Rhodium/Meena Accounts Challan)
**Business Requirement:** Create job orders for rhodium or meena work for account customers

**Functional Details:**
- Billing manager creates new challan
- Select challan type: Rhodium Accounts Challan or Meena Accounts Challan
- Select account customer (account_id required, cash_customer_id must be null)
- Enter challan date (defaults to today)
- Auto-generate challan number based on company prefix + sequence
- Add multiple line items (at least one required)
- Save as Draft or Submit for approval

**Challan Header Data:**
- Challan number (auto-generated, unique per company)
- Challan type (Rhodium Accounts / Meena Accounts)
- Challan date
- Account customer (account_id)
- Cash customer (cash_customer_id) - must be NULL for Accounts Challan
- Reference number (optional, customer's reference)
- Notes (optional, internal notes)
- Status (Draft, Submitted, Approved, Invoice Generated, Cancelled)
- Invoice generated flag (boolean, default false)
- Invoice ID (nullable, populated when invoice created from this challan)
- Created by user
- Created timestamp
- Updated by user
- Updated timestamp

**Challan Line Item Data (per line):**
- Line number (sequential, 1, 2, 3...)
- Products (multi-select, array of product IDs)
- Processes (multi-select, array of process IDs)
- Processes' Price (to keep the history of process price when challan was created)
- Quantity (number of pieces/items)
- Weight (in grams, decimal)
- Rate (calculated based on processes selected)
- Amount (calculated: weight × rate, or rate if weight is zero)
- Image (optional, photo of the item/job)
- Gold weight (nullable, captured if gold involved)
- Gold fine weight (nullable)
- Gold touch/purity (nullable, e.g., 22K, 24K)

**Rate Calculation Logic (per line):**
```
IF processes selected (not empty):
    rate = SUM of all selected process prices
ELSE:
    rate = 0
```

**Amount Calculation Logic (per line):**
```
IF weight > 0:
    amount = weight × rate
ELSE:
    amount = rate (treated as fixed price per job)
```

**Example:**
- Product: Gold Ring
- Processes: Rhodium Plating (₹50/gm) + Polishing (₹30/gm)
- Weight: 10 grams
- Rate calculation: 50 + 30 = ₹80 per gram
- Amount calculation: 10 × 80 = ₹800

**Challan Total Calculation:**
```
Challan Total = SUM of all line amounts
```

**Business Rules:**
- Challan number must be unique per company
- Challan numbering must be sequential and gap-free (concurrency-safe)
- Account ID is mandatory for Rhodium/Meena Accounts Challan
- Cash customer ID must be NULL for Accounts Challan
- At least one line item required
- Each line must have at least one product OR one process selected
- Weight can be zero (for fixed-price jobs)
- Rate is snapshot at the time of challan creation (stored in line item)
- Process prices at challan creation time are saved (not recalculated from master)
- Gold weight fields are optional (nullable) per line
- Cannot edit challan after invoice generated (invoice_generated = true)
- Cannot delete challan after invoice generated (soft delete only, with restrictions)
- Challan can be cancelled only if no invoice generated
- Status workflow: Draft → Submitted → Approved → Invoice Generated
- Only approved challans can be selected for invoice generation

**Validation Rules:**
- Challan date: Cannot be future date
- Account ID: Required, must exist, must be active
- Line items: At least 1, maximum 100 per challan
- Products: At least 1 product OR 1 process per line
- Weight: Greater than or equal to 0, up to 3 decimal places
- Rate: Greater than or equal to 0, up to 2 decimal places
- Amount: Greater than or equal to 0, up to 2 decimal places
- Image: Valid image format (JPG, PNG), max 5 MB per image

**NEW FEATURE:** Challan Templates
- Save frequently used product+process combinations as templates
- Quick-load template when creating new challan
- Reduces data entry time for repetitive jobs

#### 6.7.3 Challan Creation (Wax Challan)
**Business Requirement:** Create wax model manufacturing job orders with special pricing rules

**Functional Details:**
- Similar to Rhodium/Meena challan but with different amount calculation
- Wax challans can be for account customers
- For account customers: account_id populated, cash_customer_id null

**Challan Line Item Data (per line):**
- Upload option (User will upload file and file's name will be stored in database. For this take product_name nullable and for Wax challan product_id can be 0)
- Weight
- Rate
- Amount (Weight * rate)

- `account_price`: Account customer's negotiated price per gram (stored in account profile, or default company price)
- `weight`: Weight in grams from line item
- `company_minimum_price`: Minimum price configured in company settings

**Business Rules:**
- Wax challan amount always at least the minimum price
- If calculated amount (price × weight) is less than minimum, use minimum
- Minimum price check happens per line item
- Account price can be customer-specific (if stored in account) or company default
- For cash customers, use company default wax price

**NEW FEATURE:** Customer-Specific Wax Pricing
- Account customers can have custom wax price per gram (overrides company default)
- Stored in account profile: wax_price_per_gram (nullable)
- If null, use company default wax price

#### 6.7.4 Challan Status Workflow
**Business Requirement:** Manage challan lifecycle through defined statuses

**Status Flow:**
```
Draft → Submitted → Approved → Invoice Generated → [Closed/Cancelled]
```

**Status Definitions:**
- **Draft:** Challan created but not finalized, can be edited/deleted
- **Submitted:** Challan submitted for approval, minor edits allowed
- **Approved:** Challan approved, ready for invoice generation, no edits allowed
- **Invoice Generated:** Invoice created from this challan, challan locked, cannot edit/delete
- **Cancelled:** Challan cancelled (only from Draft or Submitted status)

**Status Transition Rules:**
- Draft → Submitted: By billing manager
- Submitted → Approved: By user with challan.approve permission
- Approved → Invoice Generated: Automatically when invoice created
- Draft → Cancelled: By billing manager or admin
- Submitted → Cancelled: By admin only
- Cannot cancel after Approved status
- Cannot revert from Invoice Generated status

**Business Rules:**
- Only Approved challans can be selected for invoice generation
- Once invoice generated, challan becomes read-only (immutable)
- Cancelled challans do not appear in invoice generation screens
- Status changes are logged in audit trail

#### 6.7.5 Challan Editing and Deletion
**Business Requirement:** Control when challans can be modified or deleted

**Functional Details:**
- Challans in Draft status: Fully editable and deletable
- Challans in Submitted status: Editable with restrictions, deletable by admin only
- Challans in Approved status: Editable with restrictions, deletable by admin only
- Challans in Invoice Generated status: Completely locked (no edits, no deletion)

**Editable Fields by Status:**
- **Draft:** All fields editable
- **Submitted:** Can edit line items (add/edit/delete lines), cannot change customer or challan date
- **Approved:** Can edit line items (add/edit/delete lines), cannot change customer or challan date
- **Invoice Generated:** No edits allowed

**Deletion Rules:**
- **Draft:** Can be deleted by billing manager or admin
- **Submitted:** Can be deleted by admin only
- **Approved:** Can be deleted by admin only
- **Invoice Generated:** Cannot be deleted (soft delete disabled, show error message)

**Business Rules:**
- Any edit logs the change in audit trail
- User attempting unauthorized edit sees error message: "Challan cannot be edited in current status"
- Deletion attempts on locked challans show error: "Cannot delete challan after invoice generated"

#### 6.7.6 Challan Viewing and Printing
**Business Requirement:** View and print challan documents

**Functional Details:**
- Users with challan.view permission can view challan details
- Display challan in formatted view (not editable if locked)
- Print challan as PDF with company letterhead
- Include all line items with products, processes, weights, rates, amounts
- Show images if uploaded for line items
- Display status and approval details

**Print Format Sections:**
- Company logo and details (top)
- Challan number, date, customer details
- Table: Line No, Products, Processes, Qty, Weight, Rate, Amount
- Challan total
- Notes section
- Authorized signatory section (footer)

**NEW FEATURE:** Email Challan
- Send challan PDF to customer email
- Log email sent timestamp and recipient in audit trail

### 6.8 Module: Invoice Management

#### 6.8.1 Invoice Types
**Business Requirement:** Support three types of invoices

**Invoice Types:**
1. **Accounts Invoice:** Generated from approved challans for account customers
2. **Cash Invoice:** Standalone invoice for walk-in cash customers (not linked to challan)
3. **Wax Invoice:** Generated from wax challans (can be account or cash customer)

**Business Rules:**
- Invoice type determined at creation time
- Invoice type cannot be changed after creation
- Accounts Invoice must have account_id, cash_customer_id must be null
- Cash Invoice must have cash_customer_id, account_id must be null
- Wax Invoice can have either account_id or cash_customer_id (not both)

#### 6.8.2 Invoice Creation (Accounts Invoice from Challans)
**Business Requirement:** Generate invoices from approved challans for billing

**Functional Details:**
- Billing manager creates new invoice
- Select invoice type: Accounts Invoice
- Select account customer
- System shows all approved challans for that customer where invoice_generated = false
- Select one or more challans to include in invoice
- System pulls all line items from selected challans
- Calculate subtotal, tax, grand total
- Auto-generate invoice number based on company prefix + sequence
- Save and post invoice

**Invoice Header Data:**
- Invoice number (auto-generated, unique per company, sequential)
- Invoice type (Accounts Invoice / Cash Invoice / Wax Invoice)
- Invoice date
- Due date (optional, calculated based on payment terms if account customer)
- Account customer (account_id) - for Accounts Invoice
- Cash customer (cash_customer_id) - for Cash Invoice
- Billing address (copied from customer)
- Shipping address (copied from customer or same as billing)
- Reference number (optional)
- Selected challan IDs (array, for Accounts Invoice)
- Subtotal (sum of line amounts before tax)
- Tax rate (percentage, from company settings or customer-specific)
- Tax amount (calculated)
- CGST amount (if intra-state)
- SGST amount (if intra-state)
- IGST amount (if inter-state)
- Grand total (subtotal + tax)
- Total paid (sum of all payments received)
- Amount due (grand total - total paid)
- Invoice status (Draft, Posted, Partially Paid, Paid, Delivered, Closed)
- Payment status (Pending, Partial Paid, Paid)
- Notes (optional)
- Terms and conditions (optional, default from company settings)
- Created by user
- Created timestamp
- Updated by user
- Updated timestamp

**Invoice Line Item Data:**
- Line number (sequential)
- Source challan ID (reference to original challan)
- Source challan line ID (reference to original challan line)
- Products (copied from challan line)
- Processes (copied from challan line)
- Processes' Price (to keep the history of process price when Invoice was created)
- Quantity
- Weight
- Rate (snapshot from challan line, not recalculated)
- Amount (line subtotal)
- Process rate snapshot (prices at the time of challan creation)
- Gold weight (copied from challan line if exists)
- Gold fine weight
- Gold touch/purity
- Line notes

**Tax Calculation Logic (Tax Inclusive System):**

The system uses **tax-inclusive pricing**, meaning the line amounts already include tax. We need to back-calculate the tax.

**Given:**
- `line_total` = amount from line item (includes tax)
- `tax_rate` = company's tax rate (e.g., 3% for jewelry)

**Calculate Subtotal (tax-exclusive amount):**
```
tax_amount = line_total × tax_rate / (100 + tax_rate)
subtotal = line_total - tax_amount
```

**Example:**
- Line total: ₹10,300 (tax inclusive)
- Tax rate: 3%
- Tax amount: 10,300 × 3 / 103 = ₹300
- Subtotal: 10,300 - 300 = ₹10,000

**Aggregate for entire invoice:**
```
Invoice Subtotal = SUM of all line subtotals
Invoice Tax Amount = SUM of all line tax amounts
Invoice Grand Total = SUM of all line totals (OR Subtotal + Tax Amount)
```

**GST Tax Display Logic (CGST/SGST vs IGST):**

**IF** customer's billing state == company's state (intra-state transaction):
```
CGST = tax_amount / 2
SGST = tax_amount / 2
IGST = 0
```

**ELSE** (inter-state transaction):
```
IGST = tax_amount
CGST = 0
SGST = 0
```

**Example (Intra-State):**
- Company state: Gujarat
- Customer state: Gujarat
- Tax amount: ₹300
- CGST: ₹150 (1.5%)
- SGST: ₹150 (1.5%)
- IGST: ₹0

**Example (Inter-State):**
- Company state: Gujarat
- Customer state: Maharashtra
- Tax amount: ₹300
- IGST: ₹300 (3%)
- CGST: ₹0
- SGST: ₹0

**Invoice Numbering Logic:**
**Business Requirement:** Invoice numbers must be sequential, unique, and gap-free (concurrency-safe)

**Functional Details:**
- Invoice number format: {company_prefix}{sequence_number}
  - Example: INV-0001, INV-0002, GST-0001, etc.
- Sequence number auto-incremented per company
- Numbering must be transaction-safe (database-level locking or sequence)
- No gaps allowed in sequence (for tax compliance)
- Deleted invoices retain their numbers (soft delete)

**Business Rules:**
- Invoice number generated at the moment of saving invoice
- Use database transaction with row-level lock on company's last invoice number
- Or use database sequence feature for atomic increment
- Once invoice generated, challans marked as invoice_generated = true
- Challans linked to invoice cannot be used in another invoice
- Invoice number cannot be changed after creation
- Admin user can delete invoice, and if deleted all challans will be revoked and can make new invoice with same challans

**Validation Rules:**
- Cannot create invoice without selecting at least one challan (for Accounts Invoice)
- Cannot select challan that already has invoice_generated = true
- Challan must be in Approved status to be selected
- Invoice date cannot be before challan date
- Grand total must be greater than zero

#### 6.8.3 Invoice Creation (Cash Invoice - Manual)
**Business Requirement:** Create standalone invoices for walk-in cash customers

**Functional Details:**
- Billing manager creates new invoice
- Select invoice type: Cash Invoice
- Enter or select cash customer (name + mobile)
- System checks for existing cash customer with same name + mobile
- If found: Reuse existing record
- If not found: Create new cash customer record
- Manually enter line items (not linked to challans)
- Calculate tax and total
- Auto-generate invoice number (same sequence as Accounts Invoice)
- Save and post invoice

**Cash Invoice Line Item Entry:**
- User manually enters line items
- Line number (sequential, 1, 2, 3...)
- Products (multi-select, array of product IDs)
- Processes (multi-select, array of process IDs)
- Processes' Price (to keep the history of process price when challan was created)
- Quantity (number of pieces/items)
- Weight (in grams, decimal)
- Rate (calculated based on processes selected)
- Amount (calculated: weight × rate, or rate if weight is zero)
- Image (optional, photo of the item/job)
- Gold weight (nullable, captured if gold involved)
- Gold fine weight (nullable)
- Gold touch/purity (nullable, e.g., 22K, 24K)

**Rate Calculation Logic (per line):**
```
IF processes selected (not empty):
    rate = SUM of all selected process prices
ELSE:
    rate = 0
```

**Amount Calculation Logic (per line):**
```
IF weight > 0:
    amount = weight × rate
ELSE:
    amount = rate (treated as fixed price per job)
```

**Example:**
- Product: Gold Ring
- Processes: Rhodium Plating (₹50/gm) + Polishing (₹30/gm)
- Weight: 10 grams
- Rate calculation: 50 + 30 = ₹80 per gram
- Amount calculation: 10 × 80 = ₹800

**Invoice Total Calculation:**
```
Invoice Total = SUM of all line amounts
```

**Business Rules:**
- Cash Invoice does NOT require challan
- Cash customer information captured at invoice creation
- Cash customer ID must be populated, account ID must be null
- Invoice numbering sequence shared with Accounts Invoice (no separate sequence)
- Invoice numbering must be sequential and gap-free (concurrency-safe)
- Tax calculation same as Accounts Invoice (tax-inclusive)
- GST logic same (CGST/SGST vs IGST based on state)
- Cash invoices appear in reports and ledgers just like Accounts Invoice
- At least one line item required
- Each line must have at least one product OR one process selected
- Weight can be zero (for fixed-price jobs)
- Rate is snapshot at the time of invoice creation (stored in line item)
- Process prices at invoice creation time are saved (not recalculated from master)
- Gold weight fields are optional (nullable) per line

**Validation Rules:**
- Cash customer name and mobile required
- Invoice date: Cannot be future date
- At least one line item required
- Products: At least 1 product OR 1 process per line
- Weight: Greater than or equal to 0, up to 2 decimal places
- Rate: Greater than or equal to 0, up to 2 decimal places
- Amount: Greater than or equal to 0, up to 2 decimal places
- Image: Valid image format (JPG, PNG), max 5 MB per image

**NEW FEATURE:** Challan Templates
- Save frequently used product+process combinations as templates
- Quick-load template when creating new challan
- Reduces data entry time for repetitive jobs

#### 6.8.4 Invoice Status Workflow
**Business Requirement:** Manage invoice lifecycle through statuses

**Status Flow:**
```
Draft → Posted → Partially Paid → Paid → Delivered → Closed
```

**Status Definitions:**
- **Draft:** Invoice created but not finalized, can be edited/deleted
- **Posted:** Invoice finalized and posted to ledger, minor edits allowed
- **Partially Paid:** Invoice has received partial payment, cannot edit amounts
- **Paid:** Invoice fully paid, locked for editing, ready for delivery
- **Delivered:** Goods delivered to customer, invoice complete
- **Closed:** Invoice closed (archived)

**Status Transition Rules:**
- Draft → Posted: By billing manager
- Posted → Partially Paid: Automatically when first payment recorded
- Partially Paid → Paid: Automatically when total_paid >= grand_total
- Paid → Delivered: Automatically when delivery marked complete
- Any status → Closed: Manually by admin (archive old invoices)

**Payment Status (separate from Invoice Status):**
- **Pending:** No payment received (total_paid = 0)
- **Partial Paid:** Partial payment received (0 < total_paid < grand_total)
- **Paid:** Full payment received (total_paid >= grand_total)

**Business Rules:**
- Invoice can only be edited before first payment
- After any payment, invoice amounts are locked (immutable)
- Cannot delete invoice after payment recorded
- Status changes logged in audit trail
- Payment status auto-calculated based on payments received

#### 6.8.5 Invoice Editing and Deletion
**Business Requirement:** Control when invoices can be modified or deleted

**Functional Details:**
- **Draft status:** Fully editable and deletable
- **Posted status (no payments):** Editable with restrictions, deletable by admin only
- **After any payment:** Completely locked, no edits, no deletion

**Editable Fields by Status:**
- **Draft:** All fields editable
- **Posted (no payments):** Can edit line items, cannot change customer or invoice date
- **After payment:** No edits allowed (amounts immutable)

**Deletion Rules:**
- **Draft:** Can be deleted by billing manager or admin
- **Posted (no payments):** Can be deleted by admin only
- **After payment:** Cannot be deleted (show error message)

**Business Rules:**
- Any attempt to edit locked invoice shows error: "Invoice cannot be edited after payment received"
- Deletion attempt after payment shows error: "Cannot delete invoice with payment history"
- Edits are logged in audit trail
- Deleted invoices are soft-deleted (data retained for audit)

#### 6.8.6 Invoice Viewing and Printing
**Business Requirement:** View and print GST-compliant invoice documents

**Functional Details:**
- Users with invoice.view permission can view invoice details
- Print invoice as PDF with GST-compliant format
- Include all mandatory GST fields: GSTIN, HSN codes, tax breakup
- Display payment status and amount due
- Show payment history summary

**GST Invoice Print Format Sections:**
- Company details: Name, Address, GSTIN, Contact
- Invoice number, date, due date
- Customer details: Name, Address, GSTIN (if applicable)
- Table: Line No, Item Description, HSN, Qty, Weight, Rate, Amount
- Subtotal (tax-exclusive)
- Tax breakup: CGST + SGST (intra-state) OR IGST (inter-state)
- Grand Total
- Amount Paid
- Amount Due
- Payment terms
- Terms and conditions
- Bank details for payment
- Authorized signatory

**NEW FEATURE:** Invoice Email and SMS
- Email invoice PDF to customer
- Send SMS with invoice number and amount due
- Log communication in audit trail

### 6.9 Module: Payment Management

#### 6.9.1 Payment Recording (Standard Payment)
**Business Requirement:** Record customer payments against invoices

**Functional Details:**
- Accounts manager selects invoice for payment
- System displays invoice details: Invoice number, date, customer, grand total, total paid, amount due
- Display all line items (read-only)
- User enters payment amount
- User selects payment mode (Cash, Cheque, Bank Transfer, UPI, Card)
- For Cheque: Enter cheque number, cheque date, bank name
- For Bank Transfer: Enter transaction reference number
- User can apply partial payment or full payment
- System validates payment amount does not exceed amount due
- Save payment record

**Payment Data Captured:**
- Payment ID (auto-generated)
- Invoice ID (reference to invoice)
- Payment date
- Payment amount
- Payment mode (Cash, Cheque, Bank Transfer, UPI, Card, Other)
- Cheque number (if cheque)
- Cheque date (if cheque)
- Bank name (if cheque or bank transfer)
- Transaction reference number (if bank transfer/UPI)
- Notes (optional)
- Received by (user who recorded payment)
- Payment timestamp

**Payment Calculation:**
```
Amount Due = Invoice Grand Total - Total Paid
Payment Amount <= Amount Due
```

**Post-Payment Updates:**
```
Invoice Total Paid = Previous Total Paid + Payment Amount
Invoice Amount Due = Grand Total - Total Paid

IF Total Paid >= Grand Total:
    Payment Status = Paid
ELSE IF Total Paid > 0:
    Payment Status = Partial Paid
ELSE:
    Payment Status = Pending
```

**Business Rules:**
- Payment amount cannot exceed invoice amount due
- Multiple partial payments allowed until fully paid
- Each payment creates entry in payment_history table
- Each payment creates entry in ledger (Credit entry for customer)
- Payment cannot be edited after saving (immutable)
- Payment can be deleted only by admin before invoice delivered
- Deleted payments are soft-deleted with audit log

**Validation Rules:**
- Payment amount: Must be greater than zero, up to 2 decimal places
- Payment amount must not exceed amount due
- Payment date cannot be before invoice date
- Payment date cannot be future date
- Cheque number required if payment mode is Cheque
- Transaction reference required if payment mode is Bank Transfer or UPI

#### 6.9.2 Payment with Gold Adjustment
**Business Requirement:** Adjust invoice amount based on gold weight changes and current gold rate

**Functional Details:**
- This is the most critical and complex payment feature
- Used when actual delivered gold weight differs from original challan weight
- Accounts manager records payment and applies gold adjustment
- System fetches today's gold rate (latest entry in gold rate table)
- For each invoice line item, user can update gold weight
- System calculates gold difference and adjustment amount for that specific line item
- Invoice amounts recalculated based on gold adjustment
- Payment recorded against adjusted invoice total

**Gold Adjustment Process (Step-by-Step):**

1. **Select Invoice for Payment:**
   - Display invoice details with all line items
   - Show original gold weight per line (from challan)

2. **Fetch Current Gold Rate:**
   - System fetches the latest gold rate entry for company
   - Display gold rate to user: "Today's Gold Rate: ₹X,XXX per gram"
   - If today's rate not available, show alert and use most recent available rate

3. **Update Gold Weight per Line:**
   - For each line item, user can enter new gold weight
   - Original gold weight shown (non-editable, for reference)
   - New gold weight field (editable)
   - System calculates gold difference per line

4. **Calculate Gold Difference:**
```
For each line item:
    gold_difference = new_gold_weight - original_gold_weight
```

5. **Calculate Gold Adjustment Amount:**
```
IF gold_difference > 0:
    // Customer used more gold, increase invoice amount
    gold_adjustment_amount = gold_difference × current_gold_rate
    
ELSE IF gold_difference < 0:
    // Customer used less gold, decrease invoice amount
    gold_adjustment_amount = gold_difference × current_gold_rate  (negative value)
    
ELSE:
    // No change in gold weight
    gold_adjustment_amount = 0
```

6. **Calculate Adjusted Line Amount:**
```
adjusted_line_amount = original_line_amount + gold_adjustment_amount
```

7. **Recalculate Invoice Totals:**
```
Adjusted Subtotal = SUM of all adjusted line amounts (without tax)
Adjusted Tax Amount = Adjusted Subtotal × tax_rate / (100 + tax_rate)
Adjusted Grand Total = Adjusted Subtotal + Adjusted Tax Amount
```

8. **Display Adjustment Summary:**
   - Show line-by-line gold adjustment
   - Display original invoice total
   - Display adjustment amount (positive or negative)
   - Display adjusted grand total
   - Display amount due (adjusted total - payments already received)

9. **Record Payment:**
   - User enters payment amount (against adjusted total)
   - Save payment record with gold adjustment details

**Gold Adjustment Data Saved (per line):**
- Original gold weight (from challan)
- New gold weight (entered during payment)
- Gold difference (new - original)
- Gold rate used (current rate at payment time)
- Gold adjustment amount (difference × rate)
- Original line amount (from invoice)
- Adjusted line amount (original + adjustment)

**Gold Adjustment Data Saved (invoice level):**
- Original grand total (before adjustment)
- Total gold adjustment amount (sum of all line adjustments)
- Adjusted grand total (after adjustment)
- Gold adjustment applied flag (boolean)
- Gold adjustment date
- Gold rate used for adjustment

**Business Rules:**
- Gold adjustment can only be applied during payment (not before, not after)
- Once gold adjustment applied, invoice amounts updated permanently
- Subsequent payments use adjusted amounts
- Gold adjustment is a one-time operation per invoice
- Cannot apply gold adjustment twice on same invoice
- If multiple payments, gold adjustment applied during first payment only
- Gold adjustment can be positive (customer owes more) or negative (customer owes less)
- If negative adjustment makes amount due negative, customer has credit balance (future invoices)
- Gold rate used must be documented in payment record (for audit)
- Adjustment shown separately in reports and ledger entries

**Validation Rules:**
- New gold weight: Must be greater than or equal to zero, up to 3 decimal places
- New gold weight cannot be same as original (no point in adjustment)
- Gold rate must be fetched (cannot proceed if rate unavailable - show error)
- Payment amount can be full or partial of adjusted total
- Cannot apply negative adjustment that makes grand total negative (sanity check)

**Example Gold Adjustment Calculation:**

**Original Invoice:**
- Line 1: 10 grams gold, Amount ₹10,000
- Line 2: 5 grams gold, Amount ₹5,000
- Original Total: ₹15,000

**At Payment Time:**
- Current Gold Rate: ₹6,000 per gram
- Line 1: New weight = 12 grams (2 grams more)
- Line 2: New weight = 4 grams (1 gram less)

**Calculations:**
- Line 1 adjustment: (12 - 10) × 6,000 = +₹12,000
- Line 2 adjustment: (4 - 5) × 6,000 = -₹6,000
- Total adjustment: +₹12,000 - ₹6,000 = +₹6,000
- Adjusted invoice total: ₹15,000 + ₹6,000 = ₹21,000

**Customer now owes ₹21,000 instead of ₹15,000**

**NEW FEATURE:** Gold Adjustment Preview
- Before saving payment, show adjustment preview screen
- User reviews line-by-line adjustments
- User confirms adjustment before proceeding
- Prevents accidental incorrect adjustments

#### 6.9.3 Payment History
**Business Requirement:** Maintain complete history of all payments per invoice

**Functional Details:**
- Each payment recorded creates an entry in payment history
- Payment history is immutable (append-only)
- Display payment history for any invoice
- Show: Payment date, amount, mode, reference, received by, timestamp

**Business Rules:**
- Payment history entries cannot be edited
- Payment history entries can only be soft-deleted by admin
- Deletion logged in audit trail
- Payment history used for ledger report generation

### 6.10 Module: Delivery Management

#### 6.10.1 Delivery Assignment
**Business Requirement:** Assign paid invoices to delivery personnel for delivery

**Functional Details:**
- Company admin or delivery manager assigns delivery
- Select invoice in "Paid" status (payment complete, not yet delivered)
- Assign to delivery user (select from users with delivery role)
- Enter expected delivery date
- Delivery user receives assigned delivery in their queue

**Delivery Data Captured:**
- Delivery ID (auto-generated)
- Invoice ID (reference)
- Assigned to user (delivery personnel)
- Assigned by user (who assigned)
- Assigned date and time
- Expected delivery date
- Actual delivery date (null until delivered)
- Delivery status (Assigned, In Transit, Delivered, Failed)
- Delivery address (copied from invoice shipping address)
- Customer contact mobile
- Delivery notes (optional)
- Delivery proof photo (uploaded after delivery)
- Customer signature (future feature)
- Delivered timestamp

**Business Rules:**
- Only invoices with payment status "Paid" can be assigned for delivery
- One invoice can have only one delivery assignment (no duplicates)
- Delivery user can only view their assigned deliveries
- Admin can view all deliveries
- Delivery can be reassigned to different user if not yet delivered

**Validation Rules:**
- Expected delivery date cannot be past date
- Invoice must be fully paid before assignment
- Assigned user must have delivery role

#### 6.10.2 Delivery Execution
**Business Requirement:** Delivery personnel mark deliveries as complete with proof

**Functional Details:**
- Delivery user logs into system
- Views assigned deliveries in their queue
- Selects delivery to execute
- Views delivery details: Customer name, address, contact, invoice items
- After reaching customer location and handing over goods:
  - Upload delivery proof photo (photo of delivered goods, customer premises, or receipt)
  - Mark delivery as "Delivered"
  - System automatically records delivered timestamp
- Invoice status automatically updated to "Delivered"

**Business Rules:**
- Delivery proof photo is mandatory before marking delivered
- Once marked delivered, cannot undo (admin can change if needed)
- Delivered timestamp auto-captured from system time
- Customer notified via SMS/Email when marked delivered (future feature)
- Invoice status changes from "Paid" to "Delivered"
- Delivered invoices appear in delivered invoices report

**Validation Rules:**
- Delivery proof image: Required, valid image format (JPG, PNG), max 10 MB
- Cannot mark delivered without uploading proof photo

#### 6.10.3 Delivery Reports
**Business Requirement:** Track delivery performance and pending deliveries

**Functional Details:**
- Pending Deliveries Report: List of all assigned but not delivered invoices
- Delivered Deliveries Report: List of all completed deliveries
- Delivery Personnel Performance: Number of deliveries completed per user
- Filter by: Date range, delivery user, status

**NEW FEATURE:** Delivery Route Optimization
- Future enhancement: Integrate with Google Maps API
- Optimize delivery routes for multiple deliveries in a day
- Show delivery sequence to user

### 6.11 Module: Reporting & Analytics

#### 6.11.1 Report 1 - Account Ledger
**Business Requirement:** Generate detailed ledger report for account customers

**Functional Details:**
- User selects account customer
- Selects date range (from date, to date)
- System generates ledger report showing all transactions

**Report Columns:**
- Date
- Invoice Number (or reference)
- Type (Invoice, Payment, Adjustment, Opening Balance)
- Description (item details, payment details, etc.)
- Item (products/processes from invoice lines)
- Weight (gold weight if applicable)
- Rate (process rate or gold rate)
- Gold (gold adjustment details if applicable)
- Amount (transaction amount)
- Debit (invoice amounts, adjustments that increase balance)
- Credit (payments, adjustments that decrease balance)
- Running Balance (cumulative balance after each transaction)

**Ledger Logic:**
- Opening Balance: Calculated as balance before "from date"
  - Opening Balance = Account's opening balance + SUM(Debits) - SUM(Credits) for all transactions before from date
- For each transaction in date range:
  - Invoice created → Debit entry (increases balance)
  - Payment received → Credit entry (decreases balance)
  - Gold adjustment positive → Debit entry
  - Gold adjustment negative → Credit entry
- Running Balance = Previous Balance + Debit - Credit

**Balance Interpretation:**
- Positive balance = Customer owes money to company (Receivable)
- Negative balance = Customer has advance payment (Credit balance)
- Zero balance = Fully settled

**Business Rules:**
- Ledger built from ledger_entries table (not calculated from invoices/payments directly)
- Ledger entries are immutable (append-only)
- Every financial transaction creates ledger entry
- Ledger report must match account statement
- Running balance calculated sequentially by date

**NEW FEATURE:** Ledger Export
- Export ledger to Excel, PDF, CSV
- Option to include/exclude opening balance
- Option to show only summary (totals) or detailed transactions

#### 6.11.2 Report 2 - Cash Customer Ledger
**Business Requirement:** Generate ledger report for cash customers (same logic as account ledger)

**Functional Details:**
- Exactly same report structure as Account Ledger
- User selects cash customer (by name + mobile)
- Select date range
- Generate ledger

**Business Rules:**
- Cash customers must have ledger exactly like account customers
- Opening balance for cash customers is always zero (no opening balance entry)
- Otherwise, logic identical to account ledger
- Cash invoices create debit entries
- Cash payments create credit entries

**Purpose:**
- Track repeat cash customers
- Identify high-value cash customers
- Convert cash customers to account customers in future

#### 6.11.3 Report 3 - Monthly Receivable Summary
**Business Requirement:** Generate month-wise receivable summary for all customers

**Functional Details:**
- User selects date range (multiple months)
- System generates summary report showing month-by-month receivables

**Report Columns:**
- Account Name
- Mobile Number
- Opening Balance (before start of selected period)
- Month 1 Column (e.g., Nov-2025): Debit, Credit, Closing Balance
- Month 2 Column (e.g., Dec-2025): Debit, Credit, Closing Balance
- Month 3 Column (e.g., Jan-2026): Debit, Credit, Closing Balance
- ...
- Final Closing Balance (end of selected period)

**Example:**

| Account Name | Mobile | Opening Bal | Nov-2025 (Dr/Cr/Bal) | Dec-2025 (Dr/Cr/Bal) | Jan-2026 (Dr/Cr/Bal) | Closing Bal |
|--------------|--------|-------------|----------------------|----------------------|----------------------|-------------|
| ABC Jewelers | 9876543210 | ₹10,000 | ₹50,000 / ₹30,000 / ₹30,000 | ₹40,000 / ₹50,000 / ₹20,000 | ₹60,000 / ₹70,000 / ₹10,000 | ₹10,000 |

**Calculation Logic:**
```
Opening Balance = Account opening balance + SUM(Dr) - SUM(Cr) before start date

For each month:
    Month Debit = SUM of all invoices in that month
    Month Credit = SUM of all payments in that month
    Month Closing = Previous Closing + Month Debit - Month Credit
```

**Business Rules:**
- Include both account customers and cash customers
- Group by customer
- Show month-by-month activity
- Heavy report - may need caching or background processing
- Use ledger_entries table (not direct invoice/payment aggregation)

**NEW FEATURE:** Aging Analysis
- Add column: 0-30 days, 31-60 days, 61-90 days, 90+ days
- Show how old the receivables are
- Highlight overdue amounts in red

#### 6.11.4 Report 4 - Outstanding Invoice Summary
**Business Requirement:** List all unpaid and partially paid invoices

**Functional Details:**
- Display all invoices where amount_due > 0
- Filter by: Customer, date range, payment status
- Sort by: Invoice date, due date, amount due

**Report Columns:**
- Invoice Number
- Invoice Date
- Due Date
- Customer Name
- Invoice Total
- Total Paid
- Amount Due
- Days Overdue (if past due date)

**Business Rules:**
- Exclude fully paid invoices (amount_due = 0)
- Highlight overdue invoices (due date < today)
- Show totals at bottom: Total Outstanding, Total Overdue

**NEW FEATURE:** Send Payment Reminders
- Bulk action: Select invoices and send payment reminder email/SMS
- Track reminders sent in audit log

#### 6.11.5 Report 5 - Payment Collection Summary
**Business Requirement:** Summarize payments collected in a date range

**Functional Details:**
- User selects date range
- System shows all payments received in that period
- Group by: Payment mode, customer, day

**Report Columns:**
- Date
- Customer Name
- Invoice Number
- Payment Amount
- Payment Mode
- Received By (user)

**Summary Totals:**
- Total Collected
- By mode: Cash total, Cheque total, Bank Transfer total, UPI total, Card total

**Business Rules:**
- Useful for daily cash reconciliation
- Export to Excel for bank deposit matching

#### 6.11.6 Dashboard & Analytics
**Business Requirement:** Provide real-time business metrics on dashboard

**Dashboard Widgets:**
1. **Today's Summary:**
   - Invoices created today: Count and total amount
   - Payments received today: Count and total amount
   - Pending deliveries: Count

2. **Outstanding Summary:**
   - Total receivables (sum of all amount_due)
   - Number of unpaid invoices
   - Number of overdue invoices

3. **Top Customers (by receivable amount)**
   - Top 10 customers with highest outstanding balance

4. **Payment Collection Trend:**
   - Line chart: Last 30 days payment collection

5. **Invoice vs Payment Chart:**
   - Bar chart: Monthly invoices vs payments

6. **Challan Status Overview:**
   - Pie chart: Draft, Approved, Invoice Generated

**NEW FEATURE:** Real-Time Notifications
- Alert when invoice overdue by 7 days
- Alert when gold rate not entered today
- Alert when pending deliveries > 10

### 6.12 Module: Audit & Activity Logging

#### 6.12.1 Audit Trail
**Business Requirement:** Log all critical system actions for compliance and security

**Functional Details:**
- Every create, update, delete action logged
- Log user, timestamp, action type, before/after data
- Logs stored in immutable audit_logs table
- Admin can view audit logs
- Search by: User, module, action type, date range

**Data Logged:**
- Audit log ID
- Company ID
- User ID (who performed action)
- Module (challan, invoice, payment, delivery, etc.)
- Action type (create, update, delete, view, print, export)
- Record type (challan, invoice, payment, etc.)
- Record ID (reference to affected record)
- Before data (JSON snapshot of record before change)
- After data (JSON snapshot of record after change)
- IP address
- User agent
- Timestamp

**Critical Actions to Log:**
- Invoice created
- Invoice edited
- Invoice deleted
- Payment recorded
- Payment deleted
- Gold adjustment applied
- Challan approved
- Delivery marked complete
- Settings changed
- Gold rate entered
- User created/edited/deleted
- Role permissions changed

**Business Rules:**
- Audit logs cannot be edited or deleted
- Admin-only access to audit logs
- Audit logs retained for 7 years (compliance)
- Large audit log tables may need archiving strategy

**NEW FEATURE:** Audit Report Export
- Export audit logs for specific date range
- Filter by user or module
- Useful for compliance audits

### 6.13 Module: Settings & Configuration

#### 6.13.1 Company Settings
**Business Requirement:** Configure company-specific business settings

**Settings Available:**
- Invoice number prefix (e.g., "INV-", "GST-")
- Challan number prefix (e.g., "CH-", "JOB-")
- Default tax rate (percentage)
- Minimum wax price (for wax challans)
- Default payment terms (e.g., Net 30 days)
- Terms and conditions text (for invoices)
- Bank details (for payment instructions on invoice)
- Company logo upload
- Email signature
- Financial year start month

**NEW FEATURE:** Email/SMS Templates
- Customize email templates for invoice, payment reminder, delivery notification
- Customize SMS templates
- Use variables: {customer_name}, {invoice_number}, {amount_due}, etc.

#### 6.13.2 Tax Rate Configuration
**Business Requirement:** Configure tax rates per product category or company default

**Functional Details:**
- Company default tax rate (applies to all invoices unless overridden)
- Can configure tax rate per product category (future feature)
- Tax rate changes apply only to future invoices

**NEW FEATURE:** Historical Tax Rate Tracking
- Maintain history of tax rate changes
- Show which tax rate was applied to each invoice
- Useful for audit and compliance

#### 6.13.3 States Master Data
**Business Requirement:** Maintain list of Indian states for address and tax calculation

**Functional Details:**
- Predefined list of Indian states (company_id = 0, global data)
- Used in: Company address, customer address
- Used for: CGST/SGST vs IGST determination

**States List:**
- Andhra Pradesh, Arunachal Pradesh, Assam, Bihar, Chhattisgarh, Goa, Gujarat, Haryana, Himachal Pradesh, Jharkhand, Karnataka, Kerala, Madhya Pradesh, Maharashtra, Manipur, Meghalaya, Mizoram, Nagaland, Odisha, Punjab, Rajasthan, Sikkim, Tamil Nadu, Telangana, Tripura, Uttar Pradesh, Uttarakhand, West Bengal
- Union Territories: Andaman and Nicobar Islands, Chandigarh, Dadra and Nagar Haveli and Daman and Diu, Delhi, Jammu and Kashmir, Ladakh, Lakshadweep, Puducherry

#### 6.13.4 Challan Type Configuration
**Business Requirement:** Define available challan types per company

**Functional Details:**
- System comes with 3 predefined challan types: Rhodium Accounts, Meena Accounts, Wax
- Company can enable/disable types based on their business
- Cannot delete types, only deactivate
- Deactivated types not shown in challan creation screen

#### 6.13.5 Invoice Type Configuration
**Business Requirement:** Define available invoice types per company

**Functional Details:**
- System comes with 3 predefined invoice types: Accounts Invoice, Cash Invoice, Wax Invoice
- Company can enable/disable types based on their business

---

## 7. Detailed Business Rules

### 7.1 Account vs Cash Customer Rules

| Aspect | Account Customer | Cash Customer |
|--------|------------------|---------------|
| **Identification** | Unique account_id | Unique combination: name + mobile |
| **Profile Data** | Full profile: address, GST, PAN, payment terms | Minimal: name + mobile only |
| **Opening Balance** | Yes (can have opening debit/credit) | No (always starts at zero) |
| **Credit Terms** | Yes (e.g., Net 30 days) | No (typically immediate payment) |
| **Challan Creation** | Can create Rhodium/Meena/Wax challans | Can create Wax challans only (future: may allow all types) |
| **Invoice Creation** | Accounts Invoice (from challans) | Cash Invoice (manual, no challan) |
| **Ledger** | Maintained in ledger_entries table | Maintained in ledger_entries table |
| **Reports** | Appears in Account Ledger report | Appears in Cash Customer Ledger report |
| **Conversion** | N/A | Cannot convert to account customer (Phase 1) |

### 7.2 Challan to Invoice Conversion Rules

| Rule | Description |
|------|-------------|
| **Challan Status** | Only "Approved" challans can be selected for invoicing |
| **Invoice Generated Flag** | Once invoice created, challan.invoice_generated = true |
| **Challan Locking** | After invoice generated, challan becomes read-only (immutable) |
| **Multiple Challans in One Invoice** | Allowed - multiple challans can be combined into single invoice |
| **Partial Challan Invoicing** | Not allowed - entire challan must be included in invoice (all lines) |
| **Challan Deletion** | Cannot delete challan after invoice generated |
| **Challan Editing** | Cannot edit challan after invoice generated |

### 7.3 Invoice Editing Rules

| Invoice Status | Payment Status | Can Edit Line Items | Can Edit Customer | Can Delete |
|----------------|----------------|---------------------|-------------------|------------|
| Draft | Pending | Yes | Yes | Yes |
| Posted | Pending | Yes (limited) | No | Admin only |
| Posted | Partial Paid | No | No | No |
| Posted | Paid | No | No | No |
| Delivered | Paid | No | No | No |

### 7.4 Payment Rules

| Rule | Description |
|------|-------------|
| **Payment Amount** | Cannot exceed invoice amount_due |
| **Partial Payments** | Allowed - multiple payments until fully paid |
| **Payment Deletion** | Admin only, before delivery, soft delete only |
| **Payment Editing** | Not allowed - payments are immutable |
| **Gold Adjustment** | Can be applied during first payment only |
| **Gold Adjustment Frequency** | Once per invoice only |
| **Zero Payment** | Not allowed - minimum payment ₹1 |

### 7.5 Gold Adjustment Rules

| Rule | Description |
|------|-------------|
| **When Applied** | During payment recording only |
| **Frequency** | Once per invoice (during first payment if multiple payments) |
| **Gold Rate Used** | Latest available gold rate at payment time |
| **Gold Rate Mandatory** | Cannot apply adjustment if gold rate not available - show error |
| **Weight Update** | Per line item - new weight entered |
| **Calculation** | Adjustment = (new_weight - original_weight) × gold_rate |
| **Positive Adjustment** | Customer owes more - increases invoice total |
| **Negative Adjustment** | Customer owes less - decreases invoice total |
| **Invoice Update** | Invoice amounts permanently updated after adjustment |
| **Reversal** | Not allowed - adjustment is permanent (admin override if critical error) |

### 7.6 Tax Calculation Rules

**Tax Inclusive System:**
- All line item amounts include tax
- Tax extracted using formula: `tax = amount × rate / (100 + rate)`
- Subtotal = amount - tax

**CGST/SGST vs IGST:**
- IF customer state == company state → CGST + SGST (split tax 50/50)
- ELSE → IGST (full tax as IGST)

**Example:**
- Company: Gujarat
- Customer: Gujarat
- Tax: 3%
- Line amount: ₹10,300
- Tax calculation: 10,300 × 3 / 103 = ₹300
- Subtotal: ₹10,000
- CGST: ₹150 (1.5%)
- SGST: ₹150 (1.5%)

### 7.7 Soft Delete Rules

| Module | Can Delete | Soft Delete | Permanent Delete |
|--------|-----------|-------------|------------------|
| Companies | Super Admin | Yes | No |
| Users | Company Admin | Yes | No |
| Products | Company Admin | Yes (if not used) | No |
| Processes | Company Admin | Yes (if not used) | No |
| Accounts | Company Admin | Yes (if no transactions) | No |
| Cash Customers | N/A | No (cannot delete) | No |
| Challans | Before invoice generated | Yes | No |
| Invoices | Before payment | Yes (Admin only) | No |
| Payments | Admin only | Yes (before delivery) | No |
| Deliveries | Admin only | Yes (if failed) | No |

**Soft Delete Implementation:**
- Every table has `is_deleted` flag (boolean, default false)
- Deleted records not shown in lists/dropdowns
- Deleted records retained for audit
- Deleted records can be restored by admin
- Deleted records included in audit trail

### 7.8 Concurrency & Locking Rules

**Invoice/Challan Numbering:**
- Use database transaction with row-level lock
- Or use database sequence for atomic increment
- Ensure no gaps in numbering sequence
- Race condition safe

**Simultaneous Editing:**
- Optimistic locking: Check record version/timestamp before update
- If version mismatch, show error: "Record was modified by another user. Please refresh and try again."

**Payment Recording:**
- Check invoice total_paid before recording payment
- Use database transaction to update invoice and create payment atomically
- Prevents double payment recording

### 7.9 Data Retention & Archiving Rules

**Financial Records:**
- Retain for minimum 7 years (Indian compliance)
- After 7 years, can archive to separate cold storage
- Never permanently delete financial records

**Audit Logs:**
- Retain for minimum 7 years
- Archive old logs to separate database/storage

**Closed Invoices:**
- Invoices in "Closed" status can be archived
- Archived invoices not shown in active lists but accessible via "View Archived"

### 7.10 Number Format & Rounding Rules

**Decimal Precision:**
- Currency (amounts): 2 decimal places
- Weight: 3 decimal places
- Rate: 2 decimal places
- Tax percentage: 2 decimal places

**Rounding:**
- All calculations use full precision (no intermediate rounding)
- Final amount rounded to 2 decimal places using standard rounding (0.5 rounds up)

**Example:**
- Rate: 33.33
- Weight: 10.555
- Amount: 33.33 × 10.555 = 351.8981 → Rounded to ₹351.90

### 7.11 Date & Time Rules

**Timezone:**
- All timestamps stored in UTC
- Display in company's timezone (configured in company settings)
- India default: IST (UTC+5:30)

**Date Validation:**
- Invoice date cannot be before challan date
- Payment date cannot be before invoice date
- Cannot enter future dates for transactions
- Delivery date can be future (expected delivery)

**Financial Year:**
- Configurable start month (default: April for Indian companies)
- Reports can filter by financial year

---

## 8. Workflows / Status Lifecycles

### 8.1 Challan Workflow

```
┌─────────┐
│  Draft  │ ← Created by Billing Manager
└────┬────┘
     │ Submit for Approval
     ▼
┌───────────┐
│ Submitted │
└─────┬─────┘
      │ Approve
      ▼
┌──────────┐
│ Approved │ ← Can be selected for invoice
└────┬─────┘
     │ Create Invoice
     ▼
┌───────────────────┐
│Invoice Generated  │ ← Locked, immutable
└───────────────────┘
```

**Status Transitions:**
- Draft → Submitted: By Billing Manager
- Submitted → Approved: By user with challan.approve permission
- Approved → Invoice Generated: Automatically when invoice created
- Draft/Submitted → Cancelled: By admin (exception flow)

### 8.2 Invoice Workflow

```
┌─────────┐
│  Draft  │ ← Created
└────┬────┘
     │ Post Invoice
     ▼
┌────────┐
│ Posted │ ← Active, awaiting payment
└────┬───┘
     │ Record Payment
     ▼
┌────────────────┐
│Partially Paid  │ ← Partial payment received
└────────┬───────┘
         │ Complete Payment
         ▼
┌────────┐
│  Paid  │ ← Fully paid, ready for delivery
└────┬───┘
     │ Assign & Complete Delivery
     ▼
┌───────────┐
│ Delivered │ ← Goods delivered
└─────┬─────┘
      │ Archive
      ▼
┌────────┐
│ Closed │ ← Archived
└────────┘
```

**Status Triggers:**
- Draft → Posted: Manual (by billing manager)
- Posted → Partially Paid: Automatic (when first payment recorded)
- Partially Paid → Paid: Automatic (when total_paid >= grand_total)
- Paid → Delivered: Automatic (when delivery marked complete)
- Any status → Closed: Manual (by admin for archiving)

### 8.3 Payment Workflow

```
┌─────────────────┐
│ Invoice: Pending│
└────────┬────────┘
         │ Record Payment (Partial)
         ▼
┌─────────────────┐
│Partial Paid     │ ← 0 < total_paid < grand_total
└────────┬────────┘
         │ Record Payment (Remaining)
         ▼
┌─────────────────┐
│ Paid            │ ← total_paid >= grand_total
└─────────────────┘
```

### 8.4 Delivery Workflow

```
┌──────────────┐
│Invoice: Paid │
└──────┬───────┘
       │ Assign Delivery
       ▼
┌──────────────┐
│   Assigned   │ ← Delivery assigned to user
└──────┬───────┘
       │ Mark In Transit
       ▼
┌──────────────┐
│  In Transit  │ ← Out for delivery
└──────┬───────┘
       │ Upload Proof & Mark Delivered
       ▼
┌──────────────┐
│  Delivered   │ ← Delivery complete
└──────────────┘

       OR (exception)
       │ Mark Failed
       ▼
┌──────────────┐
│   Failed     │ ← Delivery failed (reassign or cancel)
└──────────────┘
```

---

## 9. Financial & Accounting Logic

### 9.1 Double-Entry Accounting Principles

**While the system is not a full double-entry accounting system, it follows these principles for ledger management:**

**Account Ledger Entry Rules:**
- **Debit (Dr):** Increases receivable (customer owes money)
  - Invoice created → Debit entry
  - Gold adjustment (positive) → Debit entry
- **Credit (Cr):** Decreases receivable (customer pays money)
  - Payment received → Credit entry
  - Gold adjustment (negative) → Credit entry

**Ledger Balance Calculation:**
```
Running Balance = Opening Balance + Sum(Debits) - Sum(Credits)
```

**Balance Interpretation:**
- Positive balance → Receivable (customer owes company)
- Negative balance → Credit balance (customer has advance payment)
- Zero balance → Settled account

### 9.2 Ledger Entry Creation

**Every financial transaction MUST create a ledger entry:**

**Invoice Created:**
```
Ledger Entry:
- account_id or cash_customer_id
- reference_type: "invoice"
- reference_id: invoice.id
- date: invoice.date
- description: "Invoice #{invoice_number}"
- debit_amount: invoice.grand_total
- credit_amount: 0
- balance_after: previous_balance + debit_amount
```

**Payment Received:**
```
Ledger Entry:
- account_id or cash_customer_id
- reference_type: "payment"
- reference_id: payment.id
- date: payment.date
- description: "Payment for Invoice #{invoice_number}"
- debit_amount: 0
- credit_amount: payment.amount
- balance_after: previous_balance - credit_amount
```

**Gold Adjustment (Positive):**
```
Ledger Entry:
- reference_type: "gold_adjustment"
- description: "Gold adjustment +X grams on Invoice #{invoice_number}"
- debit_amount: adjustment_amount
- credit_amount: 0
- balance_after: previous_balance + debit_amount
```

**Gold Adjustment (Negative):**
```
Ledger Entry:
- reference_type: "gold_adjustment"
- description: "Gold adjustment -X grams on Invoice #{invoice_number}"
- debit_amount: 0
- credit_amount: abs(adjustment_amount)
- balance_after: previous_balance - credit_amount
```

**Opening Balance:**
```
Ledger Entry (created when account created):
- reference_type: "opening_balance"
- reference_id: null
- date: account.opening_balance_date
- description: "Opening Balance"
- debit_amount: IF opening_balance > 0 THEN opening_balance ELSE 0
- credit_amount: IF opening_balance < 0 THEN abs(opening_balance) ELSE 0
- balance_after: opening_balance
```

### 9.3 Ledger Integrity Rules

**Immutability:**
- Ledger entries are append-only
- Never update or delete ledger entries
- If error, create reversal entry

**Sequencing:**
- Ledger entries ordered by date, then time
- Running balance calculated sequentially
- Cannot insert entry in middle of sequence

**Reconciliation:**
- Invoice total_paid must equal sum of payment credits in ledger
- Invoice amount_due must equal last ledger balance
- Periodic reconciliation reports to catch discrepancies

### 9.4 Financial Period Closing

**Month-End Closing:**
- Generate month-end reports
- Verify invoice vs payment matching
- Verify ledger balances
- Lock financial records for the month (future feature)

**Year-End Closing:**
- Generate annual reports
- Calculate closing balances for all accounts
- Closing balance becomes opening balance for next year

---

## 10. Tax Rules

### 10.1 GST Tax Structure

**India GST Tax Types:**
- **CGST:** Central Goods and Services Tax
- **SGST:** State Goods and Services Tax
- **IGST:** Integrated Goods and Services Tax

**Tax Application Logic:**
```
IF customer_billing_state == company_state:
    // Intra-state transaction
    CGST = total_tax / 2
    SGST = total_tax / 2
    IGST = 0
ELSE:
    // Inter-state transaction
    IGST = total_tax
    CGST = 0
    SGST = 0
```

### 10.2 Tax Rates by Product Category

**Common GST Rates:**
- Gold jewelry: 3% GST
- Diamond jewelry: 1.5% GST (future feature)
- Imitation jewelry: 3% GST
- Job work/Services: 5% or 18% (configurable)

**System Configuration:**
- Company default tax rate (applied to all invoices)
- Future: Category-specific tax rates (override default)

### 10.3 Tax Inclusive Pricing

**The system uses tax-inclusive pricing by default:**
- Line item amounts include tax
- Tax is back-calculated using formula
- Subtotal = amount - tax

**Formula:**
```
Given: amount (tax-inclusive), tax_rate

tax_amount = amount × tax_rate / (100 + tax_rate)
subtotal = amount - tax_amount
```

**Example:**
- Amount: ₹10,300 (inclusive of 3% tax)
- Tax: 10,300 × 3 / 103 = ₹300
- Subtotal: 10,300 - 300 = ₹10,000

### 10.4 Tax Calculation on Gold Adjustment

**When gold adjustment applied:**
- Adjustment amount calculated first
- Adjusted line amount = original + adjustment
- Tax recalculated on adjusted amount
- Invoice totals updated

**Example:**
- Original amount: ₹10,000 (subtotal) + ₹300 (tax) = ₹10,300
- Gold adjustment: +₹5,000
- Adjusted subtotal: ₹15,000
- Adjusted tax: 15,000 × 3 / 100 = ₹450
- Adjusted total: ₹15,450

### 10.5 GST Compliance Requirements

**Invoice Must Include:**
- Company GSTIN
- Customer GSTIN (if registered business)
- HSN codes for products
- Tax rate
- CGST/SGST or IGST breakup
- Taxable amount (subtotal)
- Total tax amount
- Grand total

**Returns Filing:**
- System should support GSTR-1 report (future feature)
- Monthly sales data export
- Tax summary reports

---

## 11. Gold Adjustment Logic

### 11.1 Gold Adjustment Rationale

**Why Gold Adjustment Needed:**
- At challan creation, estimated gold weight entered
- During manufacturing, actual gold weight may differ
- At delivery/payment time, actual weight measured
- Invoice amount must reflect actual gold used
- Gold rates fluctuate daily

**Business Scenario:**
- Customer orders ring with estimated 10 grams gold
- Challan created with 10 grams
- Invoice generated based on 10 grams
- At payment time, actual weight measured: 12 grams
- Customer must pay for 12 grams, not 10
- System adjusts invoice using current gold rate

### 11.2 Gold Adjustment Calculation (Step-by-Step)

**Inputs Required:**
- Original gold weight (from challan line item)
- New gold weight (measured at payment time)
- Current gold rate (per gram, from gold rate master)

**Calculation:**
```
Step 1: Calculate gold difference per line
gold_difference = new_gold_weight - original_gold_weight

Step 2: Calculate adjustment amount per line
IF gold_difference > 0:
    gold_adjustment_amount = gold_difference × current_gold_rate
ELSE IF gold_difference < 0:
    gold_adjustment_amount = gold_difference × current_gold_rate (negative)
ELSE:
    gold_adjustment_amount = 0

Step 3: Calculate adjusted line amount
adjusted_line_amount = original_line_amount + gold_adjustment_amount

Step 4: Recalculate tax on adjusted amount
adjusted_tax = adjusted_line_amount × tax_rate / (100 + tax_rate)
adjusted_subtotal = adjusted_line_amount - adjusted_tax

Step 5: Aggregate for entire invoice
invoice_adjusted_total = SUM(all adjusted_line_amounts)
invoice_adjusted_tax = SUM(all adjusted_tax)
invoice_adjusted_subtotal = SUM(all adjusted_subtotals)
```

### 11.3 Gold Adjustment Data Storage

**Store at Line Level:**
- original_gold_weight
- new_gold_weight
- gold_difference (new - original)
- gold_rate_used (rate at adjustment time)
- gold_adjustment_amount (difference × rate)
- original_line_amount (before adjustment)
- adjusted_line_amount (after adjustment)

**Store at Invoice Level:**
- original_grand_total
- total_gold_adjustment (sum of all line adjustments)
- adjusted_grand_total
- gold_adjustment_applied (boolean flag)
- gold_adjustment_date
- gold_rate_used (reference for audit)

### 11.4 Gold Adjustment Business Rules

**Mandatory Rules:**
1. Gold adjustment can ONLY be applied during payment recording
2. Gold adjustment can be applied ONLY ONCE per invoice
3. Current gold rate MUST be available (error if not)
4. New gold weight cannot be same as original (no adjustment needed)
5. Adjustment can be positive (more gold used) or negative (less gold used)
6. Adjusted invoice total replaces original total permanently
7. Subsequent payments use adjusted total, not original
8. Gold adjustment creates separate ledger entry (not mixed with payment entry)
9. If multiple payments, adjustment applied during first payment only
10. Once adjusted, cannot revert (admin override only for critical errors)

### 11.5 Gold Adjustment Edge Cases

**Case 1: Negative Adjustment Makes Total Negative**
- Rare edge case: Adjustment so large negative that adjusted total < 0
- Validation: Prevent adjustment if adjusted total would be negative
- Show error: "Adjustment amount too large. Please review weights."

**Case 2: Gold Rate Not Available**
- Cannot apply adjustment without gold rate
- Show error: "Gold rate not available for today. Please enter gold rate first."
- Block adjustment until rate entered

**Case 3: Partial Payment Already Made Before Adjustment**
- First payment: No adjustment, partial amount paid
- Second payment: User wants to apply adjustment now
- Allowed: Adjustment applied during second payment
- Recalculate remaining due based on adjusted total

**Case 4: Zero Gold Weight Lines**
- Some lines may not have gold (e.g., pure labor charges)
- Adjustment not applicable for those lines
- Skip those lines in adjustment UI

### 11.6 Gold Adjustment Reporting

**Adjustment should be visible in:**
- Ledger reports (separate line item)
- Invoice view (adjustment amount shown separately)
- Payment receipt (adjustment details printed)
- Audit logs (adjustment recorded)

**Report Columns:**
- Original Weight
- Adjusted Weight
- Difference
- Gold Rate Used
- Adjustment Amount

---

## 12. Cash vs Account Customer Handling

### 12.1 Data Model Design

**Critical Principle: Single Table with Type-Based Design**

**DO NOT create separate tables for cash and account invoices/challans.**

**Correct Design:**
- Single `invoices` table with:
  - `invoice_type_id` (account/cash/wax)
  - `account_id` (nullable)
  - `cash_customer_id` (nullable)
  - Constraint: Exactly ONE must be populated (XOR logic)

- Single `challans` table with:
  - `challan_type_id`
  - `account_id` (nullable)
  - `cash_customer_id` (nullable)
  - Constraint: Exactly ONE must be populated

- `cash_customers` table:
  - `id`
  - `company_id`
  - `name`
  - `mobile`
  - Unique constraint: (name, mobile, company_id)

### 12.2 Validation Rules (CRITICAL)

**Database Level:**
```sql
CHECK (
    (account_id IS NOT NULL AND cash_customer_id IS NULL)
    OR
    (account_id IS NULL AND cash_customer_id IS NOT NULL)
)
```

**Application Level:**
- Before saving invoice/challan, validate XOR condition
- If both null: Error "Customer not specified"
- If both populated: Error "Cannot have both account and cash customer"

### 12.3 Cash Customer Uniqueness Logic

**Unique Key:** (name, mobile, company_id)

**Matching Logic:**
```
Before creating cash invoice:
    existing_customer = find_by(name=LOWER(TRIM(name)), mobile=mobile, company_id=company_id)
    
    IF existing_customer found:
        reuse existing_customer.id
    ELSE:
        create new cash_customer record
```

**Name Normalization:**
- Convert to lowercase for matching
- Trim leading/trailing spaces
- Normalize internal spaces (multiple spaces → single space)

**Example:**
- Input: " John  Doe " (with extra spaces)
- Normalized: "john doe"
- Match against existing: "john doe" with mobile "9876543210"

### 12.4 Cash vs Account Comparison Matrix

| Feature | Account Customer | Cash Customer |
|---------|------------------|---------------|
| **Profile** | Full profile with address, GST, PAN | Name + Mobile only |
| **Opening Balance** | Yes | No (always zero) |
| **Credit Terms** | Yes (e.g., Net 30 days) | No (immediate payment expected) |
| **Challan Types** | Rhodium, Meena, Wax | Wax only (Phase 1) |
| **Invoice Types** | Accounts Invoice (from challans) | Cash Invoice (manual) |
| **Invoice Source** | Generated from approved challans | Manually entered |
| **Ledger** | Maintained in ledger_entries | Maintained in ledger_entries |
| **Reports** | Account Ledger report | Cash Customer Ledger report |
| **Deduplication** | By account_code (unique) | By name+mobile (unique combination) |
| **Conversion** | N/A | Cannot convert to account (Phase 1) |

### 12.5 Cash Customer Search & Autocomplete

**Requirement:**
- As user types name or mobile in invoice form, show matching customers
- User selects from autocomplete to reuse existing record
- Prevents duplicate customer creation

**Implementation Logic:**
```
On keystroke in customer name/mobile field:
    IF input length >= 3:
        search_results = find_customers_by_name_or_mobile(input, company_id)
        display autocomplete dropdown with:
            - Customer name
            - Mobile number
            - Last invoice date
            - Last invoice amount
        
    User selects from dropdown:
        auto-fill customer_name and mobile fields
        cash_customer_id = selected_customer.id
        show message: "Existing customer selected"
        optionally show last 5 invoices for reference
```

### 12.6 Cash Customer in Reports

**Cash customers MUST appear in reports exactly like account customers:**

**Ledger Report:**
- Same format as account ledger
- Filter by cash_customer_id instead of account_id
- Running balance calculated same way

**Receivable Summary:**
- Include both account and cash customers
- Separate columns or merged view (configurable)

**Outstanding Invoices:**
- Show cash invoices with outstanding balance
- Group by customer name + mobile

**Payment Collection:**
- Include cash payments in daily collection summary

---

## 13. Ledger & Accounting Principles

### 13.1 Ledger Table Design

**Purpose:** Maintain append-only transaction log for all financial events

**Table: ledger_entries**

**Columns:**
- `id` (primary key)
- `company_id` (multi-tenant)
- `account_id` (nullable, for account customers)
- `cash_customer_id` (nullable, for cash customers)
- `reference_type` (enum: invoice, payment, adjustment, opening_balance)
- `reference_id` (foreign key to invoice/payment/adjustment)
- `transaction_date` (date of transaction)
- `description` (human-readable transaction description)
- `debit_amount` (increases customer balance)
- `credit_amount` (decreases customer balance)
- `balance_after` (running balance after this entry)
- `created_at` (timestamp)
- `created_by` (user_id)

**Indexes:**
- company_id
- account_id
- cash_customer_id
- reference_type, reference_id (composite)
- transaction_date

**Constraints:**
- XOR: account_id OR cash_customer_id (not both, not neither)
- debit_amount >= 0
- credit_amount >= 0
- balance_after can be positive, negative, or zero

### 13.2 Ledger Entry Creation Triggers

**EVERY financial event creates ledger entry:**

**1. Invoice Created (Posted):**
```
Ledger Entry:
- account_id or cash_customer_id: from invoice
- reference_type: "invoice"
- reference_id: invoice.id
- transaction_date: invoice.date
- description: "Invoice #{invoice.number}"
- debit_amount: invoice.grand_total
- credit_amount: 0
- balance_after: calculate_balance()
```

**2. Payment Received:**
```
Ledger Entry:
- account_id or cash_customer_id: from invoice
- reference_type: "payment"
- reference_id: payment.id
- transaction_date: payment.date
- description: "Payment for Invoice #{invoice.number} via {payment.mode}"
- debit_amount: 0
- credit_amount: payment.amount
- balance_after: calculate_balance()
```

**3. Gold Adjustment:**
```
Ledger Entry:
- reference_type: "gold_adjustment"
- reference_id: payment.id (or adjustment record id)
- transaction_date: adjustment.date
- description: "Gold adjustment {+/-}X grams on Invoice #{invoice.number}"
- debit_amount: IF adjustment > 0 THEN adjustment ELSE 0
- credit_amount: IF adjustment < 0 THEN abs(adjustment) ELSE 0
- balance_after: calculate_balance()
```

**4. Opening Balance (on account creation):**
```
Ledger Entry:
- reference_type: "opening_balance"
- reference_id: null
- transaction_date: account.opening_balance_date
- description: "Opening Balance"
- debit_amount: IF opening_balance > 0 THEN opening_balance ELSE 0
- credit_amount: IF opening_balance < 0 THEN abs(opening_balance) ELSE 0
- balance_after: opening_balance
```

### 13.3 Running Balance Calculation

**Balance Calculation Formula:**
```
balance_after = previous_balance + debit_amount - credit_amount
```

**Where:**
- `previous_balance` = balance_after of previous ledger entry (ordered by transaction_date, created_at)
- For first entry (opening balance), previous_balance = 0

**Balance Interpretation:**
- Positive balance = Customer owes money (Receivable / Asset)
- Negative balance = Customer has advance payment (Liability)
- Zero balance = Account settled

**Critical Rule:**
- Balance must be calculated SEQUENTIALLY
- Cannot calculate balance in parallel
- Use database transaction to ensure atomicity

### 13.4 Ledger Report Generation

**Account Ledger Query Logic:**
```sql
SELECT 
    le.transaction_date AS date,
    le.reference_type AS type,
    le.reference_id AS reference,
    le.description,
    le.debit_amount AS debit,
    le.credit_amount AS credit,
    le.balance_after AS balance
FROM ledger_entries le
WHERE le.account_id = {selected_account_id}
  AND le.company_id = {current_company_id}
  AND le.transaction_date BETWEEN {from_date} AND {to_date}
ORDER BY le.transaction_date ASC, le.created_at ASC
```

**Opening Balance Calculation:**
```sql
SELECT COALESCE(SUM(debit_amount - credit_amount), 0) AS opening_balance
FROM ledger_entries
WHERE account_id = {selected_account_id}
  AND company_id = {current_company_id}
  AND transaction_date < {from_date}
```

**Report Display:**
- Show opening balance as first row
- Show all transactions in date range
- Show closing balance as last row

### 13.5 Ledger Integrity & Reconciliation

**Integrity Rules:**
1. Ledger entries are immutable (never update)
2. Ledger entries are append-only
3. If error, create reversal entry (debit ↔ credit)
4. Running balance must be recalculated if entry inserted in middle (rare)

**Reconciliation Checks:**
1. Invoice total_paid = SUM(credit amounts) for that invoice in ledger
2. Invoice amount_due = invoice.grand_total - invoice.total_paid
3. Ledger balance for account = opening_balance + SUM(debits) - SUM(credits)
4. Periodic reconciliation report to catch discrepancies

**Reconciliation Report:**
- Compare invoice amounts vs ledger amounts
- Flag discrepancies
- Admin reviews and corrects

---

## 14. Reports & Analytics Requirements

### 14.1 Core Reports

#### Report 1: Account Ledger
- **Purpose:** Detailed transaction history for account customer
- **Inputs:** Account, Date range
- **Outputs:** Ledger with running balance
- **Export:** Excel, PDF, CSV

#### Report 2: Cash Customer Ledger
- **Purpose:** Detailed transaction history for cash customer
- **Inputs:** Cash customer, Date range
- **Outputs:** Same as account ledger
- **Export:** Excel, PDF, CSV

#### Report 3: Monthly Receivable Summary
- **Purpose:** Month-wise receivable analysis for all customers
- **Inputs:** Date range (multiple months)
- **Outputs:** Account-wise, month-wise debit/credit/balance
- **Export:** Excel, PDF

#### Report 4: Outstanding Invoice Summary
- **Purpose:** List all unpaid/partially paid invoices
- **Inputs:** Customer (optional), Date range (optional)
- **Outputs:** Invoice-wise outstanding with due dates
- **Export:** Excel, PDF

#### Report 5: Payment Collection Summary
- **Purpose:** Daily/monthly payment collection analysis
- **Inputs:** Date range, Payment mode (optional)
- **Outputs:** Payment totals by mode, customer, date
- **Export:** Excel, PDF

#### Report 6: Challan Status Report
- **Purpose:** Track challan progression
- **Inputs:** Date range, Status (optional)
- **Outputs:** Challan list with status, invoicing status
- **Export:** Excel, PDF

#### Report 7: Delivery Pending Report
- **Purpose:** Track pending deliveries
- **Inputs:** Date range, Delivery person (optional)
- **Outputs:** Invoices awaiting delivery
- **Export:** Excel, PDF

#### Report 8: Gold Adjustment Report
- **Purpose:** Track all gold adjustments applied
- **Inputs:** Date range
- **Outputs:** Invoice-wise gold adjustment details
- **Export:** Excel, PDF

#### Report 9: Tax Summary Report (GST Report)
- **Purpose:** GST filing data
- **Inputs:** Date range
- **Outputs:** Tax totals (CGST, SGST, IGST), invoice-wise breakup
- **Export:** Excel, CSV (for GSTR-1 import)

#### Report 10: Business Dashboard
- **Purpose:** Real-time KPIs
- **Outputs:** 
  - Today's invoices and payments
  - Outstanding receivables
  - Overdue invoices
  - Payment collection trend
  - Top customers by outstanding
- **Refresh:** Real-time

### 14.2 Report Performance Considerations

**Heavy Reports:**
- Monthly Receivable Summary (aggregates large data)
- Ledger reports for high-volume accounts
- Tax summary for large date ranges

**Optimization Strategies:**
1. **Indexing:** Ensure proper indexes on ledger_entries, invoices, payments
2. **Caching:** Cache frequently accessed reports (e.g., today's dashboard)
3. **Background Jobs:** Generate heavy reports asynchronously, notify when ready
4. **Pagination:** Paginate large result sets
5. **Materialized Views:** Pre-calculate aggregates for reporting (future)

### 14.3 Export Formats

**Excel (.xlsx):**
- Formatted with headers, borders, totals
- Use color coding (e.g., red for overdue)
- Include company logo and report title

**PDF:**
- Professional letterhead format
- Company logo, address
- Page numbers, generated date/time
- Authorized signatory section

**CSV:**
- Raw data export for further processing
- Use semicolon delimiter if needed (Excel compatibility)

---

## 15. Validation Rules & Constraints

### 15.1 Field-Level Validations

**Mandatory Fields:**
- All fields marked "Required" in data capture sections must be validated
- Show clear error message: "Field X is required"

**Data Type Validations:**
- Numeric fields: Only numbers allowed
- Decimal fields: Up to specified decimal places
- Date fields: Valid date format (YYYY-MM-DD)
- Email: Valid email format (regex: `.+@.+\..+`)
- Mobile: Exactly 10 digits (India)

**Range Validations:**
- Gold rate: 1,000 to 1,00,000 (sanity check)
- Tax rate: 0 to 28 (GST compliance)
- Weight: 0 to 10,000 grams (sanity check)
- Payment amount: > 0 and <= invoice amount_due

**Length Validations:**
- Name: 2 to 100 characters
- Mobile: Exactly 10 digits
- GST number: Exactly 15 characters
- PAN number: Exactly 10 characters
- Pincode: Exactly 6 digits

### 15.2 Business Logic Validations

**Invoice Creation:**
- Cannot create invoice without selecting at least one challan (for Accounts Invoice)
- Cannot select challan already invoiced (invoice_generated = true)
- Challan must be in "Approved" status
- Invoice date cannot be before challan date

**Payment Recording:**
- Payment amount cannot exceed invoice amount_due
- Payment date cannot be before invoice date
- Payment date cannot be future date
- Cheque number required if payment mode is Cheque

**Gold Adjustment:**
- New gold weight cannot be same as original (no adjustment needed)
- Gold rate must be available (error if not)
- Adjusted total cannot be negative

**Challan/Invoice Editing:**
- Cannot edit after payment recorded
- Cannot delete after invoice generated

**User Creation:**
- Username must be unique system-wide
- Email must be unique system-wide
- User must be assigned at least one role

### 15.3 Cross-Field Validations

**Account vs Cash Customer (XOR):**
- Exactly ONE must be populated: account_id OR cash_customer_id
- Both null: Error "Customer not specified"
- Both populated: Error "Cannot have both account and cash customer"

**State Matching for Tax:**
- Company state must be selected
- Customer state must be selected
- Tax display logic depends on state matching

**Date Sequence:**
- Payment date >= Invoice date
- Invoice date >= Challan date
- Delivery date >= Invoice date

### 15.4 Error Messaging Standards

**Clear Error Messages:**
- Specify which field has error
- Explain why it's invalid
- Suggest correction if possible

**Examples:**
- "Mobile number must be exactly 10 digits" (not "Invalid mobile")
- "Invoice date cannot be before challan date" (not "Invalid date")
- "Payment amount ₹15,000 exceeds invoice due amount ₹10,000" (not "Invalid amount")

**Error Display:**
- Inline errors next to fields (form validation)
- Summary error message at top of form
- Red border around invalid fields
- Prevent form submission until all errors fixed

---

## 16. Non-Functional Requirements

### 16.1 Performance Requirements

**Response Time:**
- Page load time: < 2 seconds
- Form submission: < 1 second
- Report generation (simple): < 3 seconds
- Report generation (heavy): < 30 seconds (with progress indicator)
- API response: < 500ms (95th percentile)

**Throughput:**
- Support 100 concurrent users per company
- 10,000 invoices per month per company
- 50,000 transactions per day (system-wide)

**Scalability:**
- System must scale to 100 companies (multi-tenant)
- Each company: 5 to 500 users
- Database must handle 100 million records

### 16.2 Availability Requirements

**Uptime:**
- 99.9% uptime (SLA)
- Planned maintenance: Max 4 hours per month, during off-hours

**Backup:**
- Daily full database backup
- Backup retention: 30 days
- Backup stored in geographically separate location
- Disaster recovery: RTO (Recovery Time Objective) = 4 hours, RPO (Recovery Point Objective) = 1 hour

### 16.3 Usability Requirements

**User Interface:**
- Responsive design (desktop, tablet, mobile browser)
- Intuitive navigation with breadcrumbs
- Consistent layout across modules
- Accessible to users with basic computer literacy

**Accessibility:**
- WCAG 2.1 Level AA compliance (future)
- Keyboard navigation support
- Screen reader friendly (future)

**User Training:**
- User manual provided
- Video tutorials for key workflows
- In-app help text and tooltips
- Customer support contact available

### 16.4 Compatibility Requirements

**Browser Support:**
- Google Chrome (latest 2 versions)
- Mozilla Firefox (latest 2 versions)
- Microsoft Edge (latest 2 versions)
- Safari (latest 2 versions)

**Device Support:**
- Desktop: Windows, macOS, Linux
- Mobile: iOS (Safari), Android (Chrome) - responsive web view

**Internet Connection:**
- Minimum 2 Mbps internet connection required
- Graceful degradation if connection slow (show loading indicators)

### 16.5 Maintainability Requirements

**Code Quality:**
- Well-documented code with comments
- Follow coding standards (PSR-12 for PHP)
- Modular architecture (MVC + Services)
- Unit tests for critical business logic

**Deployment:**
- Zero-downtime deployment (blue-green or rolling)
- Automated database migrations
- Rollback capability if deployment fails

**Monitoring:**
- Application performance monitoring (APM)
- Error logging and alerting
- Database query performance monitoring
- User activity monitoring

---

## 17. Security Requirements

### 17.1 Authentication & Authorization

**Authentication:**
- Username + Password based authentication
- Password hashing: bcrypt or Argon2 (industry standard)
- Password policy:
  - Minimum 8 characters
  - At least 1 uppercase, 1 lowercase, 1 number, 1 special character
  - Cannot reuse last 3 passwords
  - Password expiry: 90 days (configurable)
- Failed login attempts: 5 attempts → account temporarily locked for 15 minutes
- Session timeout: 30 minutes of inactivity
- Remember me: Optional, 7-day token with secure cookie

**Authorization:**
- Role-based access control (RBAC)
- Every API endpoint checks user permissions
- Permission format: {module}.{action}
- Super admin can bypass permissions (but actions still logged)
- UI dynamically shows/hides features based on permissions

### 17.2 Data Security

**Encryption:**
- Data in transit: HTTPS/TLS 1.3 (all communications encrypted)
- Data at rest: Database encryption (future: encrypt sensitive fields like PAN, bank details)
- Password storage: Hashed with salt (bcrypt/Argon2)

**Data Isolation:**
- Multi-tenant data isolation via company_id
- Row-level security (every query filters by company_id)
- Users cannot access other company data (except super admin with logging)

**Sensitive Data Handling:**
- PII (Personally Identifiable Information): GST, PAN, mobile, email
- Financial data: Invoice amounts, payment details
- Access to sensitive reports restricted by permissions
- Audit log for sensitive data access

### 17.3 Input Validation & Sanitization

**Prevent Injection Attacks:**
- SQL Injection: Use parameterized queries/prepared statements only (never concatenate SQL)
- XSS (Cross-Site Scripting): Sanitize all user inputs before displaying (escape HTML)
- CSRF (Cross-Site Request Forgery): CSRF tokens on all forms
- Command Injection: Never execute shell commands with user input

**File Upload Security:**
- Validate file type (whitelist: JPG, PNG, PDF only)
- Validate file size (max 10 MB)
- Store uploaded files outside web root
- Generate random file names (prevent overwriting/guessing)
- Scan files for malware (future: antivirus integration)

**Rate Limiting:**
- API rate limiting: 100 requests per minute per user
- Login rate limiting: 5 attempts per 15 minutes per IP
- Report generation rate limiting: 10 reports per hour per user

### 17.4 Audit & Compliance

**Audit Logging:**
- Log all financial transactions (create, edit, delete)
- Log all user actions (login, logout, failed attempts)
- Log permission changes
- Log settings changes
- Audit logs immutable (cannot be edited/deleted)
- Audit log retention: 7 years

**Compliance:**
- GST compliance: Invoice format, tax calculations, GSTR-1 data
- Data retention: 7 years for financial records
- GDPR compliance (if applicable): Data export, data deletion requests (future)

---

## 18. Performance Requirements

### 18.1 Database Optimization

**Indexing Strategy:**
- Primary keys on all tables
- Foreign keys indexed
- company_id indexed on all tables (multi-tenant filter)
- Frequently queried columns indexed: invoice_number, challan_number, customer name, mobile, date fields
- Composite indexes for common filter combinations

**Query Optimization:**
- Use EXPLAIN to analyze query performance
- Avoid N+1 queries (use eager loading/joins)
- Paginate all list views (default 50 records per page)
- Limit JOIN depth (max 3-4 levels)

**Connection Pooling:**
- Database connection pooling to reuse connections
- Max connections: 100 (configurable based on load)

### 18.2 Caching Strategy

**What to Cache:**
- Gold rate (latest entry) - cached for 1 hour
- User permissions - cached per session
- Company settings - cached for 1 hour
- Product/process master data - cached until changed

**Cache Technology:**
- In-memory cache: Redis or Memcached
- Cache invalidation on data change

### 18.3 Background Jobs & Queues

**Async Processing:**
- Heavy reports: Generate in background, notify user when ready
- Email/SMS notifications: Queue and process asynchronously
- Audit log writes: Buffered and written in batches

**Job Queue Technology:**
- Use message queue: Redis Queue or RabbitMQ
- Job retry logic (3 attempts on failure)
- Job monitoring dashboard

---

## 19. Audit & Compliance Requirements

### 19.1 Financial Compliance

**Indian GST Compliance:**
- Invoice must include all GST-mandated fields
- GSTIN, HSN codes, tax breakup (CGST/SGST/IGST)
- Support GSTR-1 data export (future)

**Accounting Standards:**
- Maintain proper ledger (debit/credit entries)
- Audit trail for all financial transactions
- Data immutability after posting

**Data Retention:**
- Financial records: 7 years minimum
- Audit logs: 7 years minimum
- Deleted records: Soft delete only (retain data)

### 19.2 Audit Trail Requirements

**What to Log:**
- User login/logout
- Failed login attempts
- Invoice/Challan/Payment creation
- Invoice/Challan/Payment editing
- Invoice/Challan/Payment deletion
- Gold rate entry
- Settings changes
- Permission changes
- Unauthorized access attempts

**Audit Log Data:**
- Timestamp (precise to second)
- User ID and username
- Action type
- Module and record type
- Record ID
- Before data (JSON snapshot)
- After data (JSON snapshot)
- IP address
- User agent (browser)

**Audit Log Security:**
- Audit logs cannot be edited
- Audit logs cannot be deleted (except by super admin after 7 years)
- Audit logs stored in separate table/database for security

### 19.3 User Activity Monitoring

**Track User Actions:**
- Report generation (which report, filters used)
- Invoice printing (which invoice, how many times)
- Data export (which module, date range)
- Sensitive data access (viewing PAN, GST, bank details)

**Purpose:**
- Security monitoring (detect unusual activity)
- Usage analytics (identify most-used features)
- Compliance (prove access controls working)

---

## 20. Assumptions

### 20.1 Business Assumptions

1. **Single Currency:** System supports only INR (Indian Rupees) in Phase 1. Multi-currency support is out of scope.

2. **India-Specific:** System designed for Indian market. GST compliance, state-based tax logic specific to India.

3. **Manual Gold Rate Entry:** Gold rate entered manually daily by admin. No automatic API integration with gold price feeds in Phase 1.

4. **No Credit Limit Enforcement:** System tracks outstanding amounts but does not enforce credit limits. Billing can proceed even if customer has large outstanding.

5. **No Inventory Management:** System does not track raw material inventory, finished goods inventory, or stock levels. Focus is on job management and billing only.

6. **No Manufacturing Floor Tracking:** System does not track production stages, machine usage, worker allocation. Only challan-based job tracking.

7. **Walk-In Cash Customers:** Cash customers are walk-ins without formal account setup. Minimal data captured (name + mobile).

8. **One Company per User (Phase 1):** Users belong to single company only. Multi-company user access is future feature.

9. **No Approval Workflows (except Challan):** Invoices, payments do not require approval. Only challans have approval workflow. Future versions may add multi-level approvals.

10. **Same-Day Delivery Assignment:** Delivery assigned on same day as payment in most cases. No advance delivery scheduling.

### 20.2 Technical Assumptions

1. **Stable Internet:** Users have stable internet connection (minimum 2 Mbps).

2. **Modern Browsers:** Users use updated browsers (Chrome, Firefox, Edge, Safari latest versions).

3. **No Offline Mode:** System requires internet connection. No offline functionality in Phase 1.

4. **Single Timezone per Company:** All users in a company operate in same timezone. Multi-timezone support out of scope.

5. **Adequate Server Resources:** Deployment environment provides sufficient CPU, RAM, storage for expected load.

6. **Database Scalability:** Database (MySQL/PostgreSQL) can scale to handle 100M+ records with proper indexing.

7. **File Storage:** Adequate file storage for images (challan photos, delivery proofs). Approximately 10 GB per company per year estimated.

### 20.3 Regulatory Assumptions

1. **GST Laws Stable:** GST tax rates and rules remain stable. If GST laws change, system may need updates.

2. **No International Transactions:** System handles only domestic (India) transactions. Export/import not supported.

3. **7-Year Data Retention Sufficient:** Assumes 7-year retention meets compliance (Indian IT Act, GST laws).

---

## 21. Out of Scope

### 21.1 Features Not Included in Phase 1

**Manufacturing & Production:**
- Production floor management (work order tracking, machine scheduling)
- Worker/artisan allocation and productivity tracking
- Raw material inventory management
- Finished goods inventory management
- Bill of Materials (BOM) management
- Production planning and scheduling
- Quality control and inspection workflows

**Procurement & Vendor Management:**
- Vendor/supplier master data
- Purchase orders
- Goods receipt notes (GRN)
- Vendor payments and payables
- Vendor ledger

**HR & Payroll:**
- Employee master data (beyond users)
- Attendance tracking
- Payroll processing
- Salary payments
- Leave management

**Advanced Financial Features:**
- Credit limit enforcement and alerts
- Credit note / Debit note generation
- Expense management
- Petty cash tracking
- Bank reconciliation
- Trial balance, Profit & Loss, Balance Sheet

**CRM & Marketing:**
- Lead management
- Customer segmentation
- Email/SMS marketing campaigns
- Customer portal (self-service)
- Quotation management
- Sales order management (before challan)

**E-Commerce:**
- Online store integration
- Shopping cart
- Payment gateway integration
- Customer self-registration

**Advanced Delivery:**
- Delivery route optimization (Google Maps integration)
- Real-time GPS tracking of delivery personnel
- Customer signature capture on mobile device
- Delivery scheduling (advance booking)

**Multi-Currency:**
- Foreign currency invoices
- Exchange rate management
- Multi-currency accounting

**Advanced Reporting:**
- Custom report builder (drag-and-drop)
- Scheduled report emails
- Report subscriptions
- Advanced analytics and BI dashboards

**Integration:**
- Tally integration (import/export)
- QuickBooks integration
- GST filing software integration (direct GSTR-1 filing)
- WhatsApp integration for notifications
- Payment gateway integration (online payment collection)

**Mobile App:**
- Native iOS app
- Native Android app
- (Responsive web app in scope)

**Barcode/RFID:**
- Barcode scanning for products
- RFID tracking for inventory

### 21.2 Deferred to Future Phases

**Phase 2 (Potential):**
- Credit limit management
- Multi-company user access
- Advanced approval workflows
- SMS/Email notification automation
- Customer portal
- Vendor management

**Phase 3 (Potential):**
- Inventory management
- Production floor tracking
- Mobile native apps
- WhatsApp integration
- Payment gateway integration

---

## 22. Open Questions (if any)

### 22.1 Clarifications Needed

1. **Gold Purity Tracking:** Do we need to track gold purity (22K, 24K, 18K) separately? Is gold rate different per purity level?
   - **Assumption for now:** Single gold rate for all purities. Purity is descriptive field only.
   - **NEW FEATURE ADDED:** Company-level purity standards defined. Can be used in future for purity-based pricing.

2. **Partial Challan Invoicing:** Can we invoice partial lines from a challan, or must entire challan be invoiced together?
   - **Assumption for now:** Entire challan must be invoiced together (all lines included).

3. **Multiple Gold Adjustments:** Can gold adjustment be applied multiple times (e.g., at each partial payment)?
   - **Decision:** No, gold adjustment can be applied only once per invoice (during first payment).

4. **Wax Challan for Cash Customers:** Can wax challans be created for cash customers, or only for account customers?
   - **Decision:** Wax challans can be created for both account and cash customers (either account_id or cash_customer_id).

5. **Invoice Numbering per Type:** Should Accounts Invoice, Cash Invoice, and Wax Invoice have separate numbering sequences?
   - **Decision:** Single shared numbering sequence for all invoice types (simplicity, tax compliance).

6. **Cash Customer Conversion:** Should we allow converting a cash customer to account customer later?
   - **Decision:** Not in Phase 1. Future feature if needed.

7. **Delivery Assignment:** Can one delivery person be assigned multiple deliveries in a day (batch delivery)?
   - **Decision:** Yes, delivery person can have multiple assigned deliveries. They complete them one by one.

8. **Challan Approval:** Who can approve challans? Is it a separate role or can billing manager self-approve?
   - **Decision:** User with `challan.approve` permission can approve. Typically not billing manager (separation of duties). Company admin can configure role as needed.

9. **Tax Rate Changes:** If company tax rate changes mid-month, do existing unpaid invoices get recalculated?
   - **Decision:** No, invoices use tax rate at time of creation. Changes apply only to new invoices going forward.

10. **Customer State Validation:** Do we validate customer state against actual state list, or allow free text?
    - **Decision:** Validate against predefined state list (dropdown). Ensures tax calculation accuracy.

---

## 23. Document Change Log

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | Feb 8, 2026 | Senior Product Manager | Initial PRD draft based on technical specification |

---

## 24. Approval & Sign-Off

| Role | Name | Signature | Date |
|------|------|-----------|------|
| **Business Sponsor** | [To be filled] | | |
| **Product Manager** | [To be filled] | | |
| **Technical Lead** | [To be filled] | | |
| **Finance Manager** | [To be filled] | | |
| **Compliance Officer** | [To be filled] | | |

---

## 25. Next Steps

### 25.1 Immediate Next Steps

1. **PRD Review & Approval:** Stakeholders review and approve this PRD
2. **Database Schema Design:** Architect designs detailed database schema based on PRD
3. **ER Diagram Creation:** Create entity-relationship diagram showing all tables and relationships
4. **API Endpoint Design:** Define all API endpoints grouped by module
5. **UI/UX Wireframes:** Create wireframes for key screens
6. **Technical Architecture Document:** Create system architecture document covering tech stack, deployment, scaling
7. **Task Breakdown:** Break down implementation into tasks and sub-tasks with time estimates
8. **Sprint Planning:** Organize tasks into sprints (2-week iterations)

### 25.2 Implementation Phases

**Phase 1: Foundation (Weeks 1-4)**
- Multi-tenant setup
- User authentication and RBAC
- Company management
- Product/Process masters
- Gold rate management

**Phase 2: Core Workflow (Weeks 5-8)**
- Challan management (all types)
- Invoice management (all types)
- Account and cash customer management
- Tax calculation logic

**Phase 3: Payment & Delivery (Weeks 9-12)**
- Payment recording
- Gold adjustment feature
- Ledger entry generation
- Delivery management

**Phase 4: Reporting (Weeks 13-16)**
- Ledger reports (account and cash)
- Receivable summary
- Outstanding invoices
- Payment collection reports
- Dashboard

**Phase 5: Polish & Testing (Weeks 17-20)**
- Audit logging
- Security hardening
- Performance optimization
- User acceptance testing (UAT)
- Bug fixes
- Documentation

### 25.3 Deliverables Expected

From this PRD, the following deliverables will be created:

1. **Database Schema Document** (tables, columns, data types, constraints, indexes)
2. **ER Diagram** (visual representation of database structure)
3. **API Specification Document** (all endpoints with request/response formats)
4. **UI Wireframes** (low-fidelity mockups for key screens)
5. **Technical Architecture Document** (system design, technology choices, deployment strategy)
6. **Task Breakdown Sheet** (comprehensive list of tasks and sub-tasks with estimates)
7. **User Stories** (feature descriptions from user perspective)
8. **Test Cases** (functional and integration test cases)
9. **User Manual** (end-user documentation)
10. **Admin Manual** (system administrator guide)

---

**END OF PRODUCT REQUIREMENTS DOCUMENT**

---

## Summary for Development Team

This PRD covers:
- ✅ All business requirements (NO code, NO technical implementation)
- ✅ 13 core modules with detailed functional requirements
- ✅ Complete workflows and status lifecycles
- ✅ Financial and accounting logic explained
- ✅ Tax rules (GST CGST/SGST/IGST)
- ✅ Gold adjustment logic (step-by-step calculation)
- ✅ Cash vs Account customer handling (data model, validation, deduplication)
- ✅ Ledger and accounting principles (ledger_entries table design)
- ✅ 10+ comprehensive reports
- ✅ Validation rules and constraints
- ✅ Non-functional requirements (performance, security, compliance)
- ✅ Audit and compliance requirements
- ✅ Assumptions and out-of-scope items

**This PRD can now be used to:**
1. Design database schema
2. Create ER diagram
3. Break down into tasks and sub-tasks
4. Start development with full clarity

**No business logic has been omitted. Several NEW FEATURES have been added where logical gaps existed.**
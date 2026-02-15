# AI CODING PROMPTS - TASK 06

## Delivery Management

**Version:** 1.1 (Updated to match DB Schema)
**Phase:** 6 - Delivery Management
**Generated:** February 14, 2026

---

## SUBTASKS: 6.1.1-6.1.3 (Database, Models, Services), 6.2.2 (Controllers & Views)

---

## 🎯 TASK 6.1: DELIVERY DATABASE & MODELS

### Subtask 6.1.1: Modify deliveries Migration

```
[PASTE .antigravity RULES FIRST]

FILE: app/Database/Migrations/2026-02-14-000001_modify_deliveries_table.php

INTRUCTIONS:
The `deliveries` table ALREADY EXISTS in the database. Do NOT create it.
You must create a migration to ADD missing columns and modify the ENUM if necessary.

EXISTING SCHEMA (Do not change these):
- id, company_id, invoice_id
- assigned_to (INT), assigned_by (INT)
- delivery_address (TEXT)
- customer_contact_mobile (VARCHAR)
- expected_delivery_date (DATE), actual_delivery_date (DATE)
- delivery_status ENUM('Assigned', 'In Transit', 'Delivered', 'Failed')
- delivery_proof_photo (VARCHAR 255)
- created_at, updated_at, is_deleted

COLUMNS TO ADD (via ALTER TABLE):
1. `delivery_contact_name` (VARCHAR 100, AFTER delivery_address)
2. `failed_reason` (TEXT, NULL, AFTER delivery_notes)

DELIVERABLES: Complete migration file using `forge->addColumn`.

ACCEPTANCE CRITERIA: Migration runs successfully without errors on existing table.
```

---

### Subtask 6.1.2: Create DeliveryModel

```
[PASTE .antigravity RULES FIRST]

FILE: app/Models/DeliveryModel.php

TABLE: deliveries
PRIMARY KEY: id
ALLOWED FIELDS:
- company_id, invoice_id
- assigned_to, assigned_by, assigned_date
- expected_delivery_date, actual_delivery_date
- delivery_status, delivery_address
- customer_contact_mobile, delivery_contact_name
- delivery_notes, failed_reason
- delivery_proof_photo, delivered_timestamp
- is_deleted, created_at, updated_at

METHODS:
1. findAll() - with company filter
2. getDeliveriesByInvoice(int $invoiceId): array
3. getMyDeliveries(int $userId): array
   - Returns deliveries assigned to $userId
   - Filters by status NOT 'Delivered' OR 'Failed' (Active deliveries)
4. getPendingDeliveries(): array
   - status = 'Assigned' or 'In Transit'
5. markAsDelivered(int $id, string $photoPath, $actualDate): bool
   - Updates status to 'Delivered'
   - Sets delivery_proof_photo
6. markAsFailed(int $id, string $reason): bool
   - Updates status to 'Failed'
   - Sets failed_reason

DELIVERABLES: Complete DeliveryModel.php

ACCEPTANCE CRITERIA: All methods working, soft deletes handled
```

---

### Subtask 6.1.3: Create DeliveryService

```
[PASTE .antigravity RULES FIRST]

FILE: app/Services/Delivery/DeliveryService.php

DEPENDENCIES: DeliveryModel, InvoiceModel, FileUploadService, AuditService

METHODS:
1. public function assignDelivery(int $invoiceId, int $assignedToUserId, int $assignedByUserId, $expectedDate, $notes = null): int
   - Validate invoice exists and is fully paid (payment_status = 'Paid')
   - Validate invoice not already assigned (check existing delivery)
   - Create delivery record with status 'Assigned'
   - Update Invoice: No status change needed yet (remains 'Paid')
   - Audit log: MODULE_DELIVERY, ACTION_ASSIGN

2. public function startDelivery(int $deliveryId): bool
   - Update delivery_status = 'In Transit'
   - Audit log

3. public function markDelivered(int $deliveryId, $proofPhotoFile): bool
   - Upload proof photo using FileUploadService
   - Update delivery:
     - delivery_status = 'Delivered'
     - actual_delivery_date = current date
     - delivered_timestamp = current timestamp
     - delivery_proof_photo = path
   - Update Invoice:
     - invoice_status = 'Delivered'
   - Audit log

4. public function markFailed(int $deliveryId, string $reason): bool
   - Update delivery:
     - delivery_status = 'Failed'
     - failed_reason = $reason
   - Audit log

5. public function getMyDashboard(int $userId): array
   - Return counts: Assigned, In Transit, Delivered Today

DELIVERABLES: Complete DeliveryService.php

ACCEPTANCE CRITERIA: Delivery assignment flow, Status transitions (Assigned -> In Transit -> Delivered)
```

---

## 🎯 TASK 6.2: DELIVERY CONTROLLERS & VIEWS

### Subtask 6.2.2: Create DeliveryController and Views

```
[PASTE .antigravity RULES FIRST]

FILE 1: app/Controllers/Deliveries/DeliveryController.php

ROUTES:
- GET /deliveries → index() (Admin/Manager view)
- GET /my-deliveries → myDeliveries() (Delivery Personnel view)
- GET /deliveries/create → create()
- POST /deliveries → store()
- GET /deliveries/{id} → show()
- POST /deliveries/{id}/start → start()
- POST /deliveries/{id}/complete → complete() (Mark Delivered)
- POST /deliveries/{id}/fail → fail()

METHODS:
1. index() - List all deliveries (Serverside DataTable)
2. myDeliveries() - List current user's active deliveries
3. create() - Form to assign delivery.
   - Fetch 'Paid' invoices that are NOT yet assigned.
   - Fetch users with 'Delivery Personnel' role.
4. store() - Validates and calls Service->assignDelivery()
5. start() - Service->startDelivery()
6. complete() - Handle file upload, Service->markDelivered()
7. fail() - Service->markFailed()

FILE 2: app/Views/deliveries/index.php
- Admin view
- Columns: ID, Invoice #, Customer, Assigned To, Status (Badge), Exp Date, Actions

FILE 3: app/Views/deliveries/my_deliveries.php
- Mobile-friendly card layout for delivery personnel
- Tabs: Assigned (New), In Transit (Ongoing), History (Delivered)
- 'Start' button for Assigned
- 'Complete' / 'Fail' buttons for In Transit

FILE 4: app/Views/deliveries/create.php
- Fields: Select Paid Invoice, Select Delivery Person, Expected Date, Notes

FILE 5: app/Views/deliveries/show.php
- Detailed view with Map link (optional placeholder), Customer details, Items list.
- Action buttons based on status.

DELIVERABLES: Controller, 4 Views, Route Config, Sidebar Menu update

ACCEPTANCE CRITERIA: Full lifecycle test: Assign -> Start -> Complete/Fail.
```

---

**END OF TASK-06 COMPLETE**

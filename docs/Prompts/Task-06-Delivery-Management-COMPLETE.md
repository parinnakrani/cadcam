# AI CODING PROMPTS - TASK 06
## Delivery Management

**Version:** 1.0  
**Phase:** 6 - Delivery Management (Weeks 15-16)  
**Generated:** February 10, 2026

---

## SUBTASKS: 6.1.1-6.1.3 (Database, Models, Services), 6.2.2 (Controllers & Views)

---

## 🎯 TASK 6.1: DELIVERY DATABASE & MODELS

### Subtask 6.1.1: Create deliveries Migration

```
[PASTE .antigravity RULES FIRST]

FILE: app/Database/Migrations/2026-01-01-000018_create_deliveries_table.php

TABLE STRUCTURE:
- id, company_id, invoice_id
- assigned_to (INT, FK to users.id) // Delivery person
- delivery_address (TEXT)
- delivery_contact_name, delivery_contact_mobile
- expected_delivery_date (DATE)
- actual_delivery_date (DATE, NULL)
- delivery_status (ENUM('Pending', 'Out for Delivery', 'Delivered', 'Failed'))
- proof_of_delivery_photo (VARCHAR 255, NULL)
- delivery_notes (TEXT)
- failed_reason (TEXT, NULL)
- created_by, is_deleted, created_at, updated_at

INDEXES: company_id, invoice_id, assigned_to, delivery_status

FOREIGN KEYS: company_id, invoice_id, assigned_to, created_by

DELIVERABLES: Complete migration

ACCEPTANCE CRITERIA: Migration runs, FKs working
```

---

### Subtask 6.1.2: Create DeliveryModel

```
[PASTE .antigravity RULES FIRST]

FILE: app/Models/DeliveryModel.php

METHODS:
1. findAll() - with company filter
2. getDeliveriesByInvoice(int $invoiceId): array
3. getMyDeliveries(int $userId): array - assigned deliveries
4. getPendingDeliveries(): array - status = Pending or Out for Delivery
5. markAsDelivered(int $id, string $photoPath, $actualDate): bool
6. markAsFailed(int $id, string $reason): bool

DELIVERABLES: Complete DeliveryModel.php

ACCEPTANCE CRITERIA: All methods working
```

---

### Subtask 6.1.3: Create DeliveryService

```
[PASTE .antigravity RULES FIRST]

FILE: app/Services/Delivery/DeliveryService.php

DEPENDENCIES: DeliveryModel, InvoiceModel, FileUploadService, AuditService

METHODS:
1. public function assignDelivery(int $invoiceId, int $userId, $expectedDate): int
   - Validate invoice fully paid
   - Create delivery record
   - Update invoice.delivery_status = 'Out for Delivery'
   - Audit log

2. public function markDelivered(int $deliveryId, $proofPhoto): bool
   - Upload proof photo
   - Update delivery status = 'Delivered', actual_date = today
   - Update invoice status = 'Delivered'
   - Audit log

3. public function markFailed(int $deliveryId, string $reason): bool
   - Update status = 'Failed', failed_reason
   - Audit log

4. public function getMyDeliveries(int $userId): array
   - Get deliveries assigned to user
   - Status not 'Delivered'

DELIVERABLES: Complete DeliveryService.php

ACCEPTANCE CRITERIA: Delivery assignment works, proof upload works
```

---

## 🎯 TASK 6.2: DELIVERY CONTROLLERS & VIEWS

### Subtask 6.2.2: Create DeliveryController and Views

```
[PASTE .antigravity RULES FIRST]

FILE 1: app/Controllers/Deliveries/DeliveryController.php

ROUTES:
- GET /deliveries → index()
- GET /deliveries/create → create()
- POST /deliveries → store()
- GET /deliveries/{id} → show()
- POST /deliveries/{id}/mark-delivered → markDelivered()
- POST /deliveries/{id}/mark-failed → markFailed()

METHODS:
1. index() - list all deliveries
2. create() - assign delivery form (select invoice, user, date)
3. store() - call DeliveryService->assignDelivery()
4. show() - delivery details
5. markDelivered() - upload photo, mark delivered
6. markFailed() - enter reason, mark failed

FILE 2: app/Views/deliveries/index.php
- DataTable: Delivery ID, Invoice No, Customer, Assigned To, Expected Date, Status, Actions

FILE 3: app/Views/deliveries/create.php
- Form: Invoice (paid invoices), Assign To (users), Expected Date, Notes

FILE 4: app/Views/deliveries/show.php
- Display delivery details
- If Pending: buttons to Mark Delivered (with photo upload) or Mark Failed

FILE 5: Routes & Sidebar
- Add delivery routes
- Add sidebar menu item

DELIVERABLES: Controller, 3 views, routes, sidebar

ACCEPTANCE CRITERIA: Delivery assignment works, mark delivered with photo upload works
```

---

**END OF TASK-06 COMPLETE**

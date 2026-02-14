# Invoice Routes Configuration

## File: `app/Config/Routes.php`

Add these routes to your Routes.php file:

```php
<?php

// Invoice Routes
$routes->group('invoices', ['namespace' => 'App\Controllers\Invoices'], function($routes) {
    // List invoices
    $routes->get('/', 'InvoiceController::index');

    // Create invoice (standalone)
    $routes->get('create', 'InvoiceController::create');
    $routes->post('/', 'InvoiceController::store');

    // Create invoice from challan
    $routes->get('create-from-challan/(:num)', 'InvoiceController::createFromChallan/$1');
    $routes->post('from-challan', 'InvoiceController::storeFromChallan');

    // View invoice
    $routes->get('(:num)', 'InvoiceController::show/$1');

    // Edit invoice
    $routes->get('(:num)/edit', 'InvoiceController::edit/$1');
    $routes->post('(:num)', 'InvoiceController::update/$1');

    // Delete invoice
    $routes->delete('(:num)', 'InvoiceController::delete/$1');

    // Print/PDF invoice
    $routes->get('(:num)/print', 'InvoiceController::print/$1');
});
```

---

## Route Summary

| Method | URI                                  | Controller Method     | Description                                    |
| ------ | ------------------------------------ | --------------------- | ---------------------------------------------- |
| GET    | `/invoices`                          | `index()`             | List all invoices with filters                 |
| GET    | `/invoices/create`                   | `create()`            | Show invoice creation form                     |
| POST   | `/invoices`                          | `store()`             | Store new invoice                              |
| GET    | `/invoices/create-from-challan/{id}` | `createFromChallan()` | Show invoice form pre-filled with challan data |
| POST   | `/invoices/from-challan`             | `storeFromChallan()`  | Create invoice from challan                    |
| GET    | `/invoices/{id}`                     | `show()`              | View invoice details                           |
| GET    | `/invoices/{id}/edit`                | `edit()`              | Show invoice edit form                         |
| POST   | `/invoices/{id}`                     | `update()`            | Update invoice                                 |
| DELETE | `/invoices/{id}`                     | `delete()`            | Delete invoice                                 |
| GET    | `/invoices/{id}/print`               | `print()`             | Generate and download PDF                      |

---

## Usage Examples

### List Invoices

```
GET /invoices
GET /invoices?invoice_type=Cash+Invoice
GET /invoices?payment_status=Pending
GET /invoices?date_from=2026-02-01&date_to=2026-02-28
```

### Create Invoice

```
GET /invoices/create
POST /invoices (with form data)
```

### Create from Challan

```
GET /invoices/create-from-challan/5
POST /invoices/from-challan (with challan_id)
```

### View Invoice

```
GET /invoices/1
```

### Edit Invoice

```
GET /invoices/1/edit
POST /invoices/1 (with form data)
```

### Delete Invoice

```
DELETE /invoices/1 (AJAX request)
```

### Print Invoice

```
GET /invoices/1/print (download)
GET /invoices/1/print?action=inline (view in browser)
```

---

## Filter Parameters

### Invoice Listing Filters

| Parameter        | Type   | Description                        | Example                            |
| ---------------- | ------ | ---------------------------------- | ---------------------------------- |
| `invoice_type`   | string | Filter by invoice type             | `Cash Invoice`, `Accounts Invoice` |
| `payment_status` | string | Filter by payment status           | `Pending`, `Partial Paid`, `Paid`  |
| `customer_type`  | string | Filter by customer type            | `Account`, `Cash`                  |
| `date_from`      | date   | Start date (YYYY-MM-DD)            | `2026-02-01`                       |
| `date_to`        | date   | End date (YYYY-MM-DD)              | `2026-02-28`                       |
| `search`         | string | Search invoice number or reference | `INV-0001`                         |

---

## AJAX Requests

All routes support AJAX requests and return JSON responses:

### Success Response

```json
{
  "success": true,
  "message": "Invoice created successfully",
  "invoice_id": 1,
  "redirect": "/invoices/1"
}
```

### Error Response

```json
{
  "error": "You do not have permission to create invoices"
}
```

### List Response

```json
{
    "success": true,
    "data": [...],
    "pager": "..."
}
```

---

## Permission Requirements

| Route                 | Permission Required |
| --------------------- | ------------------- |
| `index()`             | `invoice.view`      |
| `create()`            | `invoice.create`    |
| `createFromChallan()` | `invoice.create`    |
| `store()`             | `invoice.create`    |
| `storeFromChallan()`  | `invoice.create`    |
| `show()`              | `invoice.view`      |
| `edit()`              | `invoice.edit`      |
| `update()`            | `invoice.edit`      |
| `delete()`            | `invoice.delete`    |
| `print()`             | `invoice.view`      |

---

## Business Rules Enforced

1. ✅ **Cannot edit paid invoices** - Checked in `edit()` and `update()`
2. ✅ **Cannot delete paid invoices** - Checked in `delete()`
3. ✅ **Challan must be approved** - Checked in `createFromChallan()`
4. ✅ **Challan cannot be already invoiced** - Checked in `storeFromChallan()`
5. ✅ **All actions require permissions** - Checked in every method
6. ✅ **Multi-tenant isolation** - Automatic via session company_id

---

## Error Handling

### Exception Types

| Exception                         | HTTP Status | Message                           |
| --------------------------------- | ----------- | --------------------------------- |
| `InvoiceNotFoundException`        | 404         | Invoice not found                 |
| `InvoiceAlreadyPaidException`     | 400         | Cannot modify/delete paid invoice |
| `ChallanAlreadyInvoicedException` | 400         | Challan already invoiced          |
| `ValidationException`             | 400         | Validation error message          |
| `PermissionException`             | 403         | Permission denied                 |
| `Exception`                       | 500         | Generic error                     |

### Error Responses

**Web (Redirect)**:

```php
return redirect()->back()->with('error', 'Error message');
```

**AJAX (JSON)**:

```php
return $this->response->setStatusCode(400)->setJSON([
    'error' => 'Error message'
]);
```

---

## Complete Route File Example

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ... other routes ...

// Invoice Routes
$routes->group('invoices', ['namespace' => 'App\Controllers\Invoices'], function($routes) {
    // List invoices
    $routes->get('/', 'InvoiceController::index');

    // Create invoice (standalone)
    $routes->get('create', 'InvoiceController::create');
    $routes->post('/', 'InvoiceController::store');

    // Create invoice from challan
    $routes->get('create-from-challan/(:num)', 'InvoiceController::createFromChallan/$1');
    $routes->post('from-challan', 'InvoiceController::storeFromChallan');

    // View invoice
    $routes->get('(:num)', 'InvoiceController::show/$1');

    // Edit invoice
    $routes->get('(:num)/edit', 'InvoiceController::edit/$1');
    $routes->post('(:num)', 'InvoiceController::update/$1');

    // Delete invoice
    $routes->delete('(:num)', 'InvoiceController::delete/$1');

    // Print/PDF invoice
    $routes->get('(:num)/print', 'InvoiceController::print/$1');
});
```

---

**Invoice routes are ready to use!** 🚀

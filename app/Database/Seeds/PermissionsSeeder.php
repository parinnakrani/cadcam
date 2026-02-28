<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PermissionsSeeder extends Seeder
{
  public function run()
  {
    $db = \Config\Database::connect();

    // Skip if already seeded
    $existingCount = $db->table('permissions')->countAllResults();
    if ($existingCount > 0) {
      echo "Permissions table already has {$existingCount} records. Skipping seeder.\n";
      return;
    }

    $permissions = [];
    $sortOrder   = 0;

    // ─────────────────────────────────────────────────────────
    // 1. INVOICES (4 sub-modules × 8 actions = 32)
    // ─────────────────────────────────────────────────────────
    $invoiceSubModules = [
      'all'     => 'All Invoices',
      'account' => 'Account Invoice',
      'cash'    => 'Cash Invoice',
      'wax'     => 'Wax Invoice',
    ];
    $invoiceActions = ['list', 'view', 'create', 'edit', 'delete', 'print', 'status_change', 'record_payment'];

    foreach ($invoiceSubModules as $sub => $subLabel) {
      foreach ($invoiceActions as $action) {
        $sortOrder++;
        $permissions[] = [
          'permission' => "invoices.{$sub}.{$action}",
          'label'      => "{$subLabel} - " . $this->formatActionLabel($action),
          'module'     => 'invoices',
          'sub_module' => $sub,
          'action'     => $action,
          'sort_order' => $sortOrder,
          'is_active'  => 1,
        ];
      }
    }

    // ─────────────────────────────────────────────────────────
    // 2. CHALLANS (4 sub-modules × 7 actions = 28)
    // ─────────────────────────────────────────────────────────
    $challanSubModules = [
      'all'     => 'All Challans',
      'rhodium' => 'Rhodium Challan',
      'meena'   => 'Meena Challan',
      'wax'     => 'Wax Challan',
    ];
    $challanActions = ['list', 'view', 'create', 'edit', 'delete', 'print', 'status_change'];

    foreach ($challanSubModules as $sub => $subLabel) {
      foreach ($challanActions as $action) {
        $sortOrder++;
        $permissions[] = [
          'permission' => "challans.{$sub}.{$action}",
          'label'      => "{$subLabel} - " . $this->formatActionLabel($action),
          'module'     => 'challans',
          'sub_module' => $sub,
          'action'     => $action,
          'sort_order' => $sortOrder,
          'is_active'  => 1,
        ];
      }
    }

    // ─────────────────────────────────────────────────────────
    // 3. PAYMENTS (1 sub-module × 4 actions = 4)
    // ─────────────────────────────────────────────────────────
    foreach (['list', 'view', 'create', 'delete'] as $action) {
      $sortOrder++;
      $permissions[] = [
        'permission' => "payments.all.{$action}",
        'label'      => "Payments - " . $this->formatActionLabel($action),
        'module'     => 'payments',
        'sub_module' => 'all',
        'action'     => $action,
        'sort_order' => $sortOrder,
        'is_active'  => 1,
      ];
    }

    // ─────────────────────────────────────────────────────────
    // 4. MASTERS — Gold Rates (4 actions)
    // ─────────────────────────────────────────────────────────
    foreach (['list', 'view', 'create', 'edit'] as $action) {
      $sortOrder++;
      $permissions[] = [
        'permission' => "masters.gold_rates.{$action}",
        'label'      => "Gold Rates - " . $this->formatActionLabel($action),
        'module'     => 'masters',
        'sub_module' => 'gold_rates',
        'action'     => $action,
        'sort_order' => $sortOrder,
        'is_active'  => 1,
      ];
    }

    // ─────────────────────────────────────────────────────────
    // 5. MASTERS — Product Categories (5 actions)
    // ─────────────────────────────────────────────────────────
    foreach (['list', 'view', 'create', 'edit', 'delete'] as $action) {
      $sortOrder++;
      $permissions[] = [
        'permission' => "masters.product_categories.{$action}",
        'label'      => "Product Categories - " . $this->formatActionLabel($action),
        'module'     => 'masters',
        'sub_module' => 'product_categories',
        'action'     => $action,
        'sort_order' => $sortOrder,
        'is_active'  => 1,
      ];
    }

    // ─────────────────────────────────────────────────────────
    // 6. MASTERS — Products (5 actions)
    // ─────────────────────────────────────────────────────────
    foreach (['list', 'view', 'create', 'edit', 'delete'] as $action) {
      $sortOrder++;
      $permissions[] = [
        'permission' => "masters.products.{$action}",
        'label'      => "Products - " . $this->formatActionLabel($action),
        'module'     => 'masters',
        'sub_module' => 'products',
        'action'     => $action,
        'sort_order' => $sortOrder,
        'is_active'  => 1,
      ];
    }

    // ─────────────────────────────────────────────────────────
    // 7. MASTERS — Processes (5 actions)
    // ─────────────────────────────────────────────────────────
    foreach (['list', 'view', 'create', 'edit', 'delete'] as $action) {
      $sortOrder++;
      $permissions[] = [
        'permission' => "masters.processes.{$action}",
        'label'      => "Processes - " . $this->formatActionLabel($action),
        'module'     => 'masters',
        'sub_module' => 'processes',
        'action'     => $action,
        'sort_order' => $sortOrder,
        'is_active'  => 1,
      ];
    }

    // ─────────────────────────────────────────────────────────
    // 8. CUSTOMERS — Accounts (6 actions)
    // ─────────────────────────────────────────────────────────
    foreach (['list', 'view', 'create', 'edit', 'delete', 'view_ledger'] as $action) {
      $sortOrder++;
      $permissions[] = [
        'permission' => "customers.accounts.{$action}",
        'label'      => "Account Customers - " . $this->formatActionLabel($action),
        'module'     => 'customers',
        'sub_module' => 'accounts',
        'action'     => $action,
        'sort_order' => $sortOrder,
        'is_active'  => 1,
      ];
    }

    // ─────────────────────────────────────────────────────────
    // 9. CUSTOMERS — Cash Customers (5 actions)
    // ─────────────────────────────────────────────────────────
    foreach (['list', 'view', 'create', 'edit', 'delete'] as $action) {
      $sortOrder++;
      $permissions[] = [
        'permission' => "customers.cash_customers.{$action}",
        'label'      => "Cash Customers - " . $this->formatActionLabel($action),
        'module'     => 'customers',
        'sub_module' => 'cash_customers',
        'action'     => $action,
        'sort_order' => $sortOrder,
        'is_active'  => 1,
      ];
    }

    // ─────────────────────────────────────────────────────────
    // 10. DELIVERIES (2 sub-modules, 11 actions total)
    // ─────────────────────────────────────────────────────────
    foreach (['list', 'view', 'create', 'start', 'complete', 'fail'] as $action) {
      $sortOrder++;
      $permissions[] = [
        'permission' => "deliveries.all.{$action}",
        'label'      => "All Deliveries - " . $this->formatActionLabel($action),
        'module'     => 'deliveries',
        'sub_module' => 'all',
        'action'     => $action,
        'sort_order' => $sortOrder,
        'is_active'  => 1,
      ];
    }
    foreach (['list', 'view', 'start', 'complete', 'fail'] as $action) {
      $sortOrder++;
      $permissions[] = [
        'permission' => "deliveries.assigned.{$action}",
        'label'      => "Assigned Deliveries - " . $this->formatActionLabel($action),
        'module'     => 'deliveries',
        'sub_module' => 'assigned',
        'action'     => $action,
        'sort_order' => $sortOrder,
        'is_active'  => 1,
      ];
    }

    // ─────────────────────────────────────────────────────────
    // 11. REPORTS (4 sub-modules × 2 actions = 8)
    // ─────────────────────────────────────────────────────────
    $reportSubModules = [
      'outstanding' => 'Outstanding Report',
      'receivables' => 'Receivables Report',
      'aging'       => 'Aging Report',
      'monthly'     => 'Monthly Report',
      'daily'       => 'Daily Report',
    ];
    foreach ($reportSubModules as $sub => $subLabel) {
      foreach (['list', 'export'] as $action) {
        $sortOrder++;
        $permissions[] = [
          'permission' => "reports.{$sub}.{$action}",
          'label'      => "{$subLabel} - " . $this->formatActionLabel($action),
          'module'     => 'reports',
          'sub_module' => $sub,
          'action'     => $action,
          'sort_order' => $sortOrder,
          'is_active'  => 1,
        ];
      }
    }

    // ─────────────────────────────────────────────────────────
    // 12. LEDGERS (3 sub-modules, 8 actions total)
    // ─────────────────────────────────────────────────────────
    foreach (['list', 'view', 'export'] as $action) {
      $sortOrder++;
      $permissions[] = [
        'permission' => "ledgers.accounts.{$action}",
        'label'      => "Account Ledgers - " . $this->formatActionLabel($action),
        'module'     => 'ledgers',
        'sub_module' => 'accounts',
        'action'     => $action,
        'sort_order' => $sortOrder,
        'is_active'  => 1,
      ];
    }
    foreach (['list', 'view', 'export'] as $action) {
      $sortOrder++;
      $permissions[] = [
        'permission' => "ledgers.cash_customers.{$action}",
        'label'      => "Cash Customer Ledgers - " . $this->formatActionLabel($action),
        'module'     => 'ledgers',
        'sub_module' => 'cash_customers',
        'action'     => $action,
        'sort_order' => $sortOrder,
        'is_active'  => 1,
      ];
    }
    foreach (['list', 'send'] as $action) {
      $sortOrder++;
      $permissions[] = [
        'permission' => "ledgers.reminders.{$action}",
        'label'      => "Reminders - " . $this->formatActionLabel($action),
        'module'     => 'ledgers',
        'sub_module' => 'reminders',
        'action'     => $action,
        'sort_order' => $sortOrder,
        'is_active'  => 1,
      ];
    }

    // ─────────────────────────────────────────────────────────
    // 13. USERS (6 actions)
    // ─────────────────────────────────────────────────────────
    foreach (['list', 'view', 'create', 'edit', 'delete', 'change_password'] as $action) {
      $sortOrder++;
      $permissions[] = [
        'permission' => "users.all.{$action}",
        'label'      => "Users - " . $this->formatActionLabel($action),
        'module'     => 'users',
        'sub_module' => 'all',
        'action'     => $action,
        'sort_order' => $sortOrder,
        'is_active'  => 1,
      ];
    }

    // ─────────────────────────────────────────────────────────
    // 14. ROLES (6 actions)
    // ─────────────────────────────────────────────────────────
    foreach (['list', 'view', 'create', 'edit', 'delete', 'manage_permissions'] as $action) {
      $sortOrder++;
      $permissions[] = [
        'permission' => "roles.all.{$action}",
        'label'      => "Roles - " . $this->formatActionLabel($action),
        'module'     => 'roles',
        'sub_module' => 'all',
        'action'     => $action,
        'sort_order' => $sortOrder,
        'is_active'  => 1,
      ];
    }

    // ─────────────────────────────────────────────────────────
    // 15. AUDIT LOGS (2 actions)
    // ─────────────────────────────────────────────────────────
    foreach (['list', 'view'] as $action) {
      $sortOrder++;
      $permissions[] = [
        'permission' => "audit.logs.{$action}",
        'label'      => "Audit Logs - " . $this->formatActionLabel($action),
        'module'     => 'audit',
        'sub_module' => 'logs',
        'action'     => $action,
        'sort_order' => $sortOrder,
        'is_active'  => 1,
      ];
    }

    // ─────────────────────────────────────────────────────────
    // 16. SETTINGS (2 actions)
    // ─────────────────────────────────────────────────────────
    foreach (['view', 'edit'] as $action) {
      $sortOrder++;
      $permissions[] = [
        'permission' => "settings.company.{$action}",
        'label'      => "Company Settings - " . $this->formatActionLabel($action),
        'module'     => 'settings',
        'sub_module' => 'company',
        'action'     => $action,
        'sort_order' => $sortOrder,
        'is_active'  => 1,
      ];
    }

    // ─────────────────────────────────────────────────────────
    // INSERT ALL
    // ─────────────────────────────────────────────────────────
    $db->table('permissions')->insertBatch($permissions);

    echo "Inserted " . count($permissions) . " permissions successfully.\n";
  }

  /**
   * Format action string to human-readable label
   */
  private function formatActionLabel(string $action): string
  {
    return ucwords(str_replace('_', ' ', $action));
  }
}

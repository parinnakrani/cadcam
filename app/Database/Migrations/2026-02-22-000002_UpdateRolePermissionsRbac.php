<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateRolePermissionsRbac extends Migration
{
  public function up()
  {
    $db = \Config\Database::connect();

    // ─────────────────────────────────────────────────────────
    // STEP 1: Backup roles table
    // ─────────────────────────────────────────────────────────
    if (!$db->tableExists('roles_backup_rbac')) {
      $db->query('CREATE TABLE roles_backup_rbac AS SELECT * FROM roles');
    }

    // ─────────────────────────────────────────────────────────
    // STEP 2: Update existing roles (IDs 2–6) with new dot-notation permissions
    // Do NOT touch Role ID 1 (Super Administrator) — already has ["*"]
    // ─────────────────────────────────────────────────────────
    $db->transStart();

    // Role 2 — Company Administrator (full access to all modules)
    $db->table('roles')->where('id', 2)->update([
      'permissions' => json_encode([
        'invoices.*',
        'challans.*',
        'payments.*',
        'masters.*',
        'customers.*',
        'deliveries.*',
        'reports.*',
        'ledgers.*',
        'users.*',
        'roles.*',
        'audit.*',
        'settings.*',
      ]),
      'updated_at' => date('Y-m-d H:i:s'),
    ]);

    // Role 3 — Billing Manager (invoices, challans, basic reports/customers)
    $db->table('roles')->where('id', 3)->update([
      'permissions' => json_encode([
        'invoices.*',
        'challans.*',
        'payments.all.list',
        'payments.all.view',
        'payments.all.create',
        'reports.outstanding.list',
        'reports.receivables.list',
        'customers.accounts.list',
        'customers.accounts.view',
        'customers.cash_customers.list',
        'customers.cash_customers.view',
        'masters.products.list',
        'masters.products.view',
        'masters.processes.list',
        'masters.processes.view',
        'masters.gold_rates.list',
        'masters.gold_rates.view',
        'masters.product_categories.list',
        'masters.product_categories.view',
      ]),
      'updated_at' => date('Y-m-d H:i:s'),
    ]);

    // Role 4 — Accounts Manager (payments, reports, read-only invoices/challans, customers)
    $db->table('roles')->where('id', 4)->update([
      'permissions' => json_encode([
        'payments.*',
        'reports.*',
        'ledgers.*',
        'invoices.all.list',
        'invoices.all.view',
        'invoices.all.print',
        'invoices.account.list',
        'invoices.account.view',
        'invoices.account.print',
        'invoices.account.record_payment',
        'challans.all.list',
        'challans.all.view',
        'challans.all.print',
        'customers.*',
      ]),
      'updated_at' => date('Y-m-d H:i:s'),
    ]);

    // Role 5 — Delivery Personnel (view invoices + assigned deliveries)
    $db->table('roles')->where('id', 5)->update([
      'permissions' => json_encode([
        'invoices.all.list',
        'invoices.all.view',
        'invoices.all.print',
        'deliveries.assigned.list',
        'deliveries.assigned.view',
        'deliveries.assigned.start',
        'deliveries.assigned.complete',
        'deliveries.assigned.fail',
      ]),
      'updated_at' => date('Y-m-d H:i:s'),
    ]);

    // Role 6 — Report Viewer (read-only reports, invoices, challans, customers)
    $db->table('roles')->where('id', 6)->update([
      'permissions' => json_encode([
        'invoices.all.list',
        'invoices.all.view',
        'invoices.all.print',
        'challans.all.list',
        'challans.all.view',
        'challans.all.print',
        'reports.*',
        'ledgers.accounts.list',
        'ledgers.accounts.view',
        'ledgers.cash_customers.list',
        'ledgers.cash_customers.view',
        'customers.accounts.list',
        'customers.accounts.view',
        'customers.cash_customers.list',
        'customers.cash_customers.view',
      ]),
      'updated_at' => date('Y-m-d H:i:s'),
    ]);

    // ─────────────────────────────────────────────────────────
    // STEP 3: Insert 3 example roles for testing granular permissions
    // ─────────────────────────────────────────────────────────

    // Role A — Account Invoice Viewer
    $db->table('roles')->insert([
      'company_id'       => 1,
      'role_name'        => 'Account Invoice Viewer',
      'role_description' => 'Can see All Invoices and Account Invoice menu. List, View, Print actions only. No status change or payment recording.',
      'permissions'      => json_encode([
        'invoices.all.list',
        'invoices.all.view',
        'invoices.all.print',
        'invoices.account.list',
        'invoices.account.view',
        'invoices.account.print',
      ]),
      'is_system_role' => 0,
      'is_active'      => 1,
      'created_at'     => date('Y-m-d H:i:s'),
      'updated_at'     => date('Y-m-d H:i:s'),
    ]);

    // Role B — Cash Invoice Operator
    $db->table('roles')->insert([
      'company_id'       => 1,
      'role_name'        => 'Cash Invoice Operator',
      'role_description' => 'Can see All Invoices and Cash Invoice menu. Has List, View, Print, Status Change, and Record Payment on Cash Invoices. List/View/Print only on All Invoices page.',
      'permissions'      => json_encode([
        'invoices.all.list',
        'invoices.all.view',
        'invoices.all.print',
        'invoices.cash.list',
        'invoices.cash.view',
        'invoices.cash.print',
        'invoices.cash.status_change',
        'invoices.cash.record_payment',
      ]),
      'is_system_role' => 0,
      'is_active'      => 1,
      'created_at'     => date('Y-m-d H:i:s'),
      'updated_at'     => date('Y-m-d H:i:s'),
    ]);

    // Role C — Challan Viewer
    $db->table('roles')->insert([
      'company_id'       => 1,
      'role_name'        => 'Challan Viewer',
      'role_description' => 'Can see All Challans, Rhodium Challan, and Meena Challan menus. Has List/View/Print/Status Change on Rhodium and Meena. List/View/Print only on All Challans.',
      'permissions'      => json_encode([
        'challans.all.list',
        'challans.all.view',
        'challans.all.print',
        'challans.rhodium.list',
        'challans.rhodium.view',
        'challans.rhodium.print',
        'challans.rhodium.status_change',
        'challans.meena.list',
        'challans.meena.view',
        'challans.meena.print',
        'challans.meena.status_change',
      ]),
      'is_system_role' => 0,
      'is_active'      => 1,
      'created_at'     => date('Y-m-d H:i:s'),
      'updated_at'     => date('Y-m-d H:i:s'),
    ]);

    $db->transComplete();

    if ($db->transStatus() === false) {
      throw new \RuntimeException('Failed to update role permissions. Transaction rolled back.');
    }
  }

  public function down()
  {
    $db = \Config\Database::connect();

    // Restore from backup if it exists
    if ($db->tableExists('roles_backup_rbac')) {
      $db->query('TRUNCATE TABLE roles');
      $db->query('INSERT INTO roles SELECT * FROM roles_backup_rbac');
      $db->query('DROP TABLE roles_backup_rbac');
    }
  }
}

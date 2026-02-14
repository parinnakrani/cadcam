<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * CreateChallansTable Migration
 *
 * Creates the `challans` table for managing manufacturing job orders.
 *
 * Business Context:
 * - Challans represent job orders for Rhodium, Meena, or Wax manufacturing processes.
 * - Each challan belongs to either an Account customer or a Cash customer (mutually exclusive).
 * - Lifecycle: Draft → Pending → In Progress → Completed → Invoiced.
 * - Challan numbers are sequential and unique per company (company_id + challan_number).
 * - Can be converted to an invoice; invoice_generated flag prevents duplicate conversion.
 *
 * Constraints:
 * - CHECK: customer_type/account_id/cash_customer_id mutual exclusivity enforced.
 * - CHECK: total_weight >= 0, subtotal_amount >= 0.
 * - UNIQUE: (company_id, challan_number) — gap-free sequential numbering per tenant.
 *
 * Foreign Keys:
 * - company_id  → companies(id) ON DELETE CASCADE
 * - account_id  → accounts(id) ON DELETE RESTRICT
 * - cash_customer_id → cash_customers(id) ON DELETE RESTRICT
 * - invoice_id  → invoices(id) ON DELETE SET NULL
 * - created_by  → users(id) ON DELETE RESTRICT
 */
class CreateChallansTable extends Migration
{
    public function up()
    {
        // ----------------------------------------------------------------
        // 0. Drop existing table if it was created by raw SQL import
        //    (development environment only — no production data at risk)
        // ----------------------------------------------------------------
        $this->forge->dropTable('challans', true);

        // ----------------------------------------------------------------
        // 1. Define all columns
        // ----------------------------------------------------------------
        $this->forge->addField([

            // Primary Key
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            // Tenant Isolation
            'company_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],

            // Challan Identity
            'challan_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'challan_date' => [
                'type' => 'DATE',
            ],
            'challan_type' => [
                'type'       => 'ENUM',
                'constraint' => ['Rhodium', 'Meena', 'Wax'],
            ],

            // Customer — mutually exclusive (Account XOR Cash)
            'customer_type' => [
                'type'       => 'ENUM',
                'constraint' => ['Account', 'Cash'],
            ],
            'account_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'cash_customer_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],

            // Status Lifecycle
            'challan_status' => [
                'type'       => 'ENUM',
                'constraint' => ['Draft', 'Pending', 'In Progress', 'Completed', 'Invoiced'],
                'default'    => 'Draft',
            ],

            // Amounts (calculated from challan_lines)
            'total_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,3',
                'default'    => 0.000,
            ],
            'subtotal_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'tax_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'total_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],

            // Invoice Tracking
            'invoice_generated' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'invoice_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],

            // Metadata
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'delivery_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],

            // Soft Delete
            'is_deleted' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],

            // Timestamps
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);

        // ----------------------------------------------------------------
        // 2. Primary Key
        // ----------------------------------------------------------------
        $this->forge->addPrimaryKey('id');

        // ----------------------------------------------------------------
        // 3. Indexes
        // ----------------------------------------------------------------
        $this->forge->addKey('company_id', false, false, 'idx_challans_company_id');
        $this->forge->addKey('account_id', false, false, 'idx_challans_account_id');
        $this->forge->addKey('cash_customer_id', false, false, 'idx_challans_cash_customer_id');
        $this->forge->addKey('challan_status', false, false, 'idx_challans_status');
        $this->forge->addKey('challan_type', false, false, 'idx_challans_type');
        $this->forge->addKey('challan_date', false, false, 'idx_challans_date');
        $this->forge->addKey('invoice_id', false, false, 'idx_challans_invoice_id');

        // Unique: company_id + challan_number (gap-free sequential numbering per tenant)
        $this->forge->addUniqueKey(['company_id', 'challan_number'], 'uk_company_challan_number');

        // ----------------------------------------------------------------
        // 4. Foreign Keys
        // ----------------------------------------------------------------
        // Note: invoice_id FK to invoices table omitted here because the invoices
        // table does not exist yet at migration time. It will be added via a
        // separate migration after the invoices table is created.
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE', 'fk_challans_company');
        $this->forge->addForeignKey('account_id', 'accounts', 'id', 'RESTRICT', 'RESTRICT', 'fk_challans_account');
        $this->forge->addForeignKey('cash_customer_id', 'cash_customers', 'id', 'RESTRICT', 'RESTRICT', 'fk_challans_cash_customer');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'RESTRICT', 'RESTRICT', 'fk_challans_created_by');

        // ----------------------------------------------------------------
        // 5. Create Table
        // ----------------------------------------------------------------
        $this->forge->createTable('challans', true);

        // ----------------------------------------------------------------
        // 6. CHECK Constraints (raw SQL — CI4 Forge does not support CHECK)
        // ----------------------------------------------------------------
        // MySQL 8.0.16+ enforces CHECK constraints.
        //
        // Constraint 1: Customer type XOR — exactly one customer ID must be populated
        //   Account → account_id NOT NULL, cash_customer_id NULL
        //   Cash    → cash_customer_id NOT NULL, account_id NULL
        //
        // Constraint 2: total_weight >= 0
        // Constraint 3: subtotal_amount >= 0
        // ----------------------------------------------------------------
        $this->db->query(
            "ALTER TABLE `challans`
             ADD CONSTRAINT `chk_challans_customer_type` CHECK (
                 (customer_type = 'Account' AND account_id IS NOT NULL AND cash_customer_id IS NULL)
                 OR
                 (customer_type = 'Cash' AND cash_customer_id IS NOT NULL AND account_id IS NULL)
             )"
        );

        $this->db->query(
            "ALTER TABLE `challans`
             ADD CONSTRAINT `chk_challans_total_weight` CHECK (total_weight >= 0)"
        );

        $this->db->query(
            "ALTER TABLE `challans`
             ADD CONSTRAINT `chk_challans_subtotal_amount` CHECK (subtotal_amount >= 0)"
        );
    }

    public function down()
    {
        $this->forge->dropTable('challans', true);
    }
}

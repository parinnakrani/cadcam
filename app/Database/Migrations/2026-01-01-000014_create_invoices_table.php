<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInvoicesTable extends Migration
{
  public function up()
  {
    // Create invoices table
    $this->forge->addField([
      'id' => [
        'type'           => 'INT',
        'constraint'     => 10,
        'unsigned'       => true,
        'auto_increment' => true,
      ],
      'company_id' => [
        'type'       => 'INT',
        'constraint' => 10,
        'unsigned'   => true,
        'null'       => false,
      ],
      'invoice_number' => [
        'type'       => 'VARCHAR',
        'constraint' => 50,
        'null'       => false,
      ],
      'invoice_type' => [
        'type'       => 'ENUM',
        'constraint' => ['Accounts Invoice', 'Cash Invoice', 'Wax Invoice'],
        'null'       => false,
      ],
      'invoice_date' => [
        'type' => 'DATE',
        'null' => false,
      ],
      'due_date' => [
        'type' => 'DATE',
        'null' => true,
      ],

      // Customer references (either account OR cash customer)
      'account_id' => [
        'type'       => 'INT',
        'constraint' => 10,
        'unsigned'   => true,
        'null'       => true,
      ],
      'cash_customer_id' => [
        'type'       => 'INT',
        'constraint' => 10,
        'unsigned'   => true,
        'null'       => true,
      ],

      // Address fields
      'billing_address' => [
        'type' => 'TEXT',
        'null' => true,
      ],
      'shipping_address' => [
        'type' => 'TEXT',
        'null' => true,
      ],

      // Reference fields
      'reference_number' => [
        'type'       => 'VARCHAR',
        'constraint' => 100,
        'null'       => true,
      ],
      'challan_ids' => [
        'type' => 'JSON',
        'null' => true,
        'comment' => 'Array of linked challan IDs',
      ],

      // Financial amounts
      'subtotal' => [
        'type'       => 'DECIMAL',
        'constraint' => '15,2',
        'null'       => false,
        'default'    => 0.00,
      ],
      'tax_rate' => [
        'type'       => 'DECIMAL',
        'constraint' => '5,2',
        'null'       => false,
        'default'    => 3.00,
      ],
      'tax_amount' => [
        'type'       => 'DECIMAL',
        'constraint' => '15,2',
        'null'       => false,
        'default'    => 0.00,
      ],
      'cgst_amount' => [
        'type'       => 'DECIMAL',
        'constraint' => '15,2',
        'null'       => false,
        'default'    => 0.00,
      ],
      'sgst_amount' => [
        'type'       => 'DECIMAL',
        'constraint' => '15,2',
        'null'       => false,
        'default'    => 0.00,
      ],
      'igst_amount' => [
        'type'       => 'DECIMAL',
        'constraint' => '15,2',
        'null'       => false,
        'default'    => 0.00,
      ],
      'grand_total' => [
        'type'       => 'DECIMAL',
        'constraint' => '15,2',
        'null'       => false,
        'default'    => 0.00,
      ],
      'total_paid' => [
        'type'       => 'DECIMAL',
        'constraint' => '15,2',
        'null'       => false,
        'default'    => 0.00,
      ],
      'amount_due' => [
        'type'       => 'DECIMAL',
        'constraint' => '15,2',
        'null'       => false,
        'default'    => 0.00,
      ],

      // Status fields
      'invoice_status' => [
        'type'       => 'ENUM',
        'constraint' => ['Draft', 'Posted', 'Partially Paid', 'Paid', 'Delivered', 'Closed'],
        'null'       => false,
        'default'    => 'Draft',
      ],
      'payment_status' => [
        'type'       => 'ENUM',
        'constraint' => ['Pending', 'Partial Paid', 'Paid'],
        'null'       => false,
        'default'    => 'Pending',
      ],

      // Gold adjustment fields
      'gold_adjustment_applied' => [
        'type'    => 'TINYINT',
        'constraint' => 1,
        'null'    => false,
        'default' => 0,
      ],
      'gold_adjustment_date' => [
        'type' => 'TIMESTAMP',
        'null' => true,
      ],
      'gold_adjustment_amount' => [
        'type'       => 'DECIMAL',
        'constraint' => '15,2',
        'null'       => true,
      ],
      'gold_rate_used' => [
        'type'       => 'DECIMAL',
        'constraint' => '10,2',
        'null'       => true,
      ],

      // Additional fields
      'notes' => [
        'type' => 'TEXT',
        'null' => true,
      ],
      'terms_conditions' => [
        'type' => 'TEXT',
        'null' => true,
      ],

      // Audit fields
      'created_by' => [
        'type'       => 'INT',
        'constraint' => 10,
        'unsigned'   => true,
        'null'       => false,
      ],
      'updated_by' => [
        'type'       => 'INT',
        'constraint' => 10,
        'unsigned'   => true,
        'null'       => true,
      ],
      'created_at' => [
        'type' => 'TIMESTAMP',
        'null' => false,
      ],
      'updated_at' => [
        'type' => 'TIMESTAMP',
        'null' => false,
      ],
      'is_deleted' => [
        'type'    => 'TINYINT',
        'constraint' => 1,
        'null'    => false,
        'default' => 0,
      ],
    ]);

    // Set primary key
    $this->forge->addKey('id', true);

    // Add indexes for performance
    $this->forge->addKey('company_id');
    $this->forge->addKey('invoice_date');
    $this->forge->addKey('account_id');
    $this->forge->addKey('cash_customer_id');
    $this->forge->addKey('invoice_status');
    $this->forge->addKey('payment_status');
    $this->forge->addKey('amount_due');
    $this->forge->addKey('is_deleted');
    $this->forge->addKey('created_by');

    // Composite indexes for common queries
    $this->forge->addKey(['company_id', 'invoice_status']);
    $this->forge->addKey(['company_id', 'payment_status']);

    // Unique constraint on company_id + invoice_number
    $this->forge->addUniqueKey(['company_id', 'invoice_number'], 'uk_company_invoice_number');

    // Create table
    $this->forge->createTable('invoices', true);

    // Add foreign key constraints
    $this->db->query('
            ALTER TABLE `invoices`
            ADD CONSTRAINT `fk_invoices_company` 
                FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) 
                ON DELETE RESTRICT ON UPDATE CASCADE
        ');

    $this->db->query('
            ALTER TABLE `invoices`
            ADD CONSTRAINT `fk_invoices_account` 
                FOREIGN KEY (`account_id`) REFERENCES `accounts`(`id`) 
                ON DELETE RESTRICT ON UPDATE CASCADE
        ');

    $this->db->query('
            ALTER TABLE `invoices`
            ADD CONSTRAINT `fk_invoices_cash_customer` 
                FOREIGN KEY (`cash_customer_id`) REFERENCES `cash_customers`(`id`) 
                ON DELETE RESTRICT ON UPDATE CASCADE
        ');

    $this->db->query('
            ALTER TABLE `invoices`
            ADD CONSTRAINT `fk_invoices_created_by` 
                FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) 
                ON DELETE RESTRICT ON UPDATE CASCADE
        ');

    // Add check constraints for business rules
    // Note: MySQL 8.0.16+ supports CHECK constraints

    // Ensure either account_id OR cash_customer_id is set, not both
    $this->db->query('
            ALTER TABLE `invoices`
            ADD CONSTRAINT `chk_customer_type` 
            CHECK (
                (account_id IS NOT NULL AND cash_customer_id IS NULL) 
                OR 
                (account_id IS NULL AND cash_customer_id IS NOT NULL)
            )
        ');

    // Ensure amounts are non-negative
    $this->db->query('
            ALTER TABLE `invoices`
            ADD CONSTRAINT `chk_amounts_non_negative` 
            CHECK (
                subtotal >= 0 
                AND tax_amount >= 0 
                AND grand_total >= 0 
                AND total_paid >= 0 
                AND amount_due >= 0
            )
        ');

    // Ensure tax amounts are consistent (either CGST+SGST OR IGST, not both)
    $this->db->query('
            ALTER TABLE `invoices`
            ADD CONSTRAINT `chk_tax_type_consistency` 
            CHECK (
                (cgst_amount > 0 AND sgst_amount > 0 AND igst_amount = 0) 
                OR 
                (igst_amount > 0 AND cgst_amount = 0 AND sgst_amount = 0)
                OR
                (cgst_amount = 0 AND sgst_amount = 0 AND igst_amount = 0)
            )
        ');

    // Ensure amount_due = grand_total - total_paid (logical constraint)
    // Note: This is enforced at application level, not database level
    // because it would prevent updates during payment processing
  }

  public function down()
  {
    // Drop foreign key constraints first
    $this->db->query('ALTER TABLE `invoices` DROP FOREIGN KEY `fk_invoices_company`');
    $this->db->query('ALTER TABLE `invoices` DROP FOREIGN KEY `fk_invoices_account`');
    $this->db->query('ALTER TABLE `invoices` DROP FOREIGN KEY `fk_invoices_cash_customer`');
    $this->db->query('ALTER TABLE `invoices` DROP FOREIGN KEY `fk_invoices_created_by`');

    // Drop check constraints
    if ($this->db->DBDriver === 'MySQLi') {
      // Check if constraints exist before dropping (MySQL 8.0.16+)
      $this->db->query('ALTER TABLE `invoices` DROP CHECK `chk_customer_type`');
      $this->db->query('ALTER TABLE `invoices` DROP CHECK `chk_amounts_non_negative`');
      $this->db->query('ALTER TABLE `invoices` DROP CHECK `chk_tax_type_consistency`');
    }

    // Drop the table
    $this->forge->dropTable('invoices', true);
  }
}

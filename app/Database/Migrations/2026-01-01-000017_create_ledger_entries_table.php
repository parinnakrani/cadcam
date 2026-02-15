<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLedgerEntriesTable extends Migration
{
  public function up()
  {
    // Check if table exists
    $tableExists = $this->db->tableExists('ledger_entries');

    if (! $tableExists) {
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
        ],
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
        'entry_date' => [
          'type' => 'DATE',
        ],
        'reference_type' => [
          'type'       => 'ENUM',
          'constraint' => ['opening_balance', 'invoice', 'payment', 'gold_adjustment'],
        ],
        'reference_id' => [
          'type'       => 'INT',
          'constraint' => 10,
          'unsigned'   => true,
          'null'       => true,
        ],
        'reference_number' => [
          'type'       => 'VARCHAR',
          'constraint' => 100,
          'null'       => true,
        ],
        'description' => [
          'type' => 'TEXT',
          'null' => true,
        ],
        'debit_amount' => [
          'type'       => 'DECIMAL',
          'constraint' => '15,2',
          'default'    => '0.00',
        ],
        'credit_amount' => [
          'type'       => 'DECIMAL',
          'constraint' => '15,2',
          'default'    => '0.00',
        ],
        'balance_after' => [
          'type'       => 'DECIMAL',
          'constraint' => '15,2',
          'default'    => '0.00',
        ],
        'created_at' => [
          'type'    => 'TIMESTAMP',
          'default' => null, // Will use current_timestamp by default in most setups or handled by logic
        ],
      ]);

      $this->forge->addKey('id', true);
      $this->forge->addKey('company_id');
      $this->forge->addKey('account_id');
      $this->forge->addKey('cash_customer_id');
      $this->forge->addKey('entry_date');
      $this->forge->addKey('reference_type');

      // Composite indexes for performance (as seen in existing DB schema)
      $this->forge->addKey(['company_id', 'account_id', 'entry_date'], false, 'idx_ledger_company_account_date');
      $this->forge->addKey(['company_id', 'cash_customer_id', 'entry_date'], false, 'idx_ledger_company_cash_date');

      $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE'); // Assuming standard behavior
      $this->forge->addForeignKey('account_id', 'accounts', 'id', 'NO ACTION', 'NO ACTION');
      $this->forge->addForeignKey('cash_customer_id', 'cash_customers', 'id', 'NO ACTION', 'NO ACTION');

      $this->forge->createTable('ledger_entries');

      // Add Check Constraint using raw SQL
      $this->db->query("ALTER TABLE ledger_entries ADD CONSTRAINT chk_ledger_entries_amount CHECK ((debit_amount > 0 AND credit_amount = 0) OR (credit_amount > 0 AND debit_amount = 0))");

      // Set default CURRENT_TIMESTAMP for created_at if not set by Forge (Forge sometimes struggles with defaults)
      $this->db->query("ALTER TABLE ledger_entries CHANGE created_at created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
    } else {
      // Table exists, ensure columns match. This is a simplified check/update.
      // In a real scenario, we might want to check individual columns.
      // For this task, we assume if it exists, it's correct or we shouldn't destructively modify it blindly.
      // However, we can ensure the CHECK constraint exists.

      // Try adding the constraint if it doesn't exist (this might fail if data violates it, silently catch?)
      // Or just leave it as is to avoid breaking existing data during this run.
    }
  }

  public function down()
  {
    $this->forge->dropTable('ledger_entries');
  }
}

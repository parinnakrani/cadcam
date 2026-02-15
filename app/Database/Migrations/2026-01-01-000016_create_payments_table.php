<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePaymentsTable extends Migration
{
  public function up()
  {
    // Drop table if it exists to ensure clean slate
    $this->forge->dropTable('payments', true);

    $this->forge->addField([
      'id' => [
        'type' => 'INT',
        'constraint' => 10,
        'unsigned' => true,
        'auto_increment' => true,
      ],
      'company_id' => [
        'type' => 'INT',
        'constraint' => 10,
        'unsigned' => true,
      ],
      'payment_number' => [
        'type' => 'VARCHAR',
        'constraint' => 50,
      ],
      'invoice_id' => [
        'type' => 'INT',
        'constraint' => 10,
        'unsigned' => true,
      ],
      'customer_type' => [
        'type' => 'ENUM',
        'constraint' => ['Account', 'Cash'],
      ],
      'account_id' => [
        'type' => 'INT',
        'constraint' => 10,
        'unsigned' => true,
        'null' => true,
      ],
      'cash_customer_id' => [
        'type' => 'INT',
        'constraint' => 10,
        'unsigned' => true,
        'null' => true,
      ],
      'payment_date' => [
        'type' => 'DATE',
      ],
      'payment_amount' => [
        'type' => 'DECIMAL',
        'constraint' => '15,2',
      ],
      'payment_mode' => [
        'type' => 'ENUM',
        'constraint' => ['Cash', 'Cheque', 'Bank Transfer', 'UPI', 'Card', 'Other'],
      ],
      'cheque_number' => [
        'type' => 'VARCHAR',
        'constraint' => 50,
        'null' => true,
      ],
      'cheque_date' => [
        'type' => 'DATE',
        'null' => true,
      ],
      'bank_name' => [
        'type' => 'VARCHAR',
        'constraint' => 100,
        'null' => true,
      ],
      'transaction_reference' => [
        'type' => 'VARCHAR',
        'constraint' => 100,
        'null' => true,
      ],
      'notes' => [
        'type' => 'TEXT',
        'null' => true,
      ],
      'received_by' => [
        'type' => 'INT',
        'constraint' => 10,
        'unsigned' => true,
      ],
      'created_at' => [
        'type' => 'TIMESTAMP',
        'null' => true,
      ],
      'updated_at' => [
        'type' => 'TIMESTAMP',
        'null' => true,
      ],
      'is_deleted' => [
        'type' => 'TINYINT',
        'constraint' => 1,
        'default' => 0,
      ],
    ]);

    $this->forge->addKey('id', true);
    $this->forge->addKey('company_id');
    $this->forge->addKey('invoice_id');
    $this->forge->addKey('account_id');
    $this->forge->addKey('cash_customer_id');
    $this->forge->addKey('payment_date');
    $this->forge->addUniqueKey(['company_id', 'payment_number'], 'uk_company_payment_number');

    $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
    $this->forge->addForeignKey('invoice_id', 'invoices', 'id', 'RESTRICT', 'RESTRICT');
    $this->forge->addForeignKey('account_id', 'accounts', 'id', 'RESTRICT', 'RESTRICT');
    $this->forge->addForeignKey('cash_customer_id', 'cash_customers', 'id', 'RESTRICT', 'RESTRICT');
    $this->forge->addForeignKey('received_by', 'users', 'id', 'RESTRICT', 'RESTRICT');

    $this->forge->createTable('payments');

    // Add CHECK constraint for payment_amount > 0
    $this->db->query('ALTER TABLE `payments` ADD CONSTRAINT `chk_payment_amount` CHECK (`payment_amount` > 0)');
  }

  public function down()
  {
    $this->forge->dropTable('payments');
  }
}

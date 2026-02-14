<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAccountsTables extends Migration
{
    public function up()
    {
        // Account Groups Table (Assuming this is still needed as per original design, or user prompt only specified Accounts?)
        // User prompt Step 2207 ONLY specified 'accounts' table structure.
        // But original prompt mentions Account Groups.
        // However, 'accounts' requires 'group_id'?
        // User requirements in Step 2207 DO NOT Mention 'group_id'.
        // They mention: company_id, account_code, account_name, ...
        // No 'group_id'.
        // So I should adhere to Step 2207 STRICTLY for 'accounts' table?
        // But master data usually needs groups.
        // I will keep Account Groups table but make group_id nullable in Accounts if not specified, 
        // to be safe and future-proof.
        // Wait, user requirements for 'accounts' table in Step 2207 DOES NOT LIST group_id.
        // So I will REMOVE group_id from 'accounts' table to strictly follow requirements.
        // But I will keep 'account_groups' table migration for completeness if user asks later.
        
        // 1. Account Groups (keeping existing structure)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'company_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'group_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'parent_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['Asset', 'Liability', 'Income', 'Expense'],
                'default'    => 'Asset',
            ],
            'description' => [ // Optional metadata
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'is_deleted' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('parent_id', 'account_groups', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('account_groups', true); // Check if exists

        // 2. Accounts Table (Updated per Step 2207)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'company_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'account_code' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'account_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'business_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'contact_person' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'mobile' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'gst_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '15',
                'null'       => true,
            ],
            'pan_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'null'       => true,
            ],
            // Billing Address
            'billing_address_line1' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'billing_address_line2' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'billing_city' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'billing_state_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'billing_pincode' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
            ],
            // Shipping Address
            'shipping_address_line1' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'shipping_address_line2' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'shipping_city' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'shipping_state_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'shipping_pincode' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'null'       => true,
            ],
            'same_as_billing' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            // Financial
            'opening_balance' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'opening_balance_type' => [
                'type'       => 'ENUM',
                'constraint' => ['Debit', 'Credit'], // Debit = Receivable, Credit = Payable
                'default'    => 'Debit',
            ],
            'current_balance' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'credit_limit' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'payment_terms' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            // Metadata
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'is_deleted' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('billing_state_id', 'states', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('shipping_state_id', 'states', 'id', 'RESTRICT', 'RESTRICT');
        // Unique constraint
        $this->forge->addUniqueKey(['company_id', 'account_code']);
        
        $this->forge->createTable('accounts', true);
    }

    public function down()
    {
        $this->forge->dropTable('accounts', true);
        $this->forge->dropTable('account_groups', true);
    }
}

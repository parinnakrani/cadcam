<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixAccountsTableRest extends Migration
{
    public function up()
    {
        $fields = [
            'mobile' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'after'      => 'contact_person'
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
                'after'      => 'mobile'
            ],
            'gst_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '15',
                'null'       => true,
                'after'      => 'email'
            ],
            'pan_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'null'       => true,
                'after'      => 'gst_number'
            ],
            'billing_address_line1' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'after'      => 'pan_number'
            ],
            'billing_address_line2' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'after'      => 'billing_address_line1'
            ],
            'billing_city' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'after'      => 'billing_address_line2'
            ],
            'billing_state_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'after'      => 'billing_city'
            ],
            'billing_pincode' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'after'      => 'billing_state_id'
            ],
            'shipping_address_line1' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'after'      => 'billing_pincode'
            ],
            'shipping_address_line2' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'after'      => 'shipping_address_line1'
            ],
            'shipping_city' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
                'after'      => 'shipping_address_line2'
            ],
            'shipping_state_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'shipping_city'
            ],
            'shipping_pincode' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'null'       => true,
                'after'      => 'shipping_state_id'
            ],
            'same_as_billing' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'after'      => 'shipping_pincode'
            ],
            'opening_balance' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
                'after'      => 'same_as_billing'
            ],
            'opening_balance_type' => [
                'type'       => 'ENUM',
                'constraint' => ['Debit', 'Credit'],
                'default'    => 'Debit',
                'after'      => 'opening_balance'
            ],
            'current_balance' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
                'after'      => 'opening_balance_type'
            ],
            'credit_limit' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
                'after'      => 'current_balance'
            ],
            'payment_terms' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'after'      => 'credit_limit'
            ],
            'notes' => [
                'type'   => 'TEXT',
                'null'   => true,
                'after'      => 'payment_terms'
            ]
        ];

        foreach ($fields as $fieldName => $fieldData) {
            if (!$this->db->fieldExists($fieldName, 'accounts')) {
                $this->forge->addColumn('accounts', [$fieldName => $fieldData]);
            }
        }
    }

    public function down()
    {
        // No-op
    }
}

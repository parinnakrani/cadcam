<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixCashCustomersTableAddressLine2 extends Migration
{
    public function up()
    {
        $fields = $this->db->getFieldData('cash_customers');
        $existingFields = [];
        foreach ($fields as $field) {
            $existingFields[] = $field->name;
        }

        $fieldsToAdd = [];

        if (!in_array('address_line2', $existingFields)) {
            $fieldsToAdd['address_line2'] = [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ];
        }

        if (!empty($fieldsToAdd)) {
            $this->forge->addColumn('cash_customers', $fieldsToAdd);
        }
    }

    public function down()
    {
        // No revert
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixCashCustomersTable extends Migration
{
    public function up()
    {
        // Check if columns exist, if not add them
        $fields = $this->db->getFieldData('cash_customers');
        $existingFields = [];
        foreach ($fields as $field) {
            $existingFields[] = $field->name;
        }

        $fieldsToAdd = [];

        if (!in_array('is_active', $existingFields)) {
            $fieldsToAdd['is_active'] = [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ];
        }

        if (!in_array('is_deleted', $existingFields)) {
            $fieldsToAdd['is_deleted'] = [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ];
        }

        if (!in_array('mobile', $existingFields)) {
            $fieldsToAdd['mobile'] = [
                'type'       => 'VARCHAR',
                'constraint' => '20',
            ];
        }

        if (!in_array('email', $existingFields)) {
            $fieldsToAdd['email'] = [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ];
        }
        
        if (!in_array('gst_number', $existingFields)) { // Accounts has it, Cash might not?
            // Cash customers usually don't have GST, but prompt Step 2207 was vague.
            // CreateCashCustomersTable.php did not include it.
            // But if user asks later... I'll stick to provided schema.
        }

        if (!empty($fieldsToAdd)) {
            $this->forge->addColumn('cash_customers', $fieldsToAdd);
        }
    }

    public function down()
    {
        // No revert logic for safety
    }
}

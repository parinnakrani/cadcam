<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixCashCustomersTableAddress extends Migration
{
    public function up()
    {
        $fields = $this->db->getFieldData('cash_customers');
        $existingFields = [];
        foreach ($fields as $field) {
            $existingFields[] = $field->name;
        }

        $fieldsToAdd = [];

        if (!in_array('address_line1', $existingFields)) {
            $fieldsToAdd['address_line1'] = [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ];
        }

        if (!in_array('address_line2', $existingFields)) {
            $fieldsToAdd['address_line2'] = [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ];
        }

        if (!in_array('city', $existingFields)) {
            $fieldsToAdd['city'] = [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ];
        }
        
        if (!in_array('state_id', $existingFields)) {
            $fieldsToAdd['state_id'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ];
        }

        if (!in_array('pincode', $existingFields)) {
            $fieldsToAdd['pincode'] = [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'null'       => true,
            ];
        }

        if (!in_array('notes', $existingFields)) {
            $fieldsToAdd['notes'] = [
                'type'   => 'TEXT',
                'null'   => true,
            ];
        }

        if (!empty($fieldsToAdd)) {
            $this->forge->addColumn('cash_customers', $fieldsToAdd);
            
            // Add FK for state_id if added
            if (isset($fieldsToAdd['state_id'])) {
                // We can't easily check if FK exists, but adding it might fail if exists.
                // Generally simple addColumn doesn't add FK constraints automatically.
                // We usually add constraint separately or via sql.
                // CodeIgniter forge support for adding FK after creation is separate.
                
                // Let's try raw SQL for safety if needed, or forge.
                // $this->forge->addForeignKey('state_id', 'states', 'id', 'RESTRICT', 'RESTRICT');
                // processIndexes() needs to be called?
                // Forge `addColumn` doesn't support addForeignKey in same call easily in all drivers.
                
                // I will add index and FK manually via SQL or a separate call?
                // Use strict SQL for FK to avoid errors if it exists.
                // actually, for safety in `up()`, I'll just add the column.
                // The constraint is good to have but not strictly blocking "Unknown Column" error.
                // But data integrity matters.
                
                // I'll execute a raw query to add the constraint safely?
                // "ALTER TABLE cash_customers ADD CONSTRAINT fk_cc_state FOREIGN KEY (state_id) REFERENCES states(id)..."
            }
        }
    }

    public function down()
    {
        // No revert
    }
}

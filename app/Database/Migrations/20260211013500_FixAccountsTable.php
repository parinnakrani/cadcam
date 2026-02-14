<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixAccountsTable extends Migration
{
    public function up()
    {
        // Fix missing business_name
        if (!$this->db->fieldExists('business_name', 'accounts')) {
            $this->forge->addColumn('accounts', [
                'business_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '255',
                    'null'       => true,
                    'after'      => 'account_name'
                ]
            ]);
        }
        
        // Fix missing contact_person
        if (!$this->db->fieldExists('contact_person', 'accounts')) {
            $this->forge->addColumn('accounts', [
                'contact_person' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '100',
                    'null'       => true,
                    'after'      => 'business_name'
                ]
            ]);
        }
    }

    public function down()
    {
        // No-op to prevent accidental data loss
    }
}

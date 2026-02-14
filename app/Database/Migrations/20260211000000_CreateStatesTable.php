<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStatesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'state_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'state_code' => [ // GST State Code (e.g. 24 for Gujarat)
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'null'       => true,
            ],
            'country_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'default'    => 'India',
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('states', true);

        // Seed some data? 
        // Migrations usually for schema. Seeder for data.
        // But for completeness, maybe insert?
        // No, stay pure migration.
    }

    public function down()
    {
        $this->forge->dropTable('states', true);
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProcessesTable extends Migration
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
            'company_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'process_code' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'process_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'process_type' => [
                'type'       => 'ENUM',
                'constraint' => ['Rhodium', 'Meena', 'Wax', 'Polish', 'Coating', 'Other'],
                'default'    => 'Other',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'rate_per_unit' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'unit_of_measure' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => 'PCS',
            ],
            'is_active' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
            'is_deleted' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);

        // Primary Key
        $this->forge->addPrimaryKey('id');

        // Indexes
        $this->forge->addKey('company_id');
        $this->forge->addKey('process_name');
        $this->forge->addKey('process_type');

        // Unique Key: company_id + process_code
        $this->forge->addUniqueKey(['company_id', 'process_code']);

        // Foreign Key
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');

        // Create Table
        $this->forge->createTable('processes', true);
    }

    public function down()
    {
        $this->forge->dropTable('processes', true);
    }
}

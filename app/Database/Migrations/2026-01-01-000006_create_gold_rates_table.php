<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGoldRatesTable extends Migration
{
    public function up()
    {
        // query calls removed to fix IDE error

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
            'rate_date' => [
                'type' => 'DATE',
            ],
            'metal_type' => [
                'type'       => 'ENUM',
                'constraint' => ['22K'],
            ],
            'rate_per_gram' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'updated_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
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
        $this->forge->addKey('rate_date');

        // Unique Key: One rate per metal per day per company
        $this->forge->addUniqueKey(['company_id', 'rate_date', 'metal_type']);

        // Foreign Key
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');

        // Create Table
        $this->forge->createTable('gold_rates', true); // IF NOT EXISTS


    }

    public function down()
    {


        $this->forge->dropTable('gold_rates', true);


    }
}

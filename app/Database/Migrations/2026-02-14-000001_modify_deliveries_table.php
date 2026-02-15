<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModifyDeliveriesTable extends Migration
{
  public function up()
  {
    // Check if table exists
    if (!$this->db->tableExists('deliveries')) {
      // If table doesn't exist, we can't modify it. 
      // In a real scenario, we might create it, but instructions say it exists.
      // We will log explicitly or arguably create it if missing, but let's stick to modifying.
      return;
    }

    $fields = [];

    // Add delivery_contact_name if it doesn't exist
    if (!$this->db->fieldExists('delivery_contact_name', 'deliveries')) {
      $fields['delivery_contact_name'] = [
        'type'       => 'VARCHAR',
        'constraint' => 100,
        'null'       => true,
        'after'      => 'delivery_address'
      ];
    }

    // Add failed_reason if it doesn't exist
    if (!$this->db->fieldExists('failed_reason', 'deliveries')) {
      $fields['failed_reason'] = [
        'type'       => 'TEXT',
        'null'       => true,
        'after'      => 'delivery_notes'
      ];
    }

    if (!empty($fields)) {
      $this->forge->addColumn('deliveries', $fields);
    }

    // Ensure ENUM contains correct values
    // Note: CI4 db forge doesn't easily modify ENUM columns in a cross-platform way without raw SQL
    // But since we checked the dump and it's correct, we might skip this or do a raw query for safety.
    // The dump says: enum('Assigned','In Transit','Delivered','Failed')
    // We will assume it is correct as per instructions to just ADD columns.
  }

  public function down()
  {
    $fieldsToRemove = [];

    if ($this->db->fieldExists('delivery_contact_name', 'deliveries')) {
      $fieldsToRemove[] = 'delivery_contact_name';
    }

    if ($this->db->fieldExists('failed_reason', 'deliveries')) {
      $fieldsToRemove[] = 'failed_reason';
    }

    if (!empty($fieldsToRemove)) {
      $this->forge->dropColumn('deliveries', $fieldsToRemove);
    }
  }
}

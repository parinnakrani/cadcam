<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddImagePathToInvoiceLines extends Migration
{
  public function up()
  {
    // Check if column already exists
    $fields = $this->db->getFieldNames('invoice_lines');
    if (in_array('image_path', $fields)) {
      return; // Already exists, skip
    }

    $this->forge->addColumn('invoice_lines', [
      'image_path' => [
        'type'       => 'VARCHAR',
        'constraint' => 500,
        'null'       => true,
        'default'    => null,
        'comment'    => 'Path to uploaded line image',
        'after'      => 'line_notes',
      ],
    ]);
  }

  public function down()
  {
    $fields = $this->db->getFieldNames('invoice_lines');
    if (in_array('image_path', $fields)) {
      $this->forge->dropColumn('invoice_lines', 'image_path');
    }
  }
}

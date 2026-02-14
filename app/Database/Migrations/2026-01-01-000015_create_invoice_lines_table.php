<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInvoiceLinesTable extends Migration
{
  public function up()
  {
    // Create invoice_lines table
    $this->forge->addField([
      'id' => [
        'type'           => 'INT',
        'constraint'     => 10,
        'unsigned'       => true,
        'auto_increment' => true,
      ],
      'invoice_id' => [
        'type'       => 'INT',
        'constraint' => 10,
        'unsigned'   => true,
        'null'       => false,
      ],
      'line_number' => [
        'type'       => 'INT',
        'constraint' => 11,
        'null'       => false,
        'comment'    => 'Sequential line number within invoice',
      ],

      // Reference to challan line (optional - for invoices generated from challans)
      'challan_line_id' => [
        'type'       => 'INT',
        'constraint' => 10,
        'unsigned'   => true,
        'null'       => true,
        'comment'    => 'Reference to source challan line if invoice from challan',
      ],
      'source_challan_id' => [
        'type'       => 'INT',
        'constraint' => 10,
        'unsigned'   => true,
        'null'       => true,
        'comment'    => 'Reference to source challan',
      ],
      'source_challan_line_id' => [
        'type'       => 'INT',
        'constraint' => 10,
        'unsigned'   => true,
        'null'       => true,
        'comment'    => 'Reference to source challan line',
      ],

      // Products and Processes (JSON arrays)
      'products_json' => [
        'type'    => 'JSON',
        'null'    => true,
        'comment' => 'Array of product IDs and details',
      ],
      'product_ids' => [
        'type'    => 'JSON',
        'null'    => true,
        'comment' => 'Array of product IDs (legacy compatibility)',
      ],
      'product_name' => [
        'type'       => 'VARCHAR',
        'constraint' => 255,
        'null'       => true,
        'comment'    => 'Comma-separated product names for display',
      ],
      'processes_json' => [
        'type'    => 'JSON',
        'null'    => true,
        'comment' => 'Array of process IDs and details',
      ],
      'process_ids' => [
        'type'    => 'JSON',
        'null'    => true,
        'comment' => 'Array of process IDs (legacy compatibility)',
      ],
      'process_prices' => [
        'type'    => 'JSON',
        'null'    => true,
        'comment' => 'Snapshot of process prices at invoice creation time',
      ],

      // Quantities and measurements
      'quantity' => [
        'type'       => 'INT',
        'constraint' => 11,
        'null'       => false,
        'default'    => 1,
        'comment'    => 'Number of pieces/items',
      ],
      'weight' => [
        'type'       => 'DECIMAL',
        'constraint' => '10,3',
        'null'       => false,
        'default'    => 0.000,
        'comment'    => 'Weight in grams',
      ],

      // Gold-related fields
      'gold_weight_grams' => [
        'type'       => 'DECIMAL',
        'constraint' => '10,3',
        'null'       => false,
        'default'    => 0.000,
        'comment'    => 'Gold weight in grams',
      ],
      'gold_weight' => [
        'type'       => 'DECIMAL',
        'constraint' => '10,3',
        'null'       => true,
        'comment'    => 'Gold weight (legacy compatibility)',
      ],
      'gold_fine_weight' => [
        'type'       => 'DECIMAL',
        'constraint' => '10,3',
        'null'       => true,
        'comment'    => 'Fine gold weight after purity calculation',
      ],
      'gold_purity' => [
        'type'       => 'VARCHAR',
        'constraint' => 20,
        'null'       => true,
        'default'    => '22K',
        'comment'    => 'Gold purity (e.g., 22K, 24K)',
      ],

      // Gold adjustment fields (for payment-time adjustments)
      'original_gold_weight' => [
        'type'       => 'DECIMAL',
        'constraint' => '10,3',
        'null'       => false,
        'default'    => 0.000,
        'comment'    => 'Original gold weight before adjustment',
      ],
      'adjusted_gold_weight' => [
        'type'       => 'DECIMAL',
        'constraint' => '10,3',
        'null'       => false,
        'default'    => 0.000,
        'comment'    => 'Adjusted gold weight after payment-time adjustment',
      ],
      'gold_adjustment_amount' => [
        'type'       => 'DECIMAL',
        'constraint' => '15,2',
        'null'       => false,
        'default'    => 0.00,
        'comment'    => 'Amount added/subtracted due to gold weight adjustment',
      ],

      // Pricing fields
      'unit_price' => [
        'type'       => 'DECIMAL',
        'constraint' => '15,2',
        'null'       => false,
        'default'    => 0.00,
        'comment'    => 'Price per unit/piece',
      ],
      'rate' => [
        'type'       => 'DECIMAL',
        'constraint' => '10,2',
        'null'       => false,
        'default'    => 0.00,
        'comment'    => 'Rate per gram or per piece (legacy compatibility)',
      ],
      'line_subtotal' => [
        'type'       => 'DECIMAL',
        'constraint' => '15,2',
        'null'       => false,
        'default'    => 0.00,
        'comment'    => 'Line subtotal before tax',
      ],
      'line_tax_amount' => [
        'type'       => 'DECIMAL',
        'constraint' => '15,2',
        'null'       => false,
        'default'    => 0.00,
        'comment'    => 'Tax amount for this line',
      ],
      'line_total' => [
        'type'       => 'DECIMAL',
        'constraint' => '15,2',
        'null'       => false,
        'default'    => 0.00,
        'comment'    => 'Line total including tax',
      ],
      'amount' => [
        'type'       => 'DECIMAL',
        'constraint' => '15,2',
        'null'       => false,
        'default'    => 0.00,
        'comment'    => 'Line amount (legacy compatibility)',
      ],

      // Description and classification
      'description' => [
        'type' => 'TEXT',
        'null' => true,
        'comment' => 'Line item description',
      ],
      'line_notes' => [
        'type' => 'TEXT',
        'null' => true,
        'comment' => 'Additional notes for this line',
      ],
      'hsn_code' => [
        'type'       => 'VARCHAR',
        'constraint' => 20,
        'null'       => true,
        'comment'    => 'HSN/SAC code for GST compliance',
      ],

      // Metadata
      'is_deleted' => [
        'type'       => 'TINYINT',
        'constraint' => 1,
        'null'       => false,
        'default'    => 0,
        'comment'    => 'Soft delete flag',
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

    // Set primary key
    $this->forge->addKey('id', true);

    // Add indexes for performance
    $this->forge->addKey('invoice_id');
    $this->forge->addKey('challan_line_id');
    $this->forge->addKey('source_challan_id');
    $this->forge->addKey('line_number');
    $this->forge->addKey('is_deleted');

    // Create table
    $this->forge->createTable('invoice_lines', true);

    // Add foreign key constraints
    $this->db->query('
            ALTER TABLE `invoice_lines`
            ADD CONSTRAINT `fk_invoice_lines_invoice` 
                FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) 
                ON DELETE CASCADE ON UPDATE CASCADE
        ');

    $this->db->query('
            ALTER TABLE `invoice_lines`
            ADD CONSTRAINT `fk_invoice_lines_challan` 
                FOREIGN KEY (`source_challan_id`) REFERENCES `challans`(`id`) 
                ON DELETE RESTRICT ON UPDATE CASCADE
        ');

    // Note: challan_line_id references challan_lines table
    // Uncomment if challan_lines table has proper foreign key support
    // $this->db->query('
    //     ALTER TABLE `invoice_lines`
    //     ADD CONSTRAINT `fk_invoice_lines_challan_line` 
    //         FOREIGN KEY (`challan_line_id`) REFERENCES `challan_lines`(`id`) 
    //         ON DELETE RESTRICT ON UPDATE CASCADE
    // ');

    // Add check constraints for business rules
    $this->db->query('
            ALTER TABLE `invoice_lines`
            ADD CONSTRAINT `chk_invoice_line_amounts_non_negative` 
            CHECK (
                quantity >= 0 
                AND weight >= 0 
                AND gold_weight_grams >= 0
                AND unit_price >= 0
                AND line_subtotal >= 0
                AND line_tax_amount >= 0
                AND line_total >= 0
            )
        ');

    // Check constraint: line_total should equal line_subtotal + line_tax_amount
    // Note: This is enforced at application level for flexibility during updates
  }

  public function down()
  {
    // Drop foreign key constraints first
    $this->db->query('ALTER TABLE `invoice_lines` DROP FOREIGN KEY IF EXISTS `fk_invoice_lines_invoice`');
    $this->db->query('ALTER TABLE `invoice_lines` DROP FOREIGN KEY IF EXISTS `fk_invoice_lines_challan`');
    // $this->db->query('ALTER TABLE `invoice_lines` DROP FOREIGN KEY IF EXISTS `fk_invoice_lines_challan_line`');

    // Drop check constraints
    if ($this->db->DBDriver === 'MySQLi') {
      $this->db->query('ALTER TABLE `invoice_lines` DROP CHECK IF EXISTS `chk_invoice_line_amounts_non_negative`');
    }

    // Drop the table
    $this->forge->dropTable('invoice_lines', true);
  }
}

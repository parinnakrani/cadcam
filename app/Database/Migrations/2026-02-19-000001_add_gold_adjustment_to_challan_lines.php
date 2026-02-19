<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Add gold adjustment columns to challan_lines table.
 *
 * New columns:
 * - current_gold_price    DECIMAL(10,2) NULL — snapshot of rate_per_gram from gold_rates
 * - adjusted_gold_weight  DECIMAL(10,3) NULL — gold_weight minus line weight
 * - gold_adjustment_amount DECIMAL(15,2) NULL — adjusted_gold_weight × current_gold_price
 */
class AddGoldAdjustmentToChallanLines extends Migration
{
  public function up()
  {
    $this->forge->addColumn('challan_lines', [
      'current_gold_price' => [
        'type'       => 'DECIMAL',
        'constraint' => '10,2',
        'null'       => true,
        'after'      => 'gold_purity',
      ],
      'adjusted_gold_weight' => [
        'type'       => 'DECIMAL',
        'constraint' => '10,3',
        'null'       => true,
        'after'      => 'current_gold_price',
      ],
      'gold_adjustment_amount' => [
        'type'       => 'DECIMAL',
        'constraint' => '15,2',
        'null'       => true,
        'after'      => 'adjusted_gold_weight',
      ],
    ]);
  }

  public function down()
  {
    $this->forge->dropColumn('challan_lines', 'current_gold_price');
    $this->forge->dropColumn('challan_lines', 'adjusted_gold_weight');
    $this->forge->dropColumn('challan_lines', 'gold_adjustment_amount');
  }
}

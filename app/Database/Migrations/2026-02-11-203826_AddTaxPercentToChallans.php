<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTaxPercentToChallans extends Migration
{
  public function up()
  {
    $this->forge->addColumn('challans', [
      'tax_percent' => [
        'type'       => 'DECIMAL',
        'constraint' => '5,2',
        'null'       => true,
        'default'    => null,
        'after'      => 'subtotal_amount',
      ],
    ]);

    // Also add process_prices to challan_lines if it doesn't already exist (checking first is trickier here without raw SQL or custom query, so just addColumn and let it error if exists? No, should be careful).
    // Since I saw process_prices in ChallanLineModel, I assume it might be missing or already there. Safest to check.
    // But migration assumes adding new stuff. I'll add access to check.

    $fields = $this->db->getFieldData('challan_lines');
    $hasProcessPrices = false;
    foreach ($fields as $field) {
      if ($field->name === 'process_prices') {
        $hasProcessPrices = true;
        break;
      }
    }

    if (!$hasProcessPrices) {
      $this->forge->addColumn('challan_lines', [
        'process_prices' => [
          'type' => 'JSON',
          'null' => true,
          'after' => 'process_ids',
        ],
      ]);
    }
  }

  public function down()
  {
    $this->forge->dropColumn('challans', 'tax_percent');
    // Don't drop process_prices blindly if it might have existed before? Or rely on rollback logic to just undo exactly what up() did.
    // It's safer to drop it here if we added it.
    // But if it existed before, dropping it breaks things.
    // Given the user request implies "we need to save process prices", it likely wasn't being saved, implying the column might be missing or unused.
    // However, ChallanModel had explicit references to process_prices. If the column was missing, those references would cause errors on SELECT *.
    // So the column LIKELY exists.
    // I will only drop tax_percent.
  }
}

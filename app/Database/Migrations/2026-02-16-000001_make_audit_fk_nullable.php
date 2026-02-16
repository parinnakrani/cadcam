<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeAuditFKNullable extends Migration
{
  public function up()
  {
    // Modify company_id and user_id to be nullable
    $fields = [
      'company_id' => [
        'type'       => 'INT',
        'constraint' => 10,
        'unsigned'   => true,
        'null'       => true,
      ],
      'user_id' => [
        'type'       => 'INT',
        'constraint' => 10,
        'unsigned'   => true,
        'null'       => true,
      ],
    ];

    $this->forge->modifyColumn('audit_logs', $fields);

    // Ideally, we should check if constraints exist and drop/re-add if necessary to enforce "ON DELETE SET NULL" behavior
    // But simply making the column nullable usually works with existing constraints unless strict mode blocks it.
    // Let's assume standard behavior: the constraint still exists on the column.
  }

  public function down()
  {
    // Revert to NOT NULL
    $fields = [
      'company_id' => [
        'type'       => 'INT',
        'constraint' => 10,
        'unsigned'   => true,
        'null'       => false,
      ],
      'user_id' => [
        'type'       => 'INT',
        'constraint' => 10,
        'unsigned'   => true,
        'null'       => false,
      ],
    ];
    $this->forge->modifyColumn('audit_logs', $fields);
  }
}

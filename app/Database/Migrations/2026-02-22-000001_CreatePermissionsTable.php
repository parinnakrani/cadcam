<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePermissionsTable extends Migration
{
  public function up()
  {
    $db = \Config\Database::connect();

    if ($db->tableExists('permissions')) {
      return; // Already exists, skip
    }

    $this->forge->addField([
      'id' => [
        'type'           => 'INT',
        'constraint'     => 10,
        'unsigned'       => true,
        'auto_increment' => true,
      ],
      'permission' => [
        'type'       => 'VARCHAR',
        'constraint' => 150,
        'null'       => false,
      ],
      'label' => [
        'type'       => 'VARCHAR',
        'constraint' => 200,
        'null'       => false,
      ],
      'module' => [
        'type'       => 'VARCHAR',
        'constraint' => 50,
        'null'       => false,
      ],
      'sub_module' => [
        'type'       => 'VARCHAR',
        'constraint' => 50,
        'null'       => false,
      ],
      'action' => [
        'type'       => 'VARCHAR',
        'constraint' => 50,
        'null'       => false,
      ],
      'sort_order' => [
        'type'       => 'INT',
        'constraint' => 11,
        'null'       => false,
        'default'    => 0,
      ],
      'is_active' => [
        'type'       => 'TINYINT',
        'constraint' => 1,
        'null'       => false,
        'default'    => 1,
      ],
    ]);

    $this->forge->addKey('id', true);
    $this->forge->addUniqueKey('permission', 'uk_permission');
    $this->forge->addKey('module', false, false, 'idx_module');
    $this->forge->addKey('sub_module', false, false, 'idx_sub_module');

    $this->forge->createTable('permissions');
  }

  public function down()
  {
    $this->forge->dropTable('permissions', true);
  }
}

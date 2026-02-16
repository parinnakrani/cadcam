<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditLogsTable extends Migration
{
  public function up()
  {
    $db = \Config\Database::connect();

    // Check if table exists
    if (!$db->tableExists('audit_logs')) {
      // Create Table from scratch
      $this->forge->addField([
        'id' => [
          'type'           => 'BIGINT',
          'constraint'     => 20,
          'unsigned'       => true,
          'auto_increment' => true,
        ],
        'company_id' => [
          'type'       => 'INT',
          'constraint' => 10,
          'unsigned'   => true,
        ],
        'user_id' => [
          'type'       => 'INT',
          'constraint' => 10,
          'unsigned'   => true,
        ],
        'module' => [
          'type'       => 'VARCHAR',
          'constraint' => 100,
        ],
        'action_type' => [
          'type'       => 'ENUM',
          'constraint' => ['create', 'update', 'delete', 'view', 'login', 'logout', 'print', 'export', 'switch_company', 'access_denied'],
        ],
        'record_type' => [
          'type'       => 'VARCHAR',
          'constraint' => 100,
          'null'       => true,
        ],
        'record_id' => [
          'type'       => 'INT',
          'constraint' => 10,
          'unsigned'   => true,
          'null'       => true,
        ],
        'before_data' => [
          'type' => 'JSON',
          'null' => true,
        ],
        'after_data' => [
          'type' => 'JSON',
          'null' => true,
        ],
        'ip_address' => [
          'type'       => 'VARCHAR',
          'constraint' => 45,
        ],
        'user_agent' => [
          'type'       => 'VARCHAR',
          'constraint' => 255,
          'null'       => true,
        ],
        'created_at' => [
          'type'    => 'TIMESTAMP',
          'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
        ],
      ]);
      $this->forge->addKey('id', true);
      $this->forge->addKey(['company_id', 'user_id', 'module', 'action_type', 'created_at']);
      // Add FK if users table exists (it should)
      if ($db->tableExists('users')) {
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
      }
      $this->forge->createTable('audit_logs');
    } else {
      // Table exists, modify it to match PRD requirements
      // 1. Expand columns
      $fieldsToModify = [
        'module' => [
          'type'       => 'VARCHAR',
          'constraint' => 100,
        ],
        'record_type' => [
          'type'       => 'VARCHAR',
          'constraint' => 100,
          'null'       => true,
        ],
        'action_type' => [
          'type'       => 'ENUM',
          'constraint' => ['create', 'update', 'delete', 'view', 'login', 'logout', 'print', 'export', 'switch_company', 'access_denied'],
        ],
      ];
      $this->forge->modifyColumn('audit_logs', $fieldsToModify);
    }
  }

  public function down()
  {
    // In a hybrid scenario, dropping might be dangerous if it pre-existed.
    // However, standard migration behavior is finding the reverse of up.
    // We will assume that if this migration is rolled back, the user intends to remove the table.
    $this->forge->dropTable('audit_logs', true);
  }
}

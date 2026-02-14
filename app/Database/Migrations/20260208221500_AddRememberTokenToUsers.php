<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRememberTokenToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'remember_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'mobile_number',
            ],
            'remember_expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'remember_token',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'remember_token');
        $this->forge->dropColumn('users', 'remember_expires_at');
    }
}

<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // 1. Ensure at least one State exists (Required for Company)
        $stateBuilder = $db->table('states');
        $state = $stateBuilder->where('state_code', 'MH')->get()->getRow();
        $stateId = $state ? $state->id : null;

        if (!$stateId) {
            $stateBuilder->insert([
                'state_name' => 'Maharashtra',
                'state_code' => 'MH',
                'country'    => 'India',
                'is_active'  => 1
            ]);
            $stateId = $db->insertID();
            echo "Created default state: Maharashtra (ID: $stateId)\n";
        }

        // 2. Ensure System/Super Admin Company exists (Required for User)
        $companyBuilder = $db->table('companies');
        $company = $companyBuilder->where('company_name', 'System Administrator')->get()->getRow();
        $companyId = $company ? $company->id : null;

        if (!$companyId) {
            $companyData = [
                'company_name'        => 'System Administrator',
                'business_legal_name' => 'System Administrator',
                'business_type'       => 'Gold Manufacturing', // Default enum
                'address_line1'       => 'System HQ',
                'city'                => 'System City',
                'state_id'            => $stateId,
                'pincode'             => '000000',
                'contact_person_name' => 'System Admin',
                'contact_email'       => 'admin@gmail.com',
                'contact_phone'       => '9999999999',
                'invoice_prefix'      => 'SYS-',
                'challan_prefix'      => 'CH-',
                'status'              => 'Active',
                'created_at'          => date('Y-m-d H:i:s'),
                'updated_at'          => date('Y-m-d H:i:s'),
                'is_deleted'          => 0
            ];
            
            // Insert and handle potential unique errors gracefully
            try {
                $companyBuilder->insert($companyData);
                $companyId = $db->insertID();
                echo "Created System Company (ID: $companyId)\n";
            } catch (\Exception $e) {
                // In case logic failed but company exists differently
                $existing = $companyBuilder->get()->getRow();
                if ($existing) {
                    $companyId = $existing->id;
                    echo "Using existing company (ID: $companyId)\n";
                } else {
                    throw $e;
                }
            }
        }

        // 3. Create Super Admin User
        $userData = [
            'company_id'            => $companyId,
            'username'              => 'superadmin',
            'email'                 => 'admin@gmail.com',
            'password_hash'         => password_hash('Admin@123', PASSWORD_BCRYPT),
            'full_name'             => 'System Administrator',
            'mobile_number'         => '9999999999',
            'employment_status'     => 'Active',
            'created_at'            => date('Y-m-d H:i:s'),
            'updated_at'            => date('Y-m-d H:i:s'),
            'is_deleted'            => 0,
        ];

        $userTable = $db->table('users');
        $existingUser = $userTable->where('username', $userData['username'])
                           ->orWhere('email', $userData['email'])
                           ->get()
                           ->getRow();

        if ($existingUser) {
            $userId = $existingUser->id;
            echo "Super Admin user already exists (ID: $userId).\n";
        } else {
            $userTable->insert($userData);
            $userId = $db->insertID();
            echo "Super Admin user created (ID: $userId).\n";
        }

        // 4. Assign Super Administrator Role
        $role = $db->table('roles')
                   ->where('role_name', 'Super Administrator')
                   ->where('company_id', 0) // System roles have company_id 0
                   ->get()
                   ->getRow();

        if (!$role) {
            echo "Error: 'Super Administrator' role not found. Please run RoleSeeder first.\n";
            return;
        }

        $userRoleBuilder = $db->table('user_roles');
        $exists = $userRoleBuilder->where('user_id', $userId)
                                  ->where('role_id', $role->id)
                                  ->countAllResults();

        if ($exists == 0) {
            $userRoleBuilder->insert([
                'user_id'     => $userId,
                'role_id'     => $role->id,
                'assigned_by' => $userId,
                'assigned_at' => date('Y-m-d H:i:s'),
            ]);
            echo "Assigned 'Super Administrator' role to user.\n";
        } else {
            echo "Role already assigned.\n";
        }
    }
}

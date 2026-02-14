<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            // 1. Super Administrator
            [
                'company_id'       => 0, // System/Global Role
                'role_name'        => 'Super Administrator',
                'role_description' => 'Full system access with global privileges',
                'permissions'      => json_encode(['*']),
                'is_system_role'   => 1,
                'is_active'        => 1,
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ],
            // 2. Company Administrator
            [
                'company_id'       => 0, // System Role (Template for Companies)
                'role_name'        => 'Company Administrator',
                'role_description' => 'Complete control over company specific data and settings',
                'permissions'      => json_encode([
                    'company.manage', 'users.manage', 'roles.manage',
                    'challans.*', 'invoices.*', 'payments.*',
                    'reports.*', 'masters.*', 'deliveries.*',
                    'settings.manage'
                ]),
                'is_system_role'   => 1, // Marked as system role to prevent deletion
                'is_active'        => 1,
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ],
            // 3. Billing Manager
            [
                'company_id'       => 0,
                'role_name'        => 'Billing Manager',
                'role_description' => 'Manages invoicing, challans, and basic reporting',
                'permissions'      => json_encode([
                    'challans.*', 'invoices.*',
                    'reports.ledger', 'reports.outstanding',
                    'customers.view', 'masters.view'
                ]),
                'is_system_role'   => 1,
                'is_active'        => 1,
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ],
            // 4. Accounts Manager
            [
                'company_id'       => 0,
                'role_name'        => 'Accounts Manager',
                'role_description' => 'Focus on payments, accounting reports, and customer finances',
                'permissions'      => json_encode([
                    'payments.*', 'reports.*',
                    'invoices.view', 'challans.view',
                    'customers.*'
                ]),
                'is_system_role'   => 1,
                'is_active'        => 1,
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ],
            // 5. Delivery Personnel
            [
                'company_id'       => 0,
                'role_name'        => 'Delivery Personnel',
                'role_description' => 'Access to assigned deliveries and invoice viewing',
                'permissions'      => json_encode([
                    'deliveries.view_assigned',
                    'deliveries.mark_complete',
                    'invoices.view_assigned'
                ]),
                'is_system_role'   => 1,
                'is_active'        => 1,
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ],
            // 6. Report Viewer
            [
                'company_id'       => 0,
                'role_name'        => 'Report Viewer',
                'role_description' => 'Read-only access to reports and core data',
                'permissions'      => json_encode([
                    'reports.view_all',
                    'invoices.view', 'challans.view',
                    'customers.view'
                ]),
                'is_system_role'   => 1, // Assuming system role based on "Predefined" context
                'is_active'        => 1,
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ],
        ];

        // Insert the data
        $this->db->table('roles')->insertBatch($roles);
    }
}

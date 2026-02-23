<?php
// Test script to see what PermissionService resolves for cashier
require 'vendor/autoload.php';
$app = \Config\Services::codeigniter();
$app->initialize();

$db = \Config\Database::connect();
$user = $db->table('users')->where('email', 'cashier@gmail.com')->get()->getRowArray();

if (!$user) {
  die("Cashier user not found\n");
}

echo "User ID: " . $user['id'] . "\n";
echo "Company ID: " . $user['company_id'] . "\n";

$roles = $db->table('user_roles ur')
  ->select('r.id, r.role_name, r.is_active')
  ->join('roles r', 'r.id = ur.role_id')
  ->where('ur.user_id', $user['id'])
  ->get()
  ->getResultArray();

print_r($roles);

// Test permission service
$permService = \Config\Services::PermissionService();
$permService->boot($user['id']);

$hasPerm = $permService->can('customers.accounts.list');
echo "Can customers.accounts.list: " . ($hasPerm ? 'YES' : 'NO') . "\n";

echo "All resolved permissions:\n";
print_r($permService->getUserPermissions());

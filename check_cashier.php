<?php
$pdo = new PDO('mysql:host=localhost;dbname=cadcam_invoice', 'root', '');
$stmt = $pdo->query("SELECT u.email, r.role_name, r.permissions FROM users u JOIN user_roles ur ON u.id = ur.user_id JOIN roles r ON ur.role_id = r.id WHERE u.email = 'cashier@gmail.com'");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($result);

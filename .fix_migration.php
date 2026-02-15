<?php
$mysqli = new mysqli("localhost", "root", "", "cadcam_invoice");

if ($mysqli->connect_errno) {
  echo "Failed to connect to MySQL: " . $mysqli->connect_error;
  exit();
}

$version = '2026-01-01-000014';
$class = 'App\\Database\\Migrations\\CreateInvoicesTable';

// Check if it exists first
$check = $mysqli->query("SELECT id FROM migrations WHERE version = '$version'");
if ($check->num_rows == 0) {
  $now = time();
  $sql = "INSERT INTO migrations (version, class, `group`, namespace, time, batch) 
            VALUES ('$version', '$class', 'default', 'App', $now, 1)";

  if ($mysqli->query($sql) === TRUE) {
    echo "Migration $version successfully added to history.\n";
  } else {
    echo "Error adding migration: " . $mysqli->error . "\n";
  }
} else {
  echo "Migration $version already in history.\n";
}

$mysqli->close();

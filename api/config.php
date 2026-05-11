<?php
// Turn off mysqli strict exceptions so errors return false instead of crashing
mysqli_report(MYSQLI_REPORT_OFF);

$env  = parse_ini_file(__DIR__ . '/../.env');
$conn = new mysqli($env['DB_HOST'], $env['DB_USER'], $env['DB_PASS'], $env['DB_NAME']);
if ($conn->connect_error) die(json_encode(['success' => false, 'message' => 'Database connection failed']));
$conn->set_charset('utf8mb4');
?>

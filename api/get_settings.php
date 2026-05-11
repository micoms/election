<?php
header('Content-Type: application/json');
require 'config.php';

// Auto-create and populate with defaults
$conn->query("CREATE TABLE IF NOT EXISTS settings (`key` VARCHAR(50) PRIMARY KEY, `value` VARCHAR(200) NOT NULL)");

$defaults = [
    'election_open'      => '1',
    'allow_registration' => '1',
    'election_title'     => 'Student Council Election 2026',
    'org_name'           => 'Student Vote',
    'election_year'      => '2026',
    'logo_emoji'         => '🗳️',
];

foreach ($defaults as $key => $val) {
    $k = $conn->real_escape_string($key);
    $v = $conn->real_escape_string($val);
    $conn->query("INSERT IGNORE INTO settings (`key`, `value`) VALUES ('$k', '$v')");
}

$result = $conn->query("SELECT `key`, `value` FROM settings");
$data = [];
while ($row = $result->fetch_assoc()) $data[$row['key']] = $row['value'];

echo json_encode(['success' => true, 'data' => $data]);
$conn->close();
?>

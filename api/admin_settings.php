<?php
header('Content-Type: application/json');
require 'config.php';

$conn->query("CREATE TABLE IF NOT EXISTS settings (`key` VARCHAR(50) PRIMARY KEY, `value` VARCHAR(200) NOT NULL)");

$defaults = [
    'election_open'      => '1',
    'allow_registration' => '1',
    'election_title'     => 'Student Council Election 2026',
    'org_name'           => 'Student Vote',
    'election_year'      => '2026',
    'logo_emoji'         => '🗳️',
];
foreach ($defaults as $k => $v) {
    $k = $conn->real_escape_string($k);
    $v = $conn->real_escape_string($v);
    $conn->query("INSERT IGNORE INTO settings (`key`, `value`) VALUES ('$k', '$v')");
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $conn->query("SELECT `key`, `value` FROM settings");
    $data = [];
    while ($row = $result->fetch_assoc()) $data[$row['key']] = $row['value'];
    echo json_encode(['success' => true, 'data' => $data]);
} else {
    $key   = $conn->real_escape_string(trim($_POST['key']   ?? ''));
    $value = $conn->real_escape_string(trim($_POST['value'] ?? ''));
    $conn->query("INSERT INTO settings (`key`, `value`) VALUES ('$key', '$value')
                  ON DUPLICATE KEY UPDATE `value` = '$value'");
    echo json_encode(['success' => true, 'message' => 'Setting saved']);
}

$conn->close();
?>

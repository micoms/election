<?php
header('Content-Type: application/json');
require 'config.php';

$position = $conn->real_escape_string(trim($_GET['position'] ?? ''));
if (!$position) {
    echo json_encode(['success' => false, 'message' => 'Position is required']);
    exit;
}

$result = $conn->query("SELECT id, name, position, image_url, description
                        FROM candidates WHERE position = '$position' ORDER BY name");
$data = [];
while ($row = $result->fetch_assoc()) $data[] = $row;

echo json_encode(['success' => true, 'position' => $position, 'candidates' => $data]);
$conn->close();
?>

<?php
header('Content-Type: application/json');
require 'config.php';

$position    = $conn->real_escape_string(trim($_POST['position']    ?? ''));
$name        = $conn->real_escape_string(trim($_POST['name']        ?? ''));
$image_url   = $conn->real_escape_string(trim($_POST['image_url']   ?? ''));
$description = $conn->real_escape_string(trim($_POST['description'] ?? ''));
$department  = $conn->real_escape_string(trim($_POST['department']  ?? ''));
$year        = intval($_POST['year'] ?? 0);

if (!$position || !$name) {
    echo json_encode(['success' => false, 'message' => 'Position and name are required']);
    exit;
}

$conn->query("INSERT INTO candidates (position, name, image_url, description, department, year)
              VALUES ('$position', '$name', '$image_url', '$description', '$department', $year)");

if ($conn->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Candidate added']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add candidate']);
}

$conn->close();
?>

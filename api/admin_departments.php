<?php
header('Content-Type: application/json');
require 'config.php';

$action = $_SERVER['REQUEST_METHOD'] === 'GET' ? 'list' : ($_POST['action'] ?? '');

if ($action === 'list') {
    $result = $conn->query("SELECT id, name, code FROM departments ORDER BY name");
    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    echo json_encode(['success' => true, 'data' => $data]);

} elseif ($action === 'add') {
    $name = $conn->real_escape_string(trim($_POST['name'] ?? ''));
    $code = $conn->real_escape_string(trim($_POST['code'] ?? ''));
    if (!$name) { echo json_encode(['success' => false, 'message' => 'Name is required']); exit; }

    $conn->query("INSERT INTO departments (name, code) VALUES ('$name', '$code')");
    if ($conn->affected_rows > 0)
        echo json_encode(['success' => true, 'message' => 'Department added']);
    else
        echo json_encode(['success' => false, 'message' => 'Department already exists']);

} elseif ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    $conn->query("DELETE FROM departments WHERE id = $id");
    echo json_encode(['success' => true, 'message' => 'Department deleted']);
}

$conn->close();
?>

<?php
header('Content-Type: application/json');
require 'config.php';

$endpoint = $_GET['endpoint'] ?? '';

if ($endpoint === 'departments') {
    $result = $conn->query("SELECT id, name, code FROM departments ORDER BY name");
    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    echo json_encode(['success' => true, 'data' => $data]);

} elseif ($endpoint === 'positions') {
    $result = $conn->query("SELECT id, name, order_num, color FROM positions ORDER BY order_num");
    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    echo json_encode(['success' => true, 'data' => $data]);

} elseif ($endpoint === 'developers') {
    $result = $conn->query("SELECT d.name, d.role, d.year, dept.name as department
                            FROM developers d
                            LEFT JOIN departments dept ON d.department_id = dept.id
                            ORDER BY d.order_num");
    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    echo json_encode(['success' => true, 'data' => $data]);

} else {
    echo json_encode(['success' => false, 'message' => 'Unknown endpoint']);
}

$conn->close();
?>

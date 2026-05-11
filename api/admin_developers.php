<?php
header('Content-Type: application/json');
require 'config.php';

$action = $_SERVER['REQUEST_METHOD'] === 'GET' ? 'list' : ($_POST['action'] ?? '');

if ($action === 'list') {
    $result = $conn->query("
        SELECT d.id, d.name, d.role, d.year, d.order_num, dept.name as department
        FROM developers d
        LEFT JOIN departments dept ON d.department_id = dept.id
        ORDER BY d.order_num ASC, d.name ASC
    ");
    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    echo json_encode(['success' => true, 'data' => $data]);

} elseif ($action === 'add') {
    $name          = $conn->real_escape_string(trim($_POST['name']  ?? ''));
    $role          = $conn->real_escape_string(trim($_POST['role']  ?? ''));
    $year          = intval($_POST['year']          ?? 0);
    $department_id = intval($_POST['department_id'] ?? 0);

    if (!$name || !$role) {
        echo json_encode(['success' => false, 'message' => 'Name and role are required']);
        exit;
    }

    $next = $conn->query("SELECT COALESCE(MAX(order_num), 0) + 1 AS n FROM developers")->fetch_assoc()['n'];
    $conn->query("INSERT INTO developers (name, role, year, department_id, order_num)
                  VALUES ('$name', '$role', $year, $department_id, $next)");

    if ($conn->affected_rows > 0)
        echo json_encode(['success' => true, 'message' => 'Developer added']);
    else
        echo json_encode(['success' => false, 'message' => $conn->error]);

} elseif ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    $conn->query("DELETE FROM developers WHERE id = $id");
    echo json_encode(['success' => true, 'message' => 'Developer removed']);
}

$conn->close();
?>

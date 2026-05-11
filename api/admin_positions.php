<?php
header('Content-Type: application/json');
require 'config.php';

$action = $_SERVER['REQUEST_METHOD'] === 'GET' ? 'list' : ($_POST['action'] ?? '');

if ($action === 'list') {
    $result = $conn->query("SELECT id, name, order_num, color FROM positions ORDER BY order_num");
    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    echo json_encode(['success' => true, 'data' => $data]);

} elseif ($action === 'add') {
    $name  = $conn->real_escape_string(trim($_POST['name']  ?? ''));
    $color = $conn->real_escape_string(trim($_POST['color'] ?? '#1b9a9a'));
    if (!$name) { echo json_encode(['success' => false, 'message' => 'Name is required']); exit; }

    $next = $conn->query("SELECT COALESCE(MAX(order_num), 0) + 1 AS n FROM positions")->fetch_assoc()['n'];
    $conn->query("INSERT INTO positions (name, order_num, color) VALUES ('$name', $next, '$color')");

    if ($conn->affected_rows > 0)
        echo json_encode(['success' => true, 'message' => 'Position added']);
    else
        echo json_encode(['success' => false, 'message' => $conn->error]);

} elseif ($action === 'delete') {
    $id  = intval($_POST['id'] ?? 0);
    $row = $conn->query("SELECT name FROM positions WHERE id = $id")->fetch_assoc();
    if (!$row) { echo json_encode(['success' => false, 'message' => 'Position not found']); exit; }

    $name = $conn->real_escape_string($row['name']);
    $conn->query("DELETE FROM votes      WHERE position = '$name'");
    $conn->query("DELETE FROM candidates WHERE position = '$name'");
    $conn->query("DELETE FROM positions  WHERE id = $id");
    echo json_encode(['success' => true, 'message' => 'Position deleted']);

} elseif ($action === 'reorder') {
    $id  = intval($_POST['id'] ?? 0);
    $dir = $_POST['direction'] ?? '';

    $cur  = $conn->query("SELECT order_num FROM positions WHERE id = $id")->fetch_assoc();
    $o    = $cur['order_num'];
    $adjQ = $dir === 'up'
        ? "SELECT id, order_num FROM positions WHERE order_num < $o ORDER BY order_num DESC LIMIT 1"
        : "SELECT id, order_num FROM positions WHERE order_num > $o ORDER BY order_num ASC  LIMIT 1";

    $adj = $conn->query($adjQ)->fetch_assoc();
    if ($adj) {
        $conn->query("UPDATE positions SET order_num = {$adj['order_num']} WHERE id = $id");
        $conn->query("UPDATE positions SET order_num = $o WHERE id = {$adj['id']}");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Cannot move further']);
    }
}

$conn->close();
?>

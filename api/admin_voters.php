<?php
header('Content-Type: application/json');
require 'config.php';

$action = $_SERVER['REQUEST_METHOD'] === 'GET' ? 'list' : ($_POST['action'] ?? '');

if ($action === 'list') {
    $result = $conn->query("SELECT id, full_name, email, student_id, year, has_voted, department
                            FROM voters ORDER BY full_name ASC");
    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    echo json_encode(['success' => true, 'data' => $data]);

} elseif ($action === 'reset') {
    $id = intval($_POST['voter_id'] ?? 0);
    $conn->query("DELETE FROM votes WHERE user_id = $id");
    $conn->query("UPDATE voters SET has_voted = 0 WHERE id = $id");
    echo json_encode(['success' => true, 'message' => 'Voter reset successfully']);

} elseif ($action === 'delete') {
    $id = intval($_POST['voter_id'] ?? 0);
    $conn->query("DELETE FROM votes WHERE user_id = $id");
    $conn->query("DELETE FROM voters WHERE id = $id");
    echo json_encode(['success' => true, 'message' => 'Voter deleted']);
}

$conn->close();
?>

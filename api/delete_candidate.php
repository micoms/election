<?php
header('Content-Type: application/json');
require 'config.php';

$id = intval($_POST['candidate_id'] ?? 0);
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Invalid candidate ID']);
    exit;
}

$conn->query("DELETE FROM candidates WHERE id = $id");

if ($conn->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Candidate deleted']);
} else {
    echo json_encode(['success' => false, 'message' => 'Candidate not found']);
}

$conn->close();
?>

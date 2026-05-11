<?php
header('Content-Type: application/json');
require 'config.php';

$voter_id = intval($_POST['voter_id'] ?? 0);
if (!$voter_id) {
    echo json_encode(['success' => false, 'message' => 'voter_id is required']);
    exit;
}

$conn->query("UPDATE voters SET has_voted = 1 WHERE id = $voter_id");

// affected_rows is 0 when has_voted was already 1 (no-op update), which is
// still a valid success state — the voter exists and is marked as voted.
if ($conn->error) {
    echo json_encode(['success' => false, 'message' => 'Failed to submit vote: ' . $conn->error]);
} else {
    echo json_encode(['success' => true, 'message' => 'Voting completed successfully']);
}

$conn->close();
?>

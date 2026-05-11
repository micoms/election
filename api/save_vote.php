<?php
header('Content-Type: application/json');
require 'config.php';

$voter_id     = intval($_POST['voter_id']     ?? 0);
$position     = $conn->real_escape_string(trim($_POST['position']     ?? ''));
$candidate_id = intval($_POST['candidate_id'] ?? 0);

if (!$voter_id || !$position || !$candidate_id) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Insert or update the vote for this position.
// ON DUPLICATE KEY UPDATE handles the case where the voter changed their
// selection before final submission — the latest pick always wins.
$conn->query("
    INSERT INTO votes (user_id, position, candidate_id)
    VALUES ($voter_id, '$position', $candidate_id)
    ON DUPLICATE KEY UPDATE candidate_id = $candidate_id
");

if ($conn->error) {
    echo json_encode(['success' => false, 'message' => 'Failed to save vote: ' . $conn->error]);
} else {
    echo json_encode(['success' => true, 'message' => 'Vote recorded']);
}

$conn->close();
?>

<?php
header('Content-Type: application/json');
require 'config.php';

$voter_id = intval($_GET['voter_id'] ?? 0);
if (!$voter_id) {
    echo json_encode(['success' => false, 'message' => 'voter_id is required']);
    exit;
}

$result = $conn->query("
    SELECT v.position, c.id as candidate_id, c.name as candidate_name, c.image_url
    FROM votes v
    JOIN candidates c ON v.candidate_id = c.id
    WHERE v.user_id = $voter_id
");

$votes = [];
while ($row = $result->fetch_assoc()) $votes[] = $row;

echo json_encode(['success' => true, 'votes' => $votes]);
$conn->close();
?>

<?php
header('Content-Type: application/json');
require 'config.php';

$position = $conn->real_escape_string(trim($_GET['position'] ?? ''));
if (!$position) {
    echo json_encode(['success' => false, 'message' => 'Position is required']);
    exit;
}

$result = $conn->query("
    SELECT c.id, c.name, COUNT(v.id) as vote_count
    FROM candidates c
    LEFT JOIN votes v ON c.id = v.candidate_id AND v.position = '$position'
    WHERE c.position = '$position'
    GROUP BY c.id, c.name
    ORDER BY vote_count DESC, c.name ASC
");

$results = [];
$total   = 0;
while ($row = $result->fetch_assoc()) {
    $results[] = $row;
    $total += $row['vote_count'];
}

echo json_encode(['success' => true, 'position' => $position, 'results' => $results, 'total_votes' => $total]);
$conn->close();
?>

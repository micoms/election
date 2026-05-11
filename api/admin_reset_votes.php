<?php
header('Content-Type: application/json');
require 'config.php';

$conn->query("DELETE FROM votes");
$conn->query("UPDATE voters SET has_voted = 0");

echo json_encode(['success' => true, 'message' => 'All votes have been reset']);
$conn->close();
?>

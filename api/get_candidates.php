<?php
header('Content-Type: application/json');
require 'config.php';

$result = $conn->query("SELECT id, position, name, image_url, description FROM candidates ORDER BY position, name");
$data = [];
while ($row = $result->fetch_assoc()) $data[] = $row;

echo json_encode($data);
$conn->close();
?>

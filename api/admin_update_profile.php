<?php
header('Content-Type: application/json');
require 'config.php';

$user_id       = intval($_POST['user_id']          ?? 0);
$full_name     = $conn->real_escape_string(trim($_POST['full_name']        ?? ''));
$current_pass  = trim($_POST['current_password']   ?? '');
$new_pass      = trim($_POST['new_password']       ?? '');

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Update name if provided
if ($full_name) {
    $conn->query("UPDATE users SET full_name = '$full_name' WHERE id = $user_id");
}

// Update password if provided
if ($new_pass) {
    $result = $conn->query("SELECT password FROM users WHERE id = $user_id");
    $user   = $result->fetch_assoc();
    if ($user['password'] !== $current_pass) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        exit;
    }
    $safe = $conn->real_escape_string($new_pass);
    $conn->query("UPDATE users SET password = '$safe' WHERE id = $user_id");
}

echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
$conn->close();
?>

<?php
header('Content-Type: application/json');
require 'config.php';

$email    = $conn->real_escape_string(trim($_POST['email']    ?? ''));
$password = trim($_POST['password'] ?? '');

if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit;
}

// Check admin users table first
$result = $conn->query("SELECT * FROM users WHERE email = '$email'");
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if ($user['password'] === $password) {
        echo json_encode([
            'success'    => true,
            'user_id'    => $user['id'],
            'email'      => $user['email'],
            'role'       => $user['role'],
            'full_name'  => $user['full_name'],
            'student_id' => '',
            'type'       => 'admin'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Wrong password']);
    }
    exit;
}

// Check voters table
$result = $conn->query("SELECT * FROM voters WHERE email = '$email'");
if ($result->num_rows > 0) {
    $voter = $result->fetch_assoc();
    if ($voter['password'] === $password) {
        echo json_encode([
            'success'    => true,
            'user_id'    => $voter['id'],
            'email'      => $voter['email'],
            'role'       => 'voter',
            'full_name'  => $voter['full_name'],
            'student_id' => $voter['student_id'],
            'type'       => 'voter'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Wrong password']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Email not found']);
$conn->close();
?>

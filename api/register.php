<?php
header('Content-Type: application/json');
require 'config.php';

$full_name     = trim($_POST['full_name']      ?? '');
$email         = trim($_POST['email']          ?? '');
$student_id    = trim($_POST['student_id']     ?? '');
$department_id = intval($_POST['department_id'] ?? 0);
$year          = intval($_POST['year']          ?? 0);
$password      = trim($_POST['password']        ?? '');

if (!$full_name || !$email || !$student_id || !$department_id || !$password) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

// Get the department name from the ID
$dept = $conn->query("SELECT name FROM departments WHERE id = $department_id")->fetch_assoc();
if (!$dept) {
    echo json_encode(['success' => false, 'message' => 'Invalid department']);
    exit;
}
$department = $conn->real_escape_string($dept['name']);

// Check if email or student ID already exists
$e = $conn->real_escape_string($email);
$s = $conn->real_escape_string($student_id);
$check = $conn->query("SELECT id FROM voters WHERE email = '$e' OR student_id = '$s'");
if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email or Student ID already registered']);
    exit;
}

// Save the new voter
$name = $conn->real_escape_string($full_name);
$pass = $conn->real_escape_string($password);
$conn->query("INSERT INTO voters (full_name, email, student_id, password, department, year)
              VALUES ('$name', '$e', '$s', '$pass', '$department', $year)");

if ($conn->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Registration successful! You can now login.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $conn->error]);
}

$conn->close();
?>

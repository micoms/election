<?php

declare(strict_types=1);

require __DIR__ . '/../config.php';

if (current_user()) {
    redirect(current_user()['role'] === 'admin' ? '/pages/admin-dashboard.php' : '/pages/voter-dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $studentId = trim($_POST['student_id'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $yearLevel = trim($_POST['year_level'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($fullName === '' || $email === '' || $studentId === '' || $department === '' || $yearLevel === '' || $password === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = db()->prepare('SELECT id FROM users WHERE student_id = ? OR email = ? LIMIT 1');
        $stmt->execute([$studentId, $email]);

        if ($stmt->fetch()) {
            $error = 'Student ID or email already exists.';
        } else {
            $insert = db()->prepare(
                'INSERT INTO users (full_name, email, student_id, department, year_level, password_hash, role)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $insert->execute([
                $fullName,
                $email,
                $studentId,
                $department,
                $yearLevel,
                password_hash($password, PASSWORD_DEFAULT),
                'voter',
            ]);

            $success = 'Registration complete. You can now log in.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Council Election 2026 - Sign Up</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="card">
        <div class="top-icon">📝</div>
        <h1>Sign Up</h1>
        <p class="subtitle">Create your account to participate in the Student Council Election 2026.</p>

        <?php if ($error !== ''): ?>
            <p style="color:#c0392b;font-weight:bold;"><?= e($error) ?></p>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
            <p style="color:#1b9a9a;font-weight:bold;"><?= e($success) ?></p>
        <?php endif; ?>

        <form method="post" action="/pages/register.php">
            <label>Full Name</label>
            <div class="input-box"><input type="text" name="full_name" required></div>

            <label>Email</label>
            <div class="input-box"><input type="email" name="email" required></div>

            <label>Student ID</label>
            <div class="input-box"><input type="text" name="student_id" required></div>

            <label>Department</label>
            <div class="input-box"><input type="text" name="department" required></div>

            <label>Year</label>
            <div class="input-box"><input type="text" name="year_level" placeholder="e.g. 3rd Year" required></div>

            <label>Password</label>
            <div class="input-box"><input type="password" name="password" required></div>

            <button type="submit">Sign Up →</button>
        </form>

        <p class="help">Already have an account? <a href="/index.php">Log In</a></p>
    </div>
</body>
</html>

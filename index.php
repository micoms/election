<?php

declare(strict_types=1);

require __DIR__ . '/config.php';

if (current_user()) {
    redirect(current_user()['role'] === 'admin' ? '/pages/admin-dashboard.php' : '/pages/voter-dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = trim($_POST['student_id'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($studentId === '' || $password === '') {
        $error = 'Student ID and password are required.';
    } else {
        $stmt = db()->prepare('SELECT id, full_name, student_id, role, password_hash FROM users WHERE student_id = ? LIMIT 1');
        $stmt->execute([$studentId]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $error = 'Invalid login credentials.';
        } else {
            $_SESSION['user'] = [
                'id' => (int) $user['id'],
                'full_name' => $user['full_name'],
                'student_id' => $user['student_id'],
                'role' => $user['role'],
            ];

            redirect($user['role'] === 'admin' ? '/pages/admin-dashboard.php' : '/pages/voter-dashboard.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Council Election 2026</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="card">
        <div class="top-icon">🗳️</div>
        <h1>Student Council<br>Election 2026</h1>
        <p class="subtitle">Secure Ballot Access. Please enter your credentials to continue.</p>

        <?php if ($error !== ''): ?>
            <p style="color:#c0392b;font-weight:bold;"><?= e($error) ?></p>
        <?php endif; ?>

        <form method="post" action="/index.php">
            <label>Student ID</label>
            <div class="input-box">
                <input type="text" name="student_id" placeholder="e.g. 20248891" required>
            </div>

            <label>Password</label>
            <div class="input-box">
                <input type="password" name="password" placeholder="••••••••••" required>
            </div>
            <button type="submit">Access Ballot →</button>
        </form>

        <p class="help">Don't have an account? <a href="/pages/register.php">Sign Up</a></p>
        <div class="footer">
            <span class="footer-icon">●</span>
            Official University Portal &copy; 2026
        </div>
    </div>
</body>
</html>

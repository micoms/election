<?php

declare(strict_types=1);

require __DIR__ . '/../config.php';
$user = require_login('voter');

$positions = db()->query(
    'SELECT p.id, p.title, c.full_name AS selected_candidate
     FROM positions p
     LEFT JOIN votes v ON v.position_id = p.id AND v.user_id = ' . (int) $user['id'] . '
     LEFT JOIN candidates c ON c.id = v.candidate_id
     ORDER BY p.display_order ASC'
)->fetchAll();

$totalPositions = count($positions);
$votedCount = 0;
foreach ($positions as $position) {
    if (!empty($position['selected_candidate'])) {
        $votedCount++;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student Vote - Voter Dashboard</title>
    <link rel="stylesheet" href="/css/dash.css" />
</head>
<body>
    <div class="page">
        <div class="sidebar">
            <div class="logo">
                <div class="logo-icon">🗳️</div>
                <div>
                    <h2>Student Vote</h2>
                    <p>Fall Election 2026</p>
                </div>
            </div>
            <div class="menu">
                <a href="/pages/voter-dashboard.php" class="link current">Dashboard</a>
                <a href="/pages/review.php" class="link">Review &amp; Submit</a>
                <a href="/pages/logout.php" class="link last">Logout</a>
            </div>
            <div class="user-info">
                <div class="user-icon">👤</div>
                <div>
                    <h3><?= e($user['full_name']) ?></h3>
                    <p>Student ID: <?= e($user['student_id']) ?></p>
                </div>
            </div>
        </div>

        <div class="main">
            <div class="step-bar">Welcome <?= e($user['full_name']) ?>!</div>
            <div class="content">
                <h1>Dashboard</h1>
                <p class="subtitle">You have voted in <?= $votedCount ?> of <?= $totalPositions ?> positions.</p>
                <div class="cards">
                    <?php foreach ($positions as $position): ?>
                        <div class="dash-card">
                            <div class="icon">🗳️</div>
                            <h2><?= e($position['title']) ?></h2>
                            <p style="color: <?= $position['selected_candidate'] ? '#1b9a9a' : '#999' ?>;">
                                <?= $position['selected_candidate'] ? 'Selected: ' . e($position['selected_candidate']) : 'Not yet voted' ?>
                            </p>
                            <a href="/pages/vote.php?position_id=<?= (int) $position['id'] ?>" class="vote-link">
                                <?= $position['selected_candidate'] ? 'Change Vote →' : 'Vote Now →' ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

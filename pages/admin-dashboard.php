<?php

declare(strict_types=1);

require __DIR__ . '/../config.php';
$user = require_login('admin');

$totalVoters = (int) db()->query("SELECT COUNT(*) FROM users WHERE role = 'voter'")->fetchColumn();
$submittedBallots = (int) db()->query("SELECT COUNT(*) FROM users WHERE role = 'voter' AND finalized_at IS NOT NULL")->fetchColumn();
$totalVotes = (int) db()->query('SELECT COUNT(*) FROM votes')->fetchColumn();
$positionsCount = (int) db()->query('SELECT COUNT(*) FROM positions')->fetchColumn();

$turnoutRate = $totalVoters > 0 ? round(($submittedBallots / $totalVoters) * 100, 2) : 0;

$tallyStmt = db()->query(
    'SELECT p.title AS position_title, c.full_name AS candidate_name, COUNT(v.id) AS total_votes
     FROM candidates c
     INNER JOIN positions p ON p.id = c.position_id
     LEFT JOIN votes v ON v.candidate_id = c.id
     WHERE c.is_active = 1
     GROUP BY p.id, p.title, c.id, c.full_name
     ORDER BY p.display_order ASC, total_votes DESC, c.full_name ASC'
);
$tallies = $tallyStmt->fetchAll();

$groupedTallies = [];
foreach ($tallies as $row) {
    $groupedTallies[$row['position_title']][] = $row;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Election</title>
    <link rel="stylesheet" href="/css/dash.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
    <div class="page">
        <div class="sidebar">
            <div class="logo">
                <div class="logo-icon">🛡️</div>
                <div>
                    <h2>Election Admin</h2>
                    <p>Control Panel</p>
                </div>
            </div>
            <div class="menu">
                <a href="/pages/admin-dashboard.php" class="link current">Dashboard</a>
                <a href="/pages/logout.php" class="link last">Logout</a>
            </div>
            <div class="user-info">
                <div class="user-icon">👤</div>
                <div>
                    <h3><?= e($user['full_name']) ?></h3>
                    <p>Role: Admin</p>
                </div>
            </div>
        </div>

        <div class="main">
            <div class="step-bar">Admin Dashboard</div>
            <div class="content">
                <h1>Election Overview</h1>
                <div class="cards">
                    <div class="dash-card">
                        <h2>Total Voters</h2>
                        <p class="stat-number"><?= $totalVoters ?></p>
                    </div>
                    <div class="dash-card">
                        <h2>Submitted Ballots</h2>
                        <p class="stat-number"><?= $submittedBallots ?></p>
                    </div>
                    <div class="dash-card">
                        <h2>Total Votes Cast</h2>
                        <p class="stat-number"><?= $totalVotes ?></p>
                    </div>
                    <div class="dash-card">
                        <h2>Turnout Rate</h2>
                        <p class="stat-number"><?= $turnoutRate ?>%</p>
                    </div>
                </div>

                <h2 style="margin-top: 30px;">Vote Tally</h2>
                <?php foreach ($groupedTallies as $positionTitle => $rows): ?>
                    <?php $maxVotes = max(array_map(static fn(array $row): int => (int) $row['total_votes'], $rows)); ?>
                    <div class="tally-section">
                        <h3><?= e($positionTitle) ?></h3>
                        <?php foreach ($rows as $row): ?>
                            <?php $percent = $maxVotes > 0 ? (((int) $row['total_votes'] / $maxVotes) * 100) : 0; ?>
                            <div class="tally-row">
                                <span class="tally-name"><?= e($row['candidate_name']) ?></span>
                                <div class="tally-bar-bg">
                                    <div class="tally-bar-fill" style="width: <?= round($percent, 2) ?>%;"></div>
                                </div>
                                <span class="tally-count"><?= (int) $row['total_votes'] ?> votes</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>

                <p class="notice">Positions tracked: <?= $positionsCount ?></p>
            </div>
        </div>
    </div>
</body>
</html>

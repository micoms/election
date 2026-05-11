<?php

declare(strict_types=1);

require __DIR__ . '/../config.php';
$user = require_login('voter');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'submit_final') {
    $countStmt = db()->prepare('SELECT COUNT(*) FROM votes WHERE user_id = ?');
    $countStmt->execute([(int) $user['id']]);
    $voteCount = (int) $countStmt->fetchColumn();

    $positionsCount = (int) db()->query('SELECT COUNT(*) FROM positions')->fetchColumn();
    if ($voteCount === $positionsCount) {
        $finalStmt = db()->prepare('UPDATE users SET finalized_at = CURRENT_TIMESTAMP WHERE id = ?');
        $finalStmt->execute([(int) $user['id']]);
    }
}

$selectionsStmt = db()->prepare(
    'SELECT p.id, p.title, c.full_name AS candidate_name
     FROM positions p
     LEFT JOIN votes v ON v.position_id = p.id AND v.user_id = ?
     LEFT JOIN candidates c ON c.id = v.candidate_id
     ORDER BY p.display_order ASC'
);
$selectionsStmt->execute([(int) $user['id']]);
$selections = $selectionsStmt->fetchAll();

$finalizedStmt = db()->prepare('SELECT finalized_at FROM users WHERE id = ?');
$finalizedStmt->execute([(int) $user['id']]);
$finalized = (bool) $finalizedStmt->fetchColumn();

$complete = true;
foreach ($selections as $selection) {
    if (empty($selection['candidate_name'])) {
        $complete = false;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Ballot Summary</title>
    <link rel="stylesheet" href="/css/review.css">
</head>
<body>
    <div class="top">
        <div class="top-logo">
            <div class="top-logo-icon">🗳️</div>
            <h2>Student Council Voting</h2>
        </div>
        <div class="top-user">
            <span>Welcome, <?= e($user['full_name']) ?></span>
            <div class="top-user-icon">👤</div>
        </div>
    </div>

    <div class="box">
        <p class="slash">Ballot / <span>Review</span> / Confirm</p>
        <h1>Review Your Ballot Summary</h1>
        <p class="subtitle">Please verify your choices below. Once submitted, your vote is final.</p>

        <?php foreach ($selections as $selection): ?>
            <div class="row">
                <div class="row-left">
                    <div class="circle"><?= strtoupper(substr($selection['title'], 0, 1)) ?></div>
                    <div>
                        <p class="label"><?= e(strtoupper($selection['title'])) ?></p>
                        <h3><?= e($selection['candidate_name'] ?: 'No Selection Made') ?></h3>
                    </div>
                </div>
                <a href="/pages/vote.php?position_id=<?= (int) $selection['id'] ?>">
                    <button class="edit-btn" <?= $finalized ? 'disabled' : '' ?>>Edit</button>
                </a>
            </div>
        <?php endforeach; ?>

        <div class="bottom-buttons">
            <a href="/pages/voter-dashboard.php" class="back-link">← Back to Dashboard</a>
            <div>
                <?php if ($finalized): ?>
                    <p class="secure" style="color:#1b9a9a;font-weight:bold;">✔ Your ballot has been submitted.</p>
                <?php else: ?>
                    <form method="post" action="/pages/review.php">
                        <input type="hidden" name="action" value="submit_final">
                        <button class="submit-btn" type="submit" <?= $complete ? '' : 'disabled' ?>>Submit Final Vote</button>
                    </form>
                    <?php if (!$complete): ?>
                        <p class="secure" style="color:#c0392b;">Complete all positions before final submit.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

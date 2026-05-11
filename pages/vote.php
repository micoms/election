<?php

declare(strict_types=1);

require __DIR__ . '/../config.php';
$user = require_login('voter');

$positionId = (int) ($_GET['position_id'] ?? 0);
if ($positionId <= 0) {
    redirect('/pages/voter-dashboard.php');
}

$positionStmt = db()->prepare('SELECT id, title FROM positions WHERE id = ? LIMIT 1');
$positionStmt->execute([$positionId]);
$position = $positionStmt->fetch();
if (!$position) {
    redirect('/pages/voter-dashboard.php');
}

$finalizedStmt = db()->prepare('SELECT finalized_at FROM users WHERE id = ? LIMIT 1');
$finalizedStmt->execute([(int) $user['id']]);
$finalizedAt = $finalizedStmt->fetchColumn();
if ($finalizedAt) {
    redirect('/pages/review.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $candidateId = (int) ($_POST['candidate_id'] ?? 0);

    $candidateStmt = db()->prepare('SELECT id FROM candidates WHERE id = ? AND position_id = ? AND is_active = 1 LIMIT 1');
    $candidateStmt->execute([$candidateId, $positionId]);
    $candidate = $candidateStmt->fetch();

    if ($candidate) {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $existingStmt = $pdo->prepare('SELECT candidate_id FROM votes WHERE user_id = ? AND position_id = ? LIMIT 1');
            $existingStmt->execute([(int) $user['id'], $positionId]);
            $existingCandidateId = $existingStmt->fetchColumn();

            $voteStmt = $pdo->prepare(
                'INSERT INTO votes (user_id, position_id, candidate_id) VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE candidate_id = ?, updated_at = CURRENT_TIMESTAMP'
            );
            $voteStmt->execute([(int) $user['id'], $positionId, $candidateId, $candidateId]);

            if ($existingCandidateId !== false && (int) $existingCandidateId !== $candidateId) {
                $auditStmt = $pdo->prepare(
                    'INSERT INTO vote_audit (user_id, position_id, old_candidate_id, new_candidate_id)
                     VALUES (?, ?, ?, ?)'
                );
                $auditStmt->execute([(int) $user['id'], $positionId, (int) $existingCandidateId, $candidateId]);
            }

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }

        redirect('/pages/voter-dashboard.php');
    }
}

$candidatesStmt = db()->prepare(
    'SELECT c.id, c.full_name, c.department, c.year_level, c.slogan, c.bio, c.image_path,
            (SELECT v.candidate_id FROM votes v WHERE v.user_id = ? AND v.position_id = ?) AS selected_id
     FROM candidates c
     WHERE c.position_id = ? AND c.is_active = 1
     ORDER BY c.id ASC'
);
$candidatesStmt->execute([(int) $user['id'], $positionId, $positionId]);
$candidates = $candidatesStmt->fetchAll();

$selectedId = 0;
if (!empty($candidates) && $candidates[0]['selected_id']) {
    $selectedId = (int) $candidates[0]['selected_id'];
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student Vote - <?= e($position['title']) ?></title>
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
                <a href="/pages/voter-dashboard.php" class="link">Dashboard</a>
                <a href="/pages/review.php" class="link">Review &amp; Submit</a>
                <a href="/pages/logout.php" class="link last">Logout</a>
            </div>
        </div>
        <div class="main">
            <div class="step-bar">Vote for <?= e($position['title']) ?></div>
            <div class="content">
                <h1><?= e($position['title']) ?></h1>
                <p class="subtitle">Select one candidate and save your vote.</p>
                <form method="post" action="/pages/vote.php?position_id=<?= (int) $positionId ?>">
                    <div class="cards">
                        <?php foreach ($candidates as $candidate): ?>
                            <div class="card" style="border: <?= (int) $candidate['id'] === $selectedId ? '3px solid #1b9a9a' : '1px solid #ddd' ?>;">
                                <div class="card-photo">
                                    <img src="<?= e($candidate['image_path'] ?: '/images/default-candidate.png') ?>" alt="<?= e($candidate['full_name']) ?>">
                                </div>
                                <div class="card-text">
                                    <h2><?= e($candidate['full_name']) ?></h2>
                                    <div class="candidate-meta"><?= e($candidate['department']) ?> &bull; <?= e($candidate['year_level']) ?></div>
                                    <p class="slogan"><?= e($candidate['slogan']) ?></p>
                                    <p class="about"><?= e($candidate['bio']) ?></p>
                                    <label style="display:block;margin-top:10px;">
                                        <input type="radio" name="candidate_id" value="<?= (int) $candidate['id'] ?>" <?= (int) $candidate['id'] === $selectedId ? 'checked' : '' ?> required>
                                        Choose this candidate
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="bottom">
                        <a href="/pages/voter-dashboard.php">← Back to Dashboard</a>
                        <button class="next-btn" type="submit">Save Vote</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

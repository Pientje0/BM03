<?php
declare(strict_types=1);
require_once __DIR__ . '/team-helpers.php';

$currentTeam = null;
$message = '';

try {
    $conn = teamDb();
    $userId = currentUserId($conn);

    // Team verlaten: alleen de koppeling in team_leden verwijderen.
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['actie'] ?? '') === 'leave') {
        $stmt = $conn->prepare('DELETE FROM team_leden WHERE gebruiker_id = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->close();
    }

    $currentTeam = currentTeamName($conn, $userId);
    $conn->close();
} catch (Throwable $error) {
    $message = 'Teams are temporarily unavailable. Please try again later.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link rel="stylesheet" href="style.css?v=<?= filemtime(__DIR__ . '/style.css') ?>">
</head>
<body class="auth-page auth-page--login">
    <main class="auth-layout">
        <section class="auth-card" aria-labelledby="auth-title">

          <?php if ($message !== ''): ?>
              <p class="form-message" role="alert"><?= e($message) ?></p>
          <?php endif; ?>

          <?php if ($currentTeam !== null): ?>
              <p class="auth-subtitle">You are in team <strong><?= e($currentTeam) ?></strong>.</p>
              <form action="team.php" method="POST">
                  <button type="submit" name="actie" value="leave" class="login-button">Leave team<span aria-hidden="true">→</span></button>
              </form>
          <?php else: ?>
              <form action="create-team.php" method="GET">
                  <button type="submit" class="login-button">Create team<span aria-hidden="true">→</span></button>
              </form>
              <form action="join-team.php" method="GET">
                  <button type="submit" class="login-button">join team<span aria-hidden="true">→</span></button>
              </form>
          <?php endif; ?>

        </section>
    </main>
</body>
</html>

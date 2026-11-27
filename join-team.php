<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/team-helpers.php';

$message = '';
$messageType = 'error';
$currentTeam = null;
$teams = [];
$chosenTeamId = 0;

try {
    $conn = teamDb();
    $userId = currentUserId($conn);

    if (isPost()) {
        $chosenTeamId = (int) ($_POST['team_id'] ?? 0);
        $teamPassword = (string) ($_POST['team_password'] ?? '');

        // Wachtwoord van het gekozen team ophalen.
        $stmt = $conn->prepare('SELECT password FROM teams WHERE id = ?');
        $stmt->bind_param('i', $chosenTeamId);
        $stmt->execute();
        $stmt->bind_result($hash);
        $teamExists = $stmt->fetch();
        $stmt->close();

        if (!$teamExists) {
            $message = 'That team does not exist anymore.';
        } elseif ($teamPassword === '' || !password_verify($teamPassword, $hash)) {
            $message = 'The team password is incorrect.';
        } elseif (addTeamMember($conn, $chosenTeamId, $userId)) {
            $message = 'You joined the team!';
            $messageType = 'success';
        } else {
            $message = 'You are already in a team. Leave it first.';
        }
    }

    $currentTeam = currentTeamName($conn, $userId);

    // Alle teams met hun aantal leden, voor de lijst.
    if ($currentTeam === null) {
        $result = $conn->query(
            'SELECT t.id, t.teamnaam, COUNT(g.id) AS leden
             FROM teams t
             LEFT JOIN team_leden tl ON tl.team_id = t.id
             LEFT JOIN gebruikers g ON g.id = tl.gebruiker_id
             GROUP BY t.id, t.teamnaam
             ORDER BY t.teamnaam'
        );
        $teams = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
    }
    $conn->close();
} catch (Throwable $error) {
    $message = teamErrorMessage($error);
    $messageType = 'error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join team</title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="auth-page">
    <main class="auth-layout">
        <section class="auth-card" aria-labelledby="auth-title">
            <div class="auth-card-heading">
                <h1 class="auth-title" id="auth-title">Join a team</h1>
                <p class="auth-subtitle">Pick a team and enter its password.</p>
            </div>

            <?php if ($message !== ''): ?>
                <p class="form-message form-message--<?= e($messageType) ?>" role="<?= $messageType === 'error' ? 'alert' : 'status' ?>"><?= e($message) ?></p>
            <?php endif; ?>

            <?php if ($currentTeam !== null): ?>
                <p class="auth-subtitle">You are in team <strong><?= e($currentTeam) ?></strong>.</p>
            <?php elseif ($teams === []): ?>
                <p class="auth-subtitle">There are no teams yet. <a href="create-team.php" class="link">Create one</a></p>
            <?php else: ?>
                <ul class="team-list">
                    <?php foreach ($teams as $team): ?>
                        <?php
                        $teamId = (int) $team['id'];
                        $members = (int) $team['leden'];
                        ?>
                        <li class="team-list-item">
                            <div class="team-list-info">
                                <span class="team-list-name"><?= e($team['teamnaam']) ?></span>
                                <span class="team-list-count"><?= $members ?> <?= $members === 1 ? 'member' : 'members' ?></span>
                            </div>
                            <form class="team-list-form" action="join-team.php" method="POST">
                                <input type="hidden" name="team_id" value="<?= $teamId ?>">
                                <label class="visually-hidden" for="team_password_<?= $teamId ?>">Password for <?= e($team['teamnaam']) ?></label>
                                <input type="password" name="team_password" id="team_password_<?= $teamId ?>" placeholder="Team password" autocomplete="off" required<?= $chosenTeamId === $teamId ? ' autofocus' : '' ?>>
                                <button type="submit" class="team-join-button">Join <span aria-hidden="true">→</span></button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <p class="text-link"><a href="team.php" class="link">Back</a></p>
        </section>
    </main>
</body>
</html>

<?php
declare(strict_types=1);
require_once __DIR__ . '/team-helpers.php';

$message = '';
$messageType = 'error';
$currentTeam = null;
$teams = [];
$chosenTeamId = 0;

try {
    $conn = teamDb();
    $userId = currentUserId($conn);

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $chosenTeamId = (int) ($_POST['team_id'] ?? 0);
        $teamPassword = (string) ($_POST['team_password'] ?? '');

        // Haal het wachtwoord op van het team dat in de lijst is gekozen.
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
        } else {
            try {
                $stmt = $conn->prepare('INSERT INTO team_leden (team_id, gebruiker_id) VALUES (?, ?)');
                $stmt->bind_param('ii', $chosenTeamId, $userId);
                $stmt->execute();
                $stmt->close();
                $message = 'You joined the team!';
                $messageType = 'success';
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() !== 1062) {
                    throw $e;
                }
                $message = 'You are already in a team. Leave it first.';
            }
        }
    }

    $currentTeam = currentTeamName($conn, $userId);

    // Alle teams met het aantal leden, voor de lijst.
    if ($currentTeam === null) {
        $result = $conn->query(
            'SELECT t.id, t.teamnaam, COUNT(tl.gebruiker_id) AS leden
             FROM teams t
             LEFT JOIN team_leden tl ON tl.team_id = t.id
             GROUP BY t.id, t.teamnaam
             ORDER BY t.teamnaam'
        );
        $teams = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
    }
    $conn->close();
} catch (Throwable $error) {
    $message = 'Teams are temporarily unavailable. Please try again later.';
    $messageType = 'error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join team</title>
    <link rel="stylesheet" href="style.css?v=<?= filemtime(__DIR__ . '/style.css') ?>">
</head>
<body class="auth-page auth-page--login">
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
                        <?php $fieldId = 'team_password_' . (int) $team['id']; ?>
                        <li class="team-list-item">
                            <div class="team-list-info">
                                <span class="team-list-name"><?= e($team['teamnaam']) ?></span>
                                <span class="team-list-count"><?= (int) $team['leden'] ?> <?= (int) $team['leden'] === 1 ? 'member' : 'members' ?></span>
                            </div>
                            <form class="team-list-form" action="join-team.php" method="POST">
                                <input type="hidden" name="team_id" value="<?= (int) $team['id'] ?>">
                                <label class="visually-hidden" for="<?= $fieldId ?>">Password for <?= e($team['teamnaam']) ?></label>
                                <input type="password" name="team_password" id="<?= $fieldId ?>" placeholder="Team password" autocomplete="off" required<?= $chosenTeamId === (int) $team['id'] ? ' autofocus' : '' ?>>
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

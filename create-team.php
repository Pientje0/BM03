<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/team-helpers.php';

$message = '';
$messageType = 'error';
$currentTeam = null;

try {
    $conn = teamDb();
    $userId = currentUserId($conn);

    if (isPost()) {
        $teamnaam = trim((string) ($_POST['teamnaam'] ?? ''));
        $teamPassword = (string) ($_POST['team_password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

        if ($teamnaam === '' || $teamPassword === '') {
            $message = 'Fill in a team name and a team password.';
        } elseif (strlen($teamPassword) < 4) {
            $message = 'The team password must be at least 4 characters.';
        } elseif ($teamPassword !== $confirmPassword) {
            $message = 'Those passwords do not match. Please try again.';
        } else {
            // Team + eerste lid samen opslaan: lukt een van de twee niet, dan geen van beide.
            $conn->begin_transaction();
            try {
                $hash = password_hash($teamPassword, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('INSERT INTO teams (teamnaam, password, aangemaakt_door) VALUES (?, ?, ?)');
                $stmt->bind_param('ssi', $teamnaam, $hash, $userId);
                $stmt->execute();
                $teamId = $conn->insert_id;
                $stmt->close();
                $created = addTeamMember($conn, $teamId, $userId);
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() !== 1062) { // 1062 = teamnaam bestaat al
                    $conn->rollback();
                    throw $e;
                }
                $created = false;
            }

            if ($created) {
                $conn->commit();
                $message = 'Team created! You are now in this team.';
                $messageType = 'success';
            } else {
                $conn->rollback();
                $message = 'That team name is taken, or you are already in a team.';
            }
        }
    }

    $currentTeam = currentTeamName($conn, $userId);
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
    <title>Create team</title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="auth-page">
    <main class="auth-layout">
        <section class="auth-card" aria-labelledby="auth-title">
            <div class="auth-card-heading">
                <h1 class="auth-title" id="auth-title">Create a team</h1>
                <p class="auth-subtitle">Pick a team name and a password your teammates can use to join.</p>
            </div>

            <?php if ($message !== ''): ?>
                <p class="form-message form-message--<?= e($messageType) ?>" role="<?= $messageType === 'error' ? 'alert' : 'status' ?>"><?= e($message) ?></p>
            <?php endif; ?>

            <?php if ($currentTeam !== null): ?>
                <p class="auth-subtitle">You are in team <strong><?= e($currentTeam) ?></strong>.</p>
            <?php else: ?>
                <form class="auth-form" action="create-team.php" method="POST">
                    <div class="form-field">
                        <label for="teamnaam">Team name</label>
                        <input type="text" name="teamnaam" id="teamnaam" placeholder="Choose a team name" maxlength="50" required>
                    </div>

                    <div class="form-field">
                        <label for="team_password">Team password</label>
                        <input type="password" name="team_password" id="team_password" placeholder="At least 4 characters" minlength="4" autocomplete="new-password" required>
                    </div>

                    <div class="form-field">
                        <label for="confirm_password">Confirm team password</label>
                        <input type="password" name="confirm_password" id="confirm_password" placeholder="Enter it one more time" minlength="4" autocomplete="new-password" required>
                    </div>

                    <button type="submit" class="login-button">Create team <span aria-hidden="true">→</span></button>
                </form>
            <?php endif; ?>

            <p class="text-link"><a href="team.php" class="link">Back</a></p>
        </section>
    </main>
</body>
</html>

<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

$message = '';
$messageType = '';
$registrationSucceeded = false;

if (isPost()) {
    $user = trim((string) ($_POST['username'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($password !== $confirmPassword) {
        $message = 'Those passwords do not match. Please try again.';
        $messageType = 'error';
    } else {
        try {
            $conn = db();
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO gebruikers (username, email, password) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $user, $email, $hashedPassword);
            $stmt->execute();
            $stmt->close();
            $conn->close();

            $message = 'Your account is ready.';
            $messageType = 'success';
            $registrationSucceeded = true;
        } catch (Throwable $error) {
            $message = 'We could not create your account. Check your details and try again.';
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create an account</title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="auth-page">
    <main class="auth-layout">
        <section class="auth-card" aria-labelledby="auth-title">
            <div class="auth-card-heading">
                <h1 class="auth-title" id="auth-title">Create your account</h1>
                <p class="auth-subtitle">Get ready to explore, solve clues, and find the way out.</p>
            </div>

            <?php if ($message !== ''): ?>
                <div class="form-message form-message--<?= e($messageType) ?>" role="<?= $messageType === 'error' ? 'alert' : 'status' ?>">
                    <span><?= e($message) ?></span>
                    <?php if ($registrationSucceeded): ?>
                        <a class="message-link" href="login.php">Sign in now <span aria-hidden="true">→</span></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form class="auth-form" action="register.php" method="POST" id="regForm">
                <div class="form-field">
                    <label for="email">Email address</label>
                    <input type="email" name="email" id="email" placeholder="you@example.com" autocomplete="email" required>
                </div>

                <div class="form-field">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" placeholder="Choose a username" maxlength="100" autocomplete="username" required>
                </div>

                <div class="form-field">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="At least 8 characters" minlength="8" autocomplete="new-password" required>
                </div>

                <div class="form-field">
                    <label for="confirm_password">Confirm password</label>
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Enter it one more time" minlength="8" autocomplete="new-password" required>
                </div>

                <button type="submit" class="login-button">Create account <span aria-hidden="true">→</span></button>
            </form>

            <p class="text-link">Already have an account? <a href="login.php" class="link">Sign in</a></p>
        </section>
    </main>

    <script src="<?= asset('js/register-validation.js') ?>"></script>
    <script src="<?= asset('js/auth-transitions.js') ?>"></script>
</body>
</html>

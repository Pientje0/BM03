<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

if (isLoggedIn()) {
    header('Location: home.php');
    exit;
}

$message = '';

if (isPost()) {
    $user = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    try {
        $conn = db();
        $stmt = $conn->prepare('SELECT id, password FROM gebruikers WHERE username = ?');
        $stmt->bind_param('s', $user);
        $stmt->execute();
        $stmt->bind_result($userId, $hashedPassword);
        $userExists = $stmt->fetch();
        $stmt->close();
        $conn->close();

        if ($userExists && password_verify($password, $hashedPassword)) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $user;
            header('Location: home.php');
            exit;
        }

        $message = $userExists ? 'The password is incorrect.' : 'Username not found.';
    } catch (Throwable $error) {
        $message = 'Sign-in is temporarily unavailable. Please try again later.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="auth-page">
    <main class="auth-layout">
        <section class="auth-card" aria-labelledby="auth-title">
            <div class="auth-card-heading">
                <h1 class="auth-title" id="auth-title">Welcome</h1>
                <p class="auth-subtitle">Sign in to continue your escape room adventure.</p>
            </div>

            <?php if ($message !== ''): ?>
                <p class="form-message" role="alert"><?= e($message) ?></p>
            <?php endif; ?>

            <form class="auth-form" action="login.php" method="POST" id="loginForm">
                <div class="form-field">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" placeholder="Enter your username" maxlength="100" autocomplete="username" required>
                </div>

                <div class="form-field">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="Enter your password" autocomplete="current-password" required>
                </div>

                <button type="submit" class="login-button">Sign in <span aria-hidden="true">→</span></button>
            </form>

            <p class="text-link">New to the maze? <a href="register.php" class="link">Create an account</a></p>
        </section>
    </main>

    <script src="<?= asset('js/auth-transitions.js') ?>"></script>
</body>
</html>

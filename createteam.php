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
            <div class="auth-card-heading">

            <form class="auth-form" action="login.php" method="POST" id="loginForm">
                <div class="form-field">
                    <label for="username">Teamname</label>
                    <input type="text" name="username" id="username" placeholder="Enter your teamname" maxlength="100" autocomplete="username" required>
                </div>

                <div class="form-field">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="Enter your password" autocomplete="current-password" required>
                </div>

                <button type="submit" class="login-button">Create Team <span aria-hidden="true">→</span></button>
            </form>
        </section>
    </main>
</body>
</html>
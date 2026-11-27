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

          <a href="createteam.php"><button type="submit" class="login-button">Create team<span aria-hidden="true"></span></button></a>
          <a href="jointeam.php"><button type="submit" class="login-button">join team<span aria-hidden="true"></span></button></a>
         

        </section>
    </main>
</body>
</html>
<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Maze Escape Room</title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="home-page">
    <a href="team.php" class="teambutton"><span class="play-icon"></span></a>

    <div class="account-controls">
        <p class="signed-in-text">Signed in as <?= e((string) $_SESSION['username']) ?>.</p>
        <form class="logout-form" action="logout.php" method="post">
            <button class="logout-button" type="submit">Sign out</button>
        </form>
    </div>
    <br>
</body>
</html>

<?php
declare(strict_types=1);

// Sessie die 30 dagen ingelogd blijft.
$sessionLifetime = 30 * 24 * 60 * 60;
ini_set('session.gc_maxlifetime', (string) $sessionLifetime);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => $sessionLifetime,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function isLoggedIn(): bool
{
    return isset($_SESSION['username']);
}

// Stuurt niet-ingelogde bezoekers naar de inlogpagina.
function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function isPost(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

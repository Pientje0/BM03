<?php
declare(strict_types=1);

// Verbinding met de XAMPP-database.
// De database gaat niet mee via git, dus op een nieuwe computer maakt
// deze functie de database en de tabel `gebruikers` zelf aan.
function db(): mysqli
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conn = new mysqli('localhost', 'root', '');

    $conn->query('CREATE DATABASE IF NOT EXISTS school_project CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
    $conn->select_db('school_project');

    $conn->query(
        'CREATE TABLE IF NOT EXISTS gebruikers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL,
            password VARCHAR(255) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'
    );

    return $conn;
}

// Maakt tekst veilig om in HTML te tonen.
function e(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Link naar een css/js-bestand, met de wijzigdatum erachter zodat de browser
// na een aanpassing altijd de nieuwe versie laadt.
function asset(string $path): string
{
    return $path . '?v=' . filemtime(__DIR__ . '/../' . $path);
}

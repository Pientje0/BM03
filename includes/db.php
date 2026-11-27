<?php
declare(strict_types=1);

// Verbinding met de XAMPP-database.
function db(): mysqli
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    return new mysqli('localhost', 'root', '', 'school_project');
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

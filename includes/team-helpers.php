<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

// Alleen ingelogde accounts mogen op de teampagina's komen.
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

function e(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function teamDb(): mysqli
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    return new mysqli('localhost', 'root', '', 'school_project');
}

// Geeft het id van de ingelogde gebruiker.
// Sessies van voor de team-update hebben nog geen user_id: die zoeken we op.
function currentUserId(mysqli $conn): int
{
    if (!isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare('SELECT id FROM gebruikers WHERE username = ?');
        $stmt->bind_param('s', $_SESSION['username']);
        $stmt->execute();
        $stmt->bind_result($foundId);
        if (!$stmt->fetch()) {
            $stmt->close();
            header('Location: logout.php');
            exit;
        }
        $_SESSION['user_id'] = $foundId;
        $stmt->close();
    }
    return (int) $_SESSION['user_id'];
}

// Geeft de naam van het team waar de gebruiker in zit, of null.
function currentTeamName(mysqli $conn, int $userId): ?string
{
    $stmt = $conn->prepare('SELECT t.id, t.teamnaam FROM team_leden tl JOIN teams t ON t.id = tl.team_id WHERE tl.gebruiker_id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->bind_result($teamId, $teamName);
    $found = $stmt->fetch();
    $stmt->close();

    if ($found) {
        $_SESSION['team_id'] = $teamId;
        return $teamName;
    }
    unset($_SESSION['team_id']);
    return null;
}

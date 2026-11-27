<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

// Alleen ingelogde accounts mogen op de teampagina's komen.
requireLogin();

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

// Zet een gebruiker in een team.
// Geeft false als de gebruiker al in een team zit (of de koppeling al bestaat).
function addTeamMember(mysqli $conn, int $teamId, int $userId): bool
{
    try {
        $stmt = $conn->prepare('INSERT INTO team_leden (team_id, gebruiker_id) VALUES (?, ?)');
        $stmt->bind_param('ii', $teamId, $userId);
        $stmt->execute();
        $stmt->close();
        return true;
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() === 1062) { // 1062 = dubbele waarde
            return false;
        }
        throw $e;
    }
}

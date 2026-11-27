<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

// Alleen ingelogde accounts mogen op de teampagina's komen.
requireLogin();

// Databaseverbinding voor de teampagina's.
// Maakt de teamtabellen zelf aan als ze nog niet bestaan,
// zodat niemand iets in phpMyAdmin hoeft te doen.
// Bewust zonder FOREIGN KEY naar `gebruikers`: die mislukt als `gebruikers`
// op een andere computer net anders is aangemaakt (bv. INT UNSIGNED of MyISAM).
function teamDb(): mysqli
{
    $conn = db();

    $conn->query(
        'CREATE TABLE IF NOT EXISTS teams (
            id INT AUTO_INCREMENT PRIMARY KEY,
            teamnaam VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            aangemaakt_door INT NOT NULL,
            aangemaakt_op TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'
    );

    // Koppelt gebruikers aan teams; iedereen zit in maximaal één team.
    $conn->query(
        'CREATE TABLE IF NOT EXISTS team_leden (
            team_id INT NOT NULL,
            gebruiker_id INT NOT NULL,
            PRIMARY KEY (team_id, gebruiker_id),
            UNIQUE (gebruiker_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'
    );

    return $conn;
}

// Foutmelding voor de teampagina's. Op localhost staat de echte
// MySQL-fout erachter, zodat je kunt zien wat er misgaat.
function teamErrorMessage(Throwable $error): string
{
    $message = 'Teams are temporarily unavailable. Please try again later.';
    if (in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
        $message .= ' (' . $error->getMessage() . ')';
    }
    return $message;
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

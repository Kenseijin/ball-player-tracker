<?php
require_once '../classes/database.php';
require_once '../classes/game.php';

$db = Database::getInstance()->getConnection();
$game = new Game($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $home_team_id = $_POST['home_team_id'];
    $away_team_id = $_POST['away_team_id'];
    $game_date = $_POST['game_date'];
    $location = $_POST['location'] ?? 'TBD'; // default value if not provided
    $status = $_POST['status'] ?? 'scheduled';

    if ($home_team_id == $away_team_id) {
        die("Error: Home and away teams must be different.");
    }

    if ($id) {
        $game->update($id, $home_team_id, $away_team_id, $game_date, $location, $status);
    } else {
        $game->add($home_team_id, $away_team_id, $game_date, $location, $status);
    }

    header("Location: games_admin.php");
    exit;
}

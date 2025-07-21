<?php
require_once '../classes/database.php';
require_once '../classes/stat.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $game_id = $_POST['game_id'];
    $quarter = $_POST['quarter'];
    $stats = $_POST['stats'] ?? [];

    $db = Database::getInstance()->getConnection();
    $statObj = new Stat($db);

    foreach ($stats as $player_id => $player_stats) {
        $statObj->save([
            'game_id' => $game_id,
            'quarter' => $quarter,
            'player_id' => $player_id,
            'ft' => (int)($player_stats['ft'] ?? 0),
            'two_pt' => (int)($player_stats['two_pt'] ?? 0),
            'three_pt' => (int)($player_stats['three_pt'] ?? 0),
            'assist' => (int)($player_stats['ast'] ?? 0),
            'steal' => (int)($player_stats['stl'] ?? 0),
            'block' => (int)($player_stats['blk'] ?? 0)
        ]);
    }

    header("Location: score_entry.php?id=$game_id&quarter=" . ($quarter + 1));
    exit;
} else {
    die("Invalid request.");
}

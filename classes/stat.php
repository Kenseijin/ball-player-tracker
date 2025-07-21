<?php
require_once 'database.php';

class Stat {
    private $db;

    public function __construct($db = null) {
        $this->db = $db ?: Database::getInstance()->getConnection();
    }

    public function save($data) {
        $stmt = $this->db->prepare("INSERT INTO player_stats 
            (game_id, quarter, player_id, ft, two_pt, three_pt, assist, steal, block)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                ft = VALUES(ft),
                two_pt = VALUES(two_pt),
                three_pt = VALUES(three_pt),
                assist = VALUES(assist),
                steal = VALUES(steal),
                block = VALUES(block)
        ");
        return $stmt->execute([
            $data['game_id'],
            $data['quarter'],
            $data['player_id'],
            $data['ft'],
            $data['two_pt'],
            $data['three_pt'],
            $data['assist'],
            $data['steal'],
            $data['block']
        ]);
    }

    public function getStatsForPlayer($game_id, $quarter, $player_id) {
        $stmt = $this->db->prepare("SELECT * FROM player_stats 
            WHERE game_id = ? AND quarter = ? AND player_id = ?");
        $stmt->execute([$game_id, $quarter, $player_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPlayerTotalPoints($game_id, $player_id) {
        $stmt = $this->db->prepare("SELECT SUM(ft) as ft, SUM(two_pt) as two_pt, SUM(three_pt) as three_pt 
            FROM player_stats WHERE game_id = ? AND player_id = ?");
        $stmt->execute([$game_id, $player_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($row['ft'] ?? 0) + 2 * ($row['two_pt'] ?? 0) + 3 * ($row['three_pt'] ?? 0);
    }

    public function getTeamTotalPoints($game_id, $team_id) {
        $stmt = $this->db->prepare("
            SELECT SUM(ps.ft) as ft, SUM(ps.two_pt) as two_pt, SUM(ps.three_pt) as three_pt
            FROM player_stats ps
            JOIN players p ON ps.player_id = p.id
            WHERE ps.game_id = ? AND p.team_id = ?
        ");
        $stmt->execute([$game_id, $team_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($row['ft'] ?? 0) + 2 * ($row['two_pt'] ?? 0) + 3 * ($row['three_pt'] ?? 0);
    }
}
?>
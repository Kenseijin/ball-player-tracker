<?php
require_once 'database.php';

class Game {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function add($home_team_id, $away_team_id, $game_date, $location, $status = 'scheduled') {
        $stmt = $this->db->prepare("INSERT INTO games (home_team_id, away_team_id, game_date, location, status) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$home_team_id, $away_team_id, $game_date, $location, $status]);
    }

    public function update($id, $home_team_id, $away_team_id, $game_date, $location, $status) {
        $stmt = $this->db->prepare("UPDATE games SET home_team_id = ?, away_team_id = ?, game_date = ?, location = ?, status = ? WHERE id = ?");
        return $stmt->execute([$home_team_id, $away_team_id, $game_date, $location, $status, $id]);
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM games ORDER BY game_date DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM games WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM games WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getByDate($date) {
        $stmt = $this->db->prepare("SELECT * FROM games WHERE DATE(game_date) = ?");
        $stmt->execute([$date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

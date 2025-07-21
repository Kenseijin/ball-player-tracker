<?php
require_once 'database.php';
class Player {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll() {
    $stmt = $this->db->query(
        "SELECT players.*, teams.name AS team_name 
         FROM players 
         LEFT JOIN teams ON players.team_id = teams.id
         ORDER BY players.name ASC"
    );
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM players WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function add($name, $age, $position, $jersey_number, $photo = null, $team_id = null) {
        $stmt = $this->db->prepare("INSERT INTO players (name, age, position, jersey_number, photo, team_id) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$name, $age, $position, $jersey_number, $photo, $team_id]);
    }

    public function update($id, $name, $age, $position, $jersey_number, $photo = null, $team_id = null) {
        $stmt = $this->db->prepare("UPDATE players SET name = ?, age = ?, position = ?, jersey_number = ?, photo = ?, team_id = ? WHERE id = ?");
        return $stmt->execute([$name, $age, $position, $jersey_number, $photo, $team_id, $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM players WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function getByTeam($team_id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM players WHERE team_id = ?");
        $stmt->execute([$team_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
?>

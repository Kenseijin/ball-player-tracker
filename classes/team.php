<?php
require_once 'database.php';

class Team{
    private $db;

    public function _construct() {
        $this->db=Database::getInstance()->getConnection();
    }
    public function getAll() {
        $stmt=$this->db->query("SELECT * FROM teams ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getById($id){
         $stmt=$this->db->prepare("SELECT * FROM teams WHERE id=?");
         $stmt->execute([$id]);
         return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function add($name,$coach,$city,$logo=null) {
        $stmt=$this->db->prepare("INSERT INTO teams (names,coach,city,logo) VALUES (?,?,?,)");
        return $stmt->execute([$name,$coach,$city,$logo]);
    }
    public function update($name,$coach,$city,$logo=null) {
        $stmt=$this->db->prepare("UPDATE teams SET name=?, coach=?,city=?,logo=? WHERE id=?");
        return $stmt->execute([$name,$coach,$city,$logo,$id]);
    }
    public function delete($id){
        $stmt=$this->db->prepare("DELETE FROM teams WHERE id=?");
        return $stmt->execute([$id]);
    }
}

?>
<?php 
class Database {
    private static $instance = null;
    private $pdo;

    private $host = 'localhost';
    private $db = 'player_tracker';
    private $user = 'root';
    private $pass = '';
    private $charset = 'utf8mb4';

    // ✅ Constructor must be named __construct (double underscore)
    private function __construct() {
        $dsn = "mysql:host=$this->host;dbname=$this->db;charset=$this->charset";

        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $err) {
            die("DB connection failed: " . $err->getMessage());
        }
    }

    // ✅ Singleton instance getter
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // ✅ Expose PDO connection
    public function getConnection() {
        return $this->pdo;
    }
}
?>

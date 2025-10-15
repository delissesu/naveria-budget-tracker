<?php
class Database {
    // Database credentials
    private $host = "localhost";
    private $db_name = "budget_tracker";
    private $username = "postgres";
    private $password = "Delion21.";
    private $port = "5432";
    public $conn;

    /**
     * Establish database connection
     * @return PDO Database connection object
     * @throws PDOException if connection fails
     */
    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "pgsql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("SET NAMES 'UTF8'");
        } catch(PDOException $exception) {
            throw new PDOException("Connection error: " . $exception->getMessage(), (int)$exception->getCode());
        }
        return $this->conn;
    }
}
?>
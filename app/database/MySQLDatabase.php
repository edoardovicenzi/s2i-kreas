<?php
require_once("IDatabase.php");
class MySQLDatabase implements IDatabase{
    private $host = "mysql";
    private $db_name = "kreas";
    private $username = "kreas";
    private $password = "kreasStart2Impact";

    public $conn;

    public function getConnection(){
        $this->conn = null;
        try {

            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $this->conn-> exec("set names utf8");

        } catch (PDOException $exception) {

            die("Errore di connessione: " . $exception->getMessage());

        }
        return $this->conn;
    }
}
?>

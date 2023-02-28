<?php
namespace  app\models;
use app\configs\Database;
use PDO;
class DB {

    public  $conn;

    function __construct() {
        $this->host_name = Database::HOST;
        $this->dbname = Database::NAME;
        $this->username = Database::USER_NAME;
        $this->password = Database::PASSWORD;
        try {
            $this->conn = new PDO("mysql:host=$this->host_name;dbname=$this->dbname", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

}

?>
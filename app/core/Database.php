<?php
namespace App\Core;

use PDO;
use PDOException;

class Database {
    private $host = "localhost";
    private $db_name = "mrnathanenglish";
    private $username = "root";
    private $password = "";
    private $conn;

    public function connect() {
        $this->conn = null;
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
        return $this->conn;
    }

    // Ajout de la méthode prepare
    public function prepare($query) {
        $this->connect(); // Assure la connexion si pas faite
        return $this->conn->prepare($query);
    }

    // Ajoute d'autres méthodes comme execute, fetch si besoin
    public function query($query, $params = []) {
        $stmt = $this->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
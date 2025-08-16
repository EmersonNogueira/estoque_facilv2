<?php

namespace models;
use PDO;
use PDOException;

class Model
{
    
    protected $pdo;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        $host = 'localhost'; // Nome do host MySQL fornecido
        $dbName = 'u471146656_estoqueccbj'; // Nome do banco de dados fornecido
        $username = 'u471146656_estoqueccbj'; // Nome do usuário MySQL fornecido
        $password = 'Estoque@ccbj400'; // Senha MySQL fornecida

        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8mb4", $username, $password);

            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
            die("Falha na conexão: " . $e->getMessage());
        }
    }
    
}

?>

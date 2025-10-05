<?php

namespace App\Database;

use PDO;
use PDOException;
use PDOStatement;

/**
 * Singleton class

 * Instances only one database connection per request

 * Uses PDO Exception mode, and Fetch Assoc by default
 */
class Database{

    private static $instance = null;
    private $connection;

    private function __construct(){
        try {
            $host = $_ENV["DB_HOST"];
            $dbname = $_ENV["DB_NAME"];
            $user = $_ENV["DB_USER"];
            $pass = $_ENV["DB_PASS"];
            
            $this->connection = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            
        } catch (PDOException $e) {
            throw new PDOException("Error de conexión: " . $e->getMessage());
        
        }
    }

    public static function getInstance(): self {

        // Following singleton pattern, instanciates itself
        if( self::$instance === null ){
            self::$instance = new self();
            
        }

        return self::$instance;
    }

    public function getConnection(): PDO {

        return $this->connection;
    }
    
    /**
     * Helper method to do querys using the current instance
     * 
     * @throws PDOException
     */
    public function query(string $sql, array $params = []): PDOStatement {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        
        return $stmt;
    }

}

?>
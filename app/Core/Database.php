<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $pdo;
    private $dsn;
    private $username;
    private $password;
    private $options;
    
    private function __construct()
    {
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $database = $_ENV['DB_NAME'] ?? 'grupokey_platform';
        $this->username = $_ENV['DB_USER'] ?? 'root';
        $this->password = $_ENV['DB_PASS'] ?? '';
        
        $this->dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
        
        $this->options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 5,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET SESSION wait_timeout=300, interactive_timeout=300"
        ];
        
        try {
            set_time_limit(10);
            $this->connect();
            set_time_limit(30);
        } catch (PDOException $e) {
            set_time_limit(30);
            
            if (function_exists('write_log')) {
                write_log("Erro de conexão DB: Host={$host}, Port={$port}, DB={$database}, User={$this->username}, Erro: " . $e->getMessage(), 'database.log');
            }
            
            $errorMsg = "Erro na conexão com o banco de dados";
            if (defined('APP_DEBUG') && APP_DEBUG) {
                $errorMsg .= ": {$e->getMessage()} (Host: {$host}:{$port}, Database: {$database})";
            } else {
                $errorMsg .= ". Verifique as configurações do banco de dados.";
            }
            
            throw new PDOException($errorMsg);
        }
    }

    private function connect(): void
    {
        $this->pdo = new PDO($this->dsn, $this->username, $this->password, $this->options);
        
        if ($this->pdo->inTransaction()) {
            try {
                $this->pdo->rollBack();
            } catch (\PDOException $e) {
                // Ignorar erro de rollback se não houver transação
            }
        }
    }

    private function reconnect(): void
    {
        $this->pdo = null;
        $this->connect();
    }

    private function isGoneAway(PDOException $e): bool
    {
        $code = (string) ($e->errorInfo[1] ?? $e->getCode());
        $message = $e->getMessage();

        return in_array($code, ['2006', '2013'], true)
            || stripos($message, 'server has gone away') !== false
            || stripos($message, 'Lost connection') !== false;
    }

    private function isInTransaction(): bool
    {
        try {
            return $this->pdo && $this->pdo->inTransaction();
        } catch (\Throwable $e) {
            return false;
        }
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection()
    {
        return $this->pdo;
    }
    
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            if ($this->isGoneAway($e) && !$this->isInTransaction()) {
                $this->reconnect();
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                return $stmt;
            }
            throw $e;
        }
    }
    
    public function fetch($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    public function fetchAll($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }
    
    public function beginTransaction()
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
        return $this->pdo->beginTransaction();
    }
    
    public function commit()
    {
        if (!$this->pdo->inTransaction()) {
            return true;
        }
        try {
            return $this->pdo->commit();
        } catch (\PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
    
    public function rollback()
    {
        if (!$this->pdo->inTransaction()) {
            return true;
        }
        return $this->pdo->rollBack();
    }
}

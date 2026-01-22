<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $pdo;
    
    private function __construct()
    {
        // Usar configurações do ambiente dinâmico
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $database = $_ENV['DB_NAME'] ?? 'grupokey_platform';
        $username = $_ENV['DB_USER'] ?? 'root';
        $password = $_ENV['DB_PASS'] ?? '';
        
        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 5, // Timeout de 5 segundos para conexão
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET SESSION wait_timeout=30, interactive_timeout=30"
        ];
        
        try {
            // Definir timeout máximo de execução para conexão
            set_time_limit(10);
            
            // Log da tentativa de conexão (apenas em modo debug)
            if (defined('APP_DEBUG') && APP_DEBUG && function_exists('write_log')) {
                write_log("Tentando conectar ao banco: {$host}:{$port}/{$database} (usuário: {$username})", 'database.log');
            }
            
            $this->pdo = new PDO($dsn, $username, $password, $options);
            
            // Garantir que não há transação aberta
            if ($this->pdo->inTransaction()) {
                try {
                    $this->pdo->rollBack();
                } catch (\PDOException $e) {
                    // Ignorar erro de rollback se não houver transação
                }
            }
            
            // Restaurar timeout padrão
            set_time_limit(30);
        } catch (PDOException $e) {
            set_time_limit(30); // Restaurar timeout em caso de erro
            
            // Log detalhado do erro
            if (function_exists('write_log')) {
                write_log("Erro de conexão DB: Host={$host}, Port={$port}, DB={$database}, User={$username}, Erro: " . $e->getMessage(), 'database.log');
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
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
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
        // Se já estiver em transação, fazer rollback primeiro
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
        return $this->pdo->beginTransaction();
    }
    
    public function commit()
    {
        if (!$this->pdo->inTransaction()) {
            return true; // Já não está em transação
        }
        try {
            return $this->pdo->commit();
        } catch (\PDOException $e) {
            // Se o commit falhar, tentar rollback
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
    
    public function rollback()
    {
        if (!$this->pdo->inTransaction()) {
            return true; // Já não está em transação
        }
        return $this->pdo->rollBack();
    }
}

<?php
/**
 * Script para executar a migration do sistema WhatsApp
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../app/Core/AutoConfig.php';
require_once __DIR__ . '/../../app/Core/Database.php';

use App\Core\AutoConfig;
use App\Core\Database;

// Inicializar configuração automática
AutoConfig::init();

// Configurar timezone
date_default_timezone_set('America/Sao_Paulo');

echo "=== EXECUTANDO MIGRATION DO SISTEMA WHATSAPP ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $db = Database::getInstance();
    $migrationFile = __DIR__ . '/../migrations/031_create_whatsapp_system_tables.sql';
    
    if (!file_exists($migrationFile)) {
        echo "ERRO: Arquivo de migration não encontrado: {$migrationFile}\n";
        exit(1);
    }
    
    $sql = file_get_contents($migrationFile);
    
    // Remover comentários de linha
    $sql = preg_replace('/--.*$/m', '', $sql);
    
    // Dividir em múltiplas queries
    $queries = [];
    $currentQuery = '';
    
    // Processar linha por linha para melhor controle
    $lines = explode("\n", $sql);
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Pular linhas vazias e comentários
        if (empty($line) || preg_match('/^--/', $line)) {
            continue;
        }
        
        $currentQuery .= $line . "\n";
        
        // Se a linha termina com ;, é o fim de uma query
        if (substr(rtrim($line), -1) === ';') {
            $query = trim($currentQuery);
            if (!empty($query) && strlen($query) > 10) {
                $queries[] = $query;
            }
            $currentQuery = '';
        }
    }
    
    // Adicionar última query se não terminou com ;
    if (!empty(trim($currentQuery))) {
        $queries[] = trim($currentQuery);
    }
    
    echo "Total de queries a executar: " . count($queries) . "\n\n";
    
    $successCount = 0;
    $errorCount = 0;
    $skippedCount = 0;
    
    foreach ($queries as $index => $query) {
        if (empty(trim($query))) {
            continue;
        }
        
        // Identificar tipo de query
        $queryType = 'UNKNOWN';
        if (stripos($query, 'CREATE TABLE') !== false) {
            preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $query, $matches);
            $tableName = $matches[1] ?? 'unknown';
            $queryType = "CREATE TABLE {$tableName}";
        } elseif (stripos($query, 'ALTER TABLE') !== false) {
            preg_match('/ALTER TABLE.*?`?(\w+)`?/i', $query, $matches);
            $tableName = $matches[1] ?? 'unknown';
            $queryType = "ALTER TABLE {$tableName}";
        } elseif (stripos($query, 'INSERT INTO') !== false) {
            preg_match('/INSERT INTO.*?`?(\w+)`?/i', $query, $matches);
            $tableName = $matches[1] ?? 'unknown';
            $queryType = "INSERT INTO {$tableName}";
        }
        
        try {
            $db->query($query);
            echo "✓ {$queryType}\n";
            $successCount++;
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
            
            // Ignorar erros de tabela/campo já existente
            if (strpos($errorMsg, 'already exists') !== false || 
                strpos($errorMsg, 'Duplicate') !== false ||
                strpos($errorMsg, 'Duplicate column name') !== false) {
                echo "⊘ {$queryType} (já existe, ignorado)\n";
                $skippedCount++;
            } else {
                echo "✗ ERRO em {$queryType}: {$errorMsg}\n";
                $errorCount++;
                
                // Mostrar parte da query para debug
                echo "   Query: " . substr($query, 0, 100) . "...\n";
            }
        }
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "RESUMO:\n";
    echo str_repeat("=", 50) . "\n";
    echo "✓ Sucesso: {$successCount}\n";
    echo "⊘ Ignorados (já existem): {$skippedCount}\n";
    echo "✗ Erros: {$errorCount}\n";
    
    if ($errorCount === 0) {
        echo "\n✅ MIGRATION EXECUTADA COM SUCESSO!\n";
        
        // Verificar se as tabelas foram criadas
        echo "\nVerificando tabelas criadas...\n";
        $whatsappTables = [
            'whatsapp_instances',
            'whatsapp_contacts',
            'whatsapp_queues',
            'whatsapp_queue_users',
            'whatsapp_conversations',
            'whatsapp_attendances',
            'whatsapp_messages',
            'whatsapp_user_sessions'
        ];
        
        $allExist = true;
        foreach ($whatsappTables as $table) {
            try {
                $result = $db->fetch("SHOW TABLES LIKE '{$table}'");
                if (!empty($result)) {
                    echo "  ✓ {$table}\n";
                } else {
                    echo "  ✗ {$table} (não encontrada)\n";
                    $allExist = false;
                }
            } catch (\Exception $e) {
                echo "  ✗ {$table} (erro ao verificar)\n";
                $allExist = false;
            }
        }
        
        if ($allExist) {
            echo "\n✅ Todas as tabelas foram criadas com sucesso!\n";
        }
    } else {
        echo "\n⚠ ATENÇÃO: Houve erros durante a migration. Verifique os erros acima.\n";
    }
    
} catch (\Exception $e) {
    echo "ERRO FATAL: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}


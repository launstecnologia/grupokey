<?php
/**
 * Script para verificar se as tabelas do WhatsApp foram criadas
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

echo "=== VERIFICAÇÃO DAS TABELAS WHATSAPP ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $db = Database::getInstance();
    
    // Lista de tabelas esperadas
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
    
    $allTablesExist = true;
    $existingTables = [];
    $missingTables = [];
    
    echo "Verificando tabelas do WhatsApp:\n";
    echo str_repeat("-", 50) . "\n";
    
    foreach ($whatsappTables as $table) {
        try {
            $result = $db->fetch("SHOW TABLES LIKE '{$table}'");
            if (!empty($result)) {
                // Verificar estrutura
                $columns = $db->fetchAll("SHOW COLUMNS FROM {$table}");
                $columnCount = count($columns);
                
                echo "✓ {$table} - EXISTE ({$columnCount} colunas)\n";
                $existingTables[] = $table;
            } else {
                echo "✗ {$table} - NÃO EXISTE\n";
                $missingTables[] = $table;
                $allTablesExist = false;
            }
        } catch (\Exception $e) {
            echo "✗ {$table} - ERRO: " . $e->getMessage() . "\n";
            $missingTables[] = $table;
            $allTablesExist = false;
        }
    }
    
    echo "\n" . str_repeat("-", 50) . "\n";
    
    // Verificar campos na tabela users
    echo "\nVerificando campos adicionados na tabela 'users':\n";
    echo str_repeat("-", 50) . "\n";
    
    try {
        $columns = $db->fetchAll("SHOW COLUMNS FROM users");
        $columnNames = array_column($columns, 'Field');
        
        $requiredFields = ['whatsapp_role', 'whatsapp_is_active', 'whatsapp_max_chats'];
        $fieldsExist = true;
        
        foreach ($requiredFields as $field) {
            if (in_array($field, $columnNames)) {
                $column = array_filter($columns, function($c) use ($field) {
                    return $c['Field'] === $field;
                });
                $column = reset($column);
                echo "✓ Campo '{$field}' existe (Tipo: {$column['Type']})\n";
            } else {
                echo "✗ Campo '{$field}' NÃO existe\n";
                $fieldsExist = false;
            }
        }
    } catch (\Exception $e) {
        echo "ERRO ao verificar campos: " . $e->getMessage() . "\n";
        $fieldsExist = false;
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "RESUMO:\n";
    echo str_repeat("=", 50) . "\n";
    
    if ($allTablesExist && $fieldsExist) {
        echo "✅ SUCESSO: Todas as tabelas e campos foram criados!\n";
        echo "\nTabelas criadas: " . count($existingTables) . "/" . count($whatsappTables) . "\n";
        echo "Campos adicionados: 3/3\n";
    } else {
        echo "⚠ ATENÇÃO: Algumas tabelas ou campos estão faltando.\n";
        
        if (!empty($missingTables)) {
            echo "\nTabelas faltando (" . count($missingTables) . "):\n";
            foreach ($missingTables as $table) {
                echo "  - {$table}\n";
            }
        }
        
        if (!$fieldsExist) {
            echo "\nExecute a migration para adicionar os campos faltantes.\n";
        }
        
        echo "\nPara executar a migration, use:\n";
        echo "php database/scripts/run_migrations.php\n";
    }
    
} catch (\Exception $e) {
    echo "ERRO FATAL: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}


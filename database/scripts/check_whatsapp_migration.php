<?php
/**
 * Script para verificar se a migration do WhatsApp pode ser executada
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

echo "=== VERIFICAÇÃO DA MIGRATION WHATSAPP ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $db = Database::getInstance();
    
    // Verificar se a tabela users existe
    $usersExists = false;
    try {
        $result = $db->fetch("SHOW TABLES LIKE 'users'");
        $usersExists = !empty($result);
    } catch (\Exception $e) {
        echo "ERRO: Não foi possível verificar a tabela users\n";
        exit(1);
    }
    
    if (!$usersExists) {
        echo "ERRO: A tabela 'users' não existe. Execute as migrations anteriores primeiro.\n";
        exit(1);
    }
    
    echo "✓ Tabela 'users' existe\n";
    
    // Verificar se os campos já existem
    $columns = $db->fetchAll("SHOW COLUMNS FROM users");
    $columnNames = array_column($columns, 'Field');
    
    $fieldsToAdd = ['whatsapp_role', 'whatsapp_is_active', 'whatsapp_max_chats'];
    $fieldsMissing = [];
    
    foreach ($fieldsToAdd as $field) {
        if (!in_array($field, $columnNames)) {
            $fieldsMissing[] = $field;
        } else {
            echo "✓ Campo '{$field}' já existe na tabela users\n";
        }
    }
    
    if (!empty($fieldsMissing)) {
        echo "\n⚠ Campos que serão adicionados: " . implode(', ', $fieldsMissing) . "\n";
    }
    
    // Verificar se as tabelas do WhatsApp já existem
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
    
    $existingTables = [];
    $missingTables = [];
    
    foreach ($whatsappTables as $table) {
        try {
            $result = $db->fetch("SHOW TABLES LIKE '{$table}'");
            if (!empty($result)) {
                $existingTables[] = $table;
                echo "⚠ Tabela '{$table}' já existe\n";
            } else {
                $missingTables[] = $table;
            }
        } catch (\Exception $e) {
            $missingTables[] = $table;
        }
    }
    
    if (!empty($existingTables)) {
        echo "\n⚠ ATENÇÃO: Algumas tabelas já existem. A migration pode falhar se tentar criar novamente.\n";
        echo "Tabelas existentes: " . implode(', ', $existingTables) . "\n";
    }
    
    if (!empty($missingTables)) {
        echo "\n✓ Tabelas que serão criadas: " . implode(', ', $missingTables) . "\n";
    }
    
    echo "\n=== VERIFICAÇÃO CONCLUÍDA ===\n";
    echo "\nPara executar a migration, use:\n";
    echo "php database/scripts/run_migrations.php\n";
    echo "\nOu execute manualmente o arquivo SQL:\n";
    echo "database/migrations/031_create_whatsapp_system_tables.sql\n";
    
} catch (\Exception $e) {
    echo "ERRO FATAL: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}


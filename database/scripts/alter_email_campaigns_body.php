<?php
/**
 * Script para alterar a coluna body de TEXT para MEDIUMTEXT
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

echo "=== ALTERANDO COLUNA BODY DE EMAIL_CAMPAIGNS ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $db = Database::getInstance();
    
    // Verificar tipo atual da coluna
    $sql = "SHOW COLUMNS FROM email_campaigns WHERE Field = 'body'";
    $columnInfo = $db->fetch($sql);
    
    if ($columnInfo) {
        echo "Tipo atual da coluna 'body': " . $columnInfo['Type'] . "\n";
    }
    
    echo "\nAlterando coluna body para MEDIUMTEXT...\n";
    
    // Alterar coluna
    $alterSql = "ALTER TABLE email_campaigns MODIFY COLUMN body MEDIUMTEXT NOT NULL";
    $db->query($alterSql);
    
    echo "✓ Coluna alterada com sucesso!\n\n";
    
    // Verificar novo tipo
    $columnInfo = $db->fetch($sql);
    if ($columnInfo) {
        echo "Novo tipo da coluna 'body': " . $columnInfo['Type'] . "\n";
    }
    
    echo "\n=== ALTERAÇÃO CONCLUÍDA ===\n";
    
} catch (\Exception $e) {
    echo "ERRO FATAL: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}


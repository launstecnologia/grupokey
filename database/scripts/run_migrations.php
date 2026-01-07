<?php
/**
 * Script para executar migrations pendentes
 * 
 * Uso: php database/scripts/run_migrations.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../app/Core/AutoConfig.php';

App\Core\AutoConfig::init();

use App\Core\Database;

$db = Database::getInstance();
$migrationsDir = __DIR__ . '/../migrations';

// Migrations do CRM que precisam ser executadas
$crmMigrations = [
    '024_create_crm_pipelines_table.sql',
    '025_create_crm_stages_table.sql',
    '026_create_crm_deals_table.sql',
    '027_create_crm_deal_activities_table.sql',
    '028_create_crm_deal_tasks_table.sql',
    '029_create_crm_notifications_table.sql'
];

echo "=== Executando Migrations do CRM ===\n\n";

foreach ($crmMigrations as $migration) {
    $filePath = $migrationsDir . '/' . $migration;
    
    if (!file_exists($filePath)) {
        echo "❌ Arquivo não encontrado: $migration\n";
        continue;
    }
    
    $sql = file_get_contents($filePath);
    
    // Dividir em múltiplas queries se necessário
    $queries = array_filter(
        array_map('trim', explode(';', $sql)),
        function($query) {
            return !empty($query) && !preg_match('/^--/', $query);
        }
    );
    
    try {
        $db->beginTransaction();
        
        foreach ($queries as $query) {
            if (!empty(trim($query))) {
                $db->query($query);
            }
        }
        
        $db->commit();
        echo "✅ $migration executada com sucesso!\n";
    } catch (Exception $e) {
        $db->rollback();
        echo "❌ Erro ao executar $migration: " . $e->getMessage() . "\n";
        
        // Se a tabela já existe, continuar
        if (strpos($e->getMessage(), 'already exists') !== false || 
            strpos($e->getMessage(), 'Duplicate') !== false) {
            echo "   (Tabela já existe, continuando...)\n";
        } else {
            echo "\n";
            exit(1);
        }
    }
}

echo "\n=== Migrations concluídas! ===\n";


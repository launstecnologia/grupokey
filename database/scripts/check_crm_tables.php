<?php
/**
 * Script para verificar todas as tabelas do CRM
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../app/Core/AutoConfig.php';

App\Core\AutoConfig::init();

use App\Core\Database;

$db = Database::getInstance();

echo "=== Verificando tabelas do CRM ===\n\n";

$tables = [
    'crm_pipelines',
    'crm_stages',
    'crm_deals',
    'crm_deal_activities',
    'crm_deal_tasks',
    'crm_notifications'
];

foreach ($tables as $table) {
    try {
        $result = $db->fetch("SHOW TABLES LIKE '$table'");
        if ($result) {
            // Contar registros
            $count = $db->fetch("SELECT COUNT(*) as count FROM `$table`");
            echo "✅ $table existe (" . ($count['count'] ?? 0) . " registros)\n";
        } else {
            echo "❌ $table NÃO existe\n";
        }
    } catch (Exception $e) {
        echo "❌ Erro ao verificar $table: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Verificação concluída! ===\n";


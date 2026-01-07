<?php
/**
 * Script para verificar anexos de campanhas de email
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

$campaignId = $argv[1] ?? 9;

echo "=== VERIFICANDO ANEXOS DA CAMPANHA {$campaignId} ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $db = Database::getInstance();
    
    // Buscar anexos
    $attachments = $db->fetchAll(
        "SELECT * FROM email_campaign_attachments WHERE campaign_id = ? ORDER BY created_at ASC",
        [$campaignId]
    );
    
    echo "Total de anexos encontrados: " . count($attachments) . "\n\n";
    
    if (empty($attachments)) {
        echo "Nenhum anexo encontrado para esta campanha.\n";
    } else {
        foreach ($attachments as $index => $attachment) {
            echo "Anexo #" . ($index + 1) . ":\n";
            echo "  ID: {$attachment['id']}\n";
            echo "  Nome: {$attachment['file_name']}\n";
            echo "  Caminho: {$attachment['file_path']}\n";
            echo "  Tamanho: {$attachment['file_size']} bytes\n";
            echo "  Tipo: {$attachment['file_type']}\n";
            echo "  Criado em: {$attachment['created_at']}\n";
            
            // Verificar se o arquivo existe
            if (file_exists($attachment['file_path'])) {
                echo "  Status: ✓ Arquivo existe no disco\n";
            } else {
                echo "  Status: ✗ Arquivo NÃO existe no disco\n";
            }
            echo "\n";
        }
    }
    
    // Verificar logs recentes
    $logFile = __DIR__ . '/../../storage/logs/email-marketing.log';
    if (file_exists($logFile)) {
        echo "=== ÚLTIMAS LINHAS DO LOG ===\n";
        $lines = file($logFile);
        $lastLines = array_slice($lines, -20);
        foreach ($lastLines as $line) {
            echo $line;
        }
    }
    
} catch (\Exception $e) {
    echo "ERRO FATAL: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}


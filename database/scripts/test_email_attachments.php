<?php
/**
 * Script para testar e verificar anexos de campanhas de email marketing
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../app/Core/AutoConfig.php';
require_once __DIR__ . '/../../app/Core/Database.php';
require_once __DIR__ . '/../../app/Models/EmailCampaign.php';

use App\Core\AutoConfig;
use App\Core\Database;
use App\Models\EmailCampaign;

// Inicializar configuração automática
AutoConfig::init();

// Configurar timezone
date_default_timezone_set('America/Sao_Paulo');

echo "=== VERIFICAÇÃO DE ANEXOS DE EMAIL MARKETING ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $db = Database::getInstance();
    $campaignModel = new EmailCampaign();
    
    // Buscar todas as campanhas com anexos
    $sql = "SELECT DISTINCT campaign_id FROM email_campaign_attachments";
    $campaignsWithAttachments = $db->fetchAll($sql);
    
    if (empty($campaignsWithAttachments)) {
        echo "Nenhuma campanha com anexos encontrada.\n";
        exit(0);
    }
    
    echo "Encontradas " . count($campaignsWithAttachments) . " campanha(s) com anexos:\n\n";
    
    $basePath = dirname(__DIR__, 2);
    
    foreach ($campaignsWithAttachments as $campaignData) {
        $campaignId = $campaignData['campaign_id'];
        $campaign = $campaignModel->findById($campaignId);
        
        echo "=== CAMPANHA ID: {$campaignId} ===\n";
        echo "Nome: " . ($campaign['name'] ?? 'N/A') . "\n";
        
        $attachments = $campaignModel->getAttachments($campaignId);
        echo "Total de anexos: " . count($attachments) . "\n\n";
        
        foreach ($attachments as $index => $attachment) {
            echo "  Anexo #" . ($index + 1) . ":\n";
            echo "    ID: {$attachment['id']}\n";
            echo "    Nome: {$attachment['file_name']}\n";
            echo "    Caminho no banco: {$attachment['file_path']}\n";
            
            $filePath = $attachment['file_path'];
            $absolutePath = $filePath;
            
            // Verificar se existe
            if (file_exists($absolutePath)) {
                echo "    ✓ Arquivo existe no caminho original\n";
                $fileSize = filesize($absolutePath);
                echo "    Tamanho: {$fileSize} bytes\n";
            } else {
                // Tentar caminhos alternativos
                echo "    ✗ Arquivo não existe no caminho original\n";
                
                // Tentar caminho relativo
                if (strpos($filePath, 'storage') === 0) {
                    $absolutePath = $basePath . DIRECTORY_SEPARATOR . $filePath;
                    if (file_exists($absolutePath)) {
                        echo "    ✓ Arquivo encontrado em: {$absolutePath}\n";
                        $fileSize = filesize($absolutePath);
                        echo "    Tamanho: {$fileSize} bytes\n";
                    } else {
                        echo "    ✗ Arquivo não encontrado em caminho alternativo\n";
                    }
                } else {
                    // Tentar adicionar basePath
                    $absolutePath = $basePath . DIRECTORY_SEPARATOR . ltrim($filePath, '/\\');
                    if (file_exists($absolutePath)) {
                        echo "    ✓ Arquivo encontrado em: {$absolutePath}\n";
                        $fileSize = filesize($absolutePath);
                        echo "    Tamanho: {$fileSize} bytes\n";
                    } else {
                        echo "    ✗ Arquivo não encontrado em nenhum caminho testado\n";
                    }
                }
            }
            
            echo "\n";
        }
        
        echo "\n";
    }
    
    echo "=== VERIFICAÇÃO CONCLUÍDA ===\n";
    
} catch (\Exception $e) {
    echo "ERRO FATAL: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}


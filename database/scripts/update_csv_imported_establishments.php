<?php
/**
 * Script para atualizar estabelecimentos importados do CSV
 * Define produto como PagBank e status como APROVED
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

echo "=== ATUALIZAÇÃO DE ESTABELECIMENTOS IMPORTADOS DO CSV ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $db = Database::getInstance();
    
    // Buscar todos os estabelecimentos que não têm produto definido ou estão com status PENDING
    // ou foram criados nas últimas 48 horas (para pegar todos do CSV)
    $sql = "SELECT id, nome_completo, nome_fantasia, cpf, cnpj, produto, status, created_at
            FROM establishments 
            WHERE (produto IS NULL 
                   OR status = 'PENDING'
                   OR created_at >= DATE_SUB(NOW(), INTERVAL 48 HOUR))
            ORDER BY created_at DESC";
    
    $establishments = $db->fetchAll($sql);
    
    $total = count($establishments);
    
    if ($total === 0) {
        echo "Nenhum estabelecimento encontrado para atualizar.\n";
        exit(0);
    }
    
    echo "Encontrados $total estabelecimento(s) para atualizar:\n\n";
    
    // Mostrar alguns exemplos
    $examples = array_slice($establishments, 0, min(10, $total));
    foreach ($examples as $est) {
        echo "  ID: {$est['id']} - Nome: '{$est['nome_completo']}' | Produto: " . ($est['produto'] ?: 'NULL') . " | Status: {$est['status']}\n";
    }
    
    if ($total > 10) {
        echo "  ... e mais " . ($total - 10) . " registro(s)\n";
    }
    
    echo "\nIniciando atualização...\n\n";
    
    // Atualizar em lote
    $ids = array_column($establishments, 'id');
    $idsPlaceholder = implode(',', array_fill(0, count($ids), '?'));
    
    $db->beginTransaction();
    
    try {
        // Atualizar status e produto
        $sqlUpdate = "UPDATE establishments 
                     SET status = 'APPROVED', 
                         produto = 'PAGSEGURO_MP',
                         updated_at = NOW() 
                     WHERE id IN ($idsPlaceholder)";
        
        $result = $db->query($sqlUpdate, $ids);
        
        if ($result) {
            // Verificar se precisamos criar registros na tabela establishment_pagbank
            // Primeiro, verificar quais já têm registro
            $sqlCheck = "SELECT establishment_id FROM establishment_pagbank WHERE establishment_id IN ($idsPlaceholder)";
            $existing = $db->fetchAll($sqlCheck, $ids);
            $existingIds = array_column($existing, 'establishment_id');
            
            // Criar registros na tabela establishment_pagbank para os que não têm
            $newIds = array_diff($ids, $existingIds);
            
            if (count($newIds) > 0) {
                $inserted = 0;
                foreach ($newIds as $id) {
                    try {
                        $pagbankId = uniqid('pagbank_');
                        $sqlInsert = "INSERT INTO establishment_pagbank (id, establishment_id, created_at, updated_at) 
                                     VALUES (?, ?, NOW(), NOW())";
                        $db->query($sqlInsert, [$pagbankId, $id]);
                        $inserted++;
                    } catch (\Exception $e) {
                        // Ignorar erros de duplicação
                    }
                }
                echo "  ✓ Criados $inserted registros na tabela establishment_pagbank\n";
            }
            
            $db->commit();
            
            echo "  ✓ Atualizados $total estabelecimentos com sucesso\n";
            echo "\n=== ATUALIZAÇÃO CONCLUÍDA ===\n";
            echo "Total de registros atualizados: $total\n";
            echo "Status: APROVED\n";
            echo "Produto: PAGSEGURO_MP (PagBank)\n";
            
        } else {
            throw new Exception('Falha ao atualizar registros');
        }
        
    } catch (\Exception $e) {
        $db->rollback();
        throw $e;
    }
    
} catch (\Exception $e) {
    echo "ERRO FATAL: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}


<?php
/**
 * Script para excluir estabelecimentos que não possuem
 * CPF, nome completo, razão social ou CNPJ
 * É necessário ter pelo menos um desses 4 campos
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

echo "=== EXCLUSÃO DE ESTABELECIMENTOS INVÁLIDOS ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $db = Database::getInstance();
    
    // Buscar estabelecimentos que não têm nenhum dos 4 campos obrigatórios
    // Inclui também registros que só têm valores padrão gerados automaticamente
    $sql = "SELECT id, nome_completo, razao_social, cpf, cnpj 
            FROM establishments 
            WHERE (
                (nome_completo IS NULL OR nome_completo = '' OR nome_completo = 'N/A' OR nome_completo LIKE 'Cliente %')
                AND (razao_social IS NULL OR razao_social = '')
                AND (cpf IS NULL OR cpf = '' OR LENGTH(TRIM(cpf)) < 11)
                AND (cnpj IS NULL OR cnpj = '' OR LENGTH(TRIM(cnpj)) < 14)
            )
            AND NOT (
                (cpf IS NOT NULL AND cpf != '' AND LENGTH(TRIM(cpf)) >= 11)
                OR (cnpj IS NOT NULL AND cnpj != '' AND LENGTH(TRIM(cnpj)) >= 14)
                OR (nome_completo IS NOT NULL AND nome_completo != '' AND nome_completo != 'N/A' AND nome_completo NOT LIKE 'Cliente %')
                OR (razao_social IS NOT NULL AND razao_social != '')
            )";
    
    $invalidEstablishments = $db->fetchAll($sql);
    
    $total = count($invalidEstablishments);
    
    if ($total === 0) {
        echo "Nenhum estabelecimento inválido encontrado.\n";
        exit(0);
    }
    
    echo "Encontrados $total estabelecimento(s) inválido(s) para exclusão:\n\n";
    
    // Mostrar alguns exemplos
    $examples = array_slice($invalidEstablishments, 0, min(10, $total));
    foreach ($examples as $est) {
        echo "  ID: {$est['id']} - Nome: '{$est['nome_completo']}' | Razão: '{$est['razao_social']}' | CPF: '{$est['cpf']}' | CNPJ: '{$est['cnpj']}'\n";
    }
    
    if ($total > 10) {
        echo "  ... e mais " . ($total - 10) . " registro(s)\n";
    }
    
    echo "\n";
    echo "ATENÇÃO: Esta operação irá excluir $total registro(s) permanentemente!\n";
    echo "Iniciando exclusão...\n\n";
    
    $deleted = 0;
    $errors = 0;
    
    $db->beginTransaction();
    
    try {
        foreach ($invalidEstablishments as $est) {
            $id = $est['id'];
            
            try {
                // Primeiro, deletar produtos associados (se houver)
                $deleteProducts = [
                    "DELETE FROM establishment_cdc WHERE establishment_id = ?",
                    "DELETE FROM establishment_cdx_evo WHERE establishment_id = ?",
                    "DELETE FROM establishment_google WHERE establishment_id = ?",
                    "DELETE FROM establishment_membro_key WHERE establishment_id = ?",
                    "DELETE FROM establishment_pagbank WHERE establishment_id = ?",
                    "DELETE FROM establishment_outros WHERE establishment_id = ?",
                    "DELETE FROM establishment_products WHERE establishment_id = ?",
                    "DELETE FROM documents WHERE establishment_id = ?",
                    "DELETE FROM establishment_approvals WHERE establishment_id = ?"
                ];
                
                foreach ($deleteProducts as $sqlDelete) {
                    try {
                        $db->query($sqlDelete, [$id]);
                    } catch (\Exception $e) {
                        // Ignorar erros de tabelas que podem não existir
                    }
                }
                
                // Deletar o estabelecimento
                $sqlDelete = "DELETE FROM establishments WHERE id = ?";
                $result = $db->query($sqlDelete, [$id]);
                
                if ($result) {
                    $deleted++;
                    echo "  ✓ Excluído ID: $id\n";
                } else {
                    $errors++;
                    echo "  ✗ Erro ao excluir ID: $id\n";
                }
                
            } catch (\Exception $e) {
                $errors++;
                echo "  ✗ Erro ao excluir ID: $id - {$e->getMessage()}\n";
            }
        }
        
        $db->commit();
        
        echo "\n=== EXCLUSÃO CONCLUÍDA ===\n";
        echo "Total de registros encontrados: $total\n";
        echo "Excluídos com sucesso: $deleted\n";
        echo "Erros: $errors\n";
        
    } catch (\Exception $e) {
        $db->rollback();
        throw $e;
    }
    
} catch (\Exception $e) {
    echo "ERRO FATAL: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}


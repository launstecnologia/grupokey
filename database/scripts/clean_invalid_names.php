<?php
/**
 * Script para limpar nomes inválidos dos estabelecimentos
 * Remove "Cliente sem identificação" e outros valores padrão genéricos
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

echo "=== EXCLUSÃO DE REGISTROS COM NOMES INVÁLIDOS ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $db = Database::getInstance();
    
    // Buscar estabelecimentos com nomes inválidos
    $sql = "SELECT id, nome_completo, nome_fantasia, razao_social, cpf, cnpj 
            FROM establishments 
            WHERE nome_completo = 'Cliente sem identificação'
               OR nome_fantasia = 'Cliente sem identificação'
               OR nome_completo LIKE 'Cliente CPF %'
               OR nome_fantasia LIKE 'Cliente CPF %'
               OR nome_completo LIKE 'Cliente CNPJ %'
               OR nome_fantasia LIKE 'Cliente CNPJ %'";
    
    $invalidNames = $db->fetchAll($sql);
    
    $total = count($invalidNames);
    
    if ($total === 0) {
        echo "Nenhum registro com nome inválido encontrado.\n";
        exit(0);
    }
    
    echo "Encontrados $total estabelecimento(s) com nomes inválidos:\n\n";
    
    // Mostrar alguns exemplos
    $examples = array_slice($invalidNames, 0, min(10, $total));
    foreach ($examples as $est) {
        echo "  ID: {$est['id']} - Nome: '{$est['nome_completo']}' | Fantasia: '{$est['nome_fantasia']}' | CPF: '{$est['cpf']}' | CNPJ: '{$est['cnpj']}'\n";
    }
    
    if ($total > 10) {
        echo "  ... e mais " . ($total - 10) . " registro(s)\n";
    }
    
    echo "\nIniciando exclusão em lote...\n\n";
    
    // Coletar IDs para exclusão
    $ids = array_column($invalidNames, 'id');
    $idsPlaceholder = implode(',', array_fill(0, count($ids), '?'));
    
    $db->beginTransaction();
    
    try {
        // Deletar produtos associados primeiro (em lote)
        $deleteProducts = [
            "DELETE FROM establishment_cdc WHERE establishment_id IN ($idsPlaceholder)",
            "DELETE FROM establishment_cdx_evo WHERE establishment_id IN ($idsPlaceholder)",
            "DELETE FROM establishment_google WHERE establishment_id IN ($idsPlaceholder)",
            "DELETE FROM establishment_membro_key WHERE establishment_id IN ($idsPlaceholder)",
            "DELETE FROM establishment_pagbank WHERE establishment_id IN ($idsPlaceholder)",
            "DELETE FROM establishment_outros WHERE establishment_id IN ($idsPlaceholder)",
            "DELETE FROM establishment_products WHERE establishment_id IN ($idsPlaceholder)",
            "DELETE FROM documents WHERE establishment_id IN ($idsPlaceholder)",
            "DELETE FROM establishment_approvals WHERE establishment_id IN ($idsPlaceholder)"
        ];
        
        foreach ($deleteProducts as $sqlDelete) {
            try {
                $db->query($sqlDelete, $ids);
            } catch (\Exception $e) {
                // Ignorar erros de tabelas que podem não existir
            }
        }
        
        // Deletar os estabelecimentos em lote
        $sqlDelete = "DELETE FROM establishments WHERE id IN ($idsPlaceholder)";
        $result = $db->query($sqlDelete, $ids);
        
        if ($result) {
            $deleted = count($ids);
            echo "  ✓ Excluídos $deleted registros com sucesso\n";
        } else {
            throw new Exception('Falha ao excluir registros');
        }
        
        $db->commit();
        
        echo "\n=== EXCLUSÃO CONCLUÍDA ===\n";
        echo "Total de registros encontrados: $total\n";
        echo "Excluídos com sucesso: $deleted\n";
        
    } catch (\Exception $e) {
        $db->rollback();
        throw $e;
    }
    
} catch (\Exception $e) {
    echo "ERRO FATAL: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}


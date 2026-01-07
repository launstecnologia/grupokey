<?php

namespace App\Models;

use App\Core\Database;

class EstablishmentProduct
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    // CDX/EVO (antigo PagSeguro)
    public function createPagSeguro($establishmentId, $data)
    {
        return $this->createCdxEvo($establishmentId, $data);
    }
    
    public function updatePagSeguro($establishmentId, $data)
    {
        return $this->updateCdxEvo($establishmentId, $data);
    }
    
    public function getPagSeguro($establishmentId)
    {
        return $this->getCdxEvo($establishmentId);
    }
    
    // CDX/EVO - Nova tabela
    public function createCdxEvo($establishmentId, $data)
    {
        $id = uniqid();
        
        // Normalizar valores numéricos (tratar string vazia como null)
        $previsaoFaturamento = $this->normalizeNumericValue($data['previsao_faturamento'] ?? null);
        $valor = $this->normalizeNumericValue($data['valor'] ?? null);
        
        $sql = "INSERT INTO establishment_cdx_evo 
                (id, establishment_id, previsao_faturamento, tabela, modelo_maquininha, meio_pagamento, valor, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $params = [
            $id,
            $establishmentId,
            $previsaoFaturamento,
            $data['tabela'] ?? null,
            $data['modelo_maquininha'] ?? null,
            $data['meio_pagamento'] ?? null,
            $valor
        ];
        
        $this->db->query($sql, $params);
        return $id;
    }
    
    public function updateCdxEvo($establishmentId, $data)
    {
        $sql = "UPDATE establishment_cdx_evo SET 
                previsao_faturamento = ?, tabela = ?, modelo_maquininha = ?, meio_pagamento = ?, valor = ?, updated_at = NOW() 
                WHERE establishment_id = ?";
        
        // Normalizar valores numéricos (tratar string vazia como null)
        $previsaoFaturamento = $this->normalizeNumericValue($data['previsao_faturamento'] ?? null);
        $valor = $this->normalizeNumericValue($data['valor'] ?? null);
        
        $params = [
            $previsaoFaturamento,
            $data['tabela'] ?? null,
            $data['modelo_maquininha'] ?? null,
            $data['meio_pagamento'] ?? null,
            $valor,
            $establishmentId
        ];
        
        return $this->db->query($sql, $params);
    }
    
    public function getCdxEvo($establishmentId)
    {
        $sql = "SELECT * FROM establishment_cdx_evo WHERE establishment_id = ?";
        return $this->db->fetch($sql, [$establishmentId]);
    }
    
    // CDC (antigo Brasil Card)
    public function createBrasilCard($establishmentId, $data)
    {
        return $this->createCdc($establishmentId, $data);
    }
    
    public function updateBrasilCard($establishmentId, $data)
    {
        return $this->updateCdc($establishmentId, $data);
    }
    
    // CDC - Nova tabela
    public function createCdc($establishmentId, $data)
    {
        $id = uniqid();
        
        // Verificar se 'taxa' é um valor numérico ou texto (financeira)
        $taxa = $data['taxa'] ?? null;
        $financeira = null;
        
        // Tratar string vazia como null
        if ($taxa === '' || $taxa === null) {
            $taxa = null;
        } elseif (!is_numeric($taxa)) {
        // Se taxa não for numérico, é o nome da financeira
            $financeira = $taxa; // É o nome da financeira
            $taxa = null; // Não salvar texto no campo numérico
        } else {
            // Garantir que seja numérico (float)
            $taxa = (float) $taxa;
        }
        
        $sql = "INSERT INTO establishment_cdc 
                    (id, establishment_id, taxa, financeira, meio_pagamento, valor, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
        // Normalizar valor (tratar string vazia como null)
        $valor = $this->normalizeNumericValue($data['valor'] ?? null);
            
            $params = [
                $id,
                $establishmentId,
                $taxa,
                $financeira,
                $data['meio_pagamento'] ?? null,
            $valor
        ];
        
        $this->db->query($sql, $params);
        return $id;
    }
    
    public function updateCdc($establishmentId, $data)
    {
        // Verificar se 'taxa' é um valor numérico ou texto (financeira)
        $taxa = $data['taxa'] ?? null;
        $financeira = null;
        
        // Tratar string vazia como null
        if ($taxa === '' || $taxa === null) {
            $taxa = null;
        } elseif (!is_numeric($taxa)) {
        // Se taxa não for numérico, é o nome da financeira
            $financeira = $taxa; // É o nome da financeira
            $taxa = null; // Não salvar texto no campo numérico
        } else {
            // Garantir que seja numérico (float)
            $taxa = (float) $taxa;
        }
        
        $sql = "UPDATE establishment_cdc SET 
                    taxa = ?, financeira = ?, meio_pagamento = ?, valor = ?, updated_at = NOW() 
                    WHERE establishment_id = ?";
            
        // Normalizar valor (tratar string vazia como null)
        $valor = $this->normalizeNumericValue($data['valor'] ?? null);
            
            $params = [
                $taxa,
                $financeira,
                $data['meio_pagamento'] ?? null,
            $valor,
                $establishmentId
            ];
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Normaliza um valor numérico (converte string vazia para null, garante tipo numérico)
     */
    private function normalizeNumericValue($value)
    {
        if ($value === '' || $value === null) {
            return null;
        } elseif (is_numeric($value)) {
            return (float) $value;
        } else {
            return null; // Se não for numérico, usar null
        }
    }
    
    /**
     * Verifica se uma coluna existe em uma tabela
     */
    private function checkColumnExists($table, $column)
    {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM information_schema.COLUMNS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = ? 
                    AND COLUMN_NAME = ?";
            
            $result = $this->db->fetch($sql, [$table, $column]);
            return $result && $result['count'] > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function getBrasilCard($establishmentId)
    {
        return $this->getCdc($establishmentId);
    }
    
    public function getCdc($establishmentId)
    {
        $sql = "SELECT * FROM establishment_cdc WHERE establishment_id = ?";
        return $this->db->fetch($sql, [$establishmentId]);
    }
    
    // Outros Produtos - Método genérico que direciona para a tabela correta
    public function createOtherProduct($establishmentId, $productType, $data)
    {
        // prod-subaquirente é CDX/EVO (mesmo que prod-pagseguro)
        if ($productType === 'prod-subaquirente') {
            return $this->createCdxEvo($establishmentId, $data);
        }
        
        // Mapear productType para a tabela correta
        $tableMap = [
            'prod-google' => 'establishment_google',
            'prod-membro-key' => 'establishment_membro_key',
            'prod-pagbank' => 'establishment_pagbank',
            'prod-outros' => 'establishment_outros'
        ];
        
        $table = $tableMap[$productType] ?? null;
        
        if (!$table) {
            // Produto não mapeado - não usar tabela antiga que não existe mais
            error_log("AVISO: Tentativa de criar produto não mapeado: {$productType} para estabelecimento {$establishmentId}");
            throw new \Exception("Tipo de produto não suportado: {$productType}");
        }
        
        $id = uniqid();
        $valor = $this->normalizeNumericValue($data['valor'] ?? null);
        
        // PagBank tem campos adicionais como CDX/EVO
        if ($productType === 'prod-pagbank') {
            $previsaoFaturamento = $this->normalizeNumericValue($data['previsao_faturamento'] ?? null);
            $plan = !empty($data['plan']) ? (int)$data['plan'] : null;
            $sql = "INSERT INTO {$table} 
                    (id, establishment_id, previsao_faturamento, tabela, modelo_maquininha, meio_pagamento, valor, plan, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            $params = [
                $id,
                $establishmentId,
                $previsaoFaturamento,
                $data['tabela'] ?? null,
                $data['modelo_maquininha'] ?? null,
                $data['meio_pagamento'] ?? null,
                $valor,
                $plan
            ];
        } else {
            // Google, Membro Key, Outros - apenas meio_pagamento e valor
            $sql = "INSERT INTO {$table} 
                    (id, establishment_id, meio_pagamento, valor, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, NOW(), NOW())";
        $params = [
            $id,
            $establishmentId,
            $data['meio_pagamento'] ?? null,
                $valor
        ];
        }
        
        $this->db->query($sql, $params);
        return $id;
    }
    
    public function updateOtherProduct($establishmentId, $productType, $data)
    {
        // prod-subaquirente é CDX/EVO (mesmo que prod-pagseguro)
        if ($productType === 'prod-subaquirente') {
            return $this->updateCdxEvo($establishmentId, $data);
        }
        
        // Mapear productType para a tabela correta
        $tableMap = [
            'prod-google' => 'establishment_google',
            'prod-membro-key' => 'establishment_membro_key',
            'prod-pagbank' => 'establishment_pagbank',
            'prod-outros' => 'establishment_outros'
        ];
        
        $table = $tableMap[$productType] ?? null;
        
        if (!$table) {
            // Produto não mapeado - não usar tabela antiga que não existe mais
            error_log("AVISO: Tentativa de atualizar produto não mapeado: {$productType} para estabelecimento {$establishmentId}");
            throw new \Exception("Tipo de produto não suportado: {$productType}");
        }
        
        $valor = $this->normalizeNumericValue($data['valor'] ?? null);
        
        // PagBank tem campos adicionais
        if ($productType === 'prod-pagbank') {
            $previsaoFaturamento = $this->normalizeNumericValue($data['previsao_faturamento'] ?? null);
            $plan = !empty($data['plan']) ? (int)$data['plan'] : null;
            $sql = "UPDATE {$table} SET 
                    previsao_faturamento = ?, tabela = ?, modelo_maquininha = ?, meio_pagamento = ?, valor = ?, plan = ?, updated_at = NOW() 
                    WHERE establishment_id = ?";
            $params = [
                $previsaoFaturamento,
                $data['tabela'] ?? null,
                $data['modelo_maquininha'] ?? null,
                $data['meio_pagamento'] ?? null,
                $valor,
                $plan,
                $establishmentId
            ];
        } else {
            $sql = "UPDATE {$table} SET 
                    meio_pagamento = ?, valor = ?, updated_at = NOW() 
                    WHERE establishment_id = ?";
            $params = [
                $data['meio_pagamento'] ?? null,
                $valor,
                $establishmentId
            ];
        }
        
        return $this->db->query($sql, $params);
    }
    
    // Métodos legados para compatibilidade
    // Métodos legados removidos - tabela establishment_other_products não existe mais
    // Use os métodos específicos: createGoogle, createMembroKey, createPagBank, createOutros
    
    public function getOtherProducts($establishmentId)
    {
        try {
            // Buscar produtos das novas tabelas individuais
            $products = [];
            
            // Google
            $google = $this->db->fetch("SELECT *, 'prod-google' as product_type, 'prod-google' as product_name FROM establishment_google WHERE establishment_id = ?", [$establishmentId]);
            if ($google) {
                $products[] = $google;
            }
            
            // Membro Key
            $membroKey = $this->db->fetch("SELECT *, 'prod-membro-key' as product_type, 'prod-membro-key' as product_name FROM establishment_membro_key WHERE establishment_id = ?", [$establishmentId]);
            if ($membroKey) {
                $products[] = $membroKey;
            }
            
            // PagBank
            $pagbank = $this->db->fetch("SELECT *, 'prod-pagbank' as product_type, 'prod-pagbank' as product_name FROM establishment_pagbank WHERE establishment_id = ?", [$establishmentId]);
            if ($pagbank) {
                $products[] = $pagbank;
            }
            
            // Outros
            $outros = $this->db->fetch("SELECT *, 'prod-outros' as product_type, 'prod-outros' as product_name FROM establishment_outros WHERE establishment_id = ?", [$establishmentId]);
            if ($outros) {
                $products[] = $outros;
            }
            
            return $products;
            
        } catch (\Exception $e) {
            error_log('Erro ao buscar outros produtos: ' . $e->getMessage());
            return [];
        }
    }
    
    public function deleteOtherProduct($establishmentId, $productType)
    {
        // Mapear productType para a tabela correta
        $tableMap = [
            'prod-google' => 'establishment_google',
            'prod-membro-key' => 'establishment_membro_key',
            'prod-pagbank' => 'establishment_pagbank',
            'prod-outros' => 'establishment_outros'
        ];
        
        $table = $tableMap[$productType] ?? null;
        
        if (!$table) {
            error_log("AVISO: Tentativa de deletar produto não mapeado: {$productType} para estabelecimento {$establishmentId}");
            return false;
        }
        
        $sql = "DELETE FROM {$table} WHERE establishment_id = ?";
        return $this->db->query($sql, [$establishmentId]);
    }
    
    // Métodos gerais
    public function deleteAllProducts($establishmentId)
    {
        try {
            // Deletar das novas tabelas
            $this->db->query("DELETE FROM establishment_cdx_evo WHERE establishment_id = ?", [$establishmentId]);
            $this->db->query("DELETE FROM establishment_cdc WHERE establishment_id = ?", [$establishmentId]);
            $this->db->query("DELETE FROM establishment_google WHERE establishment_id = ?", [$establishmentId]);
            $this->db->query("DELETE FROM establishment_membro_key WHERE establishment_id = ?", [$establishmentId]);
            $this->db->query("DELETE FROM establishment_pagbank WHERE establishment_id = ?", [$establishmentId]);
            $this->db->query("DELETE FROM establishment_outros WHERE establishment_id = ?", [$establishmentId]);
            
            // Tabelas antigas não existem mais, não tentar deletar
            
            return true;
            
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    public function getAllProducts($establishmentId)
    {
        $products = [];
        $products['other'] = [];
        
        try {
            // CDX/EVO - buscar apenas da nova tabela (establishment_cdx_evo)
            // Não adicionar em 'other' pois tem card próprio na view
            try {
                $cdxEvo = $this->getCdxEvo($establishmentId);
                if ($cdxEvo) {
                    $products['pagseguro'] = $cdxEvo; // Manter chave 'pagseguro' para compatibilidade
                }
            } catch (\Exception $e) {
                // Ignorar erro se tabela não existir
                write_log('Aviso: Erro ao buscar CDX/EVO para estabelecimento ' . $establishmentId . ': ' . $e->getMessage(), 'app.log');
            }
            
            // CDC - buscar apenas da nova tabela (establishment_cdc)
            // Não adicionar em 'other' pois tem card próprio na view
            try {
                $cdc = $this->getCdc($establishmentId);
                if ($cdc) {
                    $products['brasilcard'] = $cdc; // Manter chave 'brasilcard' para compatibilidade
                }
            } catch (\Exception $e) {
                // Ignorar erro se tabela não existir
                write_log('Aviso: Erro ao buscar CDC para estabelecimento ' . $establishmentId . ': ' . $e->getMessage(), 'app.log');
            }
            
            // Produtos individuais das novas tabelas
            try {
                $google = $this->db->fetch("SELECT * FROM establishment_google WHERE establishment_id = ?", [$establishmentId]);
                if ($google) {
                    $products['other'][] = array_merge($google, ['product_type' => 'prod-google', 'product_name' => 'prod-google']);
                }
            } catch (\Exception $e) {
                write_log('Aviso: Erro ao buscar Google para estabelecimento ' . $establishmentId . ': ' . $e->getMessage(), 'app.log');
            }
            
            try {
                $membroKey = $this->db->fetch("SELECT * FROM establishment_membro_key WHERE establishment_id = ?", [$establishmentId]);
                if ($membroKey) {
                    $products['other'][] = array_merge($membroKey, ['product_type' => 'prod-membro-key', 'product_name' => 'prod-membro-key']);
                }
            } catch (\Exception $e) {
                write_log('Aviso: Erro ao buscar Membro Key para estabelecimento ' . $establishmentId . ': ' . $e->getMessage(), 'app.log');
            }
            
            try {
                $pagbank = $this->db->fetch("SELECT * FROM establishment_pagbank WHERE establishment_id = ?", [$establishmentId]);
                if ($pagbank) {
                    $products['other'][] = array_merge($pagbank, ['product_type' => 'prod-pagbank', 'product_name' => 'prod-pagbank']);
                }
            } catch (\Exception $e) {
                write_log('Aviso: Erro ao buscar PagBank para estabelecimento ' . $establishmentId . ': ' . $e->getMessage(), 'app.log');
            }
            
            try {
                $outros = $this->db->fetch("SELECT * FROM establishment_outros WHERE establishment_id = ?", [$establishmentId]);
                if ($outros) {
                    $products['other'][] = array_merge($outros, ['product_type' => 'prod-outros', 'product_name' => 'prod-outros']);
                }
            } catch (\Exception $e) {
                write_log('Aviso: Erro ao buscar Outros para estabelecimento ' . $establishmentId . ': ' . $e->getMessage(), 'app.log');
            }
            
            // Debug: Log dos produtos encontrados
            write_log('=== PRODUTOS ENCONTRADOS PARA ESTABELECIMENTO ' . $establishmentId . ' ===', 'app.log');
            write_log('Produtos: ' . json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 'app.log');
            write_log('Total de produtos em other: ' . (isset($products['other']) ? count($products['other']) : 0), 'app.log');
            
        } catch (\Exception $e) {
            write_log('Erro geral ao buscar produtos para estabelecimento ' . $establishmentId . ': ' . $e->getMessage(), 'app.log');
        }
        
        return $products;
    }
}

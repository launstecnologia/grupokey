<?php

namespace App\Models;

use App\Core\Database;

class Establishment
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function create($data)
    {
        $this->db->beginTransaction();
        
        try {
            $sql = "INSERT INTO establishments (registration_type, cpf, cnpj, razao_social, nome_completo, 
                    nome_fantasia, segmento, telefone, email, produto, cep, logradouro, numero, complemento, 
                    bairro, cidade, uf, banco, agencia, conta, tipo_conta, chave_pix, observacoes, status, created_by_user_id, created_by_representative_id, 
                    created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            // Validar e corrigir valor do ENUM produto
            // O ENUM do banco pode ter valores antigos, então vamos usar NULL se não for válido
            // Os produtos reais estão nas tabelas individuais agora
            $produto = $data['produto'] ?? null;
            if ($produto !== null) {
                // Valores válidos no ENUM do banco (valores antigos)
                $validEnumsNoBanco = ['PAGSEGURO_MP', 'FLAMEX', 'DIVERSOS', 'FGTS', 'UCREDIT', 'BRASILCARD', 'MEMBRO_KEY'];
                // Se não for um valor válido no ENUM do banco, usar null
                if (!in_array($produto, $validEnumsNoBanco)) {
                    error_log('AVISO: Valor do ENUM produto inválido no banco: ' . $produto . '. Usando null.');
                    $produto = null;
                }
            }
            
            $params = [
                $data['registration_type'],
                $data['cpf'] ?? null,
                $data['cnpj'] ?? null,
                $data['razao_social'] ?? null,
                $data['nome_completo'],
                $data['nome_fantasia'],
                $data['segmento'],
                $data['telefone'],
                $data['email'],
                $produto,
                $data['cep'],
                $data['logradouro'],
                $data['numero'],
                $data['complemento'] ?? null,
                $data['bairro'],
                $data['cidade'],
                $data['uf'],
                $data['banco'] ?? null,
                $data['agencia'] ?? null,
                $data['conta'] ?? null,
                $data['tipo_conta'] ?? null,
                $data['chave_pix'] ?? null,
                $data['observacoes'] ?? null,
                $data['status'] ?? 'PENDING',
                $data['created_by_user_id'] ?? null,
                $data['created_by_representative_id'] ?? null
            ];
            
            $result = $this->db->query($sql, $params);
            
            if (!$result) {
                throw new \Exception('Falha ao inserir estabelecimento no banco de dados');
            }
            
            $establishmentId = $this->db->lastInsertId();
            
            if (!$establishmentId) {
                throw new \Exception('Falha ao obter ID do estabelecimento criado');
            }
            
            // Inserir produtos específicos
            if (isset($data['products']) && is_array($data['products']) && !empty($data['products'])) {
                $establishmentProduct = new EstablishmentProduct();
                
                foreach ($data['products'] as $productType) {
                    if (!empty($productType)) {
                        $productData = $data['product_data'][$productType] ?? [];
                        
                        switch ($productType) {
                            case 'prod-pagseguro':
                            case 'prod-subaquirente':
                                $establishmentProduct->createCdxEvo($establishmentId, $productData);
                                break;
                            case 'prod-brasil-card':
                                $establishmentProduct->createBrasilCard($establishmentId, $productData);
                                break;
                            default:
                                $establishmentProduct->createOtherProduct($establishmentId, $productType, $productData);
                                break;
                        }
                    }
                }
            }
            
            $this->db->commit();
            return $establishmentId;
            
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    
    public function findById($id)
    {
        $sql = "SELECT e.*, 
                       u.name as created_by_user_name,
                       r.nome_completo as created_by_representative_name,
                       ea.status as approval_status,
                       ea.reason as approval_reason,
                       ea.observation as approval_observation,
                       ea.approved_at,
                       ea.reproved_at,
                       ua.name as approved_by_name,
                       ur.name as reproved_by_name
                FROM establishments e
                LEFT JOIN users u ON e.created_by_user_id = u.id
                LEFT JOIN representatives r ON e.created_by_representative_id = r.id
                LEFT JOIN establishment_approvals ea ON e.id = ea.establishment_id
                LEFT JOIN users ua ON ea.approved_by_id = ua.id
                LEFT JOIN users ur ON ea.reproved_by_id = ur.id
                WHERE e.id = ?";
        
        $establishment = $this->db->fetch($sql, [$id]);
        
        if ($establishment) {
            // Buscar produtos associados
            $establishmentProduct = new EstablishmentProduct();
            $establishment['products'] = $establishmentProduct->getAllProducts($id);
        }
        
        return $establishment;
    }
    
    public function getAll($filters = [])
    {
        // Buscar produtos das novas tabelas individuais usando múltiplas subqueries
        $sql = "SELECT e.*, 
                       u.name as created_by_user_name,
                       r.nome_completo as created_by_representative_name,
                       r.id as representative_id,
                       ea.status as approval_status,
                       TRIM(BOTH ',' FROM CONCAT_WS(',',
                           IF((SELECT COUNT(*) FROM establishment_cdx_evo WHERE establishment_id = e.id) > 0, 'CDX/EVO', NULL),
                           IF((SELECT COUNT(*) FROM establishment_cdc WHERE establishment_id = e.id) > 0, 'CDC', NULL),
                           IF((SELECT COUNT(*) FROM establishment_google WHERE establishment_id = e.id) > 0, 'Google', NULL),
                           IF((SELECT COUNT(*) FROM establishment_membro_key WHERE establishment_id = e.id) > 0, 'Membro Key', NULL),
                           IF((SELECT COUNT(*) FROM establishment_pagbank WHERE establishment_id = e.id) > 0, 'PagBank', NULL),
                           IF((SELECT COUNT(*) FROM establishment_outros WHERE establishment_id = e.id) > 0, 'Outros', NULL)
                       )) as produtos_adicionais
                FROM establishments e
                LEFT JOIN users u ON e.created_by_user_id = u.id
                LEFT JOIN representatives r ON e.created_by_representative_id = r.id
                LEFT JOIN establishment_approvals ea ON e.id = ea.establishment_id
                WHERE 1=1";
        
        $params = [];
        
        if (isset($filters['status'])) {
            $sql .= " AND e.status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['produto'])) {
            // Buscar em todas as novas tabelas de produtos
            $sql .= " AND (
                e.produto = ? 
                OR EXISTS (SELECT 1 FROM establishment_cdx_evo WHERE establishment_id = e.id)
                OR EXISTS (SELECT 1 FROM establishment_cdc WHERE establishment_id = e.id)
                OR EXISTS (SELECT 1 FROM establishment_google WHERE establishment_id = e.id)
                OR EXISTS (SELECT 1 FROM establishment_membro_key WHERE establishment_id = e.id)
                OR EXISTS (SELECT 1 FROM establishment_pagbank WHERE establishment_id = e.id)
                OR EXISTS (SELECT 1 FROM establishment_outros WHERE establishment_id = e.id)
            )";
            $params[] = $filters['produto'];
        }
        
        if (isset($filters['cidade'])) {
            $sql .= " AND e.cidade LIKE ?";
            $params[] = '%' . $filters['cidade'] . '%';
        }
        
        if (isset($filters['cpf'])) {
            $sql .= " AND e.cpf LIKE ?";
            $params[] = '%' . $filters['cpf'] . '%';
        }
        
        if (isset($filters['cnpj'])) {
            $sql .= " AND e.cnpj LIKE ?";
            $params[] = '%' . $filters['cnpj'] . '%';
        }
        
        if (isset($filters['razao_social'])) {
            $sql .= " AND e.razao_social LIKE ?";
            $params[] = '%' . $filters['razao_social'] . '%';
        }
        
        if (isset($filters['nome'])) {
            $sql .= " AND (e.nome_completo LIKE ? OR e.nome_fantasia LIKE ?)";
            $params[] = '%' . $filters['nome'] . '%';
            $params[] = '%' . $filters['nome'] . '%';
        }
        
        if (isset($filters['created_by'])) {
            if ($filters['created_by'] === 'admin') {
                $sql .= " AND e.created_by_user_id IS NOT NULL";
            } elseif ($filters['created_by'] === 'representative') {
                $sql .= " AND e.created_by_representative_id IS NOT NULL";
            }
        }
        
        if (isset($filters['representative_id'])) {
            $sql .= " AND e.created_by_representative_id = ?";
            $params[] = $filters['representative_id'];
        }
        
        if (isset($filters['date_from'])) {
            $sql .= " AND e.created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (isset($filters['date_to'])) {
            $sql .= " AND e.created_at <= ?";
            $params[] = $filters['date_to'];
        }
        
        $sql .= " GROUP BY e.id ORDER BY e.created_at DESC";
        
        // Paginação
        if (isset($filters['page']) && isset($filters['per_page'])) {
            $offset = ($filters['page'] - 1) * $filters['per_page'];
            $sql .= " LIMIT " . (int)$filters['per_page'] . " OFFSET " . (int)$offset;
        } elseif (isset($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Conta o total de estabelecimentos com filtros
     */
    public function getCount($filters = [])
    {
        $sql = "SELECT COUNT(DISTINCT e.id) as total
                FROM establishments e
                LEFT JOIN users u ON e.created_by_user_id = u.id
                LEFT JOIN representatives r ON e.created_by_representative_id = r.id
                LEFT JOIN establishment_approvals ea ON e.id = ea.establishment_id
                WHERE 1=1";
        
        $params = [];
        
        if (isset($filters['status'])) {
            $sql .= " AND e.status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['produto'])) {
            $sql .= " AND (
                e.produto = ? 
                OR EXISTS (SELECT 1 FROM establishment_cdx_evo WHERE establishment_id = e.id)
                OR EXISTS (SELECT 1 FROM establishment_cdc WHERE establishment_id = e.id)
                OR EXISTS (SELECT 1 FROM establishment_google WHERE establishment_id = e.id)
                OR EXISTS (SELECT 1 FROM establishment_membro_key WHERE establishment_id = e.id)
                OR EXISTS (SELECT 1 FROM establishment_pagbank WHERE establishment_id = e.id)
                OR EXISTS (SELECT 1 FROM establishment_outros WHERE establishment_id = e.id)
            )";
            $params[] = $filters['produto'];
        }
        
        if (isset($filters['cidade'])) {
            $sql .= " AND e.cidade LIKE ?";
            $params[] = '%' . $filters['cidade'] . '%';
        }
        
        if (isset($filters['cpf'])) {
            $sql .= " AND e.cpf LIKE ?";
            $params[] = '%' . $filters['cpf'] . '%';
        }
        
        if (isset($filters['cnpj'])) {
            $sql .= " AND e.cnpj LIKE ?";
            $params[] = '%' . $filters['cnpj'] . '%';
        }
        
        if (isset($filters['razao_social'])) {
            $sql .= " AND e.razao_social LIKE ?";
            $params[] = '%' . $filters['razao_social'] . '%';
        }
        
        if (isset($filters['nome'])) {
            $sql .= " AND (e.nome_completo LIKE ? OR e.nome_fantasia LIKE ?)";
            $params[] = '%' . $filters['nome'] . '%';
            $params[] = '%' . $filters['nome'] . '%';
        }
        
        if (isset($filters['created_by'])) {
            if ($filters['created_by'] === 'admin') {
                $sql .= " AND e.created_by_user_id IS NOT NULL";
            } elseif ($filters['created_by'] === 'representative') {
                $sql .= " AND e.created_by_representative_id IS NOT NULL";
            }
        }
        
        if (isset($filters['representative_id'])) {
            $sql .= " AND e.created_by_representative_id = ?";
            $params[] = $filters['representative_id'];
        }
        
        if (isset($filters['date_from'])) {
            $sql .= " AND e.created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (isset($filters['date_to'])) {
            $sql .= " AND e.created_at <= ?";
            $params[] = $filters['date_to'];
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result ? (int)$result['total'] : 0;
    }
    
    public function update($id, $data)
    {
        error_log('=== MODEL UPDATE ESTABELECIMENTO ===');
        error_log('ID: ' . $id);
        error_log('Dados recebidos: ' . json_encode($data));
        
        $this->db->beginTransaction();
        
        try {
            $sql = "UPDATE establishments SET 
                    registration_type = ?, cpf = ?, cnpj = ?, razao_social = ?, 
                    nome_completo = ?, nome_fantasia = ?, segmento = ?, telefone = ?, email = ?, produto = ?, 
                    cep = ?, logradouro = ?, numero = ?, complemento = ?, bairro = ?, cidade = ?, uf = ?,
                    banco = ?, agencia = ?, conta = ?, tipo_conta = ?, chave_pix = ?, observacoes = ?,
                    status = ?, updated_at = NOW() 
                    WHERE id = ?";
            
            // Validar e corrigir valor do ENUM produto
            // O ENUM do banco pode ter valores antigos, então vamos usar NULL se não for válido
            // Os produtos reais estão nas tabelas individuais agora
            $produto = $data['produto'] ?? null;
            if ($produto !== null) {
                // Valores válidos no ENUM do banco (valores antigos)
                $validEnumsNoBanco = ['PAGSEGURO_MP', 'FLAMEX', 'DIVERSOS', 'FGTS', 'UCREDIT', 'BRASILCARD', 'MEMBRO_KEY'];
                // Se não for um valor válido no ENUM do banco, usar null
                if (!in_array($produto, $validEnumsNoBanco)) {
                    error_log('AVISO: Valor do ENUM produto inválido no banco: ' . $produto . '. Usando null.');
                    $produto = null;
                }
            }
            
            $params = [
                $data['registration_type'],
                $data['cpf'] ?? null,
                $data['cnpj'] ?? null,
                $data['razao_social'] ?? null,
                $data['nome_completo'],
                $data['nome_fantasia'],
                $data['segmento'],
                $data['telefone'],
                $data['email'],
                $produto,
                $data['cep'],
                $data['logradouro'],
                $data['numero'],
                $data['complemento'] ?? null,
                $data['bairro'],
                $data['cidade'],
                $data['uf'],
                $data['banco'] ?? null,
                $data['agencia'] ?? null,
                $data['conta'] ?? null,
                $data['tipo_conta'] ?? null,
                $data['chave_pix'] ?? null,
                $data['observacoes'] ?? null,
                $data['status'] ?? 'PENDING',
                $id
            ];
            
            $result = $this->db->query($sql, $params);
            
            // Verificar se a atualização foi bem-sucedida
            if (!$result) {
                throw new \Exception('Falha ao atualizar estabelecimento no banco de dados');
            }
            
            // Atualizar produtos específicos
            if (isset($data['products']) && is_array($data['products']) && !empty($data['products'])) {
                error_log('Produtos para atualizar: ' . json_encode($data['products']));
                
                $establishmentProduct = new EstablishmentProduct();
                
                // Deletar produtos existentes
                $establishmentProduct->deleteAllProducts($id);
                
                // Inserir novos produtos
                foreach ($data['products'] as $productType) {
                    if (!empty($productType)) {
                        $productData = $data['product_data'][$productType] ?? [];
                        
                        switch ($productType) {
                            case 'prod-pagseguro':
                            case 'prod-subaquirente':
                                $establishmentProduct->createCdxEvo($id, $productData);
                                break;
                            case 'prod-brasil-card':
                                $establishmentProduct->createBrasilCard($id, $productData);
                                break;
                            default:
                                $establishmentProduct->createOtherProduct($id, $productType, $productData);
                                break;
                        }
                    }
                }
            }
            
            $this->db->commit();
            error_log('SUCESSO: Estabelecimento atualizado no banco de dados');
            return true;
            
        } catch (\Exception $e) {
            error_log('ERRO no Model: ' . $e->getMessage());
            $this->db->rollback();
            throw $e;
        }
    }
    
    public function approve($id, $reason = null, $observation = null)
    {
        $this->db->beginTransaction();
        
        try {
            // Atualizar status do estabelecimento
            $sql = "UPDATE establishments SET status = 'APPROVED', updated_at = NOW() WHERE id = ?";
            $this->db->query($sql, [$id]);
            
            // Criar registro de aprovação
            $approvalId = uniqid();
            $sql = "INSERT INTO establishment_approvals (id, establishment_id, status, reason, observation, 
                    approved_at, approved_by_id) VALUES (?, ?, 'APPROVED', ?, ?, NOW(), ?)";
            
            $userId = $_SESSION['user_id'] ?? null;
            $this->db->query($sql, [$approvalId, $id, $reason, $observation, $userId]);
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    public function reprove($id, $reason, $observation = null)
    {
        $this->db->beginTransaction();
        
        try {
            // Atualizar status do estabelecimento
            $sql = "UPDATE establishments SET status = 'REPROVED', updated_at = NOW() WHERE id = ?";
            $this->db->query($sql, [$id]);
            
            // Criar registro de reprovação
            $approvalId = uniqid();
            $sql = "INSERT INTO establishment_approvals (id, establishment_id, status, reason, observation, 
                    reproved_at, reproved_by_id) VALUES (?, ?, 'REPROVED', ?, ?, NOW(), ?)";
            
            $userId = $_SESSION['user_id'] ?? null;
            $this->db->query($sql, [$approvalId, $id, $reason, $observation, $userId]);
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    public function disable($id)
    {
        $sql = "UPDATE establishments SET status = 'DISABLED', updated_at = NOW() WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function delete($id)
    {
        $this->db->beginTransaction();
        
        try {
            // Deletar produtos associados
            $establishmentProduct = new EstablishmentProduct();
            $establishmentProduct->deleteAllProducts($id);
            
            // Deletar estabelecimento
            $sql = "DELETE FROM establishments WHERE id = ?";
            $this->db->query($sql, [$id]);
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    public function getStats($filters = [])
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN status = 'APPROVED' THEN 1 END) as aprovados,
                    COUNT(CASE WHEN status = 'PENDING' THEN 1 END) as pendentes,
                    COUNT(CASE WHEN status = 'REPROVED' THEN 1 END) as reprovados,
                    COUNT(CASE WHEN status = 'DISABLED' THEN 1 END) as desabilitados,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as cadastros_ultimo_mes
                FROM establishments WHERE 1=1";
        
        $params = [];
        
        if (isset($filters['produto'])) {
            $sql .= " AND produto = ?";
            $params[] = $filters['produto'];
        }
        
        if (isset($filters['representative_id'])) {
            $sql .= " AND created_by_representative_id = ?";
            $params[] = $filters['representative_id'];
        }
        
        if (isset($filters['date_from'])) {
            $sql .= " AND created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (isset($filters['date_to'])) {
            $sql .= " AND created_at <= ?";
            $params[] = $filters['date_to'];
        }
        
        return $this->db->fetch($sql, $params);
    }
    
    public function getTopCities($limit = 5)
    {
        $sql = "SELECT cidade, uf, COUNT(*) as total 
                FROM establishments 
                WHERE status = 'APPROVED' 
                GROUP BY cidade, uf 
                ORDER BY total DESC 
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$limit]);
    }
    
    public function getMonthlyEvolution($months = 12)
    {
        $sql = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as mes,
                    produto,
                    COUNT(*) as total
                FROM establishments 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m'), produto
                ORDER BY mes ASC";
        
        return $this->db->fetchAll($sql, [$months]);
    }
    
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM establishments WHERE email = ?";
        return $this->db->fetch($sql, [$email]);
    }
    
    public function getDocuments($establishmentId)
    {
        $sql = "SELECT * FROM documents WHERE establishment_id = ? ORDER BY uploaded_at DESC";
        return $this->db->fetchAll($sql, [$establishmentId]);
    }
    
    public function getApprovalHistory($establishmentId)
    {
        $sql = "SELECT ea.*, u.name as approved_by_name, ur.name as reproved_by_name
                FROM establishment_approvals ea
                LEFT JOIN users u ON ea.approved_by_id = u.id
                LEFT JOIN users ur ON ea.reproved_by_id = ur.id
                WHERE ea.establishment_id = ?
                ORDER BY COALESCE(ea.approved_at, ea.reproved_at) DESC";
        
        return $this->db->fetchAll($sql, [$establishmentId]);
    }
    
    public function addDocument($establishmentId, $filePath, $originalName, $documentType = 'RG_CPF_CNH', $productType = null, $description = null)
    {
        $documentId = uniqid();
        
        // Valores válidos do ENUM na tabela documents
        $validDocumentTypes = [
            'CONTRATO_SOCIAL',
            'DOCUMENTO_FOTO_FRENTE',
            'DOCUMENTO_FOTO_VERSO',
            'COMPROVANTE_RESIDENCIA',
            'FOTO_FACHADA',
            'OUTROS_DOCUMENTOS',
            'RG_CPF_CNH',
            'COMPROVANTE_BANCARIO',
            'SELFIE_DOCUMENTO',
            'COMPROVANTE_ENDERECO',
            'CARTAO_CNPJ',
            'PRINT_INSTAGRAM',
            'FOTO_TITULAR_LOJA'
        ];
        
        // Mapear tipos de documento para os valores aceitos pela tabela
        $mappedDocumentTypes = [
            'contrato_social_requerimento' => 'CONTRATO_SOCIAL',
            'documento_foto_frente' => 'DOCUMENTO_FOTO_FRENTE',
            'documento_foto_verso' => 'DOCUMENTO_FOTO_VERSO',
            'comprovante_residencia' => 'COMPROVANTE_RESIDENCIA',
            'fotos' => 'FOTO_FACHADA',
            'outros_documentos' => 'OUTROS_DOCUMENTOS',
            // Compatibilidade com tipos antigos
            'general' => 'RG_CPF_CNH',
            'cnh' => 'RG_CPF_CNH',
            'rg' => 'RG_CPF_CNH',
            'comprovante_renda' => 'COMPROVANTE_BANCARIO',
            'contrato_social' => 'CONTRATO_SOCIAL',
            'foto_fachada' => 'FOTO_FACHADA'
        ];
        
        // Normalizar o tipo de documento - garantir que seja string
        if (!is_string($documentType)) {
            $documentType = '';
        }
        $documentType = strtolower(trim($documentType));
        
        // Se estiver vazio, usar padrão
        if (empty($documentType)) {
            $documentType = 'outros_documentos';
        }
        
        // Tentar mapear primeiro
        $finalDocumentType = $mappedDocumentTypes[$documentType] ?? null;
        
        // Se não foi mapeado mas já está em formato ENUM válido, usar diretamente
        if (!$finalDocumentType && in_array(strtoupper($documentType), $validDocumentTypes)) {
            $finalDocumentType = strtoupper($documentType);
        }
        
        // Se ainda não tiver um valor válido, usar o padrão
        if (!$finalDocumentType || !in_array($finalDocumentType, $validDocumentTypes)) {
            $finalDocumentType = 'OUTROS_DOCUMENTOS'; // Valor padrão seguro
        }
        
        // Log para debug (remover em produção se necessário)
        write_log("addDocument - Tipo recebido: '{$documentType}' -> Mapeado para: '{$finalDocumentType}'", 'app.log');
        
        // Verificar valores válidos do ENUM no banco de dados
        $enumValues = [];
        try {
            $enumCheck = $this->db->fetchAll("SHOW COLUMNS FROM documents WHERE Field = 'document_type'");
            if (!empty($enumCheck) && isset($enumCheck[0]['Type'])) {
                $typeDefinition = $enumCheck[0]['Type'];
                if (preg_match("/^enum\((.*)\)$/i", $typeDefinition, $matches)) {
                    if (!empty($matches[1])) {
                        $enumValues = array_map(function($val) {
                            return trim($val, "'\"");
                        }, explode(',', $matches[1]));
                        write_log("ENUM do banco encontrado: " . implode(', ', $enumValues), 'app.log');
                    }
                }
            }
            
            // Se o valor não estiver no ENUM do banco, usar um valor que existe
            if (!empty($enumValues) && !in_array($finalDocumentType, $enumValues)) {
                write_log("AVISO: Valor '{$finalDocumentType}' não está no ENUM do banco. Valores disponíveis: " . implode(', ', $enumValues) . ". Usando fallback.", 'app.log');
                // Tentar usar COMPROVANTE_RESIDENCIA que geralmente existe
                if (in_array('COMPROVANTE_RESIDENCIA', $enumValues)) {
                    $finalDocumentType = 'COMPROVANTE_RESIDENCIA';
                } else {
                    // Usar o primeiro valor disponível
                    $finalDocumentType = $enumValues[0];
                }
                write_log("Usando valor fallback: {$finalDocumentType}", 'app.log');
            }
        } catch (\Exception $e) {
            write_log("AVISO: Não foi possível verificar ENUM do banco: " . $e->getMessage() . ". Continuando com valor mapeado.", 'app.log');
        }
        
        $sql = "INSERT INTO documents (id, establishment_id, document_type, file_path, original_name, file_name, mime_type, size, uploaded_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        // Extrair informações do arquivo
        // Garantir que filePath é uma string
        if (is_array($filePath)) {
            $filePath = $filePath['file_path'] ?? $filePath['path'] ?? '';
        }
        
        if (empty($filePath)) {
            throw new \Exception('Caminho do arquivo não fornecido');
        }
        
        $fileName = basename($filePath);
        $fileSize = file_exists($filePath) ? filesize($filePath) : 0;
        
        $mimeType = 'application/octet-stream';
        if (function_exists('mime_content_type')) {
            $mimeType = \mime_content_type($filePath) ?: $mimeType;
        } elseif (function_exists('finfo_open')) {
            $finfo = \finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mimeType = \finfo_file($finfo, $filePath) ?: $mimeType;
                \finfo_close($finfo);
            }
        }
        
        try {
            return $this->db->query($sql, [
                $documentId,
                $establishmentId,
                $finalDocumentType,
                $filePath,
                $originalName,
                $fileName,
                $mimeType,
                $fileSize
            ]);
        } catch (\PDOException $e) {
            // Se for erro de ENUM ou NULL não permitido, usar um valor que existe no ENUM
            if (strpos($e->getMessage(), 'document_type') !== false || 
                strpos($e->getMessage(), 'Data truncated') !== false ||
                strpos($e->getMessage(), 'cannot be null') !== false) {
                
                write_log("Erro de ENUM detectado. Erro original: " . $e->getMessage(), 'app.log');
                
                // Se não temos os valores do ENUM, buscar novamente
                if (empty($enumValues)) {
                    try {
                        $enumCheck = $this->db->fetchAll("SHOW COLUMNS FROM documents WHERE Field = 'document_type'");
                        if (!empty($enumCheck) && isset($enumCheck[0]['Type'])) {
                            $typeDefinition = $enumCheck[0]['Type'];
                            if (preg_match("/^enum\((.*)\)$/i", $typeDefinition, $matches)) {
                                if (!empty($matches[1])) {
                                    $enumValues = array_map(function($val) {
                                        return trim($val, "'\"");
                                    }, explode(',', $matches[1]));
                                }
                            }
                        }
                    } catch (\Exception $enumEx) {
                        write_log("Erro ao buscar ENUM no catch: " . $enumEx->getMessage(), 'app.log');
                    }
                }
                
                // Tentar usar um valor que provavelmente existe no ENUM
                if (!empty($enumValues)) {
                    // Tentar COMPROVANTE_RESIDENCIA primeiro
                    if (in_array('COMPROVANTE_RESIDENCIA', $enumValues)) {
                        $finalDocumentType = 'COMPROVANTE_RESIDENCIA';
                    } elseif (in_array('RG_CPF_CNH', $enumValues)) {
                        $finalDocumentType = 'RG_CPF_CNH';
                    } else {
                        // Usar o primeiro valor disponível
                        $finalDocumentType = $enumValues[0];
                    }
                    write_log("Usando valor fallback do ENUM: {$finalDocumentType}", 'app.log');
                } else {
                    // Último recurso: usar COMPROVANTE_RESIDENCIA
                    $finalDocumentType = 'COMPROVANTE_RESIDENCIA';
                    write_log("Usando valor padrão de emergência: {$finalDocumentType}", 'app.log');
                }
                
                return $this->db->query($sql, [
                    $documentId,
                    $establishmentId,
                    $finalDocumentType,
                    $filePath,
                    $originalName,
                    $fileName,
                    $mimeType,
                    $fileSize
                ]);
            }
            // Re-lançar se não for erro de ENUM
            throw $e;
        }
    }
    
    
    public function updateProducts($establishmentId, $products, $productData)
    {
        $this->db->beginTransaction();
        
        try {
            // Remover produtos existentes
            $sql = "DELETE FROM establishment_products WHERE establishment_id = ?";
            $this->db->query($sql, [$establishmentId]);
            
            // Inserir novos produtos
            if (is_array($products)) {
                foreach ($products as $productType) {
                    $data = $productData[$productType] ?? null;
                    
                    $sql = "INSERT INTO establishment_products (establishment_id, product_type, product_data) 
                            VALUES (?, ?, ?)";
                    
                    $this->db->query($sql, [
                        $establishmentId,
                        $productType,
                        $data ? json_encode($data) : null
                    ]);
                }
            }
            
            // Atualizar flag de múltiplos produtos
            $hasMultipleProducts = count($products) > 1;
            $sql = "UPDATE establishments SET has_multiple_products = ?, produto = ? WHERE id = ?";
            $this->db->query($sql, [$hasMultipleProducts, $products[0] ?? null, $establishmentId]);
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    public function getDocumentsByType($establishmentId, $documentType = null, $productType = null)
    {
        $sql = "SELECT * FROM documents WHERE establishment_id = ?";
        
        $params = [$establishmentId];
        
        if ($documentType) {
            $sql .= " AND document_type = ?";
            $params[] = $documentType;
        }
        
        $sql .= " ORDER BY uploaded_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getDocumentById($documentId, $establishmentId)
    {
        $sql = "SELECT * FROM documents WHERE id = ? AND establishment_id = ?";
        return $this->db->fetch($sql, [$documentId, $establishmentId]);
    }
    
    /**
     * Atualiza o sistpay_id do estabelecimento
     */
    public function updateSistPayId($id, $sistpayId)
    {
        $sql = "UPDATE establishments SET sistpay_id = ? WHERE id = ?";
        return $this->db->query($sql, [$sistpayId, $id]);
    }
    
    /**
     * Busca estabelecimento pelo sistpay_id
     */
    public function findBySistPayId($sistpayId)
    {
        $sql = "SELECT * FROM establishments WHERE sistpay_id = ?";
        return $this->db->fetch($sql, [$sistpayId]);
    }
    
    /**
     * Busca estabelecimento pelo código (EST-{id})
     */
    public function findByCode($code)
    {
        // Extrair o ID do código EST-{id}
        if (preg_match('/^EST-(\d+)$/', $code, $matches)) {
            $id = (int)$matches[1];
            return $this->findById($id);
        }
        return null;
    }
    
    /**
     * Atualiza o status do estabelecimento via webhook
     * Mapeia os status do SistPay para os status do sistema
     */
    public function updateStatusFromWebhook($id, $sistpayStatus)
    {
        // Mapeamento de status do SistPay para status do sistema
        // SistPay: 1=Habilitado, 2=Desabilitado, 3=Em Análise, 4=Pendente, 5=Cancelado, 6=Qualidade
        // Sistema: PENDING, APPROVED, REPROVED, DISABLED
        $statusMap = [
            1 => 'APPROVED',      // Habilitado -> Aprovado
            2 => 'DISABLED',     // Desabilitado -> Desabilitado
            3 => 'PENDING',      // Em Análise -> Pendente
            4 => 'PENDING',      // Pendente -> Pendente
            5 => 'REPROVED',     // Cancelado -> Reprovado
            6 => 'PENDING'       // Qualidade -> Pendente (aguardando aprovação)
        ];
        
        $newStatus = $statusMap[$sistpayStatus] ?? 'PENDING';
        
        $sql = "UPDATE establishments SET status = ?, updated_at = NOW() WHERE id = ?";
        return $this->db->query($sql, [$newStatus, $id]);
    }
    
    /**
     * Busca estabelecimento por CPF
     */
    public function findByCpf($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        $sql = "SELECT * FROM establishments WHERE cpf = ? LIMIT 1";
        return $this->db->fetch($sql, [$cpf]);
    }
    
    /**
     * Busca estabelecimento por CNPJ
     */
    public function findByCnpj($cnpj)
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        $sql = "SELECT * FROM establishments WHERE cnpj = ? LIMIT 1";
        return $this->db->fetch($sql, [$cnpj]);
    }
}

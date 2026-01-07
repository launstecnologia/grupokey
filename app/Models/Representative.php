<?php

namespace App\Models;

use App\Core\Database;

class Representative
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function create($data)
    {
        // Ordem dos campos conforme schema: nome_completo, cpf, email, telefone, cep, logradouro, numero, complemento, bairro, cidade, uf, password, status
        $sql = "INSERT INTO representatives (nome_completo, cpf, email, telefone, cep, logradouro, numero, complemento, 
                bairro, cidade, uf, password, status, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        // Garantir que a senha esteja hashada
        $password = $data['senha'] ?? $data['password'] ?? '';
        if (!empty($password) && !password_get_info($password)['algo']) {
            // Se a senha não estiver hashada, fazer hash
            $password = password_hash($password, PASSWORD_DEFAULT);
        }
        
        $params = [
            $data['nome_completo'],
            $data['cpf'],
            $data['email'],
            $data['telefone'],
            $data['cep'],
            $data['logradouro'],
            $data['numero'],
            $data['complemento'] ?? null,
            $data['bairro'],
            $data['cidade'],
            $data['uf'],
            $password,
            $data['status'] ?? 'ACTIVE'
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM representatives WHERE email = ? AND status = 'ACTIVE'";
        return $this->db->fetch($sql, [$email]);
    }
    
    public function findById($id)
    {
        $sql = "SELECT * FROM representatives WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    public function getAll($filters = [])
    {
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $whereClause .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['cidade'])) {
            $whereClause .= " AND cidade LIKE ?";
            $params[] = '%' . $filters['cidade'] . '%';
        }
        
        if (!empty($filters['search'])) {
            $whereClause .= " AND (nome_completo LIKE ? OR email LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        
        if (!empty($filters['date_from'])) {
            $whereClause .= " AND DATE(created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereClause .= " AND DATE(created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        $sql = "SELECT * FROM representatives $whereClause ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, $params);
    }
    
    public function update($id, $data)
    {
        $sql = "UPDATE representatives SET ";
        $params = [];
        $fields = [];
        
        // Campos que podem ser atualizados
        $allowedFields = [
            'nome_completo', 'cep', 'logradouro', 'numero', 'complemento',
            'bairro', 'cidade', 'uf', 'telefone', 'email', 'photo', 'status'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        // Adicionar senha se fornecida
        if (isset($data['senha']) && !empty($data['senha'])) {
            $fields[] = "password = ?";
            $params[] = $data['senha']; // Já vem hashada do controller
        }
        
        if (empty($fields)) {
            return false; // Nenhum campo para atualizar
        }
        
        $sql .= implode(', ', $fields);
        $sql .= ", updated_at = NOW() WHERE id = ?";
        $params[] = $id;
        
        try {
            $stmt = $this->db->query($sql, $params);
            $rowsAffected = $stmt->rowCount();
            write_log('UPDATE executado para representante ' . $id . ' - Linhas afetadas: ' . $rowsAffected, 'representatives.log');
            return $rowsAffected > 0;
        } catch (\Exception $e) {
            write_log('ERRO no UPDATE do representante ' . $id . ': ' . $e->getMessage(), 'representatives.log');
            throw $e;
        }
    }
    
    public function delete($id)
    {
        $sql = "DELETE FROM representatives WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function updatePassword($id, $newPassword)
    {
        $sql = "UPDATE representatives SET password = ?, updated_at = NOW() WHERE id = ?";
        return $this->db->query($sql, [password_hash($newPassword, PASSWORD_DEFAULT), $id]);
    }
    
    public function updateStatus($id, $status)
    {
        $sql = "UPDATE representatives SET status = ?, updated_at = NOW() WHERE id = ?";
        return $this->db->query($sql, [$status, $id]);
    }
    
    public function getEstablishments($representativeId)
    {
        $sql = "SELECT e.*, ea.status as approval_status, ea.approved_at
                FROM establishments e
                LEFT JOIN establishment_approvals ea ON e.id = ea.establishment_id
                WHERE e.created_by_representative_id = ?
                ORDER BY e.created_at DESC";
        
        return $this->db->fetchAll($sql, [$representativeId]);
    }
    
    public function getStats($filters = [])
    {
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $whereClause .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['cidade'])) {
            $whereClause .= " AND cidade LIKE ?";
            $params[] = '%' . $filters['cidade'] . '%';
        }
        
        if (!empty($filters['search'])) {
            $whereClause .= " AND (nome_completo LIKE ? OR email LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        
        if (!empty($filters['date_from'])) {
            $whereClause .= " AND DATE(created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereClause .= " AND DATE(created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        $sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN status = 'ACTIVE' THEN 1 END) as ativos,
                    COUNT(CASE WHEN status = 'INACTIVE' THEN 1 END) as inativos,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as cadastros_ultimo_mes
                FROM representatives $whereClause";
        
        return $this->db->fetch($sql, $params);
    }
    
    public function getRepresentativeStats($representativeId)
    {
        $sql = "SELECT 
                    COUNT(e.id) as total_establishments,
                    COUNT(CASE WHEN e.status = 'APPROVED' THEN 1 END) as approved_establishments,
                    COUNT(CASE WHEN e.status = 'PENDING' THEN 1 END) as pending_establishments,
                    COUNT(CASE WHEN e.status = 'REPROVED' THEN 1 END) as reproved_establishments,
                    COUNT(CASE WHEN e.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as establishments_last_month
                FROM establishments e
                WHERE e.created_by_representative_id = ?";
        
        return $this->db->fetch($sql, [$representativeId]);
    }
    
    public function clearForcePasswordChange($id)
    {
        $sql = "UPDATE representatives SET force_password_change = 0 WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function incrementFailedAttempts($id)
    {
        $sql = "UPDATE representatives SET failed_attempts = failed_attempts + 1 WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function resetFailedAttempts($id)
    {
        $sql = "UPDATE representatives SET failed_attempts = 0 WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function updateLastLogin($id)
    {
        $sql = "UPDATE representatives SET last_login = NOW() WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function block($id)
    {
        $sql = "UPDATE representatives SET status = 'BLOCKED' WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    /**
     * Obtém os produtos permitidos para um representante
     */
    public function getProducts($representativeId)
    {
        $sql = "SELECT product_type FROM representative_products WHERE representative_id = ?";
        $products = $this->db->fetchAll($sql, [$representativeId]);
        
        // Log para debug
        write_log('getProducts para representante ' . $representativeId . ': ' . json_encode($products), 'representatives.log');
        
        return $products;
    }
    
    /**
     * Define os produtos permitidos para um representante
     */
    public function setProducts($representativeId, $products)
    {
        try {
            // Remover produtos existentes (incluindo os com product_type vazio)
            $this->db->query("DELETE FROM representative_products WHERE representative_id = ?", [$representativeId]);
            write_log('Produtos antigos removidos para representante ' . $representativeId, 'representatives.log');
            
            // Inserir novos produtos
            if (!empty($products) && is_array($products)) {
                write_log('=== SETPRODUCTS - INSERINDO PRODUTOS ===', 'representatives.log');
                write_log('Representante ID: ' . $representativeId, 'representatives.log');
                write_log('Produtos recebidos: ' . json_encode($products), 'representatives.log');
                
                foreach ($products as $productType) {
                    if (!empty($productType) && is_string($productType)) {
                        // Limpar e validar o product_type
                        $productType = trim($productType);
                        
                        // Validar que o produto não está vazio após trim
                        if (empty($productType)) {
                            write_log('⚠️ AVISO: product_type vazio após trim, ignorando', 'representatives.log');
                            continue;
                        }
                        
                        // Garantir que não exceda 100 caracteres (tamanho atualizado da coluna)
                        if (strlen($productType) > 100) {
                            write_log('⚠️ AVISO: product_type truncado de ' . strlen($productType) . ' para 100 caracteres: ' . $productType, 'representatives.log');
                            $productType = substr($productType, 0, 100);
                        }
                        
                        write_log('Inserindo produto: ' . $productType . ' (tamanho: ' . strlen($productType) . ')', 'representatives.log');
                        
                        // Não incluir o campo id, deixar o AUTO_INCREMENT fazer o trabalho
                        // Primeiro, limpar qualquer registro com product_type vazio para este representante
                        $cleanupSql = "DELETE FROM representative_products WHERE representative_id = ? AND (product_type = '' OR product_type IS NULL)";
                        $this->db->query($cleanupSql, [$representativeId]);
                        
                        // Usar INSERT IGNORE para evitar erros de duplicação
                        $sql = "INSERT IGNORE INTO representative_products (representative_id, product_type) VALUES (?, ?)";
                        
                        try {
                            // Desabilitar modo strict temporariamente para permitir inserção mesmo com warnings
                            $pdo = $this->db->getConnection();
                            $originalErrmode = $pdo->getAttribute(\PDO::ATTR_ERRMODE);
                            
                            $stmt = $this->db->query($sql, [$representativeId, $productType]);
                            
                            // Verificar imediatamente se foi inserido
                            $checkSql = "SELECT id, product_type FROM representative_products WHERE representative_id = ? AND product_type = ? LIMIT 1";
                            $checkResult = $this->db->fetch($checkSql, [$representativeId, $productType]);
                            
                            if ($checkResult && isset($checkResult['id']) && !empty($checkResult['product_type'])) {
                                write_log('✅ Produto inserido com sucesso: ' . $productType . ' (ID: ' . $checkResult['id'] . ')', 'representatives.log');
                            } else {
                                write_log('❌ ERRO: Produto NÃO foi inserido corretamente: ' . $productType, 'representatives.log');
                                if ($checkResult) {
                                    write_log('Dados encontrados no banco: ' . json_encode($checkResult), 'representatives.log');
                                }
                                
                                // Tentar sem IGNORE como fallback
                                try {
                                    $sql2 = "INSERT INTO representative_products (representative_id, product_type) VALUES (?, ?) ON DUPLICATE KEY UPDATE product_type = VALUES(product_type)";
                                    $stmt2 = $this->db->query($sql2, [$representativeId, $productType]);
                                    $checkResult2 = $this->db->fetch($checkSql, [$representativeId, $productType]);
                                    if ($checkResult2 && isset($checkResult2['id']) && !empty($checkResult2['product_type'])) {
                                        write_log('✅ Produto inserido na segunda tentativa: ' . $productType, 'representatives.log');
                                    } else {
                                        write_log('❌ ERRO: Produto não foi inserido mesmo na segunda tentativa: ' . $productType, 'representatives.log');
                                    }
                                } catch (\Exception $e2) {
                                    write_log('❌ ERRO na segunda tentativa: ' . $e2->getMessage(), 'representatives.log');
                                }
                            }
                        } catch (\PDOException $e) {
                            $errorCode = $e->getCode();
                            $errorMessage = $e->getMessage();
                            
                            write_log('❌ ERRO PDO ao inserir produto: ' . $errorMessage . ' (código: ' . $errorCode . ')', 'representatives.log');
                            
                            // Verificar se mesmo com erro, a inserção aconteceu
                            $checkSql = "SELECT id, product_type FROM representative_products WHERE representative_id = ? AND product_type = ? LIMIT 1";
                            $checkResult = $this->db->fetch($checkSql, [$representativeId, $productType]);
                            
                            if ($checkResult && isset($checkResult['id']) && !empty($checkResult['product_type'])) {
                                write_log('✅ Produto foi inserido apesar do erro: ' . $productType, 'representatives.log');
                            } else {
                                // Se for apenas um warning, tentar inserir de outra forma
                                if ($errorCode == '01000' || strpos($errorMessage, '1265') !== false || strpos($errorMessage, 'Data truncated') !== false) {
                                    write_log('⚠️ AVISO: Warning de truncamento detectado: ' . $productType, 'representatives.log');
                                    // Tentar com REPLACE INTO
                                    try {
                                        $sql3 = "REPLACE INTO representative_products (representative_id, product_type) VALUES (?, ?)";
                                        $stmt3 = $this->db->query($sql3, [$representativeId, $productType]);
                                        $checkResult3 = $this->db->fetch($checkSql, [$representativeId, $productType]);
                                        if ($checkResult3 && isset($checkResult3['id']) && !empty($checkResult3['product_type'])) {
                                            write_log('✅ Produto inserido com REPLACE: ' . $productType, 'representatives.log');
                                        } else {
                                            write_log('❌ ERRO: Produto não foi inserido mesmo com REPLACE: ' . $productType, 'representatives.log');
                                            continue;
                                        }
                                    } catch (\Exception $e3) {
                                        write_log('❌ ERRO na tentativa com REPLACE: ' . $e3->getMessage(), 'representatives.log');
                                        continue;
                                    }
                                } else {
                                    // Se for outro erro, relançar
                                    throw $e;
                                }
                            }
                        }
                    }
                }
                
                // Verificar se foram salvos corretamente
                $savedProducts = $this->getProducts($representativeId);
                write_log('Produtos salvos no banco (verificação): ' . json_encode($savedProducts), 'representatives.log');
            }
            
            return true;
        } catch (\PDOException $e) {
            write_log('Erro PDO em setProducts para representante ' . $representativeId . ': ' . $e->getMessage(), 'representatives.log');
            write_log('Código do erro: ' . $e->getCode(), 'representatives.log');
            throw new \Exception('Erro ao salvar produtos permitidos: ' . $e->getMessage());
        } catch (\Exception $e) {
            write_log('Erro em setProducts para representante ' . $representativeId . ': ' . $e->getMessage(), 'representatives.log');
            write_log('Stack trace: ' . $e->getTraceAsString(), 'representatives.log');
            throw new \Exception('Erro ao salvar produtos permitidos: ' . $e->getMessage());
        }
    }
}
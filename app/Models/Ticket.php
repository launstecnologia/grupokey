<?php

namespace App\Models;

use App\Core\Database;

class Ticket
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function create($data)
    {
        $id = uniqid();
        $ticketNumber = 'TK' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Validar e corrigir valor do ENUM produto
        $produto = $this->validateProdutoEnum($data['produto'] ?? 'OUTROS');
        
        $sql = "INSERT INTO tickets (id, ticket_number, produto, establishment_id, assunto, descricao, status, created_at, updated_at, created_by_user_id, created_by_representative_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?, ?)";
        
        $params = [
            $id,
            $ticketNumber,
            $produto,
            $data['establishment_id'] ?? null,
            $data['titulo'],
            $data['descricao'],
            $data['status'] ?? 'OPEN',
            $data['created_by_user_id'] ?? null,
            $data['representative_id']
        ];
        
        $this->db->query($sql, $params);
        return $id;
    }
    
    public function findById($id)
    {
        $sql = "SELECT t.*, r.nome_completo as representative_nome, r.email as representative_email,
                       e.nome_fantasia as establishment_nome
                FROM tickets t 
                LEFT JOIN representatives r ON t.created_by_representative_id = r.id 
                LEFT JOIN establishments e ON t.establishment_id = e.id
                WHERE t.id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    public function getAll($filters = [])
    {
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $whereClause .= " AND t.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['produto'])) {
            $whereClause .= " AND t.produto = ?";
            $params[] = $filters['produto'];
        }
        
        if (!empty($filters['representative_id'])) {
            $whereClause .= " AND t.created_by_representative_id = ?";
            $params[] = $filters['representative_id'];
        }
        
        if (!empty($filters['search'])) {
            $whereClause .= " AND (t.assunto LIKE ? OR t.descricao LIKE ? OR r.nome_completo LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        
        if (!empty($filters['date_from'])) {
            $whereClause .= " AND DATE(t.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereClause .= " AND DATE(t.created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        $sql = "SELECT t.*, r.nome_completo as representative_nome, r.email as representative_email,
                       e.nome_fantasia as establishment_nome,
                       (SELECT COUNT(*) FROM ticket_comments tc WHERE tc.ticket_id = t.id) as total_respostas
                FROM tickets t 
                LEFT JOIN representatives r ON t.created_by_representative_id = r.id 
                LEFT JOIN establishments e ON t.establishment_id = e.id
                $whereClause 
                ORDER BY t.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function update($id, $data)
    {
        // Validar e corrigir valor do ENUM produto
        $produto = $this->validateProdutoEnum($data['produto'] ?? 'OUTROS');
        
        $sql = "UPDATE tickets SET assunto = ?, descricao = ?, produto = ?, status = ?, updated_at = NOW() WHERE id = ?";
        
        $params = [
            $data['titulo'],
            $data['descricao'],
            $produto,
            $data['status'],
            $id
        ];
        
        return $this->db->query($sql, $params);
    }
    
    public function delete($id)
    {
        // Primeiro deletar os comentários
        $this->db->query("DELETE FROM ticket_comments WHERE ticket_id = ?", [$id]);
        
        // Depois deletar o ticket
        return $this->db->query("DELETE FROM tickets WHERE id = ?", [$id]);
    }
    
    public function getStats($filters = [])
    {
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if (!empty($filters['representative_id'])) {
            $whereClause .= " AND created_by_representative_id = ?";
            $params[] = $filters['representative_id'];
        }
        
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'OPEN' THEN 1 ELSE 0 END) as abertos,
                    SUM(CASE WHEN status = 'IN_PROGRESS' THEN 1 ELSE 0 END) as em_andamento,
                    SUM(CASE WHEN status = 'CLOSED' THEN 1 ELSE 0 END) as fechados,
                    SUM(CASE WHEN produto = 'URGENTE' THEN 1 ELSE 0 END) as alta_prioridade,
                    SUM(CASE WHEN DATE(created_at) >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as ultimo_mes
                FROM tickets $whereClause";
        
        return $this->db->fetch($sql, $params);
    }
    
    public function addResposta($chamadoId, $data)
    {
        $id = uniqid();
        
        // Para comentários, sempre usar o usuário admin da tabela users
        // pois a foreign key só aceita IDs de users
        $adminUser = $this->db->fetch("SELECT id FROM users WHERE email = 'admin@sistema.com' LIMIT 1");
        $userId = $adminUser ? $adminUser['id'] : $data['user_id'];
        
        $sql = "INSERT INTO ticket_comments (id, ticket_id, user_id, comment, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        
        $params = [
            $id,
            $chamadoId,
            $userId,
            $data['mensagem']
        ];
        
        $this->db->query($sql, $params);
        
        // Atualizar status do ticket se for resposta do admin
        if ($data['user_type'] === 'admin') {
            $this->db->query("UPDATE tickets SET status = 'IN_PROGRESS', updated_at = NOW() WHERE id = ?", [$chamadoId]);
        }
        
        return $id;
    }
    
    public function getRespostas($chamadoId)
    {
        $sql = "SELECT tc.*, 
                       CASE 
                           WHEN u.email = 'admin@sistema.com' THEN 'Administrador'
                           ELSE 'Usuário'
                       END as autor_nome,
                       CASE 
                           WHEN u.email = 'admin@sistema.com' THEN 'admin'
                           ELSE 'user'
                       END as user_type
                FROM ticket_comments tc
                LEFT JOIN users u ON tc.user_id = u.id
                WHERE tc.ticket_id = ?
                ORDER BY tc.created_at ASC";
        
        return $this->db->fetchAll($sql, [$chamadoId]);
    }
    
    public function fecharChamado($id, $adminId)
    {
        $sql = "UPDATE tickets SET status = 'CLOSED', updated_at = NOW() WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function getChamadosByRepresentative($representativeId, $filters = [])
    {
        $whereClause = "WHERE t.created_by_representative_id = ?";
        $params = [$representativeId];
        
        if (!empty($filters['status'])) {
            $whereClause .= " AND t.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['search'])) {
            $whereClause .= " AND (t.assunto LIKE ? OR t.descricao LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        
        $sql = "SELECT t.*, 
                       (SELECT COUNT(*) FROM ticket_comments tc WHERE tc.ticket_id = t.id) as total_respostas
                FROM tickets t 
                $whereClause 
                ORDER BY t.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Valida e corrige o valor do ENUM produto
     * Verifica dinamicamente os valores válidos no banco de dados
     */
    private function validateProdutoEnum($produto)
    {
        // Valores esperados do formulário
        $expectedValues = ['PAGSEGURO', 'CDX_EVO', 'CDC', 'GOOGLE', 'MEMBRO_KEY', 'OUTROS', 'PAGBANK'];
        
        // Se já é um valor esperado, verificar se está no ENUM do banco
        if (in_array($produto, $expectedValues)) {
            // Buscar valores válidos do ENUM no banco de dados
            $enumValues = [];
            try {
                $enumCheck = $this->db->fetchAll("SHOW COLUMNS FROM tickets WHERE Field = 'produto'");
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
            } catch (\Exception $e) {
                write_log("Erro ao buscar ENUM produto: " . $e->getMessage(), 'app.log');
            }
            
            // Se o valor está no ENUM do banco, usar diretamente
            if (!empty($enumValues) && in_array($produto, $enumValues)) {
                return $produto;
            }
            
            // Se não está no ENUM, tentar usar um valor que existe
            if (!empty($enumValues)) {
                // Tentar OUTROS primeiro (mais comum)
                if (in_array('OUTROS', $enumValues)) {
                    write_log("Produto '{$produto}' não encontrado no ENUM. Usando 'OUTROS' como fallback.", 'app.log');
                    return 'OUTROS';
                }
                // Usar o primeiro valor disponível
                write_log("Produto '{$produto}' não encontrado no ENUM. Usando '{$enumValues[0]}' como fallback.", 'app.log');
                return $enumValues[0];
            }
        }
        
        // Se não é um valor esperado ou não conseguiu validar, usar OUTROS
        write_log("Produto '{$produto}' inválido. Usando 'OUTROS' como padrão.", 'app.log');
        return 'OUTROS';
    }
}

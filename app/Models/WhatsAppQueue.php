<?php

namespace App\Models;

use App\Core\Database;

class WhatsAppQueue
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Criar fila
     */
    public function create($data)
    {
        $sql = "INSERT INTO whatsapp_queues 
                (name, description, color, greeting_message, is_active, max_chats_per_user, 
                 auto_assign, business_hours_start, business_hours_end, timezone, created_by_user_id, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $params = [
            $data['name'],
            $data['description'] ?? null,
            $data['color'] ?? '#3B82F6',
            $data['greeting_message'] ?? null,
            $data['is_active'] ?? true,
            $data['max_chats_per_user'] ?? 5,
            $data['auto_assign'] ?? true,
            $data['business_hours_start'] ?? null,
            $data['business_hours_end'] ?? null,
            $data['timezone'] ?? 'America/Sao_Paulo',
            $data['created_by_user_id'] ?? null
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    /**
     * Buscar por ID
     */
    public function findById($id)
    {
        $sql = "SELECT q.*, u.name as created_by_name 
                FROM whatsapp_queues q
                LEFT JOIN users u ON q.created_by_user_id = u.id
                WHERE q.id = ?";
        
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Listar todas as filas
     */
    public function getAll($filters = [])
    {
        $sql = "SELECT q.*, 
                (SELECT COUNT(*) FROM whatsapp_queue_users qu WHERE qu.queue_id = q.id AND qu.is_active = TRUE) as users_count,
                (SELECT COUNT(*) FROM whatsapp_conversations c WHERE c.queue_id = q.id AND c.status = 'OPEN') as open_chats
                FROM whatsapp_queues q
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['is_active'])) {
            $sql .= " AND q.is_active = ?";
            $params[] = $filters['is_active'];
        }
        
        $sql .= " ORDER BY q.name ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Atualizar fila
     */
    public function update($id, $data)
    {
        $fields = [];
        $params = [];
        
        $allowedFields = [
            'name', 'description', 'color', 'greeting_message', 'is_active', 
            'max_chats_per_user', 'auto_assign', 'business_hours_start', 
            'business_hours_end', 'timezone'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $fields[] = "updated_at = NOW()";
        $params[] = $id;
        
        $sql = "UPDATE whatsapp_queues SET " . implode(', ', $fields) . " WHERE id = ?";
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Deletar fila
     */
    public function delete($id)
    {
        $sql = "DELETE FROM whatsapp_queues WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    /**
     * Adicionar usuário à fila
     */
    public function addUser($queueId, $userId)
    {
        $sql = "INSERT INTO whatsapp_queue_users (queue_id, user_id, is_active, created_at) 
                VALUES (?, ?, TRUE, NOW())
                ON DUPLICATE KEY UPDATE is_active = TRUE";
        
        return $this->db->query($sql, [$queueId, $userId]);
    }
    
    /**
     * Remover usuário da fila
     */
    public function removeUser($queueId, $userId)
    {
        $sql = "UPDATE whatsapp_queue_users SET is_active = FALSE WHERE queue_id = ? AND user_id = ?";
        return $this->db->query($sql, [$queueId, $userId]);
    }
    
    /**
     * Listar usuários da fila
     */
    public function getUsers($queueId)
    {
        $sql = "SELECT u.*, qu.is_active as queue_user_active
                FROM whatsapp_queue_users qu
                INNER JOIN users u ON qu.user_id = u.id
                WHERE qu.queue_id = ? AND qu.is_active = TRUE AND u.whatsapp_is_active = TRUE
                ORDER BY u.name ASC";
        
        return $this->db->fetchAll($sql, [$queueId]);
    }
    
    /**
     * Buscar próximo atendente disponível (round-robin)
     */
    public function getNextAvailableUser($queueId)
    {
        $sql = "SELECT u.id, u.name, 
                (SELECT COUNT(*) FROM whatsapp_attendances a 
                 WHERE a.user_id = u.id AND a.status = 'OPEN') as current_chats,
                us.last_activity_at
                FROM whatsapp_queue_users qu
                INNER JOIN users u ON qu.user_id = u.id
                LEFT JOIN whatsapp_user_sessions us ON u.id = us.user_id
                WHERE qu.queue_id = ? AND qu.is_active = TRUE 
                AND u.whatsapp_is_active = TRUE 
                AND u.status = 'ACTIVE'
                AND (us.is_online = TRUE OR us.is_online IS NULL)
                HAVING current_chats < COALESCE(u.whatsapp_max_chats, 5)
                ORDER BY current_chats ASC, us.last_activity_at ASC
                LIMIT 1";
        
        return $this->db->fetch($sql, [$queueId]);
    }
}


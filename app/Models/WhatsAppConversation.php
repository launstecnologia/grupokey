<?php

namespace App\Models;

use App\Core\Database;

class WhatsAppConversation
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Criar ou buscar conversa
     */
    public function findOrCreate($instanceId, $contactId, $queueId = null)
    {
        // Verificar se já existe conversa aberta
        $sql = "SELECT * FROM whatsapp_conversations 
                WHERE instance_id = ? AND contact_id = ? AND status != 'CLOSED'
                ORDER BY created_at DESC LIMIT 1";
        
        $existing = $this->db->fetch($sql, [$instanceId, $contactId]);
        
        if ($existing) {
            return $existing;
        }
        
        // Criar nova conversa
        $sql = "INSERT INTO whatsapp_conversations 
                (instance_id, contact_id, queue_id, status, created_at, updated_at) 
                VALUES (?, ?, ?, 'OPEN', NOW(), NOW())";
        
        $this->db->query($sql, [$instanceId, $contactId, $queueId]);
        $conversationId = $this->db->lastInsertId();
        
        return $this->findById($conversationId);
    }
    
    /**
     * Buscar por ID
     */
    public function findById($id)
    {
        $sql = "SELECT c.*, 
                ct.phone_number, ct.name as contact_name, ct.profile_picture_url as contact_picture,
                i.name as instance_name,
                q.name as queue_name, q.color as queue_color
                FROM whatsapp_conversations c
                LEFT JOIN whatsapp_contacts ct ON c.contact_id = ct.id
                LEFT JOIN whatsapp_instances i ON c.instance_id = i.id
                LEFT JOIN whatsapp_queues q ON c.queue_id = q.id
                WHERE c.id = ?";
        
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Listar conversas com filtros
     */
    public function getAll($filters = [])
    {
        $sql = "SELECT c.*, 
                ct.phone_number, ct.name as contact_name, ct.profile_picture_url as contact_picture,
                i.name as instance_name,
                q.name as queue_name, q.color as queue_color,
                a.user_id as current_attendant_id, u.name as current_attendant_name,
                (SELECT COUNT(*) FROM whatsapp_messages m WHERE m.conversation_id = c.id AND m.is_read = FALSE AND m.from_me = FALSE) as unread_messages
                FROM whatsapp_conversations c
                LEFT JOIN whatsapp_contacts ct ON c.contact_id = ct.id
                LEFT JOIN whatsapp_instances i ON c.instance_id = i.id
                LEFT JOIN whatsapp_queues q ON c.queue_id = q.id
                LEFT JOIN whatsapp_attendances a ON c.id = a.conversation_id AND a.status = 'OPEN'
                LEFT JOIN users u ON a.user_id = u.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND c.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['queue_id'])) {
            $sql .= " AND c.queue_id = ?";
            $params[] = $filters['queue_id'];
        }
        
        if (!empty($filters['instance_id'])) {
            $sql .= " AND c.instance_id = ?";
            $params[] = $filters['instance_id'];
        }
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND a.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (ct.name LIKE ? OR ct.phone_number LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }
        
        $sql .= " ORDER BY c.last_message_at DESC, c.created_at DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = $filters['limit'];
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Atualizar conversa
     */
    public function update($id, $data)
    {
        $fields = [];
        $params = [];
        
        $allowedFields = [
            'queue_id', 'status', 'unread_count', 'last_message_at', 
            'last_message_preview', 'priority', 'tags', 'notes'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                if ($field === 'tags' && is_array($data[$field])) {
                    $fields[] = "{$field} = ?";
                    $params[] = json_encode($data[$field]);
                } else {
                    $fields[] = "{$field} = ?";
                    $params[] = $data[$field];
                }
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $fields[] = "updated_at = NOW()";
        $params[] = $id;
        
        $sql = "UPDATE whatsapp_conversations SET " . implode(', ', $fields) . " WHERE id = ?";
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Atualizar última mensagem
     */
    public function updateLastMessage($id, $preview)
    {
        $sql = "UPDATE whatsapp_conversations 
                SET last_message_at = NOW(), last_message_preview = ?, updated_at = NOW() 
                WHERE id = ?";
        
        return $this->db->query($sql, [$preview, $id]);
    }
    
    /**
     * Incrementar não lidas
     */
    public function incrementUnread($id)
    {
        $sql = "UPDATE whatsapp_conversations 
                SET unread_count = unread_count + 1, updated_at = NOW() 
                WHERE id = ?";
        
        return $this->db->query($sql, [$id]);
    }
    
    /**
     * Zerar não lidas
     */
    public function resetUnread($id)
    {
        $sql = "UPDATE whatsapp_conversations 
                SET unread_count = 0, updated_at = NOW() 
                WHERE id = ?";
        
        return $this->db->query($sql, [$id]);
    }
    
    /**
     * Fechar conversa
     */
    public function close($id)
    {
        $sql = "UPDATE whatsapp_conversations 
                SET status = 'CLOSED', updated_at = NOW() 
                WHERE id = ?";
        
        return $this->db->query($sql, [$id]);
    }
}


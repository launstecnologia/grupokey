<?php

namespace App\Models;

use App\Core\Database;

class WhatsAppMessage
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Criar mensagem
     */
    public function create($data)
    {
        $sql = "INSERT INTO whatsapp_messages 
                (conversation_id, attendance_id, instance_id, message_key, remote_jid, from_me, 
                 message_type, body, media_url, media_mime_type, media_name, media_size, caption, 
                 quoted_message_id, is_read, timestamp, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $params = [
            $data['conversation_id'],
            $data['attendance_id'] ?? null,
            $data['instance_id'],
            $data['message_key'] ?? null,
            $data['remote_jid'],
            $data['from_me'] ?? false,
            $data['message_type'] ?? 'TEXT',
            $data['body'] ?? null,
            $data['media_url'] ?? null,
            $data['media_mime_type'] ?? null,
            $data['media_name'] ?? null,
            $data['media_size'] ?? null,
            $data['caption'] ?? null,
            $data['quoted_message_id'] ?? null,
            $data['is_read'] ?? false,
            $data['timestamp'] ?? time()
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    /**
     * Buscar por ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM whatsapp_messages WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Buscar por message_key
     */
    public function findByMessageKey($messageKey)
    {
        $sql = "SELECT * FROM whatsapp_messages WHERE message_key = ?";
        return $this->db->fetch($sql, [$messageKey]);
    }
    
    /**
     * Listar mensagens de uma conversa
     */
    public function getByConversation($conversationId, $limit = 50, $offset = 0)
    {
        $sql = "SELECT * FROM whatsapp_messages 
                WHERE conversation_id = ? AND is_deleted = FALSE
                ORDER BY timestamp ASC, created_at ASC
                LIMIT ? OFFSET ?";
        
        return $this->db->fetchAll($sql, [$conversationId, $limit, $offset]);
    }
    
    /**
     * Marcar mensagens como lidas
     */
    public function markAsRead($conversationId, $fromMe = false)
    {
        $sql = "UPDATE whatsapp_messages 
                SET is_read = TRUE, read_at = NOW() 
                WHERE conversation_id = ? AND from_me = ? AND is_read = FALSE";
        
        return $this->db->query($sql, [$conversationId, $fromMe ? 1 : 0]);
    }
    
    /**
     * Contar mensagens não lidas
     */
    public function countUnread($conversationId, $fromMe = false)
    {
        $sql = "SELECT COUNT(*) as count FROM whatsapp_messages 
                WHERE conversation_id = ? AND from_me = ? AND is_read = FALSE AND is_deleted = FALSE";
        
        $result = $this->db->fetch($sql, [$conversationId, $fromMe ? 1 : 0]);
        return $result['count'] ?? 0;
    }
    
    /**
     * Deletar mensagem
     */
    public function delete($id)
    {
        $sql = "UPDATE whatsapp_messages 
                SET is_deleted = TRUE, deleted_at = NOW() 
                WHERE id = ?";
        
        return $this->db->query($sql, [$id]);
    }
}


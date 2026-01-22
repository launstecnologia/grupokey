<?php

namespace App\Models;

use App\Core\Database;

class WhatsAppAttendance
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Criar atendimento
     */
    public function create($data)
    {
        $sql = "INSERT INTO whatsapp_attendances 
                (conversation_id, user_id, queue_id, status, started_at, created_at) 
                VALUES (?, ?, ?, 'OPEN', NOW(), NOW())";
        
        $params = [
            $data['conversation_id'],
            $data['user_id'],
            $data['queue_id'] ?? null
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    /**
     * Buscar atendimento ativo de uma conversa
     */
    public function findActiveByConversation($conversationId)
    {
        $sql = "SELECT a.*, u.name as user_name, u.email as user_email
                FROM whatsapp_attendances a
                INNER JOIN users u ON a.user_id = u.id
                WHERE a.conversation_id = ? AND a.status = 'OPEN'
                ORDER BY a.started_at DESC
                LIMIT 1";
        
        return $this->db->fetch($sql, [$conversationId]);
    }
    
    /**
     * Buscar por ID
     */
    public function findById($id)
    {
        $sql = "SELECT a.*, u.name as user_name, u.email as user_email,
                tf.name as transferred_from_name, tt.name as transferred_to_name
                FROM whatsapp_attendances a
                INNER JOIN users u ON a.user_id = u.id
                LEFT JOIN users tf ON a.transferred_from_user_id = tf.id
                LEFT JOIN users tt ON a.transferred_to_user_id = tt.id
                WHERE a.id = ?";
        
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Listar atendimentos de um usuário
     */
    public function getByUser($userId, $status = 'OPEN')
    {
        $sql = "SELECT a.*, c.id as conversation_id, c.status as conversation_status,
                ct.phone_number, ct.name as contact_name,
                q.name as queue_name
                FROM whatsapp_attendances a
                INNER JOIN whatsapp_conversations c ON a.conversation_id = c.id
                INNER JOIN whatsapp_contacts ct ON c.contact_id = ct.id
                LEFT JOIN whatsapp_queues q ON a.queue_id = q.id
                WHERE a.user_id = ? AND a.status = ?
                ORDER BY a.started_at DESC";
        
        return $this->db->fetchAll($sql, [$userId, $status]);
    }
    
    /**
     * Fechar atendimento
     */
    public function close($id, $rating = null, $ratingComment = null)
    {
        $sql = "UPDATE whatsapp_attendances 
                SET status = 'CLOSED', ended_at = NOW(), rating = ?, rating_comment = ?, updated_at = NOW() 
                WHERE id = ?";
        
        return $this->db->query($sql, [$rating, $ratingComment, $id]);
    }
    
    /**
     * Transferir atendimento
     */
    public function transfer($id, $toUserId, $reason = null)
    {
        $attendance = $this->findById($id);
        
        if (!$attendance) {
            return false;
        }
        
        // Fechar atendimento atual
        $this->close($id);
        
        // Criar novo atendimento
        $newAttendance = $this->create([
            'conversation_id' => $attendance['conversation_id'],
            'user_id' => $toUserId,
            'queue_id' => $attendance['queue_id']
        ]);
        
        // Atualizar atendimento original com informações de transferência
        $sql = "UPDATE whatsapp_attendances 
                SET transferred_to_user_id = ?, transferred_reason = ?, transferred_at = NOW() 
                WHERE id = ?";
        
        $this->db->query($sql, [$toUserId, $reason, $id]);
        
        return $newAttendance;
    }
    
    /**
     * Contar atendimentos ativos de um usuário
     */
    public function countActiveByUser($userId)
    {
        $sql = "SELECT COUNT(*) as count FROM whatsapp_attendances 
                WHERE user_id = ? AND status = 'OPEN'";
        
        $result = $this->db->fetch($sql, [$userId]);
        return $result['count'] ?? 0;
    }
}


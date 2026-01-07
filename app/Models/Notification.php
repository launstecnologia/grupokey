<?php

namespace App\Models;

use App\Core\Database;

class Notification
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function create($data)
    {
        $sql = "INSERT INTO crm_notifications (
                    user_id, type, title, message, related_type, related_id
                ) VALUES (?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['user_id'],
            $data['type'],
            $data['title'],
            $data['message'],
            $data['related_type'] ?? null,
            $data['related_id'] ?? null
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function getByUserId($userId, $filters = [])
    {
        $sql = "SELECT * FROM crm_notifications WHERE user_id = ?";
        
        $params = [$userId];
        
        if (isset($filters['is_read'])) {
            $sql .= " AND is_read = ?";
            $params[] = $filters['is_read'] ? 1 : 0;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        if (isset($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = $filters['limit'];
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getUnreadCount($userId)
    {
        $sql = "SELECT COUNT(*) as count FROM crm_notifications WHERE user_id = ? AND is_read = 0";
        $result = $this->db->fetch($sql, [$userId]);
        return $result['count'] ?? 0;
    }
    
    public function markAsRead($id, $userId)
    {
        $sql = "UPDATE crm_notifications SET is_read = 1, read_at = NOW() 
                WHERE id = ? AND user_id = ?";
        
        return $this->db->query($sql, [$id, $userId]);
    }
    
    public function markAllAsRead($userId)
    {
        $sql = "UPDATE crm_notifications SET is_read = 1, read_at = NOW() 
                WHERE user_id = ? AND is_read = 0";
        
        return $this->db->query($sql, [$userId]);
    }
    
    public function delete($id, $userId)
    {
        $sql = "DELETE FROM crm_notifications WHERE id = ? AND user_id = ?";
        return $this->db->query($sql, [$id, $userId]);
    }
}


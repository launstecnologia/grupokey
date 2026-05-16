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
                    user_id, recipient_type, representative_id, type, title, message, related_type, related_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $recipientType = $data['recipient_type'] ?? 'user';
        $userId = null;
        $representativeId = null;

        if ($recipientType === 'representative') {
            $representativeId = $data['representative_id'] ?? null;
        } else {
            $userId = $data['user_id'] ?? null;
            $recipientType = 'user';
        }
        
        $params = [
            $userId,
            $recipientType,
            $representativeId,
            $data['type'],
            $data['title'],
            $data['message'],
            $data['related_type'] ?? null,
            $data['related_id'] ?? null
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }

    public function getByRecipient(string $recipientType, int $recipientId, $filters = [])
    {
        if ($recipientType === 'representative') {
            $sql = "SELECT * FROM crm_notifications WHERE recipient_type = 'representative' AND representative_id = ?";
        } else {
            $sql = "SELECT * FROM crm_notifications WHERE recipient_type = 'user' AND user_id = ?";
        }

        $params = [$recipientId];

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
    
    public function getByUserId($userId, $filters = [])
    {
        return $this->getByRecipient('user', (int) $userId, $filters);
    }

    public function getByRepresentativeId($representativeId, $filters = [])
    {
        return $this->getByRecipient('representative', (int) $representativeId, $filters);
    }

    public function getUnreadCountByRecipient(string $recipientType, int $recipientId)
    {
        if ($recipientType === 'representative') {
            $sql = "SELECT COUNT(*) as count FROM crm_notifications WHERE recipient_type = 'representative' AND representative_id = ? AND is_read = 0";
        } else {
            $sql = "SELECT COUNT(*) as count FROM crm_notifications WHERE recipient_type = 'user' AND user_id = ? AND is_read = 0";
        }

        $result = $this->db->fetch($sql, [$recipientId]);
        return $result['count'] ?? 0;
    }
    
    public function getUnreadCount($userId)
    {
        return $this->getUnreadCountByRecipient('user', (int) $userId);
    }
    
    public function markAsRead($id, $recipientId, string $recipientType = 'user')
    {
        if ($recipientType === 'representative') {
            $sql = "UPDATE crm_notifications SET is_read = 1, read_at = NOW() 
                    WHERE id = ? AND recipient_type = 'representative' AND representative_id = ?";
        } else {
            $sql = "UPDATE crm_notifications SET is_read = 1, read_at = NOW() 
                    WHERE id = ? AND recipient_type = 'user' AND user_id = ?";
        }
        
        return $this->db->query($sql, [$id, $recipientId]);
    }
    
    public function markAllAsRead($recipientId, string $recipientType = 'user')
    {
        if ($recipientType === 'representative') {
            $sql = "UPDATE crm_notifications SET is_read = 1, read_at = NOW() 
                    WHERE recipient_type = 'representative' AND representative_id = ? AND is_read = 0";
        } else {
            $sql = "UPDATE crm_notifications SET is_read = 1, read_at = NOW() 
                    WHERE recipient_type = 'user' AND user_id = ? AND is_read = 0";
        }
        
        return $this->db->query($sql, [$recipientId]);
    }
    
    public function delete($id, $recipientId, string $recipientType = 'user')
    {
        if ($recipientType === 'representative') {
            $sql = "DELETE FROM crm_notifications WHERE id = ? AND recipient_type = 'representative' AND representative_id = ?";
        } else {
            $sql = "DELETE FROM crm_notifications WHERE id = ? AND recipient_type = 'user' AND user_id = ?";
        }

        return $this->db->query($sql, [$id, $recipientId]);
    }
}


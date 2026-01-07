<?php

namespace App\Models;

use App\Core\Database;

class DealTask
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function create($data)
    {
        $sql = "INSERT INTO crm_deal_tasks (
                    deal_id, task_type, title, description, scheduled_at, 
                    reminder_minutes, created_by_user_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['deal_id'],
            $data['task_type'] ?? 'OTHER',
            $data['title'],
            $data['description'] ?? null,
            $data['scheduled_at'],
            $data['reminder_minutes'] ?? 15,
            $data['created_by_user_id'] ?? null
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data)
    {
        $sql = "UPDATE crm_deal_tasks SET 
                task_type = ?, title = ?, description = ?, scheduled_at = ?, 
                reminder_minutes = ?, updated_at = NOW()
                WHERE id = ?";
        
        $params = [
            $data['task_type'] ?? 'OTHER',
            $data['title'],
            $data['description'] ?? null,
            $data['scheduled_at'],
            $data['reminder_minutes'] ?? 15,
            $id
        ];
        
        return $this->db->query($sql, $params);
    }
    
    public function findById($id)
    {
        $sql = "SELECT t.*, 
                       d.title as deal_title,
                       u.name as created_by_user_name
                FROM crm_deal_tasks t
                INNER JOIN crm_deals d ON t.deal_id = d.id
                LEFT JOIN users u ON t.created_by_user_id = u.id
                WHERE t.id = ?";
        
        return $this->db->fetch($sql, [$id]);
    }
    
    public function getByDealId($dealId, $filters = [])
    {
        // Verificar se a tabela existe
        try {
            $tableExists = $this->db->fetch("SHOW TABLES LIKE 'crm_deal_tasks'");
            if (!$tableExists) {
                return [];
            }
        } catch (\Exception $e) {
            return [];
        }
        
        $sql = "SELECT t.*, u.name as created_by_user_name
                FROM crm_deal_tasks t
                LEFT JOIN users u ON t.created_by_user_id = u.id
                WHERE t.deal_id = ?";
        
        $params = [$dealId];
        
        if (isset($filters['is_completed'])) {
            $sql .= " AND t.is_completed = ?";
            $params[] = $filters['is_completed'] ? 1 : 0;
        }
        
        $sql .= " ORDER BY t.scheduled_at ASC, t.created_at DESC";
        
        try {
            return $this->db->fetchAll($sql, $params);
        } catch (\Exception $e) {
            // Se houver erro, retornar array vazio
            return [];
        }
    }
    
    public function getUpcomingTasks($userId = null, $limit = 10)
    {
        $sql = "SELECT t.*, d.title as deal_title, d.id as deal_id
                FROM crm_deal_tasks t
                INNER JOIN crm_deals d ON t.deal_id = d.id
                WHERE t.is_completed = 0 
                AND t.scheduled_at >= NOW()";
        
        $params = [];
        
        if ($userId) {
            $sql .= " AND (d.assigned_to_user_id = ? OR t.created_by_user_id = ?)";
            $params[] = $userId;
            $params[] = $userId;
        }
        
        $sql .= " ORDER BY t.scheduled_at ASC LIMIT ?";
        $params[] = $limit;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getTasksForReminder($minutesBefore = 15)
    {
        $sql = "SELECT t.*, d.title as deal_title, d.assigned_to_user_id, 
                       u.email as user_email, u.name as user_name
                FROM crm_deal_tasks t
                INNER JOIN crm_deals d ON t.deal_id = d.id
                LEFT JOIN users u ON d.assigned_to_user_id = u.id
                WHERE t.is_completed = 0 
                AND t.reminder_sent = 0
                AND t.scheduled_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL ? MINUTE)
                AND t.reminder_minutes <= ?";
        
        return $this->db->fetchAll($sql, [$minutesBefore, $minutesBefore]);
    }
    
    public function markAsCompleted($id)
    {
        $sql = "UPDATE crm_deal_tasks SET 
                is_completed = 1, completed_at = NOW(), updated_at = NOW()
                WHERE id = ?";
        
        return $this->db->query($sql, [$id]);
    }
    
    public function markReminderSent($id)
    {
        $sql = "UPDATE crm_deal_tasks SET reminder_sent = 1 WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function delete($id)
    {
        $sql = "DELETE FROM crm_deal_tasks WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
}


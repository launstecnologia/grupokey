<?php

namespace App\Models;

use App\Core\Database;

class DealActivity
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function create($data)
    {
        $sql = "INSERT INTO crm_deal_activities (
                    deal_id, activity_type, title, description, 
                    activity_date, duration_minutes, old_value, new_value, created_by_user_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['deal_id'],
            $data['activity_type'] ?? 'NOTE',
            $data['title'] ?? null,
            $data['description'] ?? null,
            $data['activity_date'] ?? date('Y-m-d H:i:s'),
            $data['duration_minutes'] ?? null,
            $data['old_value'] ?? null,
            $data['new_value'] ?? null,
            $data['created_by_user_id'] ?? null
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function getByDealId($dealId, $limit = 50)
    {
        $sql = "SELECT a.*, u.name as created_by_user_name
                FROM crm_deal_activities a
                LEFT JOIN users u ON a.created_by_user_id = u.id
                WHERE a.deal_id = ?
                ORDER BY a.created_at DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$dealId, $limit]);
    }
    
    public function delete($id)
    {
        $sql = "DELETE FROM crm_deal_activities WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
}


<?php

namespace App\Models;

use App\Core\Database;

class Pipeline
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function create($data)
    {
        $sql = "INSERT INTO crm_pipelines (name, description, color, is_default, sort_order, is_active, created_by_user_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['name'],
            $data['description'] ?? null,
            $data['color'] ?? '#3B82F6',
            !empty($data['is_default']) ? 1 : 0,
            $data['sort_order'] ?? 0,
            !empty($data['is_active']) ? 1 : 0,
            $data['created_by_user_id'] ?? null
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data)
    {
        $sql = "UPDATE crm_pipelines SET 
                name = ?, description = ?, color = ?, is_default = ?, sort_order = ?, is_active = ?, updated_at = NOW()
                WHERE id = ?";
        
        $params = [
            $data['name'],
            $data['description'] ?? null,
            $data['color'] ?? '#3B82F6',
            !empty($data['is_default']) ? 1 : 0,
            $data['sort_order'] ?? 0,
            !empty($data['is_active']) ? 1 : 0,
            $id
        ];
        
        return $this->db->query($sql, $params);
    }
    
    public function findById($id)
    {
        $sql = "SELECT p.*, u.name as created_by_user_name
                FROM crm_pipelines p
                LEFT JOIN users u ON p.created_by_user_id = u.id
                WHERE p.id = ?";
        
        return $this->db->fetch($sql, [$id]);
    }
    
    public function getAll($filters = [])
    {
        $sql = "SELECT p.*, u.name as created_by_user_name,
                       (SELECT COUNT(*) FROM crm_deals d WHERE d.pipeline_id = p.id AND d.status = 'ACTIVE') as active_deals_count
                FROM crm_pipelines p
                LEFT JOIN users u ON p.created_by_user_id = u.id
                WHERE 1=1";
        
        $params = [];
        
        if (isset($filters['is_active'])) {
            $sql .= " AND p.is_active = ?";
            $params[] = $filters['is_active'] ? 1 : 0;
        }
        
        $sql .= " ORDER BY p.sort_order ASC, p.name ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getDefault()
    {
        $sql = "SELECT * FROM crm_pipelines WHERE is_default = 1 AND is_active = 1 LIMIT 1";
        return $this->db->fetch($sql);
    }
    
    public function delete($id)
    {
        // Verificar se há deals neste pipeline
        $dealsCount = $this->db->fetch("SELECT COUNT(*) as count FROM crm_deals WHERE pipeline_id = ?", [$id]);
        if ($dealsCount && $dealsCount['count'] > 0) {
            throw new \Exception('Não é possível excluir um pipeline que possui deals. Transfira os deals primeiro.');
        }
        
        $sql = "DELETE FROM crm_pipelines WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function updateSortOrder($id, $sortOrder)
    {
        $sql = "UPDATE crm_pipelines SET sort_order = ? WHERE id = ?";
        return $this->db->query($sql, [$sortOrder, $id]);
    }
}


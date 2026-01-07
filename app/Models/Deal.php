<?php

namespace App\Models;

use App\Core\Database;

class Deal
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function create($data)
    {
        $sql = "INSERT INTO crm_deals (
                    pipeline_id, stage_id, title, description, value, currency, 
                    expected_close_date, probability, priority, status,
                    establishment_id, representative_id, assigned_to_user_id, 
                    sort_order, created_by_user_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['pipeline_id'],
            $data['stage_id'],
            $data['title'],
            $data['description'] ?? null,
            $data['value'] ?? null,
            $data['currency'] ?? 'BRL',
            $data['expected_close_date'] ?? null,
            $data['probability'] ?? 0,
            $data['priority'] ?? 'MEDIUM',
            $data['status'] ?? 'ACTIVE',
            $data['establishment_id'] ?? null,
            $data['representative_id'] ?? null,
            $data['assigned_to_user_id'] ?? null,
            $data['sort_order'] ?? 0,
            $data['created_by_user_id'] ?? null
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data)
    {
        $sql = "UPDATE crm_deals SET 
                pipeline_id = ?, stage_id = ?, title = ?, description = ?, value = ?, currency = ?,
                expected_close_date = ?, actual_close_date = ?, probability = ?, priority = ?, status = ?,
                establishment_id = ?, representative_id = ?, assigned_to_user_id = ?,
                sort_order = ?, updated_at = NOW()
                WHERE id = ?";
        
        $params = [
            $data['pipeline_id'] ?? null,
            $data['stage_id'] ?? null,
            $data['title'] ?? null,
            $data['description'] ?? null,
            $data['value'] ?? null,
            $data['currency'] ?? 'BRL',
            $data['expected_close_date'] ?? null,
            $data['actual_close_date'] ?? null,
            $data['probability'] ?? 0,
            $data['priority'] ?? 'MEDIUM',
            $data['status'] ?? 'ACTIVE',
            $data['establishment_id'] ?? null,
            $data['representative_id'] ?? null,
            $data['assigned_to_user_id'] ?? null,
            $data['sort_order'] ?? 0,
            $id
        ];
        
        return $this->db->query($sql, $params);
    }
    
    public function findById($id)
    {
        $sql = "SELECT d.*, 
                       p.name as pipeline_name,
                       s.name as stage_name, s.color as stage_color,
                       e.nome_fantasia as establishment_name,
                       r.nome_completo as representative_name,
                       u.name as assigned_user_name,
                       uc.name as created_by_user_name
                FROM crm_deals d
                INNER JOIN crm_pipelines p ON d.pipeline_id = p.id
                INNER JOIN crm_stages s ON d.stage_id = s.id
                LEFT JOIN establishments e ON d.establishment_id = e.id
                LEFT JOIN representatives r ON d.representative_id = r.id
                LEFT JOIN users u ON d.assigned_to_user_id = u.id
                LEFT JOIN users uc ON d.created_by_user_id = uc.id
                WHERE d.id = ?";
        
        return $this->db->fetch($sql, [$id]);
    }
    
    public function getByPipelineId($pipelineId, $filters = [])
    {
        $sql = "SELECT d.*, 
                       s.name as stage_name, s.color as stage_color, s.sort_order as stage_sort_order,
                       e.nome_fantasia as establishment_name,
                       r.nome_completo as representative_name,
                       u.name as assigned_user_name
                FROM crm_deals d
                INNER JOIN crm_stages s ON d.stage_id = s.id
                LEFT JOIN establishments e ON d.establishment_id = e.id
                LEFT JOIN representatives r ON d.representative_id = r.id
                LEFT JOIN users u ON d.assigned_to_user_id = u.id
                WHERE d.pipeline_id = ?";
        
        $params = [$pipelineId];
        
        if (isset($filters['status'])) {
            $sql .= " AND d.status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['stage_id'])) {
            $sql .= " AND d.stage_id = ?";
            $params[] = $filters['stage_id'];
        }
        
        $sql .= " ORDER BY s.sort_order ASC, d.sort_order ASC, d.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getByStageId($stageId)
    {
        $sql = "SELECT d.*, 
                       e.nome_fantasia as establishment_name,
                       r.nome_completo as representative_name,
                       u.name as assigned_user_name
                FROM crm_deals d
                LEFT JOIN establishments e ON d.establishment_id = e.id
                LEFT JOIN representatives r ON d.representative_id = r.id
                LEFT JOIN users u ON d.assigned_to_user_id = u.id
                WHERE d.stage_id = ? AND d.status = 'ACTIVE'
                ORDER BY d.sort_order ASC, d.created_at DESC";
        
        return $this->db->fetchAll($sql, [$stageId]);
    }
    
    public function moveToStage($dealId, $stageId, $sortOrder = 0)
    {
        $sql = "UPDATE crm_deals SET stage_id = ?, sort_order = ?, updated_at = NOW() WHERE id = ?";
        return $this->db->query($sql, [$stageId, $sortOrder, $dealId]);
    }
    
    public function updateSortOrder($id, $sortOrder)
    {
        $sql = "UPDATE crm_deals SET sort_order = ? WHERE id = ?";
        return $this->db->query($sql, [$sortOrder, $id]);
    }
    
    public function delete($id)
    {
        $sql = "DELETE FROM crm_deals WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function getStats($pipelineId = null)
    {
        $where = $pipelineId ? "WHERE pipeline_id = ?" : "";
        $params = $pipelineId ? [$pipelineId] : [];
        
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'ACTIVE' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'WON' THEN 1 ELSE 0 END) as won,
                SUM(CASE WHEN status = 'LOST' THEN 1 ELSE 0 END) as lost,
                SUM(CASE WHEN value IS NOT NULL THEN value ELSE 0 END) as total_value,
                SUM(CASE WHEN status = 'WON' AND value IS NOT NULL THEN value ELSE 0 END) as won_value
                FROM crm_deals {$where}";
        
        return $this->db->fetch($sql, $params);
    }
}


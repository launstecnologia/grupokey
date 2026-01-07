<?php

namespace App\Models;

use App\Core\Database;

class Stage
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function create($data)
    {
        $sql = "INSERT INTO crm_stages (pipeline_id, name, color, sort_order, is_final, is_active) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['pipeline_id'],
            $data['name'],
            $data['color'] ?? '#6B7280',
            $data['sort_order'] ?? 0,
            !empty($data['is_final']) ? 1 : 0,
            !empty($data['is_active']) ? 1 : 0
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data)
    {
        $sql = "UPDATE crm_stages SET 
                name = ?, color = ?, sort_order = ?, is_final = ?, is_active = ?, updated_at = NOW()
                WHERE id = ?";
        
        $params = [
            $data['name'],
            $data['color'] ?? '#6B7280',
            $data['sort_order'] ?? 0,
            !empty($data['is_final']) ? 1 : 0,
            !empty($data['is_active']) ? 1 : 0,
            $id
        ];
        
        return $this->db->query($sql, $params);
    }
    
    public function findById($id)
    {
        $sql = "SELECT s.*, p.name as pipeline_name
                FROM crm_stages s
                INNER JOIN crm_pipelines p ON s.pipeline_id = p.id
                WHERE s.id = ?";
        
        return $this->db->fetch($sql, [$id]);
    }
    
    public function getByPipelineId($pipelineId, $filters = [])
    {
        $sql = "SELECT s.*, 
                       (SELECT COUNT(*) FROM crm_deals d WHERE d.stage_id = s.id AND d.status = 'ACTIVE') as deals_count
                FROM crm_stages s
                WHERE s.pipeline_id = ?";
        
        $params = [$pipelineId];
        
        if (isset($filters['is_active'])) {
            $sql .= " AND s.is_active = ?";
            $params[] = $filters['is_active'] ? 1 : 0;
        }
        
        $sql .= " ORDER BY s.sort_order ASC, s.name ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function delete($id)
    {
        // Verificar se há deals neste stage
        $dealsCount = $this->db->fetch("SELECT COUNT(*) as count FROM crm_deals WHERE stage_id = ?", [$id]);
        if ($dealsCount && $dealsCount['count'] > 0) {
            throw new \Exception('Não é possível excluir um stage que possui deals. Transfira os deals primeiro.');
        }
        
        $sql = "DELETE FROM crm_stages WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function updateSortOrder($id, $sortOrder)
    {
        $sql = "UPDATE crm_stages SET sort_order = ? WHERE id = ?";
        return $this->db->query($sql, [$sortOrder, $id]);
    }
    
    public function updateSortOrders($stageOrders)
    {
        $this->db->beginTransaction();
        try {
            foreach ($stageOrders as $order => $stageId) {
                $this->updateSortOrder($stageId, $order);
            }
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}


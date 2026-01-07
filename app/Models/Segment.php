<?php

namespace App\Models;

use App\Core\Database;

class Segment
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function create($data)
    {
        $sql = "INSERT INTO segments (nome, descricao, status) VALUES (?, ?, ?)";
        $params = [
            $data['nome'],
            $data['descricao'] ?? null,
            $data['status'] ?? 'ACTIVE'
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function findById($id)
    {
        $sql = "SELECT * FROM segments WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    public function findByName($name)
    {
        $sql = "SELECT * FROM segments WHERE nome = ? LIMIT 1";
        return $this->db->fetch($sql, [$name]);
    }
    
    public function getAll($filters = [])
    {
        $sql = "SELECT * FROM segments WHERE 1=1";
        $params = [];
        
        if (isset($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['search'])) {
            $sql .= " AND (nome LIKE ? OR descricao LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }
        
        $sql .= " ORDER BY nome ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getActive()
    {
        return $this->getAll(['status' => 'ACTIVE']);
    }
    
    public function update($id, $data)
    {
        $sql = "UPDATE segments SET nome = ?, descricao = ?, status = ?, updated_at = NOW() WHERE id = ?";
        $params = [
            $data['nome'],
            $data['descricao'] ?? null,
            $data['status'] ?? 'ACTIVE',
            $id
        ];
        
        return $this->db->query($sql, $params);
    }
    
    public function delete($id)
    {
        $sql = "DELETE FROM segments WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function toggleStatus($id)
    {
        $segment = $this->findById($id);
        if (!$segment) {
            return false;
        }
        
        $newStatus = $segment['status'] === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
        $sql = "UPDATE segments SET status = ?, updated_at = NOW() WHERE id = ?";
        return $this->db->query($sql, [$newStatus, $id]);
    }
}


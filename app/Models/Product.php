<?php

namespace App\Models;

use App\Core\Database;

class Product
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function getAll($filters = [])
    {
        $sql = "SELECT * FROM products WHERE is_active = 1";
        $params = [];
        
        if (!empty($filters['search'])) {
            $sql .= " AND (name LIKE ? OR description LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        
        $sql .= " ORDER BY name ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function findById($id)
    {
        $sql = "SELECT * FROM products WHERE id = ? AND is_active = 1";
        return $this->db->fetch($sql, [$id]);
    }
    
    public function findByName($name)
    {
        $sql = "SELECT * FROM products WHERE name = ? AND is_active = 1";
        return $this->db->fetch($sql, [$name]);
    }
    
    public function create($data)
    {
        $id = uniqid();
        
        $sql = "INSERT INTO products (id, name, description, is_active, created_at, updated_at) 
                VALUES (?, ?, ?, ?, NOW(), NOW())";
        
        $params = [
            $id,
            $data['name'],
            $data['description'] ?? null,
            $data['is_active'] ?? true
        ];
        
        $this->db->query($sql, $params);
        return $id;
    }
    
    public function update($id, $data)
    {
        $sql = "UPDATE products SET name = ?, description = ?, is_active = ?, updated_at = NOW() 
                WHERE id = ?";
        
        $params = [
            $data['name'],
            $data['description'] ?? null,
            $data['is_active'] ?? true,
            $id
        ];
        
        return $this->db->query($sql, $params);
    }
    
    public function delete($id)
    {
        $sql = "UPDATE products SET is_active = 0, updated_at = NOW() WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function getStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as ativos,
                    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inativos
                FROM products";
        
        return $this->db->fetch($sql);
    }
}

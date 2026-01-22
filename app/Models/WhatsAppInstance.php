<?php

namespace App\Models;

use App\Core\Database;

class WhatsAppInstance
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Criar nova instância
     */
    public function create($data)
    {
        $sql = "INSERT INTO whatsapp_instances 
                (name, instance_key, evolution_api_url, evolution_api_key, webhook_url, max_connections, is_active, created_by_user_id, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $params = [
            $data['name'],
            $data['instance_key'],
            $data['evolution_api_url'],
            $data['evolution_api_key'],
            $data['webhook_url'] ?? null,
            $data['max_connections'] ?? 10,
            $data['is_active'] ?? true,
            $data['created_by_user_id'] ?? null
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    /**
     * Buscar por ID
     */
    public function findById($id)
    {
        $sql = "SELECT i.*, u.name as created_by_name 
                FROM whatsapp_instances i
                LEFT JOIN users u ON i.created_by_user_id = u.id
                WHERE i.id = ?";
        
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Buscar por instance_key
     */
    public function findByInstanceKey($instanceKey)
    {
        $sql = "SELECT * FROM whatsapp_instances WHERE instance_key = ?";
        return $this->db->fetch($sql, [$instanceKey]);
    }
    
    /**
     * Listar todas as instâncias
     */
    public function getAll($filters = [])
    {
        $sql = "SELECT i.*, u.name as created_by_name 
                FROM whatsapp_instances i
                LEFT JOIN users u ON i.created_by_user_id = u.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['is_active'])) {
            $sql .= " AND i.is_active = ?";
            $params[] = $filters['is_active'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND i.status = ?";
            $params[] = $filters['status'];
        }
        
        $sql .= " ORDER BY i.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Atualizar instância
     */
    public function update($id, $data)
    {
        $fields = [];
        $params = [];
        
        $allowedFields = [
            'name', 'status', 'qr_code', 'phone_number', 'profile_name', 
            'profile_picture_url', 'webhook_url', 'max_connections', 
            'is_active', 'last_connection_at', 'last_disconnection_at'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $fields[] = "updated_at = NOW()";
        $params[] = $id;
        
        $sql = "UPDATE whatsapp_instances SET " . implode(', ', $fields) . " WHERE id = ?";
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Deletar instância
     */
    public function delete($id)
    {
        $sql = "DELETE FROM whatsapp_instances WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    /**
     * Atualizar status
     */
    public function updateStatus($id, $status)
    {
        $sql = "UPDATE whatsapp_instances SET status = ?, updated_at = NOW() WHERE id = ?";
        return $this->db->query($sql, [$status, $id]);
    }
    
    /**
     * Atualizar QR Code
     */
    public function updateQrCode($id, $qrCode)
    {
        $sql = "UPDATE whatsapp_instances SET qr_code = ?, updated_at = NOW() WHERE id = ?";
        return $this->db->query($sql, [$qrCode, $id]);
    }
    
    /**
     * Atualizar informações de conexão
     */
    public function updateConnectionInfo($id, $phoneNumber, $profileName, $profilePictureUrl = null)
    {
        $sql = "UPDATE whatsapp_instances 
                SET phone_number = ?, profile_name = ?, profile_picture_url = ?, 
                    last_connection_at = NOW(), updated_at = NOW() 
                WHERE id = ?";
        
        return $this->db->query($sql, [$phoneNumber, $profileName, $profilePictureUrl, $id]);
    }
}


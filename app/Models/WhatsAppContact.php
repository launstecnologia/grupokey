<?php

namespace App\Models;

use App\Core\Database;

class WhatsAppContact
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Criar ou atualizar contato
     */
    public function createOrUpdate($instanceId, $phoneNumber, $data = [])
    {
        // Verificar se já existe
        $existing = $this->findByPhoneNumber($instanceId, $phoneNumber);
        
        if ($existing) {
            // Atualizar e retornar o ID do contato
            $this->update($existing['id'], $data);
            return $existing['id'];
        } else {
            // Criar
            $sql = "INSERT INTO whatsapp_contacts 
                    (instance_id, phone_number, name, profile_picture_url, is_group, group_name, 
                     group_participants, last_message_at, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW())";
            
            $params = [
                $instanceId,
                $phoneNumber,
                $data['name'] ?? null,
                $data['profile_picture_url'] ?? null,
                $data['is_group'] ?? false,
                $data['group_name'] ?? null,
                $data['group_participants'] ?? 0
            ];
            
            $this->db->query($sql, $params);
            return $this->db->lastInsertId();
        }
    }
    
    /**
     * Buscar por número de telefone
     */
    public function findByPhoneNumber($instanceId, $phoneNumber)
    {
        $sql = "SELECT * FROM whatsapp_contacts 
                WHERE instance_id = ? AND phone_number = ?";
        
        return $this->db->fetch($sql, [$instanceId, $phoneNumber]);
    }
    
    /**
     * Buscar por ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM whatsapp_contacts WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Atualizar contato
     */
    public function update($id, $data)
    {
        $fields = [];
        $params = [];
        
        $allowedFields = [
            'name', 'profile_picture_url', 'is_group', 'group_name', 
            'group_participants', 'last_message_at', 'unread_count', 
            'is_blocked', 'tags', 'notes'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                if ($field === 'tags' && is_array($data[$field])) {
                    $fields[] = "{$field} = ?";
                    $params[] = json_encode($data[$field]);
                } else {
                    $fields[] = "{$field} = ?";
                    $params[] = $data[$field];
                }
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $fields[] = "updated_at = NOW()";
        $params[] = $id;
        
        $sql = "UPDATE whatsapp_contacts SET " . implode(', ', $fields) . " WHERE id = ?";
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Incrementar contador de não lidas
     */
    public function incrementUnread($id)
    {
        $sql = "UPDATE whatsapp_contacts SET unread_count = unread_count + 1, updated_at = NOW() WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    /**
     * Zerar contador de não lidas
     */
    public function resetUnread($id)
    {
        $sql = "UPDATE whatsapp_contacts SET unread_count = 0, updated_at = NOW() WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
}


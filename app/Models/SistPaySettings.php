<?php

namespace App\Models;

use App\Core\Database;

class SistPaySettings
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtém as configurações ativas
     */
    public function getActiveSettings()
    {
        $sql = "SELECT * FROM sistpay_settings ORDER BY id DESC LIMIT 1";
        return $this->db->fetch($sql);
    }
    
    /**
     * Salva ou atualiza as configurações
     */
    public function save($data)
    {
        // Verificar se já existe configuração
        $existing = $this->getActiveSettings();
        
        if ($existing) {
            // Atualizar configuração existente
            $sql = "UPDATE sistpay_settings SET 
                    token = ?,
                    auth_method = ?,
                    default_plan = ?,
                    is_sandbox = ?,
                    is_active = ?,
                    base_url = ?,
                    updated_at = NOW()
                    WHERE id = ?";
            
            $params = [
                $data['token'] ?? '',
                $data['auth_method'] ?? 'Authorization',
                !empty($data['default_plan']) ? (int)$data['default_plan'] : null,
                !empty($data['is_sandbox']) ? 1 : 0,
                !empty($data['is_active']) ? 1 : 0,
                $data['base_url'] ?? 'https://sistpay.com.br/api',
                $existing['id']
            ];
            
            return $this->db->query($sql, $params);
        } else {
            // Criar nova configuração
            $sql = "INSERT INTO sistpay_settings 
                    (token, auth_method, default_plan, is_sandbox, is_active, base_url) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $params = [
                $data['token'] ?? '',
                $data['auth_method'] ?? 'Authorization',
                !empty($data['default_plan']) ? (int)$data['default_plan'] : null,
                !empty($data['is_sandbox']) ? 1 : 0,
                !empty($data['is_active']) ? 1 : 0,
                $data['base_url'] ?? 'https://sistpay.com.br/api'
            ];
            
            $this->db->query($sql, $params);
            return $this->db->lastInsertId();
        }
    }
}


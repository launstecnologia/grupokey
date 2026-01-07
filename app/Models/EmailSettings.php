<?php

namespace App\Models;

use App\Core\Database;

class EmailSettings
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtém as configurações de email ativas
     */
    public function getActiveSettings()
    {
        $sql = "SELECT * FROM email_settings WHERE is_active = TRUE LIMIT 1";
        return $this->db->fetch($sql);
    }
    
    /**
     * Obtém todas as configurações
     */
    public function getAll()
    {
        $sql = "SELECT * FROM email_settings ORDER BY created_at DESC";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Salva ou atualiza as configurações de email
     */
    public function save($data)
    {
        // Desativar todas as outras configurações se esta for ativa
        if (!empty($data['is_active'])) {
            $this->db->query("UPDATE email_settings SET is_active = FALSE WHERE is_active = TRUE");
        }
        
        // Verificar se já existe configuração ativa
        $existing = $this->getActiveSettings();
        
        if ($existing) {
            // Atualizar configuração existente
            $sql = "UPDATE email_settings SET 
                    mail_host = ?,
                    mail_port = ?,
                    mail_user = ?,
                    mail_pass = ?,
                    mail_from = ?,
                    mail_name = ?,
                    mail_encryption = ?,
                    is_active = ?,
                    updated_at = NOW()
                    WHERE id = ?";
            
            $params = [
                $data['mail_host'] ?? 'smtp.gmail.com',
                $data['mail_port'] ?? 587,
                $data['mail_user'] ?? '',
                $data['mail_pass'] ?? '',
                $data['mail_from'] ?? '',
                $data['mail_name'] ?? 'Sistema CRM',
                $data['mail_encryption'] ?? 'tls',
                $data['is_active'] ?? true,
                $existing['id']
            ];
            
            return $this->db->query($sql, $params);
        } else {
            // Criar nova configuração
            $sql = "INSERT INTO email_settings 
                    (mail_host, mail_port, mail_user, mail_pass, mail_from, mail_name, mail_encryption, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $data['mail_host'] ?? 'smtp.gmail.com',
                $data['mail_port'] ?? 587,
                $data['mail_user'] ?? '',
                $data['mail_pass'] ?? '',
                $data['mail_from'] ?? '',
                $data['mail_name'] ?? 'Sistema CRM',
                $data['mail_encryption'] ?? 'tls',
                $data['is_active'] ?? true
            ];
            
            $this->db->query($sql, $params);
            return $this->db->lastInsertId();
        }
    }
}


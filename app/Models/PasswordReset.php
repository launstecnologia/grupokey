<?php

namespace App\Models;

use App\Core\Database;

class PasswordReset
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Cria um token de redefinição de senha
     */
    public function createToken($email, $userType, $expiresInHours = 24)
    {
        // Remover tokens antigos do mesmo email
        $this->db->query("DELETE FROM password_resets WHERE email = ? AND user_type = ?", [$email, $userType]);
        
        // Gerar token único
        $token = bin2hex(random_bytes(32));
        
        // Calcular data de expiração
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expiresInHours} hours"));
        
        $sql = "INSERT INTO password_resets (email, token, user_type, expires_at) VALUES (?, ?, ?, ?)";
        $this->db->query($sql, [$email, $token, $userType, $expiresAt]);
        
        return $token;
    }
    
    /**
     * Valida um token de redefinição
     */
    public function validateToken($token)
    {
        $sql = "SELECT * FROM password_resets 
                WHERE token = ? 
                AND (expires_at IS NULL OR expires_at > NOW()) 
                AND used_at IS NULL 
                LIMIT 1";
        
        return $this->db->fetch($sql, [$token]);
    }
    
    /**
     * Marca um token como usado
     */
    public function markAsUsed($token)
    {
        $sql = "UPDATE password_resets SET used_at = NOW() WHERE token = ?";
        return $this->db->query($sql, [$token]);
    }
    
    /**
     * Remove tokens expirados
     */
    public function cleanExpiredTokens()
    {
        $sql = "DELETE FROM password_resets WHERE expires_at < NOW() OR used_at IS NOT NULL";
        return $this->db->query($sql);
    }
}


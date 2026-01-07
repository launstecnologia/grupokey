<?php

namespace App\Core;

class Auth
{
    public static function start()
    {
        // Session já é iniciada no public/index.php
        // Não precisamos re-inicializar
        return true;
    }
    
    public static function check()
    {
        self::start();
        return isset($_SESSION['user_id']) || isset($_SESSION['representative_id']);
    }
    
    public static function isAdmin()
    {
        self::start();
        return isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'admin';
    }
    
    public static function isRepresentative()
    {
        self::start();
        return isset($_SESSION['representative_id']);
    }
    
    public static function user()
    {
        self::start();
        if (self::isAdmin()) {
            return [
                'id' => $_SESSION['user_id'] ?? null,
                'name' => $_SESSION['user_name'] ?? null,
                'email' => $_SESSION['user_email'] ?? null,
                'photo' => $_SESSION['user_photo'] ?? null,
                'type' => $_SESSION['user_type'] ?? null,
                'profile' => $_SESSION['user_profile'] ?? null
            ];
        }
        return null;
    }
    
    public static function representative()
    {
        self::start();
        if (self::isRepresentative()) {
            return [
                'id' => $_SESSION['representative_id'] ?? null,
                'nome_completo' => $_SESSION['representative_name'] ?? null,
                'name' => $_SESSION['representative_name'] ?? null,
                'email' => $_SESSION['representative_email'] ?? null,
                'photo' => $_SESSION['representative_photo'] ?? null,
                'type' => 'representative'
            ];
        }
        return null;
    }
    
    public static function loginUser($user)
    {
        self::start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_photo'] = $user['photo'] ?? null;
        $_SESSION['user_type'] = $user['type'];
        $_SESSION['user_profile'] = $user['profile'] ?? null;
        
        // Limpar dados de representante se existirem
        unset($_SESSION['representative_id']);
        unset($_SESSION['representative_name']);
        unset($_SESSION['representative_email']);
        unset($_SESSION['representative_photo']);
    }
    
    public static function loginRepresentative($representative)
    {
        self::start();
        $_SESSION['representative_id'] = $representative['id'];
        $_SESSION['representative_name'] = $representative['name'];
        $_SESSION['representative_email'] = $representative['email'];
        $_SESSION['representative_photo'] = $representative['photo'] ?? null;
        
        // Limpar dados de usuário se existirem
        unset($_SESSION['user_id']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_photo']);
        unset($_SESSION['user_type']);
        unset($_SESSION['user_profile']);
    }
    
    public static function logout()
    {
        self::start();
        session_destroy();
        // Não reiniciar sessão - será feita no próximo request
    }
    
    public static function requireAuth()
    {
        if (!self::check()) {
            redirect(url('login'));
        }
    }
    
    public static function requireAdmin()
    {
        self::requireAuth();
        if (!self::isAdmin()) {
            redirect(url('dashboard'));
        }
    }
    
    public static function requireRepresentative()
    {
        self::requireAuth();
        if (!self::isRepresentative()) {
            redirect(url('dashboard'));
        }
    }
    
    public static function regenerateSession()
    {
        self::start();
        session_regenerate_id(true);
    }
}

<?php
/**
 * Script para desbloquear usuários e alterar senha
 * 
 * Este script:
 * 1. Desbloqueia todos os usuários bloqueados (status = 'BLOCKED')
 * 2. Altera a senha para "101010" para todos os usuários
 * 3. Reseta as tentativas falhas de login
 * 
 * Uso: php unblock_and_reset_password.php
 */

// Carregar autoloader do Composer
require_once __DIR__ . '/../../vendor/autoload.php';

// Carregar sistema de configuração automático
require_once __DIR__ . '/../../app/Core/AutoConfig.php';
App\Core\AutoConfig::init();

// Carregar Database
require_once __DIR__ . '/../../app/Core/Database.php';

use App\Core\Database;

echo "\n";
echo "===========================================\n";
echo "DESBLOQUEIO E REDEFINIÇÃO DE SENHA\n";
echo "===========================================\n";
echo "\n";

try {
    $db = Database::getInstance();
    
    // Nova senha
    $newPassword = '101010';
    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    
    echo "Processando...\n\n";
    
    // 1. Desbloquear todos os usuários bloqueados e alterar senha
    $sql = "UPDATE users 
            SET status = 'ACTIVE', 
                password = ?, 
                failed_attempts = 0, 
                updated_at = NOW() 
            WHERE status = 'BLOCKED'";
    
    $stmt = $db->query($sql, [$passwordHash]);
    $blockedUsers = $stmt->rowCount();
    
    echo "✓ Usuários bloqueados desbloqueados: {$blockedUsers}\n";
    
    // 2. Alterar senha de TODOS os usuários para "101010"
    $sql = "UPDATE users 
            SET password = ?, 
                failed_attempts = 0, 
                updated_at = NOW()";
    
    $stmt = $db->query($sql, [$passwordHash]);
    $allUsers = $stmt->rowCount();
    
    echo "✓ Senha alterada para todos os usuários: {$allUsers}\n";
    
    // 3. Fazer o mesmo para representantes bloqueados
    $sql = "UPDATE representatives 
            SET status = 'ACTIVE', 
                password = ?, 
                failed_attempts = 0, 
                updated_at = NOW() 
            WHERE status = 'BLOCKED'";
    
    $stmt = $db->query($sql, [$passwordHash]);
    $blockedReps = $stmt->rowCount();
    
    echo "✓ Representantes bloqueados desbloqueados: {$blockedReps}\n";
    
    // 4. Alterar senha de TODOS os representantes
    $sql = "UPDATE representatives 
            SET password = ?, 
                failed_attempts = 0, 
                updated_at = NOW()";
    
    $stmt = $db->query($sql, [$passwordHash]);
    $allReps = $stmt->rowCount();
    
    echo "✓ Senha alterada para todos os representantes: {$allReps}\n";
    
    echo "\n";
    echo "===========================================\n";
    echo "PROCESSO CONCLUÍDO COM SUCESSO!\n";
    echo "===========================================\n";
    echo "\n";
    echo "Nova senha para todos os usuários: {$newPassword}\n";
    echo "\n";
    echo "Você pode fazer login agora com qualquer email cadastrado\n";
    echo "usando a senha: {$newPassword}\n";
    echo "\n";
    
} catch (Exception $e) {
    echo "\n";
    echo "===========================================\n";
    echo "ERRO AO PROCESSAR\n";
    echo "===========================================\n";
    echo "\n";
    echo "Erro: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
    echo "\n";
    exit(1);
}


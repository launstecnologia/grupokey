-- ===========================================
-- ATUALIZAR SENHA DO USUÁRIO ADMIN
-- ===========================================
-- Este script atualiza a senha do usuário admin existente
-- ou cria um novo se não existir
-- 
-- Senha: admin123

-- Atualizar senha do admin existente (se existir)
UPDATE users 
SET password = '$2y$10$/j2i.mI6FqvowBYpHFnxuel6xOW1NvB.QWefc1MyX0mKLICeYc9uC',
    status = 'ACTIVE',
    failed_attempts = 0,
    updated_at = NOW()
WHERE email = 'admin@grupokey.com';

-- Se não existir, criar novo usuário admin
INSERT INTO users (name, email, password, status, created_at, updated_at) 
SELECT 
    'Administrador',
    'admin@grupokey.com',
    '$2y$10$/j2i.mI6FqvowBYpHFnxuel6xOW1NvB.QWefc1MyX0mKLICeYc9uC',
    'ACTIVE',
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE email = 'admin@grupokey.com'
);

-- ===========================================
-- CREDENCIAIS:
-- ===========================================
-- Email: admin@grupokey.com
-- Senha: admin123
-- 
-- Execute este SQL e tente fazer login novamente!


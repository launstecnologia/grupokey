-- ===========================================
-- CRIAR USUÁRIO ADMINISTRADOR
-- ===========================================
-- Este script cria um usuário administrador padrão
-- 
-- IMPORTANTE: Altere a senha após o primeiro login!

-- Usuário Admin Padrão
-- Email: admin@grupokey.com
-- Senha: admin123
INSERT INTO users (name, email, password, status, created_at, updated_at) 
VALUES (
    'Administrador',
    'admin@grupokey.com',
    '$2y$10$/j2i.mI6FqvowBYpHFnxuel6xOW1NvB.QWefc1MyX0mKLICeYc9uC', -- Hash da senha: admin123
    'ACTIVE',
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE 
    name = VALUES(name),
    status = 'ACTIVE',
    updated_at = NOW();

-- ===========================================
-- INSTRUÇÕES:
-- ===========================================
-- 1. Execute este SQL no seu banco de dados MySQL
-- 2. Faça login com:
--    Email: admin@grupokey.com
--    Senha: admin123
-- 3. ALTERE A SENHA IMEDIATAMENTE após o primeiro login!
-- 
-- ===========================================
-- PARA CRIAR OUTRO USUÁRIO ADMIN COM SENHA PERSONALIZADA:
-- ===========================================
-- 1. Execute o script PHP: php database/scripts/generate_password_hash.php sua_senha
-- 2. Use o hash gerado no SQL abaixo:
-- 
-- INSERT INTO users (name, email, password, status) 
-- VALUES ('Nome do Admin', 'email@exemplo.com', 'HASH_GERADO_AQUI', 'ACTIVE');

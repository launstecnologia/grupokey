-- ===========================================
-- ADICIONAR CAMPO DE FOTO PARA USUÁRIOS E REPRESENTANTES
-- ===========================================

-- Adicionar campo photo na tabela users
ALTER TABLE users 
ADD COLUMN photo VARCHAR(255) NULL AFTER email;

-- Adicionar campo photo na tabela representatives
ALTER TABLE representatives 
ADD COLUMN photo VARCHAR(255) NULL AFTER email;


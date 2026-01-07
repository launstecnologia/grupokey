-- ===========================================
-- CORRIGIR AUTO_INCREMENT NA TABELA REPRESENTATIVES
-- ===========================================

-- Garantir que o campo id tenha AUTO_INCREMENT
ALTER TABLE representatives 
MODIFY COLUMN id INT AUTO_INCREMENT;


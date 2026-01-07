-- ===========================================
-- CORRIGIR AUTO_INCREMENT NA TABELA REPRESENTATIVE_PRODUCTS
-- ===========================================

-- Garantir que o campo id tenha AUTO_INCREMENT
ALTER TABLE representative_products 
MODIFY COLUMN id INT AUTO_INCREMENT;


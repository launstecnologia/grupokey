-- ===========================================
-- CORRIGIR TAMANHO DA COLUNA PRODUCT_TYPE
-- ===========================================

-- Aumentar o tamanho da coluna product_type para garantir que valores como CDX_EVO sejam salvos
ALTER TABLE representative_products 
MODIFY COLUMN product_type VARCHAR(100) NOT NULL;


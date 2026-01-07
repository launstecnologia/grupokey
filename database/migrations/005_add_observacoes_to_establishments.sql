-- ===========================================
-- ADICIONAR CAMPO DE OBSERVAÇÕES PARA ESTABELECIMENTOS
-- ===========================================

-- Adicionar campo observacoes na tabela establishments
ALTER TABLE establishments 
ADD COLUMN observacoes TEXT NULL AFTER chave_pix;


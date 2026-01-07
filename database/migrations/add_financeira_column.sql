-- Adicionar coluna financeira na tabela establishment_brasilcard
-- Esta coluna armazena o nome da financeira (UCRED, PARCELA FACIL, etc.)

ALTER TABLE establishment_brasilcard 
ADD COLUMN financeira VARCHAR(50) NULL AFTER taxa;

-- Comentário da coluna
ALTER TABLE establishment_brasilcard 
MODIFY COLUMN financeira VARCHAR(50) NULL COMMENT 'Nome da financeira (UCRED, PARCELA FACIL, CREDFLIP, PARCELEX)';


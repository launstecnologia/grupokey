-- Permite salvar produtos dinâmicos no chamado
ALTER TABLE tickets
    MODIFY COLUMN produto VARCHAR(100) NOT NULL;

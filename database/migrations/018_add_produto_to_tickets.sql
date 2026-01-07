-- ===========================================
-- ADICIONAR CAMPO PRODUTO NA TABELA TICKETS
-- ===========================================

-- Verificar se o campo já existe antes de adicionar
SET @dbname = DATABASE();
SET @tablename = "tickets";
SET @columnname = "produto";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " ENUM('CDC', 'CDX_EVO', 'GOOGLE', 'MEMBRO_KEY', 'OUTROS', 'PAGBANK') DEFAULT 'OUTROS'")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Se o campo já existe, atualizar o ENUM para incluir todos os valores
ALTER TABLE tickets
MODIFY COLUMN produto ENUM(
    'CDC',
    'CDX_EVO',
    'GOOGLE',
    'MEMBRO_KEY',
    'OUTROS',
    'PAGBANK'
) DEFAULT 'OUTROS';


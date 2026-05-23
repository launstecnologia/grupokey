-- Permitir dados repetidos no cadastro de estabelecimentos
-- Remove índices UNIQUE das colunas cnpj/cpf/email na tabela establishments (se existirem)
-- Compatível com bancos que tenham nomes de índices diferentes.

SET @schema_name := DATABASE();

-- UNIQUE em CNPJ
SET @drop_unique_cnpj := (
    SELECT GROUP_CONCAT(CONCAT('DROP INDEX `', index_name, '` ON `establishments`') SEPARATOR '; ')
    FROM (
        SELECT DISTINCT s.index_name
        FROM information_schema.statistics s
        WHERE s.table_schema = @schema_name
          AND s.table_name = 'establishments'
          AND s.non_unique = 0
          AND s.column_name = 'cnpj'
          AND s.index_name <> 'PRIMARY'
    ) t
);
SET @sql := IF(@drop_unique_cnpj IS NULL OR @drop_unique_cnpj = '', 'SELECT 1', @drop_unique_cnpj);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- UNIQUE em CPF
SET @drop_unique_cpf := (
    SELECT GROUP_CONCAT(CONCAT('DROP INDEX `', index_name, '` ON `establishments`') SEPARATOR '; ')
    FROM (
        SELECT DISTINCT s.index_name
        FROM information_schema.statistics s
        WHERE s.table_schema = @schema_name
          AND s.table_name = 'establishments'
          AND s.non_unique = 0
          AND s.column_name = 'cpf'
          AND s.index_name <> 'PRIMARY'
    ) t
);
SET @sql := IF(@drop_unique_cpf IS NULL OR @drop_unique_cpf = '', 'SELECT 1', @drop_unique_cpf);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- UNIQUE em EMAIL
SET @drop_unique_email := (
    SELECT GROUP_CONCAT(CONCAT('DROP INDEX `', index_name, '` ON `establishments`') SEPARATOR '; ')
    FROM (
        SELECT DISTINCT s.index_name
        FROM information_schema.statistics s
        WHERE s.table_schema = @schema_name
          AND s.table_name = 'establishments'
          AND s.non_unique = 0
          AND s.column_name = 'email'
          AND s.index_name <> 'PRIMARY'
    ) t
);
SET @sql := IF(@drop_unique_email IS NULL OR @drop_unique_email = '', 'SELECT 1', @drop_unique_email);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

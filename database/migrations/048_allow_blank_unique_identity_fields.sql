-- Permite vários cadastros sem e-mail ou CPF.
-- O índice único continua valendo para valores preenchidos. NULL pode se repetir.
ALTER TABLE users MODIFY email VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;
ALTER TABLE representatives MODIFY email VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;
ALTER TABLE representatives MODIFY cpf VARCHAR(14) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;

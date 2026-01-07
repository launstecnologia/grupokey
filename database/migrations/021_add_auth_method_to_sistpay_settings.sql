-- Adicionar campo auth_method na tabela sistpay_settings
ALTER TABLE sistpay_settings 
ADD COLUMN auth_method ENUM('Authorization', 'X-Authorization', 'X-Api-Token', 'X-Api-Key', 'Query-Param') 
DEFAULT 'Authorization' AFTER token;


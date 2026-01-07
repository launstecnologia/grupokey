-- Adicionar campo default_plan na tabela sistpay_settings
ALTER TABLE sistpay_settings 
ADD COLUMN default_plan INT NULL DEFAULT 11 AFTER auth_method;


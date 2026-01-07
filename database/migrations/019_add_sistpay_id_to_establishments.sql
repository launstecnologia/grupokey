-- Adicionar campo sistpay_id na tabela establishments para armazenar o ID retornado pelo SistPay
ALTER TABLE establishments 
ADD COLUMN sistpay_id INT NULL AFTER id,
ADD INDEX idx_sistpay_id (sistpay_id);


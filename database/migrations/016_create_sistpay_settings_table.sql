-- Tabela para configurações da API SistPay
CREATE TABLE IF NOT EXISTS sistpay_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(500) NOT NULL DEFAULT '',
    is_sandbox BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT FALSE,
    base_url VARCHAR(255) NOT NULL DEFAULT 'https://sistpay.com.br/api',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir configuração padrão (apenas se não existir)
INSERT INTO sistpay_settings (token, is_sandbox, is_active, base_url) 
SELECT '', FALSE, FALSE, 'https://sistpay.com.br/api'
WHERE NOT EXISTS (SELECT 1 FROM sistpay_settings LIMIT 1);


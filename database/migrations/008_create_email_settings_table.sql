-- Tabela para configurações de email SMTP
CREATE TABLE IF NOT EXISTS email_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mail_host VARCHAR(255) NOT NULL DEFAULT 'smtp.gmail.com',
    mail_port INT NOT NULL DEFAULT 587,
    mail_user VARCHAR(255) NOT NULL DEFAULT '',
    mail_pass VARCHAR(255) NOT NULL DEFAULT '',
    mail_from VARCHAR(255) NOT NULL DEFAULT '',
    mail_name VARCHAR(255) NOT NULL DEFAULT 'Sistema CRM',
    mail_encryption ENUM('tls', 'ssl', 'none') NOT NULL DEFAULT 'tls',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir configuração padrão (apenas se não existir)
INSERT INTO email_settings (mail_host, mail_port, mail_user, mail_pass, mail_from, mail_name, mail_encryption, is_active) 
SELECT 'smtp.gmail.com', 587, '', '', '', 'Sistema CRM', 'tls', TRUE
WHERE NOT EXISTS (SELECT 1 FROM email_settings WHERE is_active = TRUE);


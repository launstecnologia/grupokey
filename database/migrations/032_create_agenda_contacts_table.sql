-- ===========================================
-- AGENDA DE CONTATOS
-- ===========================================
-- Migration: 032
-- Descrição: Tabela para cadastro de contatos (agenda)
-- Data: 2026-02-03

CREATE TABLE IF NOT EXISTS agenda_contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL COMMENT 'Nome do contato',
    phone VARCHAR(20) NOT NULL COMMENT 'Telefone (com DDD, ex: 5516999999999)',
    email VARCHAR(255) NULL COMMENT 'E-mail',
    notes TEXT NULL COMMENT 'Observações',
    created_by_user_id INT NULL COMMENT 'Usuário que criou',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_phone (phone),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

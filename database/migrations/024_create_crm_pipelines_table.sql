-- ===========================================
-- TABELA DE PIPELINES (BOARDS) DO CRM
-- ===========================================
CREATE TABLE IF NOT EXISTS crm_pipelines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    color VARCHAR(7) DEFAULT '#3B82F6' COMMENT 'Cor hexadecimal do pipeline',
    is_default BOOLEAN DEFAULT FALSE COMMENT 'Pipeline padrão do sistema',
    sort_order INT DEFAULT 0 COMMENT 'Ordem de exibição',
    is_active BOOLEAN DEFAULT TRUE,
    created_by_user_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_is_active (is_active),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir pipeline padrão
INSERT INTO crm_pipelines (name, description, color, is_default, sort_order, is_active) 
VALUES ('Pipeline Principal', 'Pipeline padrão do sistema', '#3B82F6', TRUE, 0, TRUE)
ON DUPLICATE KEY UPDATE name=name;


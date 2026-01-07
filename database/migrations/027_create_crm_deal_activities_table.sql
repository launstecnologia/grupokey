-- ===========================================
-- TABELA DE ATIVIDADES DOS DEALS
-- ===========================================
CREATE TABLE IF NOT EXISTS crm_deal_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deal_id INT NOT NULL,
    activity_type ENUM('NOTE', 'CALL', 'EMAIL', 'MEETING', 'TASK', 'STAGE_CHANGE', 'VALUE_CHANGE') DEFAULT 'NOTE',
    title VARCHAR(255) NULL,
    description TEXT NULL,
    activity_date DATETIME NULL COMMENT 'Data/hora da atividade',
    duration_minutes INT NULL COMMENT 'Duração em minutos (para calls/meetings)',
    -- Metadados da mudança (para STAGE_CHANGE, VALUE_CHANGE)
    old_value VARCHAR(255) NULL,
    new_value VARCHAR(255) NULL,
    -- Relacionamentos
    created_by_user_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (deal_id) REFERENCES crm_deals(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_deal_id (deal_id),
    INDEX idx_activity_type (activity_type),
    INDEX idx_activity_date (activity_date),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


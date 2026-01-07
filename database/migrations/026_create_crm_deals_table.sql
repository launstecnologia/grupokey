-- ===========================================
-- TABELA DE DEALS (CARDS) DO CRM
-- ===========================================
CREATE TABLE IF NOT EXISTS crm_deals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pipeline_id INT NOT NULL,
    stage_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    value DECIMAL(15,2) NULL COMMENT 'Valor do negócio',
    currency VARCHAR(3) DEFAULT 'BRL',
    expected_close_date DATE NULL,
    actual_close_date DATE NULL,
    probability INT DEFAULT 0 COMMENT 'Probabilidade de fechamento (0-100)',
    priority ENUM('LOW', 'MEDIUM', 'HIGH', 'URGENT') DEFAULT 'MEDIUM',
    status ENUM('ACTIVE', 'WON', 'LOST', 'ARCHIVED') DEFAULT 'ACTIVE',
    -- Relacionamentos opcionais
    establishment_id INT NULL COMMENT 'Relacionado a um estabelecimento',
    representative_id INT NULL COMMENT 'Responsável pelo negócio',
    assigned_to_user_id INT NULL COMMENT 'Usuário atribuído',
    -- Metadados
    sort_order INT DEFAULT 0 COMMENT 'Ordem dentro do stage',
    created_by_user_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pipeline_id) REFERENCES crm_pipelines(id) ON DELETE CASCADE,
    FOREIGN KEY (stage_id) REFERENCES crm_stages(id) ON DELETE RESTRICT,
    FOREIGN KEY (establishment_id) REFERENCES establishments(id) ON DELETE SET NULL,
    FOREIGN KEY (representative_id) REFERENCES representatives(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_pipeline_stage (pipeline_id, stage_id),
    INDEX idx_stage_id (stage_id),
    INDEX idx_status (status),
    INDEX idx_assigned_to (assigned_to_user_id),
    INDEX idx_representative (representative_id),
    INDEX idx_establishment (establishment_id),
    INDEX idx_expected_close_date (expected_close_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


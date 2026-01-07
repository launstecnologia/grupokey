-- ===========================================
-- TABELA DE STAGES (COLUNAS) DO CRM
-- ===========================================
CREATE TABLE IF NOT EXISTS crm_stages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pipeline_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    color VARCHAR(7) DEFAULT '#6B7280' COMMENT 'Cor hexadecimal do stage',
    sort_order INT DEFAULT 0 COMMENT 'Ordem de exibição no pipeline',
    is_final BOOLEAN DEFAULT FALSE COMMENT 'Stage final (won/lost)',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pipeline_id) REFERENCES crm_pipelines(id) ON DELETE CASCADE,
    INDEX idx_pipeline_id (pipeline_id),
    INDEX idx_sort_order (pipeline_id, sort_order),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir stages padrão para o pipeline principal
INSERT INTO crm_stages (pipeline_id, name, color, sort_order, is_final, is_active)
SELECT 
    id,
    'Novo',
    '#3B82F6',
    0,
    FALSE,
    TRUE
FROM crm_pipelines WHERE is_default = TRUE
LIMIT 1;

INSERT INTO crm_stages (pipeline_id, name, color, sort_order, is_final, is_active)
SELECT 
    id,
    'Em Contato',
    '#F59E0B',
    1,
    FALSE,
    TRUE
FROM crm_pipelines WHERE is_default = TRUE
LIMIT 1;

INSERT INTO crm_stages (pipeline_id, name, color, sort_order, is_final, is_active)
SELECT 
    id,
    'Proposta Enviada',
    '#8B5CF6',
    2,
    FALSE,
    TRUE
FROM crm_pipelines WHERE is_default = TRUE
LIMIT 1;

INSERT INTO crm_stages (pipeline_id, name, color, sort_order, is_final, is_active)
SELECT 
    id,
    'Negociação',
    '#EC4899',
    3,
    FALSE,
    TRUE
FROM crm_pipelines WHERE is_default = TRUE
LIMIT 1;

INSERT INTO crm_stages (pipeline_id, name, color, sort_order, is_final, is_active)
SELECT 
    id,
    'Ganho',
    '#10B981',
    4,
    TRUE,
    TRUE
FROM crm_pipelines WHERE is_default = TRUE
LIMIT 1;

INSERT INTO crm_stages (pipeline_id, name, color, sort_order, is_final, is_active)
SELECT 
    id,
    'Perdido',
    '#EF4444',
    5,
    TRUE,
    TRUE
FROM crm_pipelines WHERE is_default = TRUE
LIMIT 1;


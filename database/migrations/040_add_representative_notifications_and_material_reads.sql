-- ===========================================
-- Notificações para representantes + leitura de material
-- Data: 2026-05-16
-- ===========================================

-- Permite notificações para representantes além de usuários admin
ALTER TABLE crm_notifications
    MODIFY COLUMN user_id INT NULL;

ALTER TABLE crm_notifications
    ADD COLUMN recipient_type VARCHAR(20) NOT NULL DEFAULT 'user' AFTER user_id,
    ADD COLUMN representative_id INT NULL AFTER recipient_type;

ALTER TABLE crm_notifications
    ADD INDEX idx_recipient_type (recipient_type),
    ADD INDEX idx_representative_id (representative_id),
    ADD CONSTRAINT fk_crm_notifications_representative
        FOREIGN KEY (representative_id) REFERENCES representatives(id) ON DELETE CASCADE;

-- Confirmação de leitura de material por representante
CREATE TABLE IF NOT EXISTS material_file_reads (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    file_id VARCHAR(50) NOT NULL,
    representative_id INT NOT NULL,
    read_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_material_file_reads_file_rep (file_id, representative_id),
    INDEX idx_material_file_reads_rep (representative_id),
    INDEX idx_material_file_reads_file (file_id),
    CONSTRAINT fk_material_file_reads_file
        FOREIGN KEY (file_id) REFERENCES material_files(id) ON DELETE CASCADE,
    CONSTRAINT fk_material_file_reads_rep
        FOREIGN KEY (representative_id) REFERENCES representatives(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

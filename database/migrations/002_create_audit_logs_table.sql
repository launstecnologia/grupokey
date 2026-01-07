-- ===========================================
-- TABELA DE LOGS DE AUDITORIA
-- ===========================================
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(50) NOT NULL,
    module VARCHAR(100) NOT NULL,
    record_id INT,
    record_type VARCHAR(50),
    user_id INT,
    representative_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (representative_id) REFERENCES representatives(id) ON DELETE SET NULL,
    INDEX idx_audit_action_module (action, module),
    INDEX idx_audit_user (user_id, created_at),
    INDEX idx_audit_representative (representative_id, created_at),
    INDEX idx_audit_record (record_id, record_type),
    INDEX idx_audit_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


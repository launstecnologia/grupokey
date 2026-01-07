-- ===========================================
-- TABELA DE TAREFAS DOS DEALS
-- ===========================================
CREATE TABLE IF NOT EXISTS crm_deal_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deal_id INT NOT NULL,
    task_type ENUM('CALL', 'MEETING', 'EMAIL', 'FOLLOW_UP', 'OTHER') DEFAULT 'OTHER',
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    scheduled_at DATETIME NOT NULL COMMENT 'Data e hora agendada da tarefa',
    reminder_minutes INT DEFAULT 15 COMMENT 'Minutos antes para enviar lembrete',
    is_completed BOOLEAN DEFAULT FALSE,
    completed_at DATETIME NULL,
    reminder_sent BOOLEAN DEFAULT FALSE COMMENT 'Se o lembrete já foi enviado',
    created_by_user_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (deal_id) REFERENCES crm_deals(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_deal_id (deal_id),
    INDEX idx_scheduled_at (scheduled_at),
    INDEX idx_is_completed (is_completed),
    INDEX idx_reminder_sent (reminder_sent),
    INDEX idx_task_type (task_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


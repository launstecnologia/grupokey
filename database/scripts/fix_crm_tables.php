<?php
/**
 * Script para criar as tabelas do CRM diretamente
 * 
 * Uso: php database/scripts/fix_crm_tables.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../app/Core/AutoConfig.php';

App\Core\AutoConfig::init();

use App\Core\Database;

$db = Database::getInstance();

echo "=== Criando tabelas do CRM ===\n\n";

// Criar tabela crm_deal_tasks
$sql1 = "CREATE TABLE IF NOT EXISTS crm_deal_tasks (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// Criar tabela crm_notifications
$sql2 = "CREATE TABLE IF NOT EXISTS crm_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL COMMENT 'TASK_REMINDER, TASK_DUE, etc',
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    related_type VARCHAR(50) NULL COMMENT 'deal, task, etc',
    related_id INT NULL COMMENT 'ID do item relacionado',
    is_read BOOLEAN DEFAULT FALSE,
    read_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    echo "Criando tabela crm_deal_tasks...\n";
    $db->query($sql1);
    echo "✅ Tabela crm_deal_tasks criada com sucesso!\n\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'already exists') !== false || 
        strpos($e->getMessage(), 'Duplicate') !== false) {
        echo "⚠️  Tabela crm_deal_tasks já existe.\n\n";
    } else {
        echo "❌ Erro ao criar crm_deal_tasks: " . $e->getMessage() . "\n\n";
    }
}

try {
    echo "Criando tabela crm_notifications...\n";
    $db->query($sql2);
    echo "✅ Tabela crm_notifications criada com sucesso!\n\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'already exists') !== false || 
        strpos($e->getMessage(), 'Duplicate') !== false) {
        echo "⚠️  Tabela crm_notifications já existe.\n\n";
    } else {
        echo "❌ Erro ao criar crm_notifications: " . $e->getMessage() . "\n\n";
    }
}

// Verificar se as tabelas existem
echo "=== Verificando tabelas ===\n";
try {
    $result = $db->fetch("SHOW TABLES LIKE 'crm_deal_tasks'");
    if ($result) {
        echo "✅ crm_deal_tasks existe\n";
    } else {
        echo "❌ crm_deal_tasks NÃO existe\n";
    }
} catch (Exception $e) {
    echo "❌ Erro ao verificar: " . $e->getMessage() . "\n";
}

try {
    $result = $db->fetch("SHOW TABLES LIKE 'crm_notifications'");
    if ($result) {
        echo "✅ crm_notifications existe\n";
    } else {
        echo "❌ crm_notifications NÃO existe\n";
    }
} catch (Exception $e) {
    echo "❌ Erro ao verificar: " . $e->getMessage() . "\n";
}

echo "\n=== Concluído! ===\n";


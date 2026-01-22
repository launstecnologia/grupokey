-- ===========================================
-- SISTEMA DE ATENDIMENTO WHATSAPP
-- ===========================================
-- Migration: 031
-- Descrição: Cria todas as tabelas necessárias para o sistema de atendimento WhatsApp
-- Data: 2026-01-07

-- ===========================================
-- 1. TABELA: whatsapp_instances
-- Armazena as instâncias do WhatsApp conectadas via Evolution API
-- ===========================================
CREATE TABLE IF NOT EXISTS whatsapp_instances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL COMMENT 'Nome da instância (ex: Atendimento Principal)',
    instance_key VARCHAR(255) UNIQUE NOT NULL COMMENT 'Chave da instância na Evolution API',
    qr_code TEXT NULL COMMENT 'QR Code para conexão',
    status ENUM('DISCONNECTED', 'CONNECTING', 'CONNECTED', 'OPENING', 'CLOSE') DEFAULT 'DISCONNECTED' COMMENT 'Status da conexão',
    phone_number VARCHAR(20) NULL COMMENT 'Número do WhatsApp conectado',
    profile_name VARCHAR(255) NULL COMMENT 'Nome do perfil do WhatsApp',
    profile_picture_url VARCHAR(500) NULL COMMENT 'URL da foto de perfil',
    evolution_api_url VARCHAR(500) NOT NULL COMMENT 'URL base da Evolution API',
    evolution_api_key VARCHAR(255) NOT NULL COMMENT 'API Key da Evolution API',
    webhook_url VARCHAR(500) NULL COMMENT 'URL do webhook configurada',
    max_connections INT DEFAULT 10 COMMENT 'Máximo de conexões simultâneas',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Se a instância está ativa',
    last_connection_at TIMESTAMP NULL COMMENT 'Última vez que conectou',
    last_disconnection_at TIMESTAMP NULL COMMENT 'Última vez que desconectou',
    created_by_user_id INT NULL COMMENT 'Usuário que criou a instância',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_is_active (is_active),
    INDEX idx_instance_key (instance_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- 2. TABELA: whatsapp_contacts
-- Armazena os contatos do WhatsApp
-- ===========================================
CREATE TABLE IF NOT EXISTS whatsapp_contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    instance_id INT NOT NULL COMMENT 'Instância do WhatsApp',
    phone_number VARCHAR(20) NOT NULL COMMENT 'Número do telefone (apenas números)',
    name VARCHAR(255) NULL COMMENT 'Nome do contato',
    profile_picture_url VARCHAR(500) NULL COMMENT 'URL da foto de perfil',
    is_group BOOLEAN DEFAULT FALSE COMMENT 'Se é um grupo',
    group_name VARCHAR(255) NULL COMMENT 'Nome do grupo (se for grupo)',
    group_participants INT DEFAULT 0 COMMENT 'Número de participantes (se for grupo)',
    last_message_at TIMESTAMP NULL COMMENT 'Data da última mensagem recebida',
    unread_count INT DEFAULT 0 COMMENT 'Contador de mensagens não lidas',
    is_blocked BOOLEAN DEFAULT FALSE COMMENT 'Se o contato está bloqueado',
    tags JSON NULL COMMENT 'Tags personalizadas (ex: ["vip", "reclamacao"])',
    notes TEXT NULL COMMENT 'Notas sobre o contato',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (instance_id) REFERENCES whatsapp_instances(id) ON DELETE CASCADE,
    UNIQUE KEY unique_contact_instance (instance_id, phone_number),
    INDEX idx_phone_number (phone_number),
    INDEX idx_instance_id (instance_id),
    INDEX idx_last_message_at (last_message_at),
    INDEX idx_is_blocked (is_blocked)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- 3. TABELA: whatsapp_queues
-- Filas de atendimento (setores)
-- ===========================================
CREATE TABLE IF NOT EXISTS whatsapp_queues (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL COMMENT 'Nome da fila (ex: Suporte, Vendas, Financeiro)',
    description TEXT NULL COMMENT 'Descrição da fila',
    color VARCHAR(7) DEFAULT '#3B82F6' COMMENT 'Cor da fila (hex)',
    greeting_message TEXT NULL COMMENT 'Mensagem de boas-vindas automática',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Se a fila está ativa',
    max_chats_per_user INT DEFAULT 5 COMMENT 'Máximo de chats por atendente',
    auto_assign BOOLEAN DEFAULT TRUE COMMENT 'Se deve distribuir automaticamente',
    business_hours_start TIME NULL COMMENT 'Horário de início do atendimento',
    business_hours_end TIME NULL COMMENT 'Horário de fim do atendimento',
    timezone VARCHAR(50) DEFAULT 'America/Sao_Paulo' COMMENT 'Fuso horário',
    created_by_user_id INT NULL COMMENT 'Usuário que criou a fila',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_is_active (is_active),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- 4. TABELA: whatsapp_queue_users
-- Relacionamento entre filas e usuários (atendentes)
-- ===========================================
CREATE TABLE IF NOT EXISTS whatsapp_queue_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    queue_id INT NOT NULL,
    user_id INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Se o usuário está ativo na fila',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (queue_id) REFERENCES whatsapp_queues(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_queue_user (queue_id, user_id),
    INDEX idx_queue_id (queue_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- 5. TABELA: whatsapp_conversations
-- Conversas/Chats
-- ===========================================
CREATE TABLE IF NOT EXISTS whatsapp_conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    instance_id INT NOT NULL COMMENT 'Instância do WhatsApp',
    contact_id INT NOT NULL COMMENT 'Contato da conversa',
    queue_id INT NULL COMMENT 'Fila de atendimento',
    status ENUM('OPEN', 'PENDING', 'CLOSED') DEFAULT 'OPEN' COMMENT 'Status da conversa',
    unread_count INT DEFAULT 0 COMMENT 'Mensagens não lidas',
    last_message_at TIMESTAMP NULL COMMENT 'Data da última mensagem',
    last_message_preview TEXT NULL COMMENT 'Preview da última mensagem',
    is_group BOOLEAN DEFAULT FALSE COMMENT 'Se é conversa de grupo',
    priority ENUM('LOW', 'NORMAL', 'HIGH', 'URGENT') DEFAULT 'NORMAL' COMMENT 'Prioridade',
    tags JSON NULL COMMENT 'Tags da conversa',
    notes TEXT NULL COMMENT 'Notas sobre a conversa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (instance_id) REFERENCES whatsapp_instances(id) ON DELETE CASCADE,
    FOREIGN KEY (contact_id) REFERENCES whatsapp_contacts(id) ON DELETE CASCADE,
    FOREIGN KEY (queue_id) REFERENCES whatsapp_queues(id) ON DELETE SET NULL,
    INDEX idx_instance_id (instance_id),
    INDEX idx_contact_id (contact_id),
    INDEX idx_queue_id (queue_id),
    INDEX idx_status (status),
    INDEX idx_last_message_at (last_message_at),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- 6. TABELA: whatsapp_attendances
-- Atendimentos (relaciona conversa com atendente)
-- ===========================================
CREATE TABLE IF NOT EXISTS whatsapp_attendances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL COMMENT 'Conversa sendo atendida',
    user_id INT NOT NULL COMMENT 'Atendente responsável',
    queue_id INT NULL COMMENT 'Fila de origem',
    status ENUM('OPEN', 'PENDING', 'CLOSED', 'TRANSFERRED') DEFAULT 'OPEN' COMMENT 'Status do atendimento',
    started_at TIMESTAMP NULL COMMENT 'Quando o atendimento começou',
    ended_at TIMESTAMP NULL COMMENT 'Quando o atendimento terminou',
    transferred_from_user_id INT NULL COMMENT 'Usuário que transferiu (se foi transferido)',
    transferred_to_user_id INT NULL COMMENT 'Usuário para quem foi transferido',
    transferred_at TIMESTAMP NULL COMMENT 'Quando foi transferido',
    transferred_reason TEXT NULL COMMENT 'Motivo da transferência',
    rating INT NULL COMMENT 'Avaliação do atendimento (1-5)',
    rating_comment TEXT NULL COMMENT 'Comentário da avaliação',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES whatsapp_conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (queue_id) REFERENCES whatsapp_queues(id) ON DELETE SET NULL,
    FOREIGN KEY (transferred_from_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (transferred_to_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_conversation_id (conversation_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_started_at (started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- 7. TABELA: whatsapp_messages
-- Mensagens trocadas
-- ===========================================
CREATE TABLE IF NOT EXISTS whatsapp_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL COMMENT 'Conversa da mensagem',
    attendance_id INT NULL COMMENT 'Atendimento relacionado',
    instance_id INT NOT NULL COMMENT 'Instância do WhatsApp',
    message_key VARCHAR(255) UNIQUE NULL COMMENT 'Chave única da mensagem (da Evolution API)',
    remote_jid VARCHAR(255) NOT NULL COMMENT 'JID remoto (número do WhatsApp)',
    from_me BOOLEAN DEFAULT FALSE COMMENT 'Se a mensagem foi enviada por nós',
    message_type ENUM('TEXT', 'IMAGE', 'VIDEO', 'AUDIO', 'DOCUMENT', 'LOCATION', 'CONTACT', 'STICKER', 'VOICE') DEFAULT 'TEXT' COMMENT 'Tipo da mensagem',
    body TEXT NULL COMMENT 'Corpo da mensagem (texto)',
    media_url VARCHAR(500) NULL COMMENT 'URL da mídia (se houver)',
    media_mime_type VARCHAR(100) NULL COMMENT 'Tipo MIME da mídia',
    media_name VARCHAR(255) NULL COMMENT 'Nome do arquivo de mídia',
    media_size INT NULL COMMENT 'Tamanho do arquivo em bytes',
    caption TEXT NULL COMMENT 'Legenda da mídia',
    quoted_message_id INT NULL COMMENT 'ID da mensagem citada (reply)',
    is_read BOOLEAN DEFAULT FALSE COMMENT 'Se a mensagem foi lida',
    read_at TIMESTAMP NULL COMMENT 'Quando foi lida',
    is_deleted BOOLEAN DEFAULT FALSE COMMENT 'Se a mensagem foi deletada',
    deleted_at TIMESTAMP NULL COMMENT 'Quando foi deletada',
    timestamp BIGINT NOT NULL COMMENT 'Timestamp da mensagem (Unix timestamp)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES whatsapp_conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (attendance_id) REFERENCES whatsapp_attendances(id) ON DELETE SET NULL,
    FOREIGN KEY (instance_id) REFERENCES whatsapp_instances(id) ON DELETE CASCADE,
    FOREIGN KEY (quoted_message_id) REFERENCES whatsapp_messages(id) ON DELETE SET NULL,
    INDEX idx_conversation_id (conversation_id),
    INDEX idx_attendance_id (attendance_id),
    INDEX idx_instance_id (instance_id),
    INDEX idx_message_key (message_key),
    INDEX idx_remote_jid (remote_jid),
    INDEX idx_from_me (from_me),
    INDEX idx_timestamp (timestamp),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- 8. TABELA: whatsapp_user_sessions
-- Sessões de usuários (para controle de online/offline)
-- ===========================================
CREATE TABLE IF NOT EXISTS whatsapp_user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    is_online BOOLEAN DEFAULT FALSE COMMENT 'Se o usuário está online',
    last_activity_at TIMESTAMP NULL COMMENT 'Última atividade',
    current_chats_count INT DEFAULT 0 COMMENT 'Número de chats ativos',
    max_chats INT DEFAULT 5 COMMENT 'Máximo de chats permitidos',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_session (user_id),
    INDEX idx_is_online (is_online),
    INDEX idx_last_activity_at (last_activity_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- 9. ADICIONAR CAMPOS NA TABELA USERS
-- Adiciona campos necessários para o sistema WhatsApp
-- ===========================================
ALTER TABLE users 
ADD COLUMN whatsapp_role ENUM('ADMIN', 'SUPERVISOR', 'ATTENDANT') DEFAULT 'ATTENDANT' COMMENT 'Papel no sistema WhatsApp' AFTER status,
ADD COLUMN whatsapp_is_active BOOLEAN DEFAULT TRUE COMMENT 'Se pode usar o sistema WhatsApp' AFTER whatsapp_role,
ADD COLUMN whatsapp_max_chats INT DEFAULT 5 COMMENT 'Máximo de chats simultâneos' AFTER whatsapp_is_active;

-- ===========================================
-- 10. INSERIR FILA PADRÃO
-- ===========================================
INSERT INTO whatsapp_queues (name, description, color, greeting_message, is_active, auto_assign, created_at) 
VALUES ('Geral', 'Fila padrão de atendimento', '#3B82F6', 'Olá! Como posso ajudá-lo?', TRUE, TRUE, NOW())
ON DUPLICATE KEY UPDATE name = name;


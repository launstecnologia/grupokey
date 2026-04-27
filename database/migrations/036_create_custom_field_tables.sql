-- Campos dinâmicos genéricos para entidades do sistema
CREATE TABLE IF NOT EXISTS custom_field_definitions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entity_type ENUM('establishment', 'representative') NOT NULL,
    field_key VARCHAR(100) NOT NULL,
    label VARCHAR(150) NOT NULL,
    field_type ENUM('text', 'number', 'email', 'date', 'datetime-local', 'textarea', 'select', 'currency', 'phone', 'cpf', 'cnpj') NOT NULL DEFAULT 'text',
    is_required TINYINT(1) NOT NULL DEFAULT 0,
    placeholder VARCHAR(255) NULL,
    help_text VARCHAR(255) NULL,
    options_json LONGTEXT NULL,
    sort_order INT NOT NULL DEFAULT 1,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_custom_field_entity_key (entity_type, field_key),
    INDEX idx_custom_field_entity_active (entity_type, is_active, sort_order)
);

CREATE TABLE IF NOT EXISTS custom_field_values (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    entity_type ENUM('establishment', 'representative') NOT NULL,
    entity_id INT NOT NULL,
    field_id INT NOT NULL,
    value_text LONGTEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_custom_field_value_unique (entity_type, entity_id, field_id),
    INDEX idx_custom_field_values_lookup (entity_type, entity_id),
    CONSTRAINT fk_custom_field_values_definition
        FOREIGN KEY (field_id) REFERENCES custom_field_definitions(id)
        ON DELETE CASCADE
);

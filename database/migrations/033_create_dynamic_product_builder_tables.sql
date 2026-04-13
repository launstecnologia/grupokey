-- Tabelas para construtor dinâmico de produtos

CREATE TABLE IF NOT EXISTS dynamic_products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(120) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    has_api TINYINT(1) NOT NULL DEFAULT 0,
    api_provider VARCHAR(120) NULL,
    api_config_json LONGTEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_dynamic_products_active (is_active),
    INDEX idx_dynamic_products_slug (slug)
);

CREATE TABLE IF NOT EXISTS dynamic_product_fields (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    field_key VARCHAR(120) NOT NULL,
    label VARCHAR(150) NOT NULL,
    field_type VARCHAR(40) NOT NULL,
    is_required TINYINT(1) NOT NULL DEFAULT 0,
    placeholder VARCHAR(255) NULL,
    help_text VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_dynamic_product_fields_product
        FOREIGN KEY (product_id) REFERENCES dynamic_products(id)
        ON DELETE CASCADE,
    UNIQUE KEY uq_dynamic_product_field_key (product_id, field_key),
    INDEX idx_dynamic_product_fields_product (product_id, is_active)
);

CREATE TABLE IF NOT EXISTS dynamic_product_field_options (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    field_id BIGINT UNSIGNED NOT NULL,
    option_value VARCHAR(160) NOT NULL,
    option_label VARCHAR(160) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_dynamic_product_field_options_field
        FOREIGN KEY (field_id) REFERENCES dynamic_product_fields(id)
        ON DELETE CASCADE,
    INDEX idx_dynamic_product_field_options_field (field_id)
);

-- Vínculo de estabelecimentos com produtos dinâmicos (modo aditivo, sem remover legado)

CREATE TABLE IF NOT EXISTS establishment_dynamic_products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    establishment_id INT NOT NULL,
    dynamic_product_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_establishment_dynamic_product (establishment_id, dynamic_product_id),
    INDEX idx_establishment_dynamic_product_establishment (establishment_id),
    INDEX idx_establishment_dynamic_product_product (dynamic_product_id),
    CONSTRAINT fk_establishment_dynamic_products_establishment
        FOREIGN KEY (establishment_id) REFERENCES establishments(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_establishment_dynamic_products_product
        FOREIGN KEY (dynamic_product_id) REFERENCES dynamic_products(id)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS establishment_dynamic_product_values (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    establishment_dynamic_product_id BIGINT UNSIGNED NOT NULL,
    field_key VARCHAR(120) NOT NULL,
    field_value LONGTEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_establishment_dynamic_product_field (establishment_dynamic_product_id, field_key),
    INDEX idx_establishment_dynamic_product_values_parent (establishment_dynamic_product_id),
    CONSTRAINT fk_establishment_dynamic_product_values_parent
        FOREIGN KEY (establishment_dynamic_product_id) REFERENCES establishment_dynamic_products(id)
        ON DELETE CASCADE
);


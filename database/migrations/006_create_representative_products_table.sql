-- Criar tabela para produtos permitidos por representante
CREATE TABLE IF NOT EXISTS representative_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    representative_id INT NOT NULL,
    product_type VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (representative_id) REFERENCES representatives(id) ON DELETE CASCADE,
    UNIQUE KEY unique_representative_product (representative_id, product_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


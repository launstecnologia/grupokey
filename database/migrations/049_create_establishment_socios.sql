CREATE TABLE IF NOT EXISTS establishment_socios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    establishment_id INT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    nome VARCHAR(255) NULL,
    cpf VARCHAR(20) NULL,
    data_nascimento DATE NULL,
    rg VARCHAR(40) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_establishment_socios_establishment (establishment_id, sort_order)
);

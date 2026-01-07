-- ===========================================
-- TABELA DE SEGMENTOS
-- ===========================================
CREATE TABLE IF NOT EXISTS segments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL UNIQUE,
    descricao TEXT,
    status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserir segmentos padrão
INSERT INTO segments (nome, descricao, status) VALUES 
('Comércio', 'Estabelecimentos comerciais', 'ACTIVE'),
('Serviços', 'Prestadores de serviços', 'ACTIVE'),
('Indústria', 'Empresas industriais', 'ACTIVE'),
('Agricultura', 'Atividades agrícolas', 'ACTIVE'),
('Tecnologia', 'Empresas de tecnologia', 'ACTIVE'),
('Saúde', 'Estabelecimentos de saúde', 'ACTIVE'),
('Educação', 'Instituições de ensino', 'ACTIVE'),
('Outros', 'Outros segmentos', 'ACTIVE')
ON DUPLICATE KEY UPDATE nome=nome;


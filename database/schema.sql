-- ===========================================
-- SCHEMA DO BANCO DE DADOS - SISTEMA CRM
-- ===========================================
-- Versão: 1.0
-- Data: 2025
-- Descrição: Schema completo do sistema CRM com todas as tabelas necessárias

-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS grup_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE grup_platform;

-- ===========================================
-- TABELA DE USUÁRIOS (ADMINISTRADORES)
-- ===========================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    status ENUM('ACTIVE', 'INACTIVE', 'BLOCKED') DEFAULT 'ACTIVE',
    failed_attempts INT DEFAULT 0,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ===========================================
-- TABELA DE REPRESENTANTES
-- ===========================================
CREATE TABLE representatives (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_completo VARCHAR(255) NOT NULL,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    cep VARCHAR(9),
    logradouro VARCHAR(255),
    numero VARCHAR(10),
    complemento VARCHAR(100),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    uf VARCHAR(2),
    password VARCHAR(255) NOT NULL,
    status ENUM('ACTIVE', 'INACTIVE', 'BLOCKED') DEFAULT 'ACTIVE',
    failed_attempts INT DEFAULT 0,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ===========================================
-- TABELA DE ESTABELECIMENTOS
-- ===========================================
CREATE TABLE establishments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_fantasia VARCHAR(255) NOT NULL,
    razao_social VARCHAR(255) NOT NULL,
    cnpj VARCHAR(18) UNIQUE NOT NULL,
    email VARCHAR(255),
    telefone VARCHAR(20),
    cep VARCHAR(9),
    logradouro VARCHAR(255),
    numero VARCHAR(10),
    complemento VARCHAR(100),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    uf VARCHAR(2),
    status ENUM('PENDING', 'APPROVED', 'REJECTED', 'INACTIVE') DEFAULT 'PENDING',
    representative_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (representative_id) REFERENCES representatives(id) ON DELETE SET NULL
);

-- ===========================================
-- TABELA DE PRODUTOS
-- ===========================================
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    categoria VARCHAR(100),
    status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ===========================================
-- TABELA DE PRODUTOS POR ESTABELECIMENTO
-- ===========================================
CREATE TABLE establishment_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    establishment_id INT NOT NULL,
    product_id INT NOT NULL,
    preco_venda DECIMAL(10,2),
    estoque INT DEFAULT 0,
    status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (establishment_id) REFERENCES establishments(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_establishment_product (establishment_id, product_id)
);

-- ===========================================
-- TABELA DE TICKETS (SUPORTE)
-- ===========================================
CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT NOT NULL,
    status ENUM('OPEN', 'IN_PROGRESS', 'RESOLVED', 'CLOSED') DEFAULT 'OPEN',
    prioridade ENUM('LOW', 'MEDIUM', 'HIGH', 'URGENT') DEFAULT 'MEDIUM',
    user_id INT,
    representative_id INT,
    establishment_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (representative_id) REFERENCES representatives(id) ON DELETE SET NULL,
    FOREIGN KEY (establishment_id) REFERENCES establishments(id) ON DELETE SET NULL
);

-- ===========================================
-- TABELA DE COMENTÁRIOS DOS TICKETS
-- ===========================================
CREATE TABLE ticket_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    user_id INT,
    representative_id INT,
    comentario TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (representative_id) REFERENCES representatives(id) ON DELETE SET NULL
);

-- ===========================================
-- TABELA DE MATERIAIS (ARQUIVOS)
-- ===========================================
CREATE TABLE material_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE material_subcategories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NOT NULL,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES material_categories(id) ON DELETE CASCADE
);

CREATE TABLE material_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subcategoria_id INT NOT NULL,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    arquivo_path VARCHAR(500) NOT NULL,
    tamanho INT,
    tipo VARCHAR(100),
    user_id INT,
    status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (subcategoria_id) REFERENCES material_subcategories(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ===========================================
-- TABELA DE FATURAMENTO
-- ===========================================
CREATE TABLE billing_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    establishment_id INT NOT NULL,
    mes_referencia VARCHAR(7) NOT NULL, -- Formato: YYYY-MM
    total_vendas DECIMAL(12,2) DEFAULT 0,
    total_comissao DECIMAL(12,2) DEFAULT 0,
    status ENUM('PENDING', 'PROCESSED', 'PAID') DEFAULT 'PENDING',
    arquivo_path VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (establishment_id) REFERENCES establishments(id) ON DELETE CASCADE,
    UNIQUE KEY unique_establishment_month (establishment_id, mes_referencia)
);

-- ===========================================
-- INSERIR DADOS INICIAIS
-- ===========================================

-- Usuário administrador padrão
INSERT INTO users (name, email, password, status) VALUES 
('Administrador', 'admin@sistema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ACTIVE');

-- Categorias de material padrão
INSERT INTO material_categories (nome, descricao) VALUES 
('Documentos', 'Documentos oficiais e formulários'),
('Manuais', 'Manuais de uso e instruções'),
('Treinamentos', 'Materiais de treinamento e capacitação');

-- Produtos padrão
INSERT INTO products (nome, descricao, preco, categoria) VALUES 
('Produto Padrão', 'Produto de exemplo para demonstração', 100.00, 'Geral'),
('Serviço Básico', 'Serviço básico do sistema', 50.00, 'Serviços');

-- ===========================================
-- ÍNDICES PARA PERFORMANCE
-- ===========================================
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_representatives_cpf ON representatives(cpf);
CREATE INDEX idx_representatives_email ON representatives(email);
CREATE INDEX idx_representatives_status ON representatives(status);
CREATE INDEX idx_establishments_cnpj ON establishments(cnpj);
CREATE INDEX idx_establishments_status ON establishments(status);
CREATE INDEX idx_establishments_representative ON establishments(representative_id);
CREATE INDEX idx_tickets_status ON tickets(status);
CREATE INDEX idx_tickets_user ON tickets(user_id);
CREATE INDEX idx_tickets_representative ON tickets(representative_id);
CREATE INDEX idx_billing_reports_establishment ON billing_reports(establishment_id);
CREATE INDEX idx_billing_reports_month ON billing_reports(mes_referencia);

-- ===========================================
-- FIM DO SCHEMA
-- ===========================================
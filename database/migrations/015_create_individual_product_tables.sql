-- Migration: Criar tabelas individuais para cada produto
-- Data: 2025-12-02
-- Produtos: CDC, Google, Membro Key, CDX/EVO, PagBank, Outros

-- ===========================================
-- TABELA: establishment_cdc (CDC)
-- ===========================================
CREATE TABLE IF NOT EXISTS establishment_cdc (
    id VARCHAR(50) PRIMARY KEY,
    establishment_id INT NOT NULL,
    taxa DECIMAL(10,2) NULL,
    financeira VARCHAR(50) NULL COMMENT 'Nome da financeira (UCRED, PARCELA FACIL, CREDFLIP...)',
    meio_pagamento VARCHAR(20) NULL,
    valor DECIMAL(10,2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (establishment_id) REFERENCES establishments(id) ON DELETE CASCADE,
    INDEX idx_establishment_id (establishment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- TABELA: establishment_google (Google)
-- ===========================================
CREATE TABLE IF NOT EXISTS establishment_google (
    id VARCHAR(50) PRIMARY KEY,
    establishment_id INT NOT NULL,
    meio_pagamento VARCHAR(20) NULL,
    valor DECIMAL(10,2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (establishment_id) REFERENCES establishments(id) ON DELETE CASCADE,
    INDEX idx_establishment_id (establishment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- TABELA: establishment_membro_key (Membro Key)
-- ===========================================
CREATE TABLE IF NOT EXISTS establishment_membro_key (
    id VARCHAR(50) PRIMARY KEY,
    establishment_id INT NOT NULL,
    meio_pagamento VARCHAR(20) NULL,
    valor DECIMAL(10,2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (establishment_id) REFERENCES establishments(id) ON DELETE CASCADE,
    INDEX idx_establishment_id (establishment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- TABELA: establishment_cdx_evo (CDX/EVO)
-- ===========================================
CREATE TABLE IF NOT EXISTS establishment_cdx_evo (
    id VARCHAR(50) PRIMARY KEY,
    establishment_id INT NOT NULL,
    previsao_faturamento DECIMAL(10,2) NULL,
    tabela VARCHAR(50) NULL,
    modelo_maquininha VARCHAR(50) NULL,
    meio_pagamento VARCHAR(20) NULL,
    valor DECIMAL(10,2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (establishment_id) REFERENCES establishments(id) ON DELETE CASCADE,
    INDEX idx_establishment_id (establishment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- TABELA: establishment_pagbank (PagBank)
-- ===========================================
-- Criar tabela se não existir
CREATE TABLE IF NOT EXISTS establishment_pagbank (
    id VARCHAR(50) PRIMARY KEY,
    establishment_id INT NOT NULL,
    meio_pagamento VARCHAR(20) NULL,
    valor DECIMAL(10,2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (establishment_id) REFERENCES establishments(id) ON DELETE CASCADE,
    INDEX idx_establishment_id (establishment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Adicionar campos se não existirem
-- NOTA: Se algum campo já existir, você pode ignorar o erro ou executar apenas os que faltam
ALTER TABLE establishment_pagbank 
ADD COLUMN previsao_faturamento DECIMAL(10,2) NULL AFTER establishment_id;

ALTER TABLE establishment_pagbank 
ADD COLUMN tabela VARCHAR(50) NULL AFTER previsao_faturamento;

ALTER TABLE establishment_pagbank 
ADD COLUMN modelo_maquininha VARCHAR(50) NULL AFTER tabela;

-- ===========================================
-- TABELA: establishment_outros (Outros)
-- ===========================================
CREATE TABLE IF NOT EXISTS establishment_outros (
    id VARCHAR(50) PRIMARY KEY,
    establishment_id INT NOT NULL,
    meio_pagamento VARCHAR(20) NULL,
    valor DECIMAL(10,2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (establishment_id) REFERENCES establishments(id) ON DELETE CASCADE,
    INDEX idx_establishment_id (establishment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- MIGRAÇÃO DE DADOS
-- ===========================================

-- Migrar dados de establishment_brasilcard para establishment_cdc
INSERT INTO establishment_cdc (id, establishment_id, taxa, financeira, meio_pagamento, valor, created_at, updated_at)
SELECT id, establishment_id, taxa, 
       COALESCE(financeira, NULL) as financeira,
       meio_pagamento, valor, created_at, updated_at
FROM establishment_brasilcard
WHERE NOT EXISTS (
    SELECT 1 FROM establishment_cdc WHERE establishment_cdc.establishment_id = establishment_brasilcard.establishment_id
);

-- Migrar dados de establishment_pagseguro para establishment_cdx_evo
INSERT INTO establishment_cdx_evo (id, establishment_id, previsao_faturamento, tabela, modelo_maquininha, meio_pagamento, valor, created_at, updated_at)
SELECT id, establishment_id, previsao_faturamento, tabela, modelo_maquininha, meio_pagamento, valor, created_at, updated_at
FROM establishment_pagseguro
WHERE NOT EXISTS (
    SELECT 1 FROM establishment_cdx_evo WHERE establishment_cdx_evo.establishment_id = establishment_pagseguro.establishment_id
);

-- Migrar dados de establishment_other_products para as novas tabelas
-- Google
INSERT INTO establishment_google (id, establishment_id, meio_pagamento, valor, created_at, updated_at)
SELECT id, establishment_id, meio_pagamento, valor, created_at, updated_at
FROM establishment_other_products
WHERE product_type = 'prod-google'
AND NOT EXISTS (
    SELECT 1 FROM establishment_google WHERE establishment_google.establishment_id = establishment_other_products.establishment_id
);

-- Membro Key
INSERT INTO establishment_membro_key (id, establishment_id, meio_pagamento, valor, created_at, updated_at)
SELECT id, establishment_id, meio_pagamento, valor, created_at, updated_at
FROM establishment_other_products
WHERE product_type = 'prod-membro-key'
AND NOT EXISTS (
    SELECT 1 FROM establishment_membro_key WHERE establishment_membro_key.establishment_id = establishment_other_products.establishment_id
);

-- Outros
INSERT INTO establishment_outros (id, establishment_id, meio_pagamento, valor, created_at, updated_at)
SELECT id, establishment_id, meio_pagamento, valor, created_at, updated_at
FROM establishment_other_products
WHERE product_type = 'prod-outros'
AND NOT EXISTS (
    SELECT 1 FROM establishment_outros WHERE establishment_outros.establishment_id = establishment_other_products.establishment_id
);


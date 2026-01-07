-- ===========================================
-- TABELA DE DOCUMENTOS DOS ESTABELECIMENTOS
-- ===========================================
CREATE TABLE IF NOT EXISTS documents (
    id VARCHAR(36) PRIMARY KEY,
    establishment_id INT NOT NULL,
    document_type ENUM(
        'CONTRATO_SOCIAL',
        'DOCUMENTO_FOTO_FRENTE',
        'DOCUMENTO_FOTO_VERSO',
        'COMPROVANTE_RESIDENCIA',
        'FOTO_FACHADA',
        'OUTROS_DOCUMENTOS',
        'RG_CPF_CNH',
        'COMPROVANTE_BANCARIO',
        'SELFIE_DOCUMENTO',
        'COMPROVANTE_ENDERECO',
        'CARTAO_CNPJ',
        'PRINT_INSTAGRAM',
        'FOTO_TITULAR_LOJA'
    ) DEFAULT 'OUTROS_DOCUMENTOS',
    file_path VARCHAR(500) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100),
    size INT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (establishment_id) REFERENCES establishments(id) ON DELETE CASCADE,
    INDEX idx_establishment_documents (establishment_id),
    INDEX idx_document_type (document_type)
);


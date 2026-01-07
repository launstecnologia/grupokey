-- ===========================================
-- ATUALIZAR ENUM DA TABELA DOCUMENTS
-- Adicionar valores faltantes ao ENUM document_type
-- ===========================================

-- Adicionar valores faltantes ao ENUM
ALTER TABLE documents 
MODIFY COLUMN document_type ENUM(
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
) DEFAULT 'OUTROS_DOCUMENTOS' NOT NULL;


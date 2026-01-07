-- Migration: Atualizar tabela products para ter apenas os produtos corretos
-- Data: 2025-01-XX
-- Produtos permitidos: CDC, Google, Membro Key, CDX/EVO, PagBank, Outros

-- Desativar produtos que não devem existir
UPDATE products SET is_active = 0 WHERE id IN ('prod-conteudo-digital', 'prod-flamex', 'prod-griva', 'prod-mercado-pago');

-- Atualizar prod-brasil-card para CDC (manter ID para compatibilidade)
UPDATE products SET 
    name = 'CDC',
    slug = 'prod-brasil-card',
    description = 'CDC - Cartão de Crédito Consignado'
WHERE id = 'prod-brasil-card';

-- Atualizar prod-pagseguro para CDX/EVO (manter ID para compatibilidade)
UPDATE products SET 
    name = 'CDX/EVO',
    slug = 'prod-pagseguro',
    description = 'CDX/EVO - Soluções de pagamento'
WHERE id = 'prod-pagseguro';

-- Criar PagBank se não existir (usar prod-pagbank como ID)
INSERT INTO products (id, name, slug, description, is_active, created_at, updated_at)
SELECT 'prod-pagbank', 'PagBank', 'prod-pagbank', 'PagBank - Soluções de pagamento', 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM products WHERE id = 'prod-pagbank');

-- Atualizar Google (manter mas garantir nome correto)
UPDATE products SET 
    name = 'Google',
    description = 'Google - Serviços Google'
WHERE id = 'prod-google';

-- Atualizar Membro Key (manter mas garantir nome correto)
UPDATE products SET 
    name = 'Membro Key',
    description = 'Membro Key - Programa de fidelidade'
WHERE id = 'prod-membro-key';

-- Atualizar Outros (manter mas garantir nome correto)
UPDATE products SET 
    name = 'Outros',
    description = 'Outros produtos e serviços'
WHERE id = 'prod-outros';


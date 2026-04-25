-- Backfill seguro (idempotente) para produtos dinâmicos a partir de tabelas legadas
-- Mantém PagBank manual fora deste backfill

-- 1) Vincular CDC legado -> produto dinâmico CDC
INSERT INTO establishment_dynamic_products (establishment_id, dynamic_product_id, created_at, updated_at)
SELECT c.establishment_id, dp.id, NOW(), NOW()
FROM establishment_cdc c
INNER JOIN dynamic_products dp ON dp.is_active = 1 AND dp.slug = 'cdc'
LEFT JOIN establishment_dynamic_products edp
       ON edp.establishment_id = c.establishment_id
      AND edp.dynamic_product_id = dp.id
WHERE edp.id IS NULL;

-- 2) Vincular Google legado -> produto dinâmico Google
INSERT INTO establishment_dynamic_products (establishment_id, dynamic_product_id, created_at, updated_at)
SELECT g.establishment_id, dp.id, NOW(), NOW()
FROM establishment_google g
INNER JOIN dynamic_products dp ON dp.is_active = 1 AND dp.slug = 'google'
LEFT JOIN establishment_dynamic_products edp
       ON edp.establishment_id = g.establishment_id
      AND edp.dynamic_product_id = dp.id
WHERE edp.id IS NULL;

-- 3) Vincular CDX/EVO legado -> produto dinâmico EVO (slug flexível)
INSERT INTO establishment_dynamic_products (establishment_id, dynamic_product_id, created_at, updated_at)
SELECT e.establishment_id, dp.id, NOW(), NOW()
FROM establishment_cdx_evo e
INNER JOIN dynamic_products dp
        ON dp.is_active = 1
       AND dp.slug IN ('evo', 'cdx_evo', 'cdx-evo')
LEFT JOIN establishment_dynamic_products edp
       ON edp.establishment_id = e.establishment_id
      AND edp.dynamic_product_id = dp.id
WHERE edp.id IS NULL;

-- 4) Valores CDC
INSERT INTO establishment_dynamic_product_values (establishment_dynamic_product_id, field_key, field_value, created_at, updated_at)
SELECT
    edp.id,
    f.field_key,
    CASE
        WHEN f.field_key IN ('taxa', 'financeira') THEN COALESCE(c.financeira, CAST(c.taxa AS CHAR))
        WHEN f.field_key IN ('meio_pagamento', 'meio_pagamento_adesao') THEN c.meio_pagamento
        WHEN f.field_key IN ('valor', 'valor_adesao') THEN CAST(c.valor AS CHAR)
        ELSE NULL
    END AS field_value,
    NOW(),
    NOW()
FROM establishment_cdc c
INNER JOIN dynamic_products dp ON dp.is_active = 1 AND dp.slug = 'cdc'
INNER JOIN establishment_dynamic_products edp
        ON edp.establishment_id = c.establishment_id
       AND edp.dynamic_product_id = dp.id
INNER JOIN dynamic_product_fields f
        ON f.product_id = dp.id
       AND f.is_active = 1
WHERE (
        (f.field_key IN ('taxa', 'financeira') AND (c.taxa IS NOT NULL OR c.financeira IS NOT NULL))
     OR (f.field_key IN ('meio_pagamento', 'meio_pagamento_adesao') AND c.meio_pagamento IS NOT NULL)
     OR (f.field_key IN ('valor', 'valor_adesao') AND c.valor IS NOT NULL)
)
ON DUPLICATE KEY UPDATE field_value = VALUES(field_value), updated_at = NOW();

-- 5) Valores Google
INSERT INTO establishment_dynamic_product_values (establishment_dynamic_product_id, field_key, field_value, created_at, updated_at)
SELECT
    edp.id,
    f.field_key,
    CASE
        WHEN f.field_key IN ('meio_pagamento', 'meio_pagamento_adesao') THEN g.meio_pagamento
        WHEN f.field_key IN ('valor', 'valor_adesao') THEN CAST(g.valor AS CHAR)
        ELSE NULL
    END AS field_value,
    NOW(),
    NOW()
FROM establishment_google g
INNER JOIN dynamic_products dp ON dp.is_active = 1 AND dp.slug = 'google'
INNER JOIN establishment_dynamic_products edp
        ON edp.establishment_id = g.establishment_id
       AND edp.dynamic_product_id = dp.id
INNER JOIN dynamic_product_fields f
        ON f.product_id = dp.id
       AND f.is_active = 1
WHERE (
        (f.field_key IN ('meio_pagamento', 'meio_pagamento_adesao') AND g.meio_pagamento IS NOT NULL)
     OR (f.field_key IN ('valor', 'valor_adesao') AND g.valor IS NOT NULL)
)
ON DUPLICATE KEY UPDATE field_value = VALUES(field_value), updated_at = NOW();

-- 6) Valores EVO
INSERT INTO establishment_dynamic_product_values (establishment_dynamic_product_id, field_key, field_value, created_at, updated_at)
SELECT
    edp.id,
    f.field_key,
    CASE
        WHEN f.field_key IN ('previsao_faturamento') THEN CAST(e.previsao_faturamento AS CHAR)
        WHEN f.field_key IN ('tabela') THEN e.tabela
        WHEN f.field_key IN ('modelo_maquininha', 'maquininha') THEN e.modelo_maquininha
        WHEN f.field_key IN ('meio_pagamento', 'meio_pagamento_adesao') THEN e.meio_pagamento
        WHEN f.field_key IN ('valor', 'valor_adesao') THEN CAST(e.valor AS CHAR)
        ELSE NULL
    END AS field_value,
    NOW(),
    NOW()
FROM establishment_cdx_evo e
INNER JOIN dynamic_products dp
        ON dp.is_active = 1
       AND dp.slug IN ('evo', 'cdx_evo', 'cdx-evo')
INNER JOIN establishment_dynamic_products edp
        ON edp.establishment_id = e.establishment_id
       AND edp.dynamic_product_id = dp.id
INNER JOIN dynamic_product_fields f
        ON f.product_id = dp.id
       AND f.is_active = 1
WHERE (
        (f.field_key IN ('previsao_faturamento') AND e.previsao_faturamento IS NOT NULL)
     OR (f.field_key IN ('tabela') AND e.tabela IS NOT NULL)
     OR (f.field_key IN ('modelo_maquininha', 'maquininha') AND e.modelo_maquininha IS NOT NULL)
     OR (f.field_key IN ('meio_pagamento', 'meio_pagamento_adesao') AND e.meio_pagamento IS NOT NULL)
     OR (f.field_key IN ('valor', 'valor_adesao') AND e.valor IS NOT NULL)
)
ON DUPLICATE KEY UPDATE field_value = VALUES(field_value), updated_at = NOW();


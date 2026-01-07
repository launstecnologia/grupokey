-- Migration: Adicionar campo plan na tabela establishment_pagbank
-- Data: 2025-12-05

ALTER TABLE establishment_pagbank
ADD COLUMN plan INT NULL AFTER modelo_maquininha;

-- Adicionar índice para buscas rápidas
CREATE INDEX idx_plan ON establishment_pagbank (plan);


-- PONTO DE RETORNO (restore point) antes de ativar campos dinâmicos genéricos
-- Obs.: backup criado apenas uma vez para evitar sobrescrita acidental.

CREATE TABLE IF NOT EXISTS backup_restore_establishments_before_custom_fields AS
SELECT * FROM establishments;

CREATE TABLE IF NOT EXISTS backup_restore_representatives_before_custom_fields AS
SELECT * FROM representatives;

CREATE TABLE IF NOT EXISTS backup_restore_establishment_products_before_custom_fields AS
SELECT * FROM establishment_products;

CREATE TABLE IF NOT EXISTS backup_restore_establishment_dynamic_products_before_custom_fields AS
SELECT * FROM establishment_dynamic_products;

CREATE TABLE IF NOT EXISTS backup_restore_representative_products_before_custom_fields AS
SELECT * FROM representative_products;

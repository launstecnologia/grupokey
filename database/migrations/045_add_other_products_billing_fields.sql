ALTER TABLE billing_reports
ADD COLUMN report_layout VARCHAR(30) NULL AFTER company_code,
ADD COLUMN product_scope VARCHAR(120) NULL AFTER report_layout;

ALTER TABLE billing_data
ADD COLUMN razao_fantasia VARCHAR(255) NULL AFTER nome,
ADD COLUMN cidade VARCHAR(120) NULL AFTER representante,
ADD COLUMN uf VARCHAR(2) NULL AFTER cidade;


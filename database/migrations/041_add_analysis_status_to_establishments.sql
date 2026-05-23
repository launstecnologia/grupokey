-- Adiciona o status "ANALYSIS" para permitir fluxo "Em análise" nos estabelecimentos
ALTER TABLE establishments
MODIFY COLUMN status ENUM('PENDING', 'ANALYSIS', 'APPROVED', 'REPROVED', 'DISABLED') DEFAULT 'PENDING';

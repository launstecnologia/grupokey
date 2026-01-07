-- Alterar coluna body de TEXT para MEDIUMTEXT para suportar conteúdo maior
ALTER TABLE email_campaigns 
MODIFY COLUMN body MEDIUMTEXT NOT NULL;


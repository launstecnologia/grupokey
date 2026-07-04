ALTER TABLE users
ADD COLUMN birth_date DATE NULL AFTER name;

ALTER TABLE representatives
ADD COLUMN birth_date DATE NULL AFTER cpf;

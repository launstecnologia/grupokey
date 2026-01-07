<?php
/**
 * Script auxiliar para gerar hash de senha
 * 
 * Uso: php generate_password_hash.php sua_senha
 * Ou execute diretamente e digite a senha quando solicitado
 */

$password = $argv[1] ?? null;

if (empty($password)) {
    echo "Digite a senha para gerar o hash: ";
    $password = trim(fgets(STDIN));
}

if (empty($password)) {
    echo "Erro: Senha não pode estar vazia.\n";
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);

echo "\n";
echo "===========================================\n";
echo "HASH GERADO COM SUCESSO\n";
echo "===========================================\n";
echo "Senha: {$password}\n";
echo "Hash:  {$hash}\n";
echo "\n";
echo "SQL para inserir no banco:\n";
echo "INSERT INTO users (name, email, password, status) VALUES \n";
echo "('Nome do Admin', 'email@exemplo.com', '{$hash}', 'ACTIVE');\n";
echo "\n";


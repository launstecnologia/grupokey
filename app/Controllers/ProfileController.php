<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\User;
use App\Models\Representative;

class ProfileController
{
    private $userModel;
    private $representativeModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->representativeModel = new Representative();
    }

    public function index()
    {
        Auth::requireAuth();
        
        if (Auth::isAdmin()) {
            $user = Auth::user();
            // Buscar dados completos do banco para garantir que temos a foto
            $fullUser = $this->userModel->findById($user['id']);
            // Garantir que temos todos os dados, incluindo photo
            if ($fullUser) {
                $data = [
                    'title' => 'Meu Perfil',
                    'currentPage' => 'perfil',
                    'user' => $fullUser,
                    'type' => 'admin'
                ];
            } else {
                // Fallback para dados da sessão
                $data = [
                    'title' => 'Meu Perfil',
                    'currentPage' => 'perfil',
                    'user' => $user,
                    'type' => 'admin'
                ];
            }
        } else {
            $representative = Auth::representative();
            // Buscar dados completos do banco para garantir que temos a foto
            $fullRepresentative = $this->representativeModel->findById($representative['id']);
            // Garantir que temos todos os dados, incluindo photo
            if ($fullRepresentative) {
                $data = [
                    'title' => 'Meu Perfil',
                    'currentPage' => 'perfil',
                    'user' => $fullRepresentative,
                    'type' => 'representative'
                ];
            } else {
                // Fallback para dados da sessão
                $data = [
                    'title' => 'Meu Perfil',
                    'currentPage' => 'perfil',
                    'user' => $representative,
                    'type' => 'representative'
                ];
            }
        }
        
        view('profile/index', $data);
    }

    public function edit()
    {
        Auth::requireAuth();
        
        if (Auth::isAdmin()) {
            $user = Auth::user();
            // Buscar dados completos do banco para garantir que temos a foto
            $fullUser = $this->userModel->findById($user['id']);
            $data = [
                'title' => 'Editar Perfil',
                'currentPage' => 'perfil',
                'user' => $fullUser ?: $user,
                'type' => 'admin'
            ];
        } else {
            $representative = Auth::representative();
            // Buscar dados completos do banco para garantir que temos a foto
            $fullRepresentative = $this->representativeModel->findById($representative['id']);
            $data = [
                'title' => 'Editar Perfil',
                'currentPage' => 'perfil',
                'user' => $fullRepresentative ?: $representative,
                'type' => 'representative'
            ];
        }
        
        view('profile/edit', $data);
    }

    public function update()
    {
        Auth::requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('perfil/edit'));
        }
        
        $data = $this->validateAndSanitizeInput();
        
        if (isset($_SESSION['validation_errors'])) {
            redirect(url('perfil/edit'));
        }
        
        // Processar upload de foto
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            // Usar caminho absoluto baseado na raiz do projeto
            $rootPath = dirname(__DIR__, 2); // Volta 2 níveis: Controllers -> app -> raiz
            $uploadDir = $rootPath . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'profiles' . DIRECTORY_SEPARATOR;
            
            $userId = Auth::isAdmin() ? Auth::user()['id'] : Auth::representative()['id'];
            $userType = Auth::isAdmin() ? 'admin' : 'representative';
            
            write_log("=== INÍCIO UPLOAD DE FOTO ===", 'photo_upload.log');
            write_log("Usuário ID: {$userId} | Tipo: {$userType}", 'photo_upload.log');
            write_log("Caminho do diretório: {$uploadDir}", 'photo_upload.log');
            write_log("Diretório existe? " . (is_dir($uploadDir) ? 'SIM' : 'NÃO'), 'photo_upload.log');
            write_log("É gravável? " . (is_writable($uploadDir) ? 'SIM' : 'NÃO'), 'photo_upload.log');
            
            error_log('=== UPLOAD DE FOTO ===');
            error_log('Caminho do diretório: ' . $uploadDir);
            error_log('Diretório existe? ' . (is_dir($uploadDir) ? 'SIM' : 'NÃO'));
            error_log('É gravável? ' . (is_writable($uploadDir) ? 'SIM' : 'NÃO'));
            
            // Criar diretório se não existir
            if (!is_dir($uploadDir)) {
                $created = mkdir($uploadDir, 0755, true);
                write_log("Diretório criado? " . ($created ? 'SIM' : 'NÃO'), 'photo_upload.log');
                error_log('Diretório criado? ' . ($created ? 'SIM' : 'NÃO'));
                if (!$created) {
                    write_log("ERRO: Falha ao criar diretório de upload", 'photo_upload.log');
                    $_SESSION['error'] = 'Erro ao criar diretório de upload. Verifique as permissões.';
                    redirect(url('perfil/edit'));
                }
            }
            
            $file = $_FILES['photo'];
            write_log("Arquivo recebido: Nome={$file['name']} | Tamanho={$file['size']} bytes | Tipo={$file['type']} | Erro={$file['error']}", 'photo_upload.log');
            error_log('Arquivo recebido: ' . json_encode($file));
            
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $maxSize = 2 * 1024 * 1024; // 2MB
            
            // Validar tipo
            if (!in_array($file['type'], $allowedTypes)) {
                write_log("ERRO: Tipo de arquivo inválido: {$file['type']}", 'photo_upload.log');
                error_log('Tipo de arquivo inválido: ' . $file['type']);
                $_SESSION['error'] = 'Tipo de arquivo não permitido. Use JPG, PNG ou GIF.';
                redirect(url('perfil/edit'));
            }
            
            // Validar tamanho
            if ($file['size'] > $maxSize) {
                $sizeMB = round($file['size'] / 1024 / 1024, 2);
                write_log("ERRO: Arquivo muito grande: {$sizeMB}MB (máximo: 2MB)", 'photo_upload.log');
                error_log('Arquivo muito grande: ' . $file['size']);
                $_SESSION['error'] = 'Arquivo muito grande. Tamanho máximo: 2MB.';
                redirect(url('perfil/edit'));
            }
            
            // Gerar nome único
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $type = Auth::isAdmin() ? 'user' : 'representative';
            $fileName = $type . '_' . $userId . '_' . time() . '.' . $extension;
            $filePath = $uploadDir . $fileName;
            
            write_log("Nome do arquivo gerado: {$fileName}", 'photo_upload.log');
            write_log("Caminho completo: {$filePath}", 'photo_upload.log');
            error_log('Nome do arquivo: ' . $fileName);
            error_log('Caminho completo: ' . $filePath);
            
            // Remover foto antiga se existir - buscar do banco para ter certeza
            if (Auth::isAdmin()) {
                $oldUser = $this->userModel->findById(Auth::user()['id']);
                if (!empty($oldUser['photo']) && file_exists($uploadDir . $oldUser['photo'])) {
                    @unlink($uploadDir . $oldUser['photo']);
                    write_log("Foto antiga removida: {$oldUser['photo']}", 'photo_upload.log');
                    error_log('Foto antiga removida: ' . $oldUser['photo']);
                }
            } else {
                $oldRepresentative = $this->representativeModel->findById(Auth::representative()['id']);
                if (!empty($oldRepresentative['photo']) && file_exists($uploadDir . $oldRepresentative['photo'])) {
                    @unlink($uploadDir . $oldRepresentative['photo']);
                    write_log("Foto antiga removida: {$oldRepresentative['photo']}", 'photo_upload.log');
                    error_log('Foto antiga removida: ' . $oldRepresentative['photo']);
                }
            }
            
            // Fazer upload
            write_log("Tentando mover arquivo de: {$file['tmp_name']} para: {$filePath}", 'photo_upload.log');
            error_log('Tentando mover arquivo de: ' . $file['tmp_name'] . ' para: ' . $filePath);
            
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                $savedSize = filesize($filePath);
                write_log("SUCESSO: Arquivo movido com sucesso!", 'photo_upload.log');
                write_log("Arquivo existe no destino? " . (file_exists($filePath) ? 'SIM' : 'NÃO'), 'photo_upload.log');
                write_log("Tamanho do arquivo salvo: {$savedSize} bytes", 'photo_upload.log');
                write_log("Nome da foto a ser salva no banco: {$fileName}", 'photo_upload.log');
                
                error_log('Arquivo movido com sucesso!');
                error_log('Arquivo existe no destino? ' . (file_exists($filePath) ? 'SIM' : 'NÃO'));
                error_log('Tamanho do arquivo salvo: ' . $savedSize);
                $data['photo'] = $fileName;
                error_log('Nome da foto a ser salva no banco: ' . $fileName);
            } else {
                $lastError = error_get_last();
                $errorMsg = $lastError['message'] ?? 'N/A';
                write_log("ERRO ao mover arquivo: {$errorMsg}", 'photo_upload.log');
                error_log('ERRO ao mover arquivo!');
                error_log('Erro PHP: ' . $errorMsg);
                $_SESSION['error'] = 'Erro ao fazer upload da foto. Verifique as permissões do diretório.';
                redirect(url('perfil/edit'));
            }
        } else {
            if (isset($_FILES['photo'])) {
                error_log('Erro no upload: ' . $_FILES['photo']['error']);
                $uploadErrors = [
                    UPLOAD_ERR_INI_SIZE => 'Arquivo excede o tamanho máximo permitido pelo PHP',
                    UPLOAD_ERR_FORM_SIZE => 'Arquivo excede o tamanho máximo do formulário',
                    UPLOAD_ERR_PARTIAL => 'Upload parcial do arquivo',
                    UPLOAD_ERR_NO_FILE => 'Nenhum arquivo foi enviado',
                    UPLOAD_ERR_NO_TMP_DIR => 'Diretório temporário não encontrado',
                    UPLOAD_ERR_CANT_WRITE => 'Falha ao escrever arquivo no disco',
                    UPLOAD_ERR_EXTENSION => 'Upload bloqueado por extensão'
                ];
                $errorMsg = $uploadErrors[$_FILES['photo']['error']] ?? 'Erro desconhecido no upload';
                error_log('Mensagem de erro: ' . $errorMsg);
            }
        }
        
        try {
            if (Auth::isAdmin()) {
                write_log("Atualizando perfil do usuário ID: {$userId}", 'photo_upload.log');
                write_log("Dados a serem salvos: " . json_encode($data), 'photo_upload.log');
                
                $this->userModel->update(Auth::user()['id'], $data);
                
                // Atualizar sessão com dados atualizados
                $updatedUser = $this->userModel->findById(Auth::user()['id']);
                if ($updatedUser) {
                    $_SESSION['user_name'] = $updatedUser['name'];
                    $_SESSION['user_email'] = $updatedUser['email'];
                    $_SESSION['user_photo'] = $updatedUser['photo'] ?? null;
                    write_log("SUCESSO: Perfil atualizado no banco. Foto salva: " . ($updatedUser['photo'] ?? 'NENHUMA'), 'photo_upload.log');
                }
                $_SESSION['success'] = 'Perfil atualizado com sucesso!';
            } else {
                write_log("Atualizando perfil do representante ID: {$userId}", 'photo_upload.log');
                write_log("Dados a serem salvos: " . json_encode($data), 'photo_upload.log');
                
                $this->representativeModel->update(Auth::representative()['id'], $data);
                
                // Atualizar sessão com dados atualizados
                $updatedRepresentative = $this->representativeModel->findById(Auth::representative()['id']);
                if ($updatedRepresentative) {
                    $_SESSION['representative_name'] = $updatedRepresentative['nome_completo'];
                    $_SESSION['representative_email'] = $updatedRepresentative['email'];
                    $_SESSION['representative_photo'] = $updatedRepresentative['photo'] ?? null;
                    write_log("SUCESSO: Perfil atualizado no banco. Foto salva: " . ($updatedRepresentative['photo'] ?? 'NENHUMA'), 'photo_upload.log');
                }
                $_SESSION['success'] = 'Perfil atualizado com sucesso!';
            }
            
            write_log("=== FIM UPLOAD DE FOTO - SUCESSO ===", 'photo_upload.log');
            redirect(url('perfil'));
            
        } catch (\Exception $e) {
            write_log("ERRO ao atualizar perfil no banco: " . $e->getMessage(), 'photo_upload.log');
            write_log("Stack trace: " . $e->getTraceAsString(), 'photo_upload.log');
            $_SESSION['error'] = 'Erro ao atualizar perfil: ' . $e->getMessage();
            redirect(url('perfil/edit'));
        }
    }

    public function showChangePassword()
    {
        // Verificar se está logado ou se precisa alterar senha
        if (!Auth::check() && !isset($_SESSION['representative_id'])) {
            redirect(url('login'));
        }
        
        $data = [
            'title' => 'Alterar Senha',
            'currentPage' => 'perfil',
            'force_change' => isset($_SESSION['representative_id'])
        ];
        
        view('profile/change-password', $data);
    }

    public function changePassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('change-password'));
        }
        
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        $errors = [];
        
        // Validações
        if (!isset($_SESSION['representative_id']) && empty($currentPassword)) {
            $errors[] = 'Senha atual é obrigatória';
        }
        
        if (empty($newPassword)) {
            $errors[] = 'Nova senha é obrigatória';
        } elseif (strlen($newPassword) < 6) {
            $errors[] = 'Nova senha deve ter pelo menos 6 caracteres';
        }
        
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'Confirmação de senha não confere';
        }
        
        if (!empty($errors)) {
            $_SESSION['validation_errors'] = $errors;
            redirect(url('change-password'));
        }
        
        try {
            if (Auth::isAdmin()) {
                $user = Auth::user();
                
                // Verificar senha atual
                if (!password_verify($currentPassword, $user['password'])) {
                    $_SESSION['error'] = 'Senha atual incorreta';
                    redirect(url('change-password'));
                }
                
                // Atualizar senha
                $this->userModel->updatePassword($user['id'], $newPassword);
                $_SESSION['success'] = 'Senha alterada com sucesso!';
                
            } else {
                // Se está forçando mudança de senha (primeiro acesso)
                if (isset($_SESSION['representative_id'])) {
                    $representativeId = $_SESSION['representative_id'];
                    
                    // Atualizar senha
                    $this->representativeModel->updatePassword($representativeId, $newPassword);
                    
                    // Remover flag de mudança obrigatória
                    $this->representativeModel->clearForcePasswordChange($representativeId);
                    
                    // Fazer login do representante
                    $representative = $this->representativeModel->findById($representativeId);
                    Auth::loginRepresentative([
                        'id' => $representative['id'],
                        'nome_completo' => $representative['nome_completo'],
                        'email' => $representative['email'],
                        'type' => 'representative'
                    ]);
                    
                    // Limpar sessão temporária
                    unset($_SESSION['representative_id']);
                    unset($_SESSION['representative_name']);
                    unset($_SESSION['representative_email']);
                    
                    $_SESSION['success'] = 'Senha alterada com sucesso! Bem-vindo ao sistema!';
                    redirect(url('dashboard'));
                    
                } else {
                    // Mudança normal de senha
                    $representative = Auth::representative();
                    
                    // Verificar senha atual (apenas se não for primeiro acesso)
                    if (!isset($_SESSION['representative_id']) && !password_verify($currentPassword, $representative['password'])) {
                        $_SESSION['error'] = 'Senha atual incorreta';
                        redirect(url('change-password'));
                    }
                    
                    // Atualizar senha
                    $this->representativeModel->updatePassword($representative['id'], $newPassword);
                    $_SESSION['success'] = 'Senha alterada com sucesso!';
                }
            }
            
            redirect(url('perfil'));
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao alterar senha: ' . $e->getMessage();
            redirect(url('change-password'));
        }
    }

    private function validateAndSanitizeInput()
    {
        $errors = [];
        
        if (Auth::isAdmin()) {
            // Validações para admin
            $name = sanitize_input($_POST['name'] ?? '');
            if (empty($name)) {
                $errors[] = 'Nome é obrigatório';
            }
            
            $email = sanitize_input($_POST['email'] ?? '');
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email válido é obrigatório';
            } else {
                // Verificar se email já existe (exceto para o próprio usuário)
                $existingUser = $this->userModel->findByEmail($email);
                if ($existingUser && $existingUser['id'] != Auth::user()['id']) {
                    $errors[] = 'Este email já está sendo usado por outro usuário';
                }
            }
            
            $data = [
                'name' => $name,
                'email' => $email
            ];
            
        } else {
            // Validações para representante
            $nomeCompleto = sanitize_input($_POST['nome_completo'] ?? '');
            if (empty($nomeCompleto)) {
                $errors[] = 'Nome completo é obrigatório';
            }
            
            $email = sanitize_input($_POST['email'] ?? '');
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email válido é obrigatório';
            } else {
                // Verificar se email já existe (exceto para o próprio representante)
                $existingRepresentative = $this->representativeModel->findByEmail($email);
                if ($existingRepresentative && $existingRepresentative['id'] != Auth::representative()['id']) {
                    $errors[] = 'Este email já está sendo usado por outro representante';
                }
            }
            
            $data = [
                'nome_completo' => $nomeCompleto,
                'email' => $email,
                'telefone' => sanitize_input($_POST['telefone'] ?? ''),
                'cep' => sanitize_input($_POST['cep'] ?? ''),
                'logradouro' => sanitize_input($_POST['logradouro'] ?? ''),
                'numero' => sanitize_input($_POST['numero'] ?? ''),
                'complemento' => sanitize_input($_POST['complemento'] ?? ''),
                'bairro' => sanitize_input($_POST['bairro'] ?? ''),
                'cidade' => sanitize_input($_POST['cidade'] ?? ''),
                'uf' => sanitize_input($_POST['uf'] ?? '')
            ];
        }
        
        if (!empty($errors)) {
            $_SESSION['validation_errors'] = $errors;
            return [];
        }
        
        return $data;
    }
}

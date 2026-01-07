<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\User;
use App\Core\Mailer;

class UserController
{
    private $userModel;
    private $mailer;

    public function __construct()
    {
        $this->userModel = new User();
        $this->mailer = new Mailer();
    }

    public function index()
    {
        Auth::requireAdmin();
        
        $filters = $this->getFilters();
        $users = $this->userModel->getAll($filters);
        $stats = $this->userModel->getStats($filters);
        
        $data = [
            'title' => 'Usuários Administrativos',
            'currentPage' => 'usuarios',
            'users' => $users,
            'stats' => $stats,
            'filters' => $filters
        ];
        
        view('users/index', $data);
    }

    public function create()
    {
        Auth::requireAdmin();
        
        $data = [
            'title' => 'Novo Usuário',
            'currentPage' => 'usuarios'
        ];
        
        view('users/create', $data);
    }

    public function store()
    {
        Auth::requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('usuarios/create'));
        }
        
        $data = $this->validateAndSanitizeInput();
        
        if (isset($_SESSION['validation_errors'])) {
            redirect(url('usuarios/create'));
        }
        
        try {
            $userId = $this->userModel->create($data);
            
            // Enviar email de boas-vindas
            $this->mailer->sendWelcomeUser($data['email'], $data['name'], $data['password']);
            
            $_SESSION['success'] = 'Usuário cadastrado com sucesso!';
            redirect(url('usuarios'));
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao cadastrar usuário: ' . $e->getMessage();
            redirect(url('usuarios/create'));
        }
    }

    public function show($id)
    {
        Auth::requireAdmin();
        
        $user = $this->userModel->findById($id);
        
        if (!$user) {
            $_SESSION['error'] = 'Usuário não encontrado';
            redirect(url('usuarios'));
        }
        
        // Buscar atividades do usuário
        $activities = $this->userModel->getUserActivities($id);
        $stats = $this->userModel->getUserStats($id);
        
        $data = [
            'title' => 'Detalhes do Usuário',
            'currentPage' => 'usuarios',
            'user' => $user,
            'activities' => $activities,
            'stats' => $stats
        ];
        
        view('users/show', $data);
    }

    public function edit($id)
    {
        Auth::requireAdmin();
        
        $user = $this->userModel->findById($id);
        
        if (!$user) {
            $_SESSION['error'] = 'Usuário não encontrado';
            redirect(url('usuarios'));
        }
        
        // Se for POST, processar o formulário
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processEditForm($id, $user);
            return; // O processamento já redireciona ou mostra a view
        }
        
        $data = [
            'title' => 'Editar Usuário',
            'currentPage' => 'usuarios',
            'user' => $user
        ];
        
        view('users/edit', $data);
    }
    
    private function processEditForm($id, $user)
    {
        // Verificar CSRF token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!csrf_verify($csrfToken)) {
            $_SESSION['error'] = 'Token de segurança inválido. Recarregue a página e tente novamente.';
            redirect(url('usuarios/' . $id . '/edit'));
        }
        
        // Validar dados
        $name = sanitize_input($_POST['name'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $status = sanitize_input($_POST['status'] ?? 'ACTIVE');
        
        $errors = [];
        
        // Validações
        if (empty($name)) {
            $errors[] = 'Nome é obrigatório';
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email válido é obrigatório';
        }
        
        if (!empty($password) && strlen($password) < 6) {
            $errors[] = 'Senha deve ter pelo menos 6 caracteres';
        }
        
        // Verificar se email já existe (exceto para o próprio usuário)
        if (!empty($email)) {
            $existingUser = $this->userModel->findByEmail($email);
            if ($existingUser && $existingUser['id'] != $id) {
                $errors[] = 'Este email já está sendo usado por outro usuário';
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['validation_errors'] = $errors;
            redirect(url('usuarios/' . $id . '/edit'));
        }
        
        // Se não há erros, tentar atualizar
        try {
            $data = [
                'name' => $name,
                'email' => $email,
                'status' => $status
            ];
            
            if (!empty($password)) {
                $data['password'] = password_hash($password, PASSWORD_DEFAULT);
            }
            
            $result = $this->userModel->update($id, $data);
            
            if ($result) {
                // Verificar mudanças
                $changes = [];
                if ($name !== ($_POST['original_name'] ?? '')) {
                    $changes[] = "Nome alterado";
                }
                if ($email !== ($_POST['original_email'] ?? '')) {
                    $changes[] = "Email alterado";
                }
                if ($status !== ($_POST['original_status'] ?? '')) {
                    $changes[] = "Status alterado";
                }
                if (!empty($password)) {
                    $changes[] = "Senha alterada";
                }
                
                if (empty($changes)) {
                    $_SESSION['success'] = 'Usuário atualizado com sucesso! (Nenhuma alteração foi necessária)';
                } else {
                    $_SESSION['success'] = 'Usuário atualizado com sucesso! Alterações: ' . implode(', ', $changes);
                }
                
                redirect(url('usuarios/' . $id));
            } else {
                $_SESSION['error'] = 'Falha ao atualizar usuário. Tente novamente.';
                redirect(url('usuarios/' . $id . '/edit'));
            }
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar usuário: ' . $e->getMessage();
            redirect(url('usuarios/' . $id . '/edit'));
        }
    }

    public function update($id)
    {
        Auth::requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método de requisição inválido. Tente novamente.';
            redirect(url('usuarios/' . $id . '/edit'));
        }
        
        // Verificar CSRF token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!csrf_verify($csrfToken)) {
            $_SESSION['error'] = 'Token de segurança inválido. Recarregue a página e tente novamente.';
            redirect(url('usuarios/' . $id . '/edit'));
        }
        
        $user = $this->userModel->findById($id);
        
        if (!$user) {
            $_SESSION['error'] = 'Usuário não encontrado. Ele pode ter sido removido.';
            redirect(url('usuarios'));
        }
        
        $data = $this->validateAndSanitizeInput($id);
        
        if (isset($_SESSION['validation_errors'])) {
            redirect(url('usuarios/' . $id . '/edit'));
        }
        
        try {
            $result = $this->userModel->update($id, $data);
            
            if (!$result) {
                throw new \Exception('A operação de atualização não foi executada. Verifique os dados e tente novamente.');
            }
            
            // Verificar se houve mudanças reais
            $updatedUser = $this->userModel->findById($id);
            $changes = [];
            
            if ($updatedUser['name'] !== $user['name']) {
                $changes[] = "Nome alterado de '{$user['name']}' para '{$updatedUser['name']}'";
            }
            if ($updatedUser['email'] !== $user['email']) {
                $changes[] = "Email alterado de '{$user['email']}' para '{$updatedUser['email']}'";
            }
            if ($updatedUser['status'] !== $user['status']) {
                $statusMap = ['ACTIVE' => 'Ativo', 'INACTIVE' => 'Inativo', 'BLOCKED' => 'Bloqueado'];
                $changes[] = "Status alterado de '{$statusMap[$user['status']]}' para '{$statusMap[$updatedUser['status']]}'";
            }
            if (isset($data['password'])) {
                $changes[] = "Senha alterada";
            }
            
            if (empty($changes)) {
                $_SESSION['success'] = 'Usuário atualizado com sucesso! (Nenhuma alteração foi necessária)';
            } else {
                $_SESSION['success'] = 'Usuário atualizado com sucesso! Alterações: ' . implode(', ', $changes);
            }
            
            redirect(url('usuarios/' . $id));
            
        } catch (\PDOException $e) {
            // Erro específico do banco de dados
            $errorMessage = 'Erro no banco de dados: ';
            
            switch ($e->getCode()) {
                case 23000: // Integrity constraint violation
                    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                        $errorMessage .= 'Email já está sendo usado por outro usuário.';
                    } elseif (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                        $errorMessage .= 'Erro de referência - dados relacionados não encontrados.';
                    } else {
                        $errorMessage .= 'Violação de integridade dos dados.';
                    }
                    break;
                case '42S22': // Column not found
                    $errorMessage .= 'Campo não encontrado na tabela.';
                    break;
                case '42S02': // Table doesn't exist
                    $errorMessage .= 'Tabela não encontrada no banco de dados.';
                    break;
                default:
                    $errorMessage .= $e->getMessage();
            }
            
            $_SESSION['error'] = $errorMessage;
            redirect(url('usuarios/' . $id . '/edit'));
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar usuário: ' . $e->getMessage();
            redirect(url('usuarios/' . $id . '/edit'));
        }
    }

    public function destroy($id)
    {
        Auth::requireAdmin();
        
        $user = $this->userModel->findById($id);
        
        if (!$user) {
            $_SESSION['error'] = 'Usuário não encontrado';
            redirect(url('usuarios'));
        }
        
        // Não permitir excluir o próprio usuário
        if ($id == Auth::user()['id']) {
            $_SESSION['error'] = 'Você não pode excluir sua própria conta';
            redirect(url('usuarios'));
        }
        
        try {
            $this->userModel->delete($id);
            $_SESSION['success'] = 'Usuário excluído com sucesso!';
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao excluir usuário: ' . $e->getMessage();
        }
        
        redirect(url('usuarios'));
    }

    public function resetPassword($id)
    {
        Auth::requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método de requisição inválido';
            redirect(url('usuarios'));
        }
        
        $user = $this->userModel->findById($id);
        
        if (!$user) {
            $_SESSION['error'] = 'Usuário não encontrado';
            redirect(url('usuarios'));
        }
        
        // Validar dados do formulário
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        
        $errors = [];
        
        if (empty($password)) {
            $errors[] = 'Senha é obrigatória';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Senha deve ter pelo menos 6 caracteres';
        }
        
        if (empty($passwordConfirm)) {
            $errors[] = 'Confirmação de senha é obrigatória';
        }
        
        if ($password !== $passwordConfirm) {
            $errors[] = 'As senhas não coincidem';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = 'Erros encontrados: ' . implode(', ', $errors);
            redirect(url('usuarios/' . $id));
        }
        
        try {
            // Atualizar senha
            $this->userModel->updatePassword($id, $password);
            
            // Tentar enviar email (opcional)
            try {
                $this->mailer->sendWelcomeUser($user['email'], $user['name'], $password);
                $_SESSION['success'] = 'Senha alterada com sucesso e enviada por email!';
            } catch (\Exception $emailError) {
                // Se falhar o email, ainda assim mostrar sucesso
                $_SESSION['success'] = 'Senha alterada com sucesso! (Email não enviado - verifique configurações)';
            }
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao alterar senha: ' . $e->getMessage();
        }
        
        redirect(url('usuarios/' . $id));
    }

    public function toggleStatus($id)
    {
        Auth::requireAdmin();
        
        $user = $this->userModel->findById($id);
        
        if (!$user) {
            $_SESSION['error'] = 'Usuário não encontrado';
            redirect(url('usuarios'));
        }
        
        // Não permitir desativar o próprio usuário
        if ($id == Auth::user()['id']) {
            $_SESSION['error'] = 'Você não pode desativar sua própria conta';
            redirect(url('usuarios'));
        }
        
        try {
            $newStatus = $user['status'] === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
            $this->userModel->updateStatus($id, $newStatus);
            
            $statusText = $newStatus === 'ACTIVE' ? 'ativado' : 'desativado';
            $_SESSION['success'] = "Usuário {$statusText} com sucesso!";
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao alterar status: ' . $e->getMessage();
        }
        
        redirect(url('usuarios/' . $id));
    }

    private function getFilters()
    {
        return [
            'status' => sanitize_input($_GET['status'] ?? ''),
            'search' => sanitize_input($_GET['search'] ?? ''),
            'date_from' => sanitize_input($_GET['date_from'] ?? ''),
            'date_to' => sanitize_input($_GET['date_to'] ?? '')
        ];
    }

    private function validateAndSanitizeInput($id = null)
    {
        $errors = [];
        
        // Nome
        $name = sanitize_input($_POST['name'] ?? '');
        if (empty($name)) {
            $errors[] = 'Nome é obrigatório';
        }
        
        // Email
        $email = sanitize_input($_POST['email'] ?? '');
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email válido é obrigatório';
        } else {
            // Verificar se email já existe (exceto para o próprio usuário sendo editado)
            $existingUser = $this->userModel->findByEmail($email);
            if ($existingUser && (!$id || $existingUser['id'] != $id)) {
                $errors[] = 'Este email já está sendo usado por outro usuário';
            }
        }
        
        // Senha (obrigatória apenas para novos usuários)
        $password = $_POST['password'] ?? '';
        if (!$id && empty($password)) {
            $errors[] = 'Senha é obrigatória';
        } elseif ($password && strlen($password) < 6) {
            $errors[] = 'Senha deve ter pelo menos 6 caracteres';
        }
        
        // Perfil (opcional)
        $profile = sanitize_input($_POST['profile'] ?? '');
        
        if (!empty($errors)) {
            $_SESSION['validation_errors'] = $errors;
            return [];
        }
        
        // Preparar dados para inserção/atualização
        $data = [
            'name' => $name,
            'email' => $email,
            'status' => sanitize_input($_POST['status'] ?? 'ACTIVE')
        ];
        
        // Adicionar senha apenas se fornecida
        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }
        
        return $data;
    }
}

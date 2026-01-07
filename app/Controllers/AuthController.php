<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Mailer;
use App\Models\User;
use App\Models\Representative;
use App\Models\PasswordReset;

class AuthController
{
    private $userModel;
    private $representativeModel;
    private $mailer;
    private $passwordResetModel;
    
    public function __construct()
    {
        $this->userModel = new User();
        $this->representativeModel = new Representative();
        $this->passwordResetModel = new PasswordReset();
        // Mailer será instanciado apenas quando necessário
    }
    
    /**
     * Obtém instância do Mailer (lazy loading)
     */
    private function getMailer()
    {
        if ($this->mailer === null) {
            $this->mailer = new Mailer();
        }
        return $this->mailer;
    }
    
    public function showLogin()
    {
        // Verificar se está autenticado E se a sessão é válida (admin ou representante)
        if (Auth::check() && (Auth::isAdmin() || Auth::isRepresentative())) {
            redirect(url('dashboard'));
        }
        
        // Se a sessão está corrompida (autenticado mas sem tipo válido), limpar
        if (Auth::check() && !Auth::isAdmin() && !Auth::isRepresentative()) {
            Auth::logout();
        }
        
        view('auth/login');
    }
    
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('login'));
        }
        
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Email e senha são obrigatórios';
            redirect(url('login'));
        }
        
        // Verificar se é usuário admin
        $user = $this->userModel->findByEmail($email);
        if ($user) {
            return $this->authenticateUser($user, $password);
        }
        
        // Verificar se é representante
        $representative = $this->representativeModel->findByEmail($email);
        if ($representative) {
            return $this->authenticateRepresentative($representative, $password);
        }
        
        $_SESSION['error'] = 'Credenciais inválidas';
        redirect(url('login'));
    }
    
    private function authenticateUser($user, $password)
    {
        // Debug temporário (remover em produção)
        if (defined('APP_DEBUG') && APP_DEBUG) {
            error_log('Tentativa de login - Email: ' . $user['email']);
            error_log('Hash no banco: ' . substr($user['password'], 0, 20) . '...');
            error_log('Verificação: ' . (password_verify($password, $user['password']) ? 'SUCESSO' : 'FALHA'));
        }
        
        // Verificar senha
        if (!password_verify($password, $user['password'])) {
            $this->userModel->incrementFailedAttempts($user['id']);
            
            if ($user['failed_attempts'] >= 4) {
                $this->userModel->block($user['id']);
                $_SESSION['error'] = 'Conta bloqueada devido a múltiplas tentativas de login';
            } else {
                $_SESSION['error'] = 'Senha incorreta';
            }
            
            redirect(url('login'));
        }
        
        // Reset tentativas e atualizar último login
        $this->userModel->resetFailedAttempts($user['id']);
        $this->userModel->updateLastLogin($user['id']);
        
        // Fazer login
        Auth::loginUser([
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'photo' => $user['photo'] ?? null,
            'type' => 'admin',
            'profile' => $user['profile'] ?? null // Campo pode não existir
        ]);
        
        Auth::regenerateSession();
        
        // Log de auditoria
        log_audit('LOGIN', 'AUTH', $user['id'], 'User');
        
        redirect(url('dashboard'));
    }
    
    private function authenticateRepresentative($representative, $password)
    {
        // Verificar senha
        if (!password_verify($password, $representative['password'])) {
            $this->representativeModel->incrementFailedAttempts($representative['id']);
            
            if ($representative['failed_attempts'] >= 4) {
                $this->representativeModel->block($representative['id']);
                $_SESSION['error'] = 'Conta bloqueada devido a múltiplas tentativas de login';
            } else {
                $_SESSION['error'] = 'Senha incorreta';
            }
            
            redirect(url('login'));
        }
        
        // Verificar se precisa alterar senha
        if ($representative['force_password_change']) {
            $_SESSION['representative_id'] = $representative['id'];
            $_SESSION['representative_name'] = $representative['nome_completo'];
            $_SESSION['representative_email'] = $representative['email'];
            redirect(url('change-password'));
        }
        
        // Reset tentativas e atualizar último login
        $this->representativeModel->resetFailedAttempts($representative['id']);
        $this->representativeModel->updateLastLogin($representative['id']);
        
        // Fazer login
        Auth::loginRepresentative([
            'id' => $representative['id'],
            'name' => $representative['nome_completo'],
            'email' => $representative['email'],
            'photo' => $representative['photo'] ?? null
        ]);
        
        Auth::regenerateSession();
        
        // Log de auditoria
        log_audit('LOGIN', 'AUTH', $representative['id'], 'Representative');
        
        redirect(url('dashboard'));
    }
    
    public function logout()
    {
        if (Auth::check()) {
            $user = Auth::user() ?: Auth::representative();
            log_audit('LOGOUT', 'AUTH', $user['id'], $user['type']);
        }
        
        Auth::logout();
        redirect(url('login'));
    }
    
    public function showForgotPassword()
    {
        if (Auth::check()) {
            redirect(url('dashboard'));
        }
        
        view('auth/forgot-password');
    }
    
    public function forgotPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('forgot-password'));
        }
        
        $email = sanitize_input($_POST['email'] ?? '');
        
        if (empty($email)) {
            $_SESSION['error'] = 'Email é obrigatório';
            redirect(url('forgot-password'));
        }
        
        // Verificar se email existe (usuário ou representante)
        $user = $this->userModel->findByEmail($email);
        $representative = $this->representativeModel->findByEmail($email);
        
        if (!$user && !$representative) {
            // Por segurança, não revelar se o email existe ou não
            $_SESSION['success'] = 'Se o email estiver cadastrado, você receberá um link de redefinição de senha.';
            redirect(url('login'));
        }
        
        // Determinar tipo de usuário
        $userType = $user ? 'user' : 'representative';
        $name = $user ? $user['name'] : $representative['nome_completo'];
        
        // Gerar e salvar token no banco
        try {
            $token = $this->passwordResetModel->createToken($email, $userType, 24); // Expira em 24 horas
            
            // Enviar email
            $this->getMailer()->sendPasswordReset($email, $name, $token);
            
            $_SESSION['success'] = 'Link de redefinição enviado para seu email. Verifique sua caixa de entrada.';
            redirect(url('login'));
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao enviar email: ' . $e->getMessage();
            redirect(url('forgot-password'));
        }
    }
    
    public function showResetPassword()
    {
        if (Auth::check()) {
            redirect(url('dashboard'));
        }
        
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            $_SESSION['error'] = 'Token inválido';
            redirect(url('login'));
        }
        
        view('auth/reset-password', ['token' => $token]);
    }
    
    public function resetPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('login'));
        }
        
        $token = sanitize_input($_POST['token'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        
        if (empty($token) || empty($password) || empty($passwordConfirm)) {
            $_SESSION['error'] = 'Todos os campos são obrigatórios';
            redirect(url('reset-password?token=' . $token));
        }
        
        if ($password !== $passwordConfirm) {
            $_SESSION['error'] = 'Senhas não coincidem';
            redirect(url('reset-password?token=' . $token));
        }
        
        if (strlen($password) < 6) {
            $_SESSION['error'] = 'Senha deve ter pelo menos 6 caracteres';
            redirect(url('reset-password?token=' . $token));
        }
        
        // Validar token
        $resetData = $this->passwordResetModel->validateToken($token);
        
        if (!$resetData) {
            $_SESSION['error'] = 'Token inválido ou expirado. Solicite um novo link de redefinição.';
            redirect(url('forgot-password'));
        }
        
        // Atualizar senha baseado no tipo de usuário
        try {
            if ($resetData['user_type'] === 'user') {
                $user = $this->userModel->findByEmail($resetData['email']);
                if ($user) {
                    $this->userModel->updatePassword($user['id'], $password);
                } else {
                    throw new \Exception('Usuário não encontrado');
                }
            } else {
                $representative = $this->representativeModel->findByEmail($resetData['email']);
                if ($representative) {
                    $this->representativeModel->updatePassword($representative['id'], $password);
                } else {
                    throw new \Exception('Representante não encontrado');
                }
            }
            
            // Marcar token como usado
            $this->passwordResetModel->markAsUsed($token);
            
            $_SESSION['success'] = 'Senha redefinida com sucesso! Faça login com sua nova senha.';
            redirect(url('login'));
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao redefinir senha: ' . $e->getMessage();
            redirect(url('reset-password?token=' . $token));
        }
    }
}

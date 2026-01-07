<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\EmailSettings;

class EmailSettingsController
{
    private $emailSettingsModel;
    
    public function __construct()
    {
        $this->emailSettingsModel = new EmailSettings();
    }
    
    public function index()
    {
        Auth::requireAdmin();
        
        $settings = $this->emailSettingsModel->getActiveSettings();
        
        $data = [
            'title' => 'Configurações de Email',
            'currentPage' => 'configuracoes',
            'settings' => $settings ?: [
                'mail_host' => 'smtp.gmail.com',
                'mail_port' => 587,
                'mail_user' => '',
                'mail_pass' => '',
                'mail_from' => '',
                'mail_name' => 'Sistema CRM',
                'mail_encryption' => 'tls'
            ]
        ];
        
        view('email-settings/index', $data);
    }
    
    public function update()
    {
        Auth::requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('email-settings'));
        }
        
        // Validar CSRF
        try {
            csrf_verify();
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Token de segurança inválido. Recarregue a página e tente novamente.';
            redirect(url('email-settings'));
        }
        
        $data = [
            'mail_host' => sanitize_input($_POST['mail_host'] ?? 'smtp.gmail.com'),
            'mail_port' => (int)($_POST['mail_port'] ?? 587),
            'mail_user' => sanitize_input($_POST['mail_user'] ?? ''),
            'mail_pass' => $_POST['mail_pass'] ?? '', // Não sanitizar senha
            'mail_from' => sanitize_input($_POST['mail_from'] ?? ''),
            'mail_name' => sanitize_input($_POST['mail_name'] ?? 'Sistema CRM'),
            'mail_encryption' => sanitize_input($_POST['mail_encryption'] ?? 'tls'),
            'is_active' => true
        ];
        
        // Validações
        $errors = [];
        
        if (empty($data['mail_host'])) {
            $errors[] = 'Servidor SMTP é obrigatório';
        }
        
        if (empty($data['mail_port']) || $data['mail_port'] < 1 || $data['mail_port'] > 65535) {
            $errors[] = 'Porta SMTP inválida';
        }
        
        if (empty($data['mail_user'])) {
            $errors[] = 'Usuário/Email SMTP é obrigatório';
        }
        
        if (empty($data['mail_from'])) {
            $errors[] = 'Email remetente é obrigatório';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode(', ', $errors);
            redirect(url('email-settings'));
        }
        
        try {
            $this->emailSettingsModel->save($data);
            $_SESSION['success'] = 'Configurações de email salvas com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao salvar configurações: ' . $e->getMessage();
        }
        
        redirect(url('email-settings'));
    }
    
    public function test()
    {
        Auth::requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('email-settings'));
        }
        
        // Validar CSRF
        try {
            csrf_verify();
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Token de segurança inválido. Recarregue a página e tente novamente.';
            redirect(url('email-settings'));
        }
        
        $testEmail = sanitize_input($_POST['test_email'] ?? '');
        
        if (empty($testEmail) || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Email de teste inválido';
            redirect(url('email-settings'));
        }
        
        try {
            $mailer = new \App\Core\Mailer();
            $mailer->send(
                $testEmail,
                'Teste de Configuração SMTP - Sistema CRM',
                '<h2>Teste de Email</h2><p>Se você recebeu este email, as configurações SMTP estão funcionando corretamente!</p>',
                'Teste de Email - Se você recebeu este email, as configurações SMTP estão funcionando corretamente!'
            );
            
            $_SESSION['success'] = 'Email de teste enviado com sucesso! Verifique a caixa de entrada de ' . $testEmail;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao enviar email de teste: ' . $e->getMessage();
        }
        
        redirect(url('email-settings'));
    }
}


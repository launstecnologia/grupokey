<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\SistPaySettings;
use App\Services\SistPayApi;

class SistPaySettingsController
{
    private $sistPaySettingsModel;
    
    public function __construct()
    {
        $this->sistPaySettingsModel = new SistPaySettings();
    }
    
    public function index()
    {
        Auth::requireAdmin();
        
        $settings = $this->sistPaySettingsModel->getActiveSettings();
        
        $data = [
            'title' => 'Configurações API SistPay',
            'currentPage' => 'configuracoes',
            'settings' => $settings ?: [
                'token' => '',
                'auth_method' => 'Authorization',
                'is_sandbox' => false,
                'is_active' => false,
                'base_url' => 'https://sistpay.com.br/api'
            ]
        ];
        
        view('sistpay-settings/index', $data);
    }
    
    public function update()
    {
        Auth::requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('sistpay-settings'));
        }
        
        // Validar CSRF
        try {
            csrf_verify();
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Token de segurança inválido. Recarregue a página e tente novamente.';
            redirect(url('sistpay-settings'));
        }
        
        $data = [
            'token' => sanitize_input($_POST['token'] ?? ''),
            'auth_method' => sanitize_input($_POST['auth_method'] ?? 'Authorization'),
            'is_sandbox' => !empty($_POST['is_sandbox']),
            'is_active' => !empty($_POST['is_active']),
            'base_url' => sanitize_input($_POST['base_url'] ?? 'https://sistpay.com.br/api')
        ];
        
        // Validar método de autenticação
        $validAuthMethods = ['Authorization', 'X-Authorization', 'X-Api-Token', 'X-Api-Key', 'Query-Param'];
        if (!in_array($data['auth_method'], $validAuthMethods)) {
            $data['auth_method'] = 'Authorization';
        }
        
        // Validações
        $errors = [];
        
        if (empty($data['token']) && $data['is_active']) {
            $errors[] = 'Token é obrigatório quando a API está ativa';
        }
        
        if (empty($data['base_url'])) {
            $errors[] = 'URL base é obrigatória';
        }
        
        if (!filter_var($data['base_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'URL base inválida';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode(', ', $errors);
            redirect(url('sistpay-settings'));
        }
        
        try {
            $this->sistPaySettingsModel->save($data);
            $_SESSION['success'] = 'Configurações da API SistPay salvas com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao salvar configurações: ' . $e->getMessage();
        }
        
        redirect(url('sistpay-settings'));
    }
    
    public function test()
    {
        Auth::requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('sistpay-settings'));
        }
        
        // Validar CSRF
        try {
            csrf_verify();
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Token de segurança inválido. Recarregue a página e tente novamente.';
            redirect(url('sistpay-settings'));
        }
        
        try {
            $api = new SistPayApi();
            
            if (!$api->isConfigured()) {
                $_SESSION['error'] = 'API não está configurada ou não está ativa. Configure o token e ative a API primeiro.';
                redirect(url('sistpay-settings'));
            }
            
            // Testar listagem de planos
            $plans = $api->getPlans();
            
            $_SESSION['success'] = 'Conexão com a API SistPay bem-sucedida! ' . count($plans) . ' plano(s) encontrado(s).';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao testar conexão: ' . $e->getMessage();
        }
        
        redirect(url('sistpay-settings'));
    }
}


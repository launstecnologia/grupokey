<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\AutoConfig;
use App\Models\WhatsAppInstance;
use App\Models\WhatsAppContact;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppQueue;
use App\Services\EvolutionApiService;

class WhatsAppController
{
    /**
     * Retorna a URL pública do webhook para a Evolution API.
     * Use APP_URL no config.env (ex: https://grupokey.com.br) para forçar URL acessível pela internet.
     */
    private function getWebhookUrl()
    {
        $appUrl = AutoConfig::get('APP_URL', '');
        if ($appUrl !== '' && $appUrl !== null) {
            $base = rtrim($appUrl, '/');
            $folder = defined('FOLDER') ? FOLDER : '';
            $path = ($folder !== '' && $folder !== '/') ? rtrim($folder, '/') . '/whatsapp/webhook' : 'whatsapp/webhook';
            return $base . '/' . ltrim($path, '/');
        }
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $port = '';
        if (isset($_SERVER['SERVER_PORT']) &&
            (($protocol === 'http' && $_SERVER['SERVER_PORT'] != 80) ||
             ($protocol === 'https' && $_SERVER['SERVER_PORT'] != 443))) {
            $port = ':' . $_SERVER['SERVER_PORT'];
        }
        $folder = defined('FOLDER') ? FOLDER : '';
        return $protocol . '://' . $host . $port . rtrim($folder, '/') . '/whatsapp/webhook';
    }

    private $instanceModel;
    private $contactModel;
    private $conversationModel;
    private $queueModel;
    
    public function __construct()
    {
        $this->instanceModel = new WhatsAppInstance();
        $this->contactModel = new WhatsAppContact();
        $this->conversationModel = new WhatsAppConversation();
        $this->queueModel = new WhatsAppQueue();
    }
    
    /**
     * Listar instâncias (sincroniza status com Evolution API para refletir CONNECTED)
     */
    public function instances()
    {
        Auth::requireAdmin();

        $instances = $this->instanceModel->getAll();

        foreach ($instances as $instance) {
            try {
                $apiService = new EvolutionApiService($instance);
                $status = $apiService->getStatus();
                $current = $instance['status'] ?? 'DISCONNECTED';
                if ($status === 'CONNECTED' && $current !== 'CONNECTED') {
                    $this->instanceModel->updateStatus($instance['id'], 'CONNECTED');
                    $this->instanceModel->updateQrCode($instance['id'], null);
                } elseif ($status !== 'CONNECTED' && $current === 'CONNECTED') {
                    $this->instanceModel->updateStatus($instance['id'], $status);
                }
            } catch (\Exception $e) {
                // Ignorar erro (API indisponível ou instância inexistente)
            }
        }

        $instances = $this->instanceModel->getAll();

        $data = [
            'title' => 'Instâncias WhatsApp',
            'currentPage' => 'whatsapp',
            'instances' => $instances
        ];

        view('whatsapp/instances', $data);
    }
    
    /**
     * Criar nova instância
     */
    public function createInstance()
    {
        Auth::requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'name' => $_POST['name'] ?? '',
                    'instance_key' => $_POST['instance_key'] ?? '',
                    'evolution_api_url' => $_POST['evolution_api_url'] ?? '',
                    'evolution_api_key' => $_POST['evolution_api_key'] ?? '',
                    'webhook_url' => $_POST['webhook_url'] ?? null,
                    'max_connections' => intval($_POST['max_connections'] ?? 10),
                    'is_active' => isset($_POST['is_active']),
                    'created_by_user_id' => $_SESSION['user_id'] ?? null
                ];
                
                // Validar campos obrigatórios
                if (empty($data['name']) || empty($data['instance_key']) || 
                    empty($data['evolution_api_url']) || empty($data['evolution_api_key'])) {
                    throw new \Exception('Preencha todos os campos obrigatórios');
                }
                
                // Criar instância no banco
                $instanceId = $this->instanceModel->create($data);
                
                // Criar instância na Evolution API
                $instance = $this->instanceModel->findById($instanceId);
                $apiService = new EvolutionApiService($instance);
                
                try {
                    $apiService->createInstance();
                    
                    // Configurar webhook automaticamente
                    // Se webhook_url foi fornecido, usar ele; senão, detectar automaticamente
                    if (!empty($data['webhook_url'])) {
                        $webhookUrl = rtrim($data['webhook_url'], '/') . (strpos($data['webhook_url'], '/whatsapp/webhook') !== false ? '' : '/whatsapp/webhook');
                    } else {
                        $webhookUrl = $this->getWebhookUrl();
                    }
                    
                    // Configurar webhook na Evolution API
                    try {
                        $apiService->setWebhook($webhookUrl);
                        write_log('Webhook configurado automaticamente: ' . $webhookUrl, 'whatsapp.log');
                        
                        // Atualizar webhook_url no banco
                        $this->instanceModel->update($instanceId, ['webhook_url' => $webhookUrl]);
                    } catch (\Exception $webhookError) {
                        write_log('Aviso: Não foi possível configurar webhook automaticamente: ' . $webhookError->getMessage(), 'whatsapp.log');
                        write_log('URL do webhook que seria configurada: ' . $webhookUrl, 'whatsapp.log');
                    }
                } catch (\Exception $e) {
                    // Log do erro mas não falhar a criação
                    write_log('Erro ao criar instância na Evolution API: ' . $e->getMessage(), 'whatsapp.log');
                }
                
                $_SESSION['success'] = 'Instância criada com sucesso!';
                redirect(url('whatsapp/instances'));
                
            } catch (\Exception $e) {
                $_SESSION['error'] = 'Erro ao criar instância: ' . $e->getMessage();
                redirect(url('whatsapp/instances/create'));
            }
        } else {
            $data = [
                'title' => 'Nova Instância WhatsApp',
                'currentPage' => 'whatsapp'
            ];
            
            view('whatsapp/create-instance', $data);
        }
    }
    
    /**
     * Conectar instância (gerar QR Code)
     */
    public function connect($id)
    {
        Auth::requireAdmin();
        
        try {
            $instance = $this->instanceModel->findById($id);
            
            if (!$instance) {
                throw new \Exception('Instância não encontrada');
            }
            
            $apiService = new EvolutionApiService($instance);
            
            write_log('Tentando conectar instância: ' . $instance['instance_key'], 'whatsapp.log');
            write_log('URL da API: ' . $instance['evolution_api_url'], 'whatsapp.log');
            
            // Tentar obter QR Code diretamente
            try {
                $qrCode = $apiService->getQrCode();
                
                if ($qrCode) {
                    $this->instanceModel->updateQrCode($id, $qrCode);
                    $this->instanceModel->updateStatus($id, 'CONNECTING');
                    write_log('QR Code obtido com sucesso', 'whatsapp.log');

                    // Configurar webhook na Evolution API se ainda não estiver configurado
                    if (empty($instance['webhook_url'])) {
                        $webhookUrl = $this->getWebhookUrl();
                        try {
                            $apiService->setWebhook($webhookUrl);
                            $this->instanceModel->update($id, ['webhook_url' => $webhookUrl]);
                            write_log('Webhook configurado ao conectar: ' . $webhookUrl, 'whatsapp.log');
                        } catch (\Exception $webhookError) {
                            write_log('Aviso: Não foi possível configurar webhook ao conectar: ' . $webhookError->getMessage(), 'whatsapp.log');
                        }
                    }
                } else {
                    // Se não obteve QR Code, tentar criar/recriar instância
                    write_log('QR Code não obtido, tentando criar/recriar instância', 'whatsapp.log');
                    try {
                        $apiService->createInstance(true);
                        sleep(2);
                        $qrCode = $apiService->getQrCode();
                        
                        if ($qrCode) {
                            $this->instanceModel->updateQrCode($id, $qrCode);
                            $this->instanceModel->updateStatus($id, 'CONNECTING');
                            write_log('Instância criada e QR Code obtido', 'whatsapp.log');
                        }
                    } catch (\Exception $e2) {
                        write_log('Erro ao criar instância: ' . $e2->getMessage(), 'whatsapp.log');
                        throw $e2;
                    }
                }
            } catch (\Exception $e) {
                write_log('Erro ao obter QR Code: ' . $e->getMessage(), 'whatsapp.log');
                write_log('Stack trace: ' . $e->getTraceAsString(), 'whatsapp.log');
                throw $e;
            }
            
            if ($qrCode) {
                $_SESSION['success'] = 'QR Code gerado! Escaneie com o WhatsApp.';
            } else {
                $_SESSION['warning'] = 'Instância criada, mas QR Code não foi gerado. Tente novamente.';
            }
            
            redirect(url('whatsapp/instances/' . $id));
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao conectar: ' . $e->getMessage();
            redirect(url('whatsapp/instances'));
        }
    }
    
    /**
     * Ver detalhes da instância
     */
    public function showInstance($id)
    {
        Auth::requireAdmin();
        
        $instance = $this->instanceModel->findById($id);
        
        if (!$instance) {
            $_SESSION['error'] = 'Instância não encontrada';
            redirect(url('whatsapp/instances'));
        }
        
        // Sincronizar status com a Evolution API (open -> CONNECTED)
        try {
            $apiService = new EvolutionApiService($instance);
            $status = $apiService->getStatus();
            $currentStatus = $instance['status'] ?? 'DISCONNECTED';
            if ($status === 'CONNECTED') {
                $this->instanceModel->updateStatus($id, $status);
                $this->instanceModel->updateQrCode($id, null);
            } elseif ($currentStatus === 'CONNECTED' && $status !== 'CONNECTED') {
                $this->instanceModel->updateStatus($id, $status);
            }
            $instance = $this->instanceModel->findById($id);
        } catch (\Exception $e) {
            // Ignorar erro
        }
        
        $data = [
            'title' => 'Detalhes da Instância',
            'currentPage' => 'whatsapp',
            'instance' => $instance,
            'webhook_url_esperada' => $this->getWebhookUrl()
        ];
        
        view('whatsapp/show-instance', $data);
    }
    
    /**
     * Desconectar instância
     */
    public function disconnect($id)
    {
        Auth::requireAdmin();
        
        try {
            $instance = $this->instanceModel->findById($id);
            
            if (!$instance) {
                throw new \Exception('Instância não encontrada');
            }
            
            $apiService = new EvolutionApiService($instance);
            $apiService->disconnect();
            
            $this->instanceModel->updateStatus($id, 'DISCONNECTED');
            $this->instanceModel->update($id, [
                'last_disconnection_at' => date('Y-m-d H:i:s')
            ]);
            
            $_SESSION['success'] = 'Instância desconectada com sucesso!';
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao desconectar: ' . $e->getMessage();
        }
        
        redirect(url('whatsapp/instances/' . $id));
    }

    /**
     * Checar/sincronizar status com a Evolution API (botão "Checar conexão")
     */
    public function checkStatus($id)
    {
        Auth::requireAdmin();

        try {
            $instance = $this->instanceModel->findById($id);
            if (!$instance) {
                throw new \Exception('Instância não encontrada');
            }

            $apiService = new EvolutionApiService($instance);
            $status = $apiService->getStatus();
            $current = $instance['status'] ?? 'DISCONNECTED';

            if ($status === 'CONNECTED') {
                $this->instanceModel->updateStatus($id, 'CONNECTED');
                $this->instanceModel->updateQrCode($id, null);
                $_SESSION['success'] = 'Status atualizado: conectado na Evolution API.';
            } elseif ($status !== 'CONNECTED' && $current === 'CONNECTED') {
                $this->instanceModel->updateStatus($id, $status);
                $_SESSION['warning'] = 'Status atualizado: desconectado na Evolution API.';
            } else {
                $_SESSION['success'] = 'Status verificado: ' . $status . ' na Evolution API.';
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao checar: ' . $e->getMessage();
        }

        redirect(url('whatsapp/instances/' . $id));
    }

    /**
     * Configurar webhook na Evolution API (quando estiver vazio)
     */
    public function setWebhook($id)
    {
        Auth::requireAdmin();

        try {
            $instance = $this->instanceModel->findById($id);
            if (!$instance) {
                throw new \Exception('Instância não encontrada');
            }

            $webhookUrl = $this->getWebhookUrl();

            $apiService = new EvolutionApiService($instance);
            $apiService->setWebhook($webhookUrl);
            $this->instanceModel->update($id, ['webhook_url' => $webhookUrl]);

            $_SESSION['success'] = 'Webhook configurado com sucesso: ' . $webhookUrl;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao configurar webhook: ' . $e->getMessage();
        }

        redirect(url('whatsapp/instances/' . $id));
    }

    /**
     * Excluir instância (Evolution API + banco)
     */
    public function deleteInstance($id)
    {
        Auth::requireAdmin();

        try {
            $instance = $this->instanceModel->findById($id);
            if (!$instance) {
                throw new \Exception('Instância não encontrada');
            }

            try {
                $apiService = new EvolutionApiService($instance);
                $apiService->deleteInstance();
            } catch (\Exception $e) {
                write_log('Aviso ao excluir na Evolution API: ' . $e->getMessage(), 'whatsapp.log');
            }

            $this->instanceModel->delete($id);
            $_SESSION['success'] = 'Instância excluída com sucesso.';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao excluir instância: ' . $e->getMessage();
        }

        redirect(url('whatsapp/instances'));
    }
}


<?php

namespace App\Services;

use App\Core\Database;

/**
 * Service para integração com Evolution API
 * Documentação: https://doc.evolution-api.com/
 */
class EvolutionApiService
{
    private $instance;
    private $apiUrl;
    private $apiKey;
    
    public function __construct($instance)
    {
        $this->instance = $instance;
        $this->apiUrl = rtrim($instance['evolution_api_url'], '/');
        $this->apiKey = $instance['evolution_api_key'];
    }
    
    /**
     * Fazer requisição à Evolution API (URL completa - para create que não usa key no path)
     */
    private function requestToUrl($method, $url, $data = null)
    {
        return $this->doRequest($method, $url, $data);
    }

    /**
     * Fazer requisição à Evolution API (endpoint relativo à instância: /instance/{key}{endpoint})
     */
    private function request($method, $endpoint, $data = null)
    {
        $url = $this->apiUrl . '/instance/' . $this->instance['instance_key'] . $endpoint;
        return $this->doRequest($method, $url, $data);
    }

    /**
     * Executa a requisição HTTP à Evolution API
     */
    private function doRequest($method, $url, $data = null)
    {
        
        // Log da requisição
        if (function_exists('write_log')) {
            write_log("Evolution API Request: {$method} {$url}", 'whatsapp.log');
            if ($data) {
                write_log("Request Data: " . json_encode($data), 'whatsapp.log');
            }
        }
        
        $ch = curl_init($url);
        
        $headers = [
            'Content-Type: application/json',
            'apikey: ' . $this->apiKey
        ];
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        // Log da resposta
        if (function_exists('write_log')) {
            write_log("Evolution API Response: HTTP {$httpCode}", 'whatsapp.log');
            if ($response) {
                write_log("Response Body: " . substr($response, 0, 500), 'whatsapp.log');
            }
        }
        
        if ($error) {
            if (function_exists('write_log')) {
                write_log("CURL Error: {$error}", 'whatsapp.log');
            }
            throw new \Exception("Erro na requisição: {$error}");
        }
        
        $decoded = json_decode($response, true);
        
        if ($httpCode >= 400) {
            $errorMsg = $decoded['message'] ?? ($decoded['error'] ?? ($decoded['response']['message'] ?? 'Erro desconhecido'));
            $errorDetails = isset($decoded['response']) ? json_encode($decoded['response']) : '';
            if (function_exists('write_log')) {
                write_log("API Error ({$httpCode}): {$errorMsg}", 'whatsapp.log');
            }
            throw new \Exception("Erro da API ({$httpCode}): {$errorMsg}" . ($errorDetails ? " - {$errorDetails}" : ''));
        }
        
        return $decoded;
    }
    
    /**
     * Criar instância na Evolution API (v2: POST /instance/create - key não vai no path)
     */
    public function createInstance($qrcode = true, $integration = 'WHATSAPP-BAILEYS')
    {
        $url = $this->apiUrl . '/instance/create';
        $data = [
            'instanceName' => $this->instance['instance_key'],
            'qrcode' => $qrcode,
            'integration' => $integration
        ];
        return $this->requestToUrl('POST', $url, $data);
    }
    
    /**
     * Conectar instância (gerar QR Code)
     * Na Evolution API, o QR Code é obtido via endpoint /qrcode
     * Se a instância não existir, cria ela primeiro
     */
    public function connect()
    {
        // Primeiro, verificar se a instância existe
        try {
            $info = $this->getInstanceInfo();
            if (!$info) {
                // Instância não existe, criar
                write_log('Instância não existe, criando...', 'whatsapp.log');
                $this->createInstance(true);
                sleep(2); // Aguardar criação
            }
        } catch (\Exception $e) {
            // Se falhar ao verificar, tentar criar mesmo assim
            write_log('Erro ao verificar instância, tentando criar: ' . $e->getMessage(), 'whatsapp.log');
            try {
                $this->createInstance(true);
                sleep(2);
            } catch (\Exception $e2) {
                // Ignorar se já existe
                if (strpos($e2->getMessage(), 'already exists') === false) {
                    throw $e2;
                }
            }
        }
        
        // Agora obter QR Code
        return $this->getQrCode();
    }
    
    /**
     * Obter QR Code (Evolution API v2: GET /instance/{key}/connect retorna pairCode/base64)
     */
    public function getQrCode()
    {
        $endpoint = '/connect';
        try {
            $response = $this->request('GET', $endpoint);
            return $this->extractQrCodeFromResponse($response);
        } catch (\Exception $e) {
            if (function_exists('write_log')) {
                write_log('Erro ao obter QR Code do endpoint /connect: ' . $e->getMessage(), 'whatsapp.log');
            }
            if (strpos($e->getMessage(), '404') !== false) {
                if (function_exists('write_log')) {
                    write_log('Endpoint /connect retornou 404, tentando criar instância primeiro', 'whatsapp.log');
                }
                try {
                    $this->createInstance(true);
                    sleep(2);
                    $response = $this->request('GET', $endpoint);
                    return $this->extractQrCodeFromResponse($response);
                } catch (\Exception $e2) {
                    throw new \Exception("Erro ao obter QR Code: " . $e->getMessage() . " | " . $e2->getMessage());
                }
            }
            throw $e;
        }
    }

    /**
     * Extrai o QR Code (base64 ou pairCode) da resposta da Evolution API
     */
    private function extractQrCodeFromResponse($response)
    {
        if (isset($response['base64'])) {
            return $response['base64'];
        }
        if (isset($response['pairCode'])) {
            return $response['pairCode'];
        }
        if (isset($response['qrcode']['base64'])) {
            return $response['qrcode']['base64'];
        }
        if (isset($response['qrcode'])) {
            if (is_string($response['qrcode'])) {
                return $response['qrcode'];
            }
            if (is_array($response['qrcode']) && isset($response['qrcode']['base64'])) {
                return $response['qrcode']['base64'];
            }
        }
        if (isset($response['code'])) {
            return $response['code'];
        }
        if (isset($response['result']) && is_string($response['result'])) {
            return $response['result'];
        }
        if (function_exists('write_log')) {
            write_log('QR Code não encontrado na resposta: ' . json_encode($response), 'whatsapp.log');
        }
        return null;
    }
    
    /**
     * Obter status da conexão
     */
    public function getStatus()
    {
        $endpoint = '/connectionState';
        $response = $this->request('GET', $endpoint);
        return $response['state'] ?? 'DISCONNECTED';
    }
    
    /**
     * Obter informações da instância
     */
    public function getInstanceInfo()
    {
        // Tentar endpoint /fetchInstances (pode não existir em todas as versões)
        try {
            $endpoint = '/fetchInstances';
            $response = $this->request('GET', $endpoint);
            
            // Se a resposta é um array, buscar nossa instância
            if (is_array($response)) {
                foreach ($response as $instance) {
                    if (isset($instance['instance']['instanceName']) && 
                        $instance['instance']['instanceName'] === $this->instance['instance_key']) {
                        return $instance;
                    }
                }
            }
            
            return $response;
        } catch (\Exception $e) {
            // Se o endpoint não existir, tentar endpoint alternativo
            if (strpos($e->getMessage(), '404') !== false) {
                try {
                    // Tentar endpoint /fetchInstance (singular)
                    $endpoint = '/fetchInstance';
                    return $this->request('GET', $endpoint);
                } catch (\Exception $e2) {
                    // Se também não existir, retornar null (instância pode não existir)
                    return null;
                }
            }
            throw $e;
        }
    }
    
    /**
     * Desconectar instância
     */
    public function disconnect()
    {
        $endpoint = '/logout';
        return $this->request('DELETE', $endpoint);
    }
    
    /**
     * Deletar instância
     */
    public function deleteInstance()
    {
        $endpoint = '/delete';
        return $this->request('DELETE', $endpoint);
    }
    
    /**
     * Enviar mensagem de texto
     */
    public function sendText($number, $text, $quotedMessageId = null)
    {
        $endpoint = '/sendText';
        
        $data = [
            'number' => $number,
            'text' => $text
        ];
        
        if ($quotedMessageId) {
            $data['quoted'] = $quotedMessageId;
        }
        
        return $this->request('POST', $endpoint, $data);
    }
    
    /**
     * Enviar mídia (imagem, vídeo, áudio, documento)
     */
    public function sendMedia($number, $mediaUrl, $type = 'image', $caption = null, $fileName = null)
    {
        $endpoint = '/sendMedia';
        
        $data = [
            'number' => $number,
            'mediatype' => $type,
            'media' => $mediaUrl
        ];
        
        if ($caption) {
            $data['caption'] = $caption;
        }
        
        if ($fileName) {
            $data['fileName'] = $fileName;
        }
        
        return $this->request('POST', $endpoint, $data);
    }
    
    /**
     * Marcar mensagens como lidas
     */
    public function markAsRead($number, $messageIds = [])
    {
        $endpoint = '/chat/markMessageAsRead';
        
        $data = [
            'number' => $number,
            'readMessages' => $messageIds
        ];
        
        return $this->request('PUT', $endpoint, $data);
    }
    
    /**
     * Configurar webhook
     */
    public function setWebhook($webhookUrl, $events = ['MESSAGES_UPSERT', 'CONNECTION_UPDATE', 'QRCODE_UPDATED'])
    {
        $endpoint = '/webhook/set';
        
        $data = [
            'url' => $webhookUrl,
            'webhook_by_events' => true,
            'events' => $events,
            'webhook_base64' => false
        ];
        
        return $this->request('POST', $endpoint, $data);
    }
    
    /**
     * Obter contatos
     */
    public function getContacts()
    {
        $endpoint = '/chat/fetchContacts';
        return $this->request('GET', $endpoint);
    }
    
    /**
     * Obter chats
     */
    public function getChats()
    {
        $endpoint = '/chat/fetchChats';
        return $this->request('GET', $endpoint);
    }
    
    /**
     * Obter mensagens de um chat
     */
    public function getMessages($number, $limit = 50)
    {
        $endpoint = '/chat/fetchMessages';
        
        $data = [
            'number' => $number,
            'limit' => $limit
        ];
        
        return $this->request('POST', $endpoint, $data);
    }
}


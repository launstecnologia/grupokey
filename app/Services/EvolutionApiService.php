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
     * Evolution API v2.3 (Postman): /instance/connect/{{instance}}, /instance/create, /message/sendText/{{instance}}, etc.
     * Monta URL a partir dos segmentos do path (ex: ['instance','connect','key-teste']).
     */
    private function requestByPath($method, array $pathSegments, $data = null)
    {
        $path = implode('/', array_map('trim', $pathSegments));
        $url = $this->apiUrl . '/' . $path;
        return $this->doRequest($method, $url, $data);
    }

    /** Helper: path com instance key no final (ex: instance/connect/key-teste) */
    private function requestInstanceAction($method, $action, $data = null)
    {
        return $this->requestByPath($method, ['instance', $action, $this->instance['instance_key']], $data);
    }

    /** Helper: path message/action/instance ou chat/action/instance */
    private function requestResourceAction($method, $resource, $action, $data = null)
    {
        return $this->requestByPath($method, [$resource, $action, $this->instance['instance_key']], $data);
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
     * Criar instância na Evolution API v2.3: POST /instance/create (Postman)
     */
    public function createInstance($qrcode = true, $integration = 'WHATSAPP-BAILEYS')
    {
        $data = [
            'instanceName' => $this->instance['instance_key'],
            'qrcode' => $qrcode,
            'integration' => $integration
        ];
        return $this->requestByPath('POST', ['instance', 'create'], $data);
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
                // Ignorar se já existe ou nome já em uso (403)
                $msg = $e2->getMessage();
                if (strpos($msg, 'already exists') === false && strpos($msg, 'already in use') === false) {
                    throw $e2;
                }
            }
        }
        
        // Agora obter QR Code
        return $this->getQrCode();
    }
    
    /**
     * Obter QR Code (Evolution API v2.3 Postman: GET /instance/connect/{{instance}} retorna base64)
     */
    public function getQrCode()
    {
        try {
            $response = $this->requestInstanceAction('GET', 'connect');
            $value = $this->extractQrCodeFromResponse($response);
            if ($value && function_exists('write_log')) {
                $preview = is_string($value) ? (strlen($value) . ' chars, starts: ' . substr($value, 0, 30)) : 'non-string';
                write_log('QR Code extraído: ' . $preview, 'whatsapp.log');
            }
            return $value;
        } catch (\Exception $e) {
            if (function_exists('write_log')) {
                write_log('Erro ao obter QR Code do endpoint connect: ' . $e->getMessage(), 'whatsapp.log');
            }
            if (strpos($e->getMessage(), '404') !== false) {
                if (function_exists('write_log')) {
                    write_log('Endpoint connect retornou 404, tentando criar instância primeiro', 'whatsapp.log');
                }
                try {
                    $this->createInstance(true);
                    sleep(2);
                    $response = $this->requestInstanceAction('GET', 'connect');
                    return $this->extractQrCodeFromResponse($response);
                } catch (\Exception $e2) {
                    if (strpos($e2->getMessage(), '403') !== false && strpos($e2->getMessage(), 'already in use') !== false) {
                        if (function_exists('write_log')) {
                            write_log('Instância já existe na API, obtendo QR Code novamente', 'whatsapp.log');
                        }
                        sleep(1);
                        $response = $this->requestInstanceAction('GET', 'connect');
                        return $this->extractQrCodeFromResponse($response);
                    }
                    throw new \Exception("Erro ao obter QR Code: " . $e->getMessage() . " | " . $e2->getMessage());
                }
            }
            throw $e;
        }
    }

    /**
     * Extrai o QR Code (base64 ou pairCode) da resposta da Evolution API
     * Normaliza base64 (remove quebras de linha) para exibição em data URL
     */
    private function extractQrCodeFromResponse($response)
    {
        $raw = null;
        if (isset($response['base64'])) {
            $raw = $response['base64'];
        } elseif (isset($response['pairCode'])) {
            $raw = $response['pairCode'];
        } elseif (isset($response['qrcode']['base64'])) {
            $raw = $response['qrcode']['base64'];
        } elseif (isset($response['qrcode'])) {
            $raw = is_string($response['qrcode']) ? $response['qrcode'] : ($response['qrcode']['base64'] ?? null);
        } elseif (isset($response['result']['base64'])) {
            $raw = $response['result']['base64'];
        } elseif (isset($response['result']) && is_string($response['result'])) {
            $raw = $response['result'];
        } elseif (isset($response['code'])) {
            $raw = $response['code'];
        }
        if ($raw === null) {
            if (function_exists('write_log')) {
                write_log('QR Code não encontrado na resposta: ' . json_encode($response), 'whatsapp.log');
            }
            return null;
        }
        return $this->normalizeQrCodeValue($raw);
    }

    /**
     * Normaliza valor do QR: remove espaços/quebras do base64; mantém data URL ou pairCode como está
     */
    private function normalizeQrCodeValue($value)
    {
        if (!is_string($value)) {
            return $value;
        }
        $trimmed = trim($value);
        if (strpos($trimmed, 'data:image') === 0) {
            return $trimmed;
        }
        if (preg_match('/^[A-Za-z0-9+\/=]+$/s', $trimmed) || strpos($trimmed, "\n") !== false || strpos($trimmed, "\r") !== false) {
            return preg_replace('/\s+/', '', $trimmed);
        }
        return $trimmed;
    }
    
    /**
     * Obter status da conexão (v2.3: GET /instance/connectionState/{{instance}})
     * Evolution API pode retornar state em response direto ou aninhado (instance, data, result)
     */
    public function getStatus()
    {
        $response = $this->requestInstanceAction('GET', 'connectionState');
        $state = null;
        if (isset($response['state'])) {
            $state = $response['state'];
        } elseif (isset($response['instance']['state'])) {
            $state = $response['instance']['state'];
        } elseif (isset($response['instance']['status'])) {
            $state = $response['instance']['status'];
        } elseif (isset($response['data']['state'])) {
            $state = $response['data']['state'];
        } elseif (isset($response['result']['state'])) {
            $state = $response['result']['state'];
        }
        $state = $state ?? 'DISCONNECTED';
        $normalized = $this->normalizeConnectionState($state);
        if ($normalized === 'DISCONNECTED' && function_exists('write_log')) {
            $keys = is_array($response) ? array_keys($response) : [];
            write_log('getStatus DISCONNECTED - instance_key=' . ($this->instance['instance_key'] ?? '') . ', response_keys=' . json_encode($keys), 'whatsapp.log');
        }
        return $normalized;
    }

    /**
     * Normaliza estado da API para CONNECTED / CONNECTING / DISCONNECTED
     */
    private function normalizeConnectionState($state)
    {
        $s = strtolower(trim((string) $state));
        if ($s === 'open' || $s === 'connected') {
            return 'CONNECTED';
        }
        if ($s === 'connecting' || $s === 'opening') {
            return 'CONNECTING';
        }
        return 'DISCONNECTED';
    }
    
    /**
     * Obter informações da instância (v2.3: GET /instance/fetchInstances)
     */
    public function getInstanceInfo()
    {
        try {
            $response = $this->requestByPath('GET', ['instance', 'fetchInstances']);
            if (is_array($response)) {
                foreach ($response as $item) {
                    if (isset($item['instance']['instanceName']) &&
                        $item['instance']['instanceName'] === $this->instance['instance_key']) {
                        return $item;
                    }
                }
            }
            return $response;
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), '404') !== false) {
                return null;
            }
            throw $e;
        }
    }
    
    /**
     * Desconectar instância (v2.3: DELETE /instance/logout/{{instance}})
     */
    public function disconnect()
    {
        return $this->requestInstanceAction('DELETE', 'logout');
    }

    /**
     * Deletar instância (v2.3: DELETE /instance/delete/{{instance}})
     */
    public function deleteInstance()
    {
        return $this->requestInstanceAction('DELETE', 'delete');
    }
    
    /**
     * Enviar mensagem de texto (v2.3: POST /message/sendText/{{instance}})
     */
    public function sendText($number, $text, $quotedMessageId = null)
    {
        $data = ['number' => $number, 'text' => $text];
        if ($quotedMessageId) {
            $data['quoted'] = $quotedMessageId;
        }
        return $this->requestResourceAction('POST', 'message', 'sendText', $data);
    }

    /**
     * Enviar mídia (v2.3: POST /message/sendMedia/{{instance}})
     */
    public function sendMedia($number, $mediaUrl, $type = 'image', $caption = null, $fileName = null)
    {
        $data = ['number' => $number, 'mediatype' => $type, 'media' => $mediaUrl];
        if ($caption) $data['caption'] = $caption;
        if ($fileName) $data['fileName'] = $fileName;
        return $this->requestResourceAction('POST', 'message', 'sendMedia', $data);
    }

    /**
     * Marcar mensagens como lidas (v2.3: POST /chat/markMessageAsRead/{{instance}})
     */
    public function markAsRead($number, $messageIds = [])
    {
        $data = ['number' => $number, 'readMessages' => $messageIds];
        return $this->requestResourceAction('POST', 'chat', 'markMessageAsRead', $data);
    }

    /**
     * Configurar webhook (v2.3: POST /webhook/set/{{instance}})
     * Evolution API espera objeto "webhook" com enabled: true (Postman collection)
     */
    public function setWebhook($webhookUrl, $events = ['MESSAGES_UPSERT', 'MESSAGES_UPDATE', 'CONNECTION_UPDATE', 'QRCODE_UPDATED'])
    {
        $data = [
            'webhook' => [
                'enabled' => true,
                'url' => $webhookUrl,
                'byEvents' => false,
                'base64' => false,
                'events' => $events
            ]
        ];
        return $this->requestResourceAction('POST', 'webhook', 'set', $data);
    }

    /**
     * Consultar webhook configurado na Evolution API (GET /webhook/find/{{instance}})
     */
    public function getWebhook()
    {
        try {
            return $this->requestByPath('GET', ['webhook', 'find', $this->instance['instance_key']]);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Obter contatos (v2.3: POST /chat/findContacts/{{instance}})
     */
    public function getContacts()
    {
        return $this->requestResourceAction('POST', 'chat', 'findContacts');
    }

    /**
     * Obter chats (v2.3: POST /chat/findChats/{{instance}})
     */
    public function getChats()
    {
        return $this->requestResourceAction('POST', 'chat', 'findChats');
    }

    /**
     * Obter mensagens de um chat (v2.3: POST /chat/findMessages/{{instance}})
     */
    public function getMessages($number, $limit = 50)
    {
        $data = ['number' => $number, 'limit' => $limit];
        return $this->requestResourceAction('POST', 'chat', 'findMessages', $data);
    }
}


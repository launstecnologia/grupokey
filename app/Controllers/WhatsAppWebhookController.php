<?php

namespace App\Controllers;

use App\Models\WhatsAppInstance;
use App\Models\WhatsAppContact;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppQueue;
use App\Models\WhatsAppAttendance;

/**
 * Controller para receber webhooks da Evolution API
 * Este controller não requer autenticação, pois é chamado pela Evolution API
 */
class WhatsAppWebhookController
{
    private $instanceModel;
    private $contactModel;
    private $conversationModel;
    private $messageModel;
    private $queueModel;
    private $attendanceModel;
    
    public function __construct()
    {
        $this->instanceModel = new WhatsAppInstance();
        $this->contactModel = new WhatsAppContact();
        $this->conversationModel = new WhatsAppConversation();
        $this->messageModel = new WhatsAppMessage();
        $this->queueModel = new WhatsAppQueue();
        $this->attendanceModel = new WhatsAppAttendance();
    }
    
    /**
     * Resposta para GET (testar se a URL está acessível). A Evolution API envia POST.
     */
    public function ping()
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(200);
        echo json_encode([
            'ok' => true,
            'message' => 'Webhook ativo. A Evolution API deve enviar eventos via POST.',
            'endpoint' => 'POST /whatsapp/webhook'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Processar webhook da Evolution API.
     * $event = path opcional quando Evolution usa "Webhook by Events" (ex: messages-upsert).
     */
    public function handle($event = null)
    {
        $payload = file_get_contents('php://input');
        $method = $_SERVER['REQUEST_METHOD'] ?? '?';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';

        // Log imediato (garantir que sempre gere log quando a rota for atingida)
        if (!function_exists('write_log')) {
            $logDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
            $logFile = $logDir . DIRECTORY_SEPARATOR . 'whatsapp-webhook.log';
            $line = '[' . date('Y-m-d H:i:s') . '] ';
            @file_put_contents($logFile, $line . "WEBHOOK ENTRADA | Método: {$method} | URI: {$requestUri}" . PHP_EOL, FILE_APPEND | LOCK_EX);
            @file_put_contents($logFile, $line . "Body: " . ($payload ?: '(vazio)') . PHP_EOL, FILE_APPEND | LOCK_EX);
        } else {
            write_log('========== WEBHOOK ENTRADA ========== ' . date('Y-m-d H:i:s'), 'whatsapp-webhook.log');
            write_log('Método: ' . $method . ' | URI: ' . $requestUri . ($event ? ' | Event path: ' . $event : ''), 'whatsapp-webhook.log');
            write_log('Content-Type: ' . $contentType, 'whatsapp-webhook.log');
            write_log('Body (raw): ' . ($payload ?: '(vazio)'), 'whatsapp-webhook.log');
            write_log('======================================', 'whatsapp-webhook.log');
        }

        $data = json_decode($payload, true);

        if (!$data) {
            write_log('Webhook rejeitado: JSON inválido. Body: ' . substr($payload, 0, 500), 'whatsapp-webhook.log');
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON']);
            return;
        }
        
        try {
            // Identificar o tipo de evento (Evolution API v1: messages.upsert | v2: MESSAGES_UPSERT)
            $event = $data['event'] ?? $data['eventType'] ?? null;
            $event = $event ? strtolower(str_replace('_', '.', $event)) : null;
            $instanceName = $data['instance'] ?? $data['instanceName'] ?? $data['data']['instance'] ?? null;
            
            if (!$instanceName) {
                write_log('Webhook sem instance: ' . substr($payload, 0, 500), 'whatsapp-webhook.log');
                http_response_code(400);
                echo json_encode(['error' => 'Instance name missing']);
                return;
            }
            
            // Buscar instância
            $instance = $this->instanceModel->findByInstanceKey($instanceName);
            
            if (!$instance) {
                write_log('Instância não encontrada: ' . $instanceName, 'whatsapp-webhook.log');
                http_response_code(404);
                echo json_encode(['error' => 'Instance not found']);
                return;
            }
            
            // Processar evento (aceitar tanto messages.upsert quanto MESSAGES_UPSERT)
            switch ($event) {
                case 'connection.update':
                    $this->handleConnectionUpdate($instance, $data);
                    break;
                    
                case 'qrcode.updated':
                    $this->handleQrCodeUpdate($instance, $data);
                    break;
                    
                case 'messages.upsert':
                    $this->handleMessagesUpsert($instance, $data);
                    break;
                    
                case 'messages.update':
                    $this->handleMessagesUpdate($instance, $data);
                    break;
                    
                default:
                    write_log('Evento não tratado: ' . ($data['event'] ?? $data['eventType'] ?? '') . ' | payload inicio: ' . substr($payload, 0, 300), 'whatsapp-webhook.log');
            }

            write_log('Webhook processado: evento=' . ($data['event'] ?? $data['eventType'] ?? '') . ', instance=' . ($instanceName ?? ''), 'whatsapp-webhook.log');

            http_response_code(200);
            echo json_encode(['success' => true]);
            
        } catch (\Exception $e) {
            write_log('Erro ao processar webhook: ' . $e->getMessage(), 'whatsapp-webhook.log');
            write_log('Stack trace: ' . $e->getTraceAsString(), 'whatsapp-webhook.log');
            
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Processar atualização de conexão
     * Evolution API envia state "open" quando conectado; normalizamos para CONNECTED no sistema
     */
    private function handleConnectionUpdate($instance, $data)
    {
        $state = $data['data']['state'] ?? 'DISCONNECTED';
        $stateNormalized = $this->normalizeConnectionState($state);

        $this->instanceModel->updateStatus($instance['id'], $stateNormalized);

        if ($stateNormalized === 'CONNECTED') {
            $this->instanceModel->updateQrCode($instance['id'], null);
            $phoneNumber = $data['data']['user'] ?? null;
            $profileName = $data['data']['userName'] ?? null;
            if ($phoneNumber) {
                $this->instanceModel->updateConnectionInfo(
                    $instance['id'],
                    $phoneNumber,
                    $profileName
                );
            }
        }
    }

    /**
     * Normaliza estado da Evolution API para o valor usado no sistema (CONNECTED, DISCONNECTED, CONNECTING)
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
     * Processar atualização de QR Code
     */
    private function handleQrCodeUpdate($instance, $data)
    {
        $qrCode = $data['data']['qrcode']['base64'] ?? null;
        
        if ($qrCode) {
            $this->instanceModel->updateQrCode($instance['id'], $qrCode);
            $this->instanceModel->updateStatus($instance['id'], 'CONNECTING');
        }
    }
    
    /**
     * Processar novas mensagens
     * Evolution pode enviar: data.messages[] OU um único objeto em data (com data.key, data.message)
     */
    private function handleMessagesUpsert($instance, $data)
    {
        $messages = $data['data']['messages'] ?? null;
        if ($messages === null && isset($data['data']['key'])) {
            // Payload com uma única mensagem em data (Evolution v2)
            $messages = [$data['data']];
        }
        if ($messages === null) {
            $messages = $data['messages'] ?? [];
        }
        if (!is_array($messages)) {
            $messages = [$messages];
        }
        foreach ($messages as $messageData) {
            if (is_array($messageData) && !empty($messageData['key'])) {
                $this->processMessage($instance, $messageData);
            }
        }
    }
    
    /**
     * Processar atualização de mensagens (lidas, deletadas, etc)
     */
    private function handleMessagesUpdate($instance, $data)
    {
        $updates = $data['data']['update'] ?? [];
        
        foreach ($updates as $update) {
            $messageKey = $update['key']['id'] ?? null;
            $updateType = $update['update']['status'] ?? null;
            
            if ($messageKey && $updateType === 1) { // Status: lida
                $message = $this->messageModel->findByMessageKey($messageKey);
                
                if ($message) {
                    $this->messageModel->markAsRead($message['conversation_id'], !$message['from_me']);
                }
            }
        }
    }
    
    /**
     * Processar uma mensagem individual
     */
    private function processMessage($instance, $messageData)
    {
        try {
            $key = $messageData['key'] ?? [];
            $remoteJid = $key['remoteJid'] ?? $messageData['remoteJid'] ?? null;
            $fromMe = !empty($key['fromMe']) || !empty($messageData['fromMe']);
            $messageKey = $key['id'] ?? $messageData['key']['id'] ?? $messageData['id'] ?? null;
            $timestamp = $messageData['messageTimestamp'] ?? $messageData['messageTimestamp'] ?? time();
            
            if (!$remoteJid) {
                write_log('processMessage sem remoteJid: ' . json_encode($messageData), 'whatsapp-webhook.log');
                return;
            }
            
            // Normalizar número (remover @s.whatsapp.net, etc)
            $phoneNumber = preg_replace('/@.*/', '', $remoteJid);
            
            // Verificar se já existe a mensagem
            if ($messageKey) {
                $existing = $this->messageModel->findByMessageKey($messageKey);
                if ($existing) {
                    return; // Mensagem já processada
                }
            }
            
            // Criar ou atualizar contato
            $contactData = [
                'name' => $messageData['pushName'] ?? null,
                'profile_picture_url' => null
            ];
            
            $contactId = $this->contactModel->createOrUpdate($instance['id'], $phoneNumber, $contactData);
            
            // Criar ou buscar conversa
            $queueId = null; // TODO: Implementar lógica de direcionamento para fila
            $conversation = $this->conversationModel->findOrCreate($instance['id'], $contactId, $queueId);
            
            // Extrair conteúdo da mensagem
            $messageType = 'TEXT';
            $body = null;
            $mediaUrl = null;
            $mediaMimeType = null;
            $mediaName = null;
            $mediaSize = null;
            $caption = null;
            
            $messageContent = $messageData['message'] ?? [];
            if (is_string($messageContent)) {
                $body = $messageContent;
            } elseif (isset($messageContent['conversation'])) {
                $body = $messageContent['conversation'];
            } elseif (isset($messageContent['extendedTextMessage']['text'])) {
                $body = $messageContent['extendedTextMessage']['text'];
            } elseif (isset($messageContent['textMessage'])) {
                $tm = $messageContent['textMessage'];
                $body = is_string($tm) ? $tm : ($tm['text'] ?? null);
            } elseif (isset($messageContent['imageMessage'])) {
                $messageType = 'IMAGE';
                $mediaUrl = $messageContent['imageMessage']['url'] ?? null;
                $mediaMimeType = $messageContent['imageMessage']['mimetype'] ?? null;
                $caption = $messageContent['imageMessage']['caption'] ?? null;
            } elseif (isset($messageContent['videoMessage'])) {
                $messageType = 'VIDEO';
                $mediaUrl = $messageContent['videoMessage']['url'] ?? null;
                $mediaMimeType = $messageContent['videoMessage']['mimetype'] ?? null;
                $caption = $messageContent['videoMessage']['caption'] ?? null;
            } elseif (isset($messageContent['audioMessage'])) {
                $messageType = 'AUDIO';
                $mediaUrl = $messageContent['audioMessage']['url'] ?? null;
                $mediaMimeType = $messageContent['audioMessage']['mimetype'] ?? null;
            } elseif (isset($messageContent['documentMessage'])) {
                $messageType = 'DOCUMENT';
                $mediaUrl = $messageContent['documentMessage']['url'] ?? null;
                $mediaMimeType = $messageContent['documentMessage']['mimetype'] ?? null;
                $mediaName = $messageContent['documentMessage']['fileName'] ?? null;
            }
            
            // Buscar atendimento ativo
            $attendance = $this->attendanceModel->findActiveByConversation($conversation['id']);
            
            // Criar mensagem
            $messageId = $this->messageModel->create([
                'conversation_id' => $conversation['id'],
                'attendance_id' => $attendance['id'] ?? null,
                'instance_id' => $instance['id'],
                'message_key' => $messageKey,
                'remote_jid' => $remoteJid,
                'from_me' => $fromMe,
                'message_type' => $messageType,
                'body' => $body,
                'media_url' => $mediaUrl,
                'media_mime_type' => $mediaMimeType,
                'media_name' => $mediaName,
                'media_size' => $mediaSize,
                'caption' => $caption,
                'timestamp' => $timestamp
            ]);
            
            // Atualizar última mensagem da conversa
            $preview = $body ? substr($body, 0, 100) : ($messageType !== 'TEXT' ? ucfirst(strtolower($messageType)) : 'Mensagem');
            $this->conversationModel->updateLastMessage($conversation['id'], $preview);
            
            // Se mensagem não foi enviada por nós, incrementar não lidas
            if (!$fromMe) {
                $this->conversationModel->incrementUnread($conversation['id']);
                $this->contactModel->incrementUnread($contactId);
                
                // Se não há atendimento ativo e há fila, tentar distribuir
                if (!$attendance && $conversation['queue_id']) {
                    $this->assignToQueue($conversation['id'], $conversation['queue_id']);
                }
            }
            
        } catch (\Exception $e) {
            write_log('Erro ao processar mensagem: ' . $e->getMessage(), 'whatsapp-webhook.log');
            write_log('Dados da mensagem: ' . json_encode($messageData), 'whatsapp-webhook.log');
        }
    }
    
    /**
     * Atribuir conversa a uma fila (distribuição automática)
     */
    private function assignToQueue($conversationId, $queueId)
    {
        try {
            $user = $this->queueModel->getNextAvailableUser($queueId);
            
            if ($user) {
                $this->attendanceModel->create([
                    'conversation_id' => $conversationId,
                    'user_id' => $user['id'],
                    'queue_id' => $queueId
                ]);
            }
        } catch (\Exception $e) {
            write_log('Erro ao atribuir à fila: ' . $e->getMessage(), 'whatsapp-webhook.log');
        }
    }
}


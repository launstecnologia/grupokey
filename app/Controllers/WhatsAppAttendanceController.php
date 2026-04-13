<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\AutoConfig;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppAttendance;
use App\Models\WhatsAppQueue;
use App\Models\WhatsAppInstance;
use App\Models\WhatsAppContact;
use App\Services\EvolutionApiService;

/**
 * Controller para gerenciar atendimentos WhatsApp
 */
class WhatsAppAttendanceController
{
    private $conversationModel;
    private $messageModel;
    private $attendanceModel;
    private $queueModel;
    private $instanceModel;
    private $contactModel;
    
    public function __construct()
    {
        $this->conversationModel = new WhatsAppConversation();
        $this->messageModel = new WhatsAppMessage();
        $this->attendanceModel = new WhatsAppAttendance();
        $this->queueModel = new WhatsAppQueue();
        $this->instanceModel = new WhatsAppInstance();
        $this->contactModel = new WhatsAppContact();
    }
    
    /**
     * Listar conversas (dashboard de atendimento)
     */
    public function index()
    {
        Auth::requireAuth();
        
        $userId = $_SESSION['user_id'] ?? null;
        $filters = [
            'status' => $_GET['status'] ?? 'OPEN',
            'queue_id' => $_GET['queue_id'] ?? null,
            'search' => $_GET['search'] ?? null
        ];
        
        // Se não for admin, mostrar apenas conversas do usuário
        if (!Auth::isAdmin()) {
            $filters['user_id'] = $userId;
        }
        
        $conversations = $this->conversationModel->getAll($filters);
        $queues = $this->queueModel->getAll(['is_active' => true]);
        $connectedInstances = $this->instanceModel->getAll(['status' => 'CONNECTED']);
        
        $data = [
            'title' => 'Atendimento WhatsApp',
            'currentPage' => 'whatsapp-attendance',
            'conversations' => $conversations,
            'queues' => $queues,
            'filters' => $filters,
            'connected_instances' => $connectedInstances
        ];
        
        view('whatsapp/attendance/index', $data);
    }
    
    /**
     * Iniciar conversa por número (API REST) - adicionar número para conversar
     */
    public function startConversationByNumber()
    {
        Auth::requireAuth();

        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true) ?: [];
            $phoneNumber = trim($data['phone_number'] ?? '');
            $instanceId = !empty($data['instance_id']) ? (int) $data['instance_id'] : null;
            $queueId = !empty($data['queue_id']) ? (int) $data['queue_id'] : null;

            if ($phoneNumber === '') {
                throw new \Exception('Informe o número de telefone');
            }

            $phoneNumber = preg_replace('/\D/', '', $phoneNumber);
            if (strlen($phoneNumber) < 10) {
                throw new \Exception('Número inválido. Use DDD + número (ex: 5516999999999).');
            }

            if (!preg_match('/^\d+$/', $phoneNumber)) {
                throw new \Exception('Número deve conter apenas dígitos');
            }

            if ($instanceId) {
                $instance = $this->instanceModel->findById($instanceId);
            } else {
                $instances = $this->instanceModel->getAll(['status' => 'CONNECTED']);
                $instance = $instances[0] ?? null;
            }

            if (!$instance || ($instance['status'] ?? '') !== 'CONNECTED') {
                throw new \Exception('Nenhuma instância conectada. Conecte uma instância WhatsApp primeiro.');
            }

            $instanceId = (int) $instance['id'];

            $this->contactModel->createOrUpdate($instanceId, $phoneNumber, []);
            $contact = $this->contactModel->findByPhoneNumber($instanceId, $phoneNumber);
            if (!$contact) {
                throw new \Exception('Erro ao criar contato');
            }

            $conversation = $this->conversationModel->findOrCreate($instanceId, $contact['id'], $queueId);

            $userId = $_SESSION['user_id'];
            $attendance = $this->attendanceModel->findActiveByConversation($conversation['id']);
            if (!$attendance) {
                $attendanceId = $this->attendanceModel->create([
                    'conversation_id' => $conversation['id'],
                    'user_id' => $userId,
                    'queue_id' => $conversation['queue_id']
                ]);
                $attendance = $this->attendanceModel->findById($attendanceId);
            } elseif ($attendance['user_id'] != $userId) {
                throw new \Exception('Esta conversa já está sendo atendida por outro usuário');
            }

            $conversation = $this->conversationModel->findById($conversation['id']);
            $messages = $this->messageModel->getByConversation($conversation['id'], 100);

            echo json_encode([
                'success' => true,
                'conversation_id' => $conversation['id'],
                'conversation' => $conversation,
                'attendance' => $attendance,
                'messages' => $messages
            ]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Abrir conversa (API REST)
     */
    public function openConversation($conversationId)
    {
        Auth::requireAuth();
        
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $userId = $_SESSION['user_id'];
            $conversationId = is_array($conversationId) ? (int) ($conversationId['id'] ?? $conversationId[0] ?? 0) : (int) $conversationId;
            if ($conversationId <= 0) {
                throw new \Exception('ID da conversa inválido');
            }
            
            $conversation = $this->conversationModel->findById($conversationId);
            
            if (!$conversation) {
                throw new \Exception('Conversa não encontrada');
            }
            
            // Verificar se já existe atendimento ativo
            $attendance = $this->attendanceModel->findActiveByConversation($conversationId);
            
            if (!$attendance) {
                // Criar novo atendimento
                $attendanceId = $this->attendanceModel->create([
                    'conversation_id' => $conversationId,
                    'user_id' => $userId,
                    'queue_id' => $conversation['queue_id']
                ]);
                
                $attendance = $this->attendanceModel->findById($attendanceId);
            } elseif ($attendance['user_id'] != $userId) {
                throw new \Exception('Esta conversa já está sendo atendida por outro usuário');
            }
            
            // Buscar mensagens
            $messages = $this->messageModel->getByConversation($conversationId, 100);
            
            // Marcar mensagens como lidas
            $this->messageModel->markAsRead($conversationId, false);
            $this->conversationModel->resetUnread($conversationId);
            
            echo json_encode([
                'success' => true,
                'conversation' => $conversation,
                'attendance' => $attendance,
                'messages' => $messages
            ], JSON_UNESCAPED_UNICODE);
            exit;
        } catch (\Throwable $e) {
            if (function_exists('write_log')) {
                write_log('openConversation erro: ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine(), 'whatsapp.log');
                write_log('Stack: ' . $e->getTraceAsString(), 'whatsapp.log');
            }
            http_response_code($e instanceof \Exception ? 400 : 500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    
    /**
     * Enviar mensagem (API REST)
     */
    public function sendMessage()
    {
        Auth::requireAuth();
        
        header('Content-Type: application/json');
        
        try {
            $userId = $_SESSION['user_id'];
            $data = json_decode(file_get_contents('php://input'), true);
            
            $conversationId = $data['conversation_id'] ?? null;
            $message = $data['message'] ?? null;
            $messageType = $data['type'] ?? 'TEXT';
            $mediaUrl = $data['media_url'] ?? null;
            
            if (!$conversationId) {
                throw new \Exception('Conversa não informada');
            }
            if (!$message && !$mediaUrl) {
                throw new \Exception('Informe a mensagem ou anexe um arquivo');
            }
            if ($messageType !== 'TEXT' && !$mediaUrl) {
                throw new \Exception('URL da mídia é obrigatória para este tipo');
            }
            
            // Verificar se o usuário tem acesso à conversa
            $attendance = $this->attendanceModel->findActiveByConversation($conversationId);
            
            if (!$attendance || $attendance['user_id'] != $userId) {
                throw new \Exception('Você não tem permissão para enviar mensagens nesta conversa');
            }
            
            $conversation = $this->conversationModel->findById($conversationId);
            $instance = $this->instanceModel->findById($conversation['instance_id']);
            $contact = $this->contactModel->findById($conversation['contact_id']);
            
            // Mídia: preferir URL pública (com APP_URL) para a Evolution baixar e enviar — entrega no WhatsApp costuma ser melhor. Senão, enviar em base64.
            $mediaToSend = $mediaUrl;
            if ($mediaUrl && $messageType !== 'TEXT') {
                $localPath = $this->resolveLocalMediaPath($mediaUrl);
                if ($localPath && is_readable($localPath)) {
                    $appUrl = AutoConfig::get('APP_URL', '');
                    if ($appUrl !== '' && $appUrl !== null && strpos($mediaUrl, 'http') === 0) {
                        $mediaToSend = $mediaUrl;
                    } else {
                        $mediaToSend = base64_encode(file_get_contents($localPath));
                    }
                }
            }
            
            // Enviar via Evolution API
            $apiService = new EvolutionApiService($instance);
            
            $bodyToSave = $message;
            if ($messageType === 'TEXT') {
                $response = $apiService->sendText($contact['phone_number'], $message);
            } else {
                $mediaType = strtolower($messageType);
                if ($mediaType === 'document' && !empty($data['file_name'])) {
                    $response = $apiService->sendMedia(
                        $contact['phone_number'],
                        $mediaToSend,
                        $mediaType,
                        $message ?: null,
                        $data['file_name']
                    );
                } else {
                    $response = $apiService->sendMedia(
                        $contact['phone_number'],
                        $mediaToSend,
                        $mediaType,
                        $message ?: null,
                        $data['file_name'] ?? null
                    );
                }
                if (!$bodyToSave) {
                    $bodyToSave = '[Mídia]';
                }
            }
            
            // Salvar mensagem no banco
            $messageId = $this->messageModel->create([
                'conversation_id' => $conversationId,
                'attendance_id' => $attendance['id'],
                'instance_id' => $instance['id'],
                'message_key' => $response['key']['id'] ?? null,
                'remote_jid' => $contact['phone_number'] . '@s.whatsapp.net',
                'from_me' => true,
                'message_type' => $messageType,
                'body' => $bodyToSave,
                'media_url' => $mediaUrl,
                'timestamp' => time()
            ]);
            
            // Atualizar última mensagem
            $this->conversationModel->updateLastMessage($conversationId, substr($bodyToSave, 0, 100));
            
            $savedMessage = $this->messageModel->findById($messageId);
            
            echo json_encode([
                'success' => true,
                'message' => $savedMessage
            ]);
            
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Upload de mídia para envio no WhatsApp (imagem, áudio, vídeo, documento)
     * Retorna URL pública para uso na Evolution API
     */
    public function uploadMedia()
    {
        header('Content-Type: application/json');
        try {
            Auth::requireAuth();
        } catch (\Throwable $e) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Não autorizado']);
            return;
        }
        $allowedExtensions = [
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'video' => ['mp4', '3gp'],
            'audio' => ['mp3', 'ogg', 'm4a', 'wav', 'webm'], // webm = gravação do microfone no navegador
            'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx']
        ];
        $allExt = array_merge(
            $allowedExtensions['image'],
            $allowedExtensions['video'],
            $allowedExtensions['audio'],
            $allowedExtensions['document']
        );
        $maxSize = 16 * 1024 * 1024; // 16MB
        
        try {
            if (empty($_FILES['file']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
                throw new \Exception('Nenhum arquivo enviado');
            }
            $file = $_FILES['file'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errors = [
                    UPLOAD_ERR_INI_SIZE => 'Arquivo excede o limite do servidor',
                    UPLOAD_ERR_FORM_SIZE => 'Arquivo muito grande',
                    UPLOAD_ERR_PARTIAL => 'Upload incompleto',
                    UPLOAD_ERR_NO_FILE => 'Nenhum arquivo enviado',
                    UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária indisponível',
                    UPLOAD_ERR_CANT_WRITE => 'Falha ao gravar arquivo',
                    UPLOAD_ERR_EXTENSION => 'Upload bloqueado por extensão'
                ];
                throw new \Exception($errors[$file['error']] ?? 'Erro no upload');
            }
            if ($file['size'] > $maxSize) {
                throw new \Exception('Arquivo muito grande. Máximo 16MB');
            }
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allExt)) {
                throw new \Exception('Tipo de arquivo não permitido. Use: imagem, vídeo, áudio ou documento (PDF, DOC, etc.)');
            }
            
            $baseDir = dirname(__DIR__, 2);
            $uploadDir = $baseDir . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'whatsapp';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $fileName = uniqid('wa_', true) . '.' . $ext;
            $filePath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                throw new \Exception('Erro ao salvar arquivo');
            }
            
            $pathUrl = 'public/uploads/whatsapp/' . $fileName;
            $relativeUrl = url($pathUrl);
            $appUrl = AutoConfig::get('APP_URL', '');
            if ($appUrl !== '' && $appUrl !== null) {
                $fullUrl = rtrim($appUrl, '/') . '/' . ltrim($relativeUrl, '/');
            } else {
                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $fullUrl = $scheme . '://' . $host . $relativeUrl;
            }
            
            if (in_array($ext, $allowedExtensions['image'])) {
                $mediaType = 'image';
            } elseif (in_array($ext, $allowedExtensions['video'])) {
                $mediaType = 'video';
            } elseif (in_array($ext, $allowedExtensions['audio'])) {
                $mediaType = 'audio';
            } else {
                $mediaType = 'document';
            }
            
            echo json_encode([
                'success' => true,
                'url' => $fullUrl,
                'type' => strtoupper($mediaType),
                'file_name' => $file['name']
            ]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        } catch (\Throwable $e) {
            if (function_exists('write_log')) {
                write_log('uploadMedia error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine(), 'database.log');
            }
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro interno ao enviar mídia. Tente novamente.'
            ]);
        }
    }
    
    /**
     * Fechar atendimento
     */
    public function closeAttendance($attendanceId)
    {
        Auth::requireAuth();
        
        header('Content-Type: application/json');
        
        try {
            $userId = $_SESSION['user_id'];
            $data = json_decode(file_get_contents('php://input'), true);
            
            $attendance = $this->attendanceModel->findById($attendanceId);
            
            if (!$attendance || $attendance['user_id'] != $userId) {
                throw new \Exception('Atendimento não encontrado ou sem permissão');
            }
            
            $this->attendanceModel->close(
                $attendanceId,
                $data['rating'] ?? null,
                $data['rating_comment'] ?? null
            );
            
            // Fechar conversa
            $this->conversationModel->close($attendance['conversation_id']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Atendimento encerrado com sucesso'
            ]);
            
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Transferir atendimento
     */
    public function transferAttendance($attendanceId)
    {
        Auth::requireAuth();
        
        header('Content-Type: application/json');
        
        try {
            $userId = $_SESSION['user_id'];
            $data = json_decode(file_get_contents('php://input'), true);
            
            $toUserId = $data['to_user_id'] ?? null;
            $reason = $data['reason'] ?? null;
            
            if (!$toUserId) {
                throw new \Exception('Usuário de destino não informado');
            }
            
            $attendance = $this->attendanceModel->findById($attendanceId);
            
            if (!$attendance || $attendance['user_id'] != $userId) {
                throw new \Exception('Atendimento não encontrado ou sem permissão');
            }
            
            $newAttendanceId = $this->attendanceModel->transfer($attendanceId, $toUserId, $reason);
            
            echo json_encode([
                'success' => true,
                'message' => 'Atendimento transferido com sucesso',
                'new_attendance_id' => $newAttendanceId
            ]);
            
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Buscar novas mensagens (polling)
     */
    public function getNewMessages($conversationId)
    {
        Auth::requireAuth();
        
        header('Content-Type: application/json');
        
        try {
            $lastMessageId = $_GET['last_message_id'] ?? 0;
            
            $messages = $this->messageModel->getByConversation($conversationId, 50);
            
            // Filtrar apenas mensagens novas
            $newMessages = array_filter($messages, function($msg) use ($lastMessageId) {
                return $msg['id'] > $lastMessageId;
            });
            
            // Marcar como lidas
            if (!empty($newMessages)) {
                $this->messageModel->markAsRead($conversationId, false);
                $this->conversationModel->resetUnread($conversationId);
            }
            
            echo json_encode([
                'success' => true,
                'messages' => array_values($newMessages)
            ]);
            
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Se a URL apontar para um arquivo nosso (uploads/whatsapp), retorna o caminho absoluto no disco.
     */
    private function resolveLocalMediaPath($mediaUrl)
    {
        if (!is_string($mediaUrl) || strpos($mediaUrl, 'http') !== 0) {
            return null;
        }
        $path = parse_url($mediaUrl, PHP_URL_PATH);
        if (!$path) return null;
        // Ex: /public/uploads/whatsapp/wa_xxx.webm ou /uploads/whatsapp/wa_xxx.webm
        if (preg_match('#/(?:public/)?uploads/whatsapp/([a-zA-Z0-9_.-]+\.[a-zA-Z0-9]+)$#', $path, $m)) {
            $baseDir = dirname(__DIR__, 2);
            $filePath = $baseDir . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'whatsapp' . DIRECTORY_SEPARATOR . $m[1];
            return $filePath;
        }
        return null;
    }

    private function getMimeForMediaType($mediaType, $filePath)
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimes = [
            'webm' => 'audio/webm',
            'ogg' => 'audio/ogg',
            'mp3' => 'audio/mpeg',
            'm4a' => 'audio/mp4',
            'wav' => 'audio/wav',
            'mp4' => 'video/mp4',
            '3gp' => 'video/3gpp',
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp',
        ];
        return $mimes[$ext] ?? ($mediaType === 'audio' ? 'audio/webm' : 'application/octet-stream');
    }

    /**
     * Proxy de mídia: baixa o arquivo da Evolution API (com API key) e entrega ao navegador.
     * Permite reproduzir áudio/imagem recebidos cuja URL exige autenticação.
     * GET ?message_id=123
     */
    public function getMedia()
    {
        try {
            $messageId = (int) ($_GET['message_id'] ?? 0);
            if (!$messageId) {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'message_id obrigatório']);
                return;
            }
            Auth::requireAuth();
            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Não autorizado']);
                return;
            }
            $msg = $this->messageModel->findById($messageId);
            if (!$msg || empty($msg['media_url'])) {
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Mensagem ou mídia não encontrada']);
                return;
            }
            $conv = $this->conversationModel->findById($msg['conversation_id']);
            if (!$conv) {
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Conversa não encontrada']);
                return;
            }
            // Atendimento: conversa não tem attendance_id; buscar por conversation_id (ativo ou último)
            $att = $this->attendanceModel->findActiveByConversation($conv['id']);
            if (!$att) {
                $att = $this->attendanceModel->findLatestByConversation($conv['id']);
            }
            if (!$att) {
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Atendimento não encontrado']);
                return;
            }
            if ((int) $att['user_id'] !== (int) $userId) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Sem permissão']);
                return;
            }
            $instance = $this->instanceModel->findById($msg['instance_id']);
            if (!$instance) {
                http_response_code(502);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Instância não encontrada']);
                return;
            }
            $mediaUrl = $msg['media_url'] ?? '';
            $body = null;
            $contentType = null;
            
            // Mídia salva localmente no webhook (download direto da URL ao receber)
            if (strpos($mediaUrl, 'local:') === 0) {
                $relativePath = substr($mediaUrl, 6); // "received/1/arquivo.ogg"
                $baseDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'whatsapp';
                $filePath = $baseDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
                if (is_file($filePath) && is_readable($filePath)) {
                    $body = file_get_contents($filePath);
                    $contentType = $msg['media_mime_type'] ?? 'application/octet-stream';
                }
            }
            
            if ($body === null && !empty($instance['evolution_api_key'])) {
                $isWhatsAppCdn = (strpos($mediaUrl, 'mmg.whatsapp.net') !== false || strpos($mediaUrl, 'whatsapp.net') !== false);
                // URLs do CDN do WhatsApp (webhook) não aceitam nossa apikey; usar Evolution getBase64
            if (($isWhatsAppCdn || $mediaUrl !== '') && !empty($msg['message_key']) && !empty($msg['remote_jid'])) {
                $evolution = new EvolutionApiService($instance);
                $key = [
                    'remoteJid' => $msg['remote_jid'],
                    'fromMe' => !empty($msg['from_me']),
                    'id' => $msg['message_key']
                ];
                $msgType = strtoupper($msg['message_type'] ?? 'AUDIO');
                $base64Result = $evolution->getMediaBase64($key, $msgType);
                if ($base64Result && !empty($base64Result['base64'])) {
                    $decoded = base64_decode($base64Result['base64'], true);
                    if ($decoded !== false) {
                        $body = $decoded;
                        $contentType = $msg['media_mime_type'] ?? 'application/octet-stream';
                    }
                }
            }
            // Se não temos body ainda, tentar GET na URL (Evolution própria ou URL relativa)
            if ($body === null && $mediaUrl !== '') {
                if (strpos($mediaUrl, 'http') !== 0) {
                    $baseUrl = rtrim($instance['evolution_api_url'], '/');
                    $mediaUrl = $baseUrl . '/' . ltrim($mediaUrl, '/');
                }
                $ch = curl_init($mediaUrl);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTPHEADER => [
                        'apikey: ' . $instance['evolution_api_key']
                    ],
                    CURLOPT_TIMEOUT => 30
                ]);
                $body = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                curl_close($ch);
                if ($httpCode !== 200 || $body === false) {
                    $body = null; // falhou; tentar getBase64 se tiver message_key
                }
            }
            // Fallback: pedir mídia via getBase64 (Evolution descriptografa e retorna base64)
            if ($body === null && !empty($msg['message_key']) && !empty($msg['remote_jid'])) {
                $evolution = new EvolutionApiService($instance);
                $msgType = strtoupper($msg['message_type'] ?? 'AUDIO');
                $base64Result = $evolution->getMediaBase64([
                    'remoteJid' => $msg['remote_jid'],
                    'fromMe' => !empty($msg['from_me']),
                    'id' => $msg['message_key']
                ], $msgType);
                if ($base64Result && !empty($base64Result['base64'])) {
                    $decoded = base64_decode($base64Result['base64'], true);
                    if ($decoded !== false) {
                        $body = $decoded;
                        $contentType = $msg['media_mime_type'] ?? 'application/octet-stream';
                    }
                }
            }
            } // fim if ($body === null && evolution_api_key)
            if ($body === null || $body === false) {
                http_response_code(502);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Mídia indisponível']);
                return;
            }
            $trimmed = trim($body);
            if (strlen($trimmed) > 0 && (strpos($trimmed, '{') === 0 || strpos($trimmed, '[') === 0)) {
                http_response_code(502);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Mídia indisponível']);
                return;
            }
            $messageType = strtoupper($msg['message_type'] ?? '');
            if ($messageType === 'AUDIO') {
                $mime = $msg['media_mime_type'] ?? null;
                if ($mime && preg_match('#^audio/#', $mime)) {
                    header('Content-Type: ' . preg_replace('#\s*;.*$#', '', $mime));
                } elseif ($contentType && preg_match('#^audio/#', $contentType)) {
                    header('Content-Type: ' . preg_replace('#\s*;.*$#', '', $contentType));
                } else {
                    header('Content-Type: audio/ogg');
                }
            } elseif ($contentType && strpos($contentType, 'application/json') === false) {
                header('Content-Type: ' . preg_replace('#\s*;.*$#', '', $contentType));
            } else {
                header('Content-Type: ' . ($msg['media_mime_type'] ?: 'application/octet-stream'));
            }
            header('Content-Length: ' . strlen($body));
            header('Cache-Control: private, max-age=3600');
            header('Accept-Ranges: bytes');
            echo $body;
        } catch (\Throwable $e) {
            if (function_exists('write_log')) {
                write_log('getMedia error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine(), 'database.log');
            }
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar mídia']);
        }
    }
}


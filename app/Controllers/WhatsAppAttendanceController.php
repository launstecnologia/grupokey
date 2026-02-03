<?php

namespace App\Controllers;

use App\Core\Auth;
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
                $this->attendanceModel->create([
                    'conversation_id' => $conversation['id'],
                    'user_id' => $userId,
                    'queue_id' => $conversation['queue_id']
                ]);
            } elseif ($attendance['user_id'] != $userId) {
                throw new \Exception('Esta conversa já está sendo atendida por outro usuário');
            }

            $conversation = $this->conversationModel->findById($conversation['id']);
            $messages = $this->messageModel->getByConversation($conversation['id'], 100);

            echo json_encode([
                'success' => true,
                'conversation_id' => $conversation['id'],
                'conversation' => $conversation,
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
                        $mediaUrl,
                        $mediaType,
                        $message ?: null,
                        $data['file_name']
                    );
                } else {
                    $response = $apiService->sendMedia(
                        $contact['phone_number'],
                        $mediaUrl,
                        $mediaType,
                        $message ?: null
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
        Auth::requireAuth();
        header('Content-Type: application/json');
        
        $allowedExtensions = [
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'video' => ['mp4', 'webm', '3gp'],
            'audio' => ['mp3', 'ogg', 'm4a', 'wav'],
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
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $fullUrl = $scheme . '://' . $host . $relativeUrl;
            
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
}


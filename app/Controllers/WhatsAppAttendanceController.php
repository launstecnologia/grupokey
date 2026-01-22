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
        
        $data = [
            'title' => 'Atendimento WhatsApp',
            'currentPage' => 'whatsapp-attendance',
            'conversations' => $conversations,
            'queues' => $queues,
            'filters' => $filters
        ];
        
        view('whatsapp/attendance/index', $data);
    }
    
    /**
     * Abrir conversa (API REST)
     */
    public function openConversation($conversationId)
    {
        Auth::requireAuth();
        
        header('Content-Type: application/json');
        
        try {
            $userId = $_SESSION['user_id'];
            
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
            
            if (!$conversationId || !$message) {
                throw new \Exception('Dados incompletos');
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
            
            if ($messageType === 'TEXT') {
                $response = $apiService->sendText($contact['phone_number'], $message);
            } else {
                $response = $apiService->sendMedia(
                    $contact['phone_number'],
                    $mediaUrl,
                    strtolower($messageType),
                    $message
                );
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
                'body' => $message,
                'media_url' => $mediaUrl,
                'timestamp' => time()
            ]);
            
            // Atualizar última mensagem
            $this->conversationModel->updateLastMessage($conversationId, substr($message, 0, 100));
            
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


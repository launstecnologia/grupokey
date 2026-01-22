<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\WhatsAppQueue;
use App\Models\User;

class WhatsAppQueueController
{
    private $queueModel;
    private $userModel;
    
    public function __construct()
    {
        $this->queueModel = new WhatsAppQueue();
        $this->userModel = new User();
    }
    
    /**
     * Listar filas
     */
    public function index()
    {
        Auth::requireAdmin();
        
        $queues = $this->queueModel->getAll();
        
        $data = [
            'title' => 'Filas de Atendimento WhatsApp',
            'currentPage' => 'whatsapp-queues',
            'queues' => $queues
        ];
        
        view('whatsapp/queues/index', $data);
    }
    
    /**
     * Criar fila
     */
    public function create()
    {
        Auth::requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'name' => $_POST['name'] ?? '',
                    'description' => $_POST['description'] ?? null,
                    'color' => $_POST['color'] ?? '#3B82F6',
                    'greeting_message' => $_POST['greeting_message'] ?? null,
                    'is_active' => isset($_POST['is_active']),
                    'max_chats_per_user' => intval($_POST['max_chats_per_user'] ?? 5),
                    'auto_assign' => isset($_POST['auto_assign']),
                    'business_hours_start' => $_POST['business_hours_start'] ?? null,
                    'business_hours_end' => $_POST['business_hours_end'] ?? null,
                    'timezone' => $_POST['timezone'] ?? 'America/Sao_Paulo',
                    'created_by_user_id' => $_SESSION['user_id'] ?? null
                ];
                
                if (empty($data['name'])) {
                    throw new \Exception('Nome da fila é obrigatório');
                }
                
                $queueId = $this->queueModel->create($data);
                
                // Adicionar usuários selecionados
                if (!empty($_POST['users'])) {
                    foreach ($_POST['users'] as $userId) {
                        $this->queueModel->addUser($queueId, $userId);
                    }
                }
                
                $_SESSION['success'] = 'Fila criada com sucesso!';
                redirect(url('whatsapp/queues'));
                
            } catch (\Exception $e) {
                $_SESSION['error'] = 'Erro ao criar fila: ' . $e->getMessage();
                redirect(url('whatsapp/queues/create'));
            }
        } else {
            $users = $this->userModel->getAll(['status' => 'ACTIVE']);
            
            $data = [
                'title' => 'Nova Fila de Atendimento',
                'currentPage' => 'whatsapp-queues',
                'users' => $users
            ];
            
            view('whatsapp/queues/create', $data);
        }
    }
    
    /**
     * Editar fila
     */
    public function edit($id)
    {
        Auth::requireAdmin();
        
        $queue = $this->queueModel->findById($id);
        
        if (!$queue) {
            $_SESSION['error'] = 'Fila não encontrada';
            redirect(url('whatsapp/queues'));
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'name' => $_POST['name'] ?? '',
                    'description' => $_POST['description'] ?? null,
                    'color' => $_POST['color'] ?? '#3B82F6',
                    'greeting_message' => $_POST['greeting_message'] ?? null,
                    'is_active' => isset($_POST['is_active']),
                    'max_chats_per_user' => intval($_POST['max_chats_per_user'] ?? 5),
                    'auto_assign' => isset($_POST['auto_assign']),
                    'business_hours_start' => $_POST['business_hours_start'] ?? null,
                    'business_hours_end' => $_POST['business_hours_end'] ?? null,
                    'timezone' => $_POST['timezone'] ?? 'America/Sao_Paulo'
                ];
                
                $this->queueModel->update($id, $data);
                
                // Atualizar usuários
                // Remover todos primeiro
                $currentUsers = $this->queueModel->getUsers($id);
                foreach ($currentUsers as $user) {
                    $this->queueModel->removeUser($id, $user['id']);
                }
                
                // Adicionar selecionados
                if (!empty($_POST['users'])) {
                    foreach ($_POST['users'] as $userId) {
                        $this->queueModel->addUser($id, $userId);
                    }
                }
                
                $_SESSION['success'] = 'Fila atualizada com sucesso!';
                redirect(url('whatsapp/queues'));
                
            } catch (\Exception $e) {
                $_SESSION['error'] = 'Erro ao atualizar fila: ' . $e->getMessage();
                redirect(url('whatsapp/queues/' . $id . '/edit'));
            }
        } else {
            $users = $this->userModel->getAll(['status' => 'ACTIVE']);
            $queueUsers = $this->queueModel->getUsers($id);
            $queueUserIds = array_column($queueUsers, 'id');
            
            $data = [
                'title' => 'Editar Fila',
                'currentPage' => 'whatsapp-queues',
                'queue' => $queue,
                'users' => $users,
                'queueUserIds' => $queueUserIds
            ];
            
            view('whatsapp/queues/edit', $data);
        }
    }
    
    /**
     * Deletar fila
     */
    public function delete($id)
    {
        Auth::requireAdmin();
        
        try {
            $this->queueModel->delete($id);
            $_SESSION['success'] = 'Fila deletada com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao deletar fila: ' . $e->getMessage();
        }
        
        redirect(url('whatsapp/queues'));
    }
}


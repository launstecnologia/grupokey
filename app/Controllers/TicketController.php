<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Ticket;

class TicketController
{
    private $ticketModel;

    public function __construct()
    {
        $this->ticketModel = new Ticket();
    }

    public function index()
    {
        $filters = $this->getFilters();
        
        // Se for representante, mostrar apenas seus chamados
        if (Auth::isRepresentative()) {
            $representativeId = Auth::representative()['id'];
            $chamados = $this->ticketModel->getChamadosByRepresentative($representativeId, $filters);
            $stats = $this->ticketModel->getStats(['representative_id' => $representativeId]);
        } else {
            // Admin vê todos os chamados
            Auth::requireAdmin();
            $chamados = $this->ticketModel->getAll($filters);
            $stats = $this->ticketModel->getStats($filters);
        }
        
        $data = [
            'title' => 'Chamados',
            'currentPage' => 'chamados',
            'chamados' => $chamados,
            'stats' => $stats,
            'filters' => $filters
        ];
        
        view('tickets/index', $data);
    }

    public function create()
    {
        Auth::requireAuth();
        
        $data = [
            'title' => 'Novo Chamado',
            'currentPage' => 'chamados'
        ];
        
        view('tickets/create', $data);
    }

    public function store()
    {
        Auth::requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('chamados/create'));
        }
        
        $data = $this->validateAndSanitizeInput();
        
        if (isset($_SESSION['validation_errors'])) {
            redirect(url('chamados/create'));
        }
        
        try {
            $chamadoId = $this->ticketModel->create($data);
            
            $_SESSION['success'] = 'Chamado criado com sucesso!';
            redirect(url('chamados'));
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao criar chamado: ' . $e->getMessage();
            redirect(url('chamados/create'));
        }
    }

    public function show($id)
    {
        Auth::requireAuth();
        
        $chamado = $this->ticketModel->findById($id);
        
        if (!$chamado) {
            $_SESSION['error'] = 'Chamado não encontrado';
            redirect(url('chamados'));
        }
        
        // Verificar se o representante pode ver este chamado
        if (Auth::isRepresentative() && $chamado['created_by_representative_id'] != Auth::representative()['id']) {
            $_SESSION['error'] = 'Você não tem permissão para ver este chamado';
            redirect(url('chamados'));
        }
        
        $respostas = $this->ticketModel->getRespostas($id);
        
        $data = [
            'title' => 'Detalhes do Chamado',
            'currentPage' => 'chamados',
            'chamado' => $chamado,
            'respostas' => $respostas
        ];
        
        view('tickets/show', $data);
    }

    public function edit($id)
    {
        Auth::requireAuth();
        
        $chamado = $this->ticketModel->findById($id);
        
        if (!$chamado) {
            $_SESSION['error'] = 'Chamado não encontrado';
            redirect(url('chamados'));
        }
        
        // Apenas o representante que criou pode editar (e apenas se estiver aberto)
        if (Auth::isRepresentative()) {
            if ($chamado['created_by_representative_id'] != Auth::representative()['id']) {
                $_SESSION['error'] = 'Você não tem permissão para editar este chamado';
                redirect(url('chamados'));
            }
            
            if ($chamado['status'] !== 'OPEN') {
                $_SESSION['error'] = 'Apenas chamados abertos podem ser editados';
                redirect(url('chamados'));
            }
        }
        
        $data = [
            'title' => 'Editar Chamado',
            'currentPage' => 'chamados',
            'chamado' => $chamado
        ];
        
        view('tickets/edit', $data);
    }

    public function update($id)
    {
        Auth::requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('chamados/' . $id . '/edit'));
        }
        
        $chamado = $this->ticketModel->findById($id);
        
        if (!$chamado) {
            $_SESSION['error'] = 'Chamado não encontrado';
            redirect(url('chamados'));
        }
        
        // Verificar permissões
        if (Auth::isRepresentative()) {
            if ($chamado['created_by_representative_id'] != Auth::representative()['id']) {
                $_SESSION['error'] = 'Você não tem permissão para editar este chamado';
                redirect(url('chamados'));
            }
            
            if ($chamado['status'] !== 'OPEN') {
                $_SESSION['error'] = 'Apenas chamados abertos podem ser editados';
                redirect(url('chamados'));
            }
        }
        
        $data = $this->validateAndSanitizeInput();
        
        if (isset($_SESSION['validation_errors'])) {
            redirect(url('chamados/' . $id . '/edit'));
        }
        
        try {
            $this->ticketModel->update($id, $data);
            
            $_SESSION['success'] = 'Chamado atualizado com sucesso!';
            redirect(url('chamados/' . $id));
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar chamado: ' . $e->getMessage();
            redirect(url('chamados/' . $id . '/edit'));
        }
    }

    public function destroy($id)
    {
        Auth::requireAuth();
        
        $chamado = $this->ticketModel->findById($id);
        
        if (!$chamado) {
            $_SESSION['error'] = 'Chamado não encontrado';
            redirect(url('chamados'));
        }
        
        // Apenas o representante que criou pode deletar (e apenas se estiver aberto)
        if (Auth::isRepresentative()) {
            if ($chamado['created_by_representative_id'] != Auth::representative()['id']) {
                $_SESSION['error'] = 'Você não tem permissão para excluir este chamado';
                redirect(url('chamados'));
            }
            
            if ($chamado['status'] !== 'OPEN') {
                $_SESSION['error'] = 'Apenas chamados abertos podem ser excluídos';
                redirect(url('chamados'));
            }
        }
        
        try {
            $this->ticketModel->delete($id);
            
            $_SESSION['success'] = 'Chamado excluído com sucesso!';
            redirect(url('chamados'));
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao excluir chamado: ' . $e->getMessage();
            redirect(url('chamados'));
        }
    }

    public function responder($id)
    {
        Auth::requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('chamados/' . $id));
        }
        
        $chamado = $this->ticketModel->findById($id);
        
        if (!$chamado) {
            $_SESSION['error'] = 'Chamado não encontrado';
            redirect(url('chamados'));
        }
        
        // Verificar se pode responder
        if (Auth::isRepresentative() && $chamado['created_by_representative_id'] != Auth::representative()['id']) {
            $_SESSION['error'] = 'Você não tem permissão para responder este chamado';
            redirect(url('chamados'));
        }
        
        $mensagem = trim($_POST['mensagem'] ?? '');
        
        if (empty($mensagem)) {
            $_SESSION['error'] = 'Mensagem é obrigatória';
            redirect(url('chamados/' . $id));
        }
        
        try {
            $data = [
                'user_id' => Auth::isRepresentative() ? Auth::representative()['id'] : Auth::user()['id'],
                'user_type' => Auth::isAdmin() ? 'admin' : 'representative',
                'mensagem' => $mensagem
            ];
            
            $this->ticketModel->addResposta($id, $data);
            
            $_SESSION['success'] = 'Resposta adicionada com sucesso!';
            redirect(url('chamados/' . $id));
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao adicionar resposta: ' . $e->getMessage();
            redirect(url('chamados/' . $id));
        }
    }

    public function fechar($id)
    {
        Auth::requireAdmin();
        
        $chamado = $this->ticketModel->findById($id);
        
        if (!$chamado) {
            $_SESSION['error'] = 'Chamado não encontrado';
            redirect(url('chamados'));
        }
        
        try {
            $this->ticketModel->fecharChamado($id, $_SESSION['user']['id']);
            
            $_SESSION['success'] = 'Chamado fechado com sucesso!';
            redirect(url('chamados/' . $id));
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao fechar chamado: ' . $e->getMessage();
            redirect(url('chamados'));
        }
    }

    private function getFilters()
    {
        return [
            'status' => $_GET['status'] ?? '',
            'produto' => $_GET['produto'] ?? '',
            'search' => $_GET['search'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];
    }

    private function validateAndSanitizeInput()
    {
        $errors = [];
        
        $titulo = trim($_POST['titulo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $produto = $_POST['produto'] ?? 'OUTROS';
        
        if (empty($titulo)) {
            $errors[] = 'Título é obrigatório';
        } elseif (strlen($titulo) < 5) {
            $errors[] = 'Título deve ter pelo menos 5 caracteres';
        }
        
        if (empty($descricao)) {
            $errors[] = 'Descrição é obrigatória';
        } elseif (strlen($descricao) < 10) {
            $errors[] = 'Descrição deve ter pelo menos 10 caracteres';
        }
        
        if (!in_array($produto, ['CDC', 'CDX_EVO', 'GOOGLE', 'MEMBRO_KEY', 'OUTROS', 'PAGBANK'])) {
            $errors[] = 'Produto inválido';
        }
        
        if (!empty($errors)) {
            $_SESSION['validation_errors'] = $errors;
            return [];
        }
        
        return [
            'representative_id' => Auth::isRepresentative() ? Auth::representative()['id'] : Auth::user()['id'],
            'titulo' => $titulo,
            'descricao' => $descricao,
            'produto' => $produto,
            'status' => 'OPEN'
        ];
    }
}

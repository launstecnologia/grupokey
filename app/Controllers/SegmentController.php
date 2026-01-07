<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Segment;

class SegmentController
{
    private $segmentModel;
    
    public function __construct()
    {
        $this->segmentModel = new Segment();
    }
    
    public function index()
    {
        Auth::requireAdmin();
        
        $filters = [];
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $filters['search'] = sanitize_input($_GET['search']);
        }
        
        if (isset($_GET['status']) && !empty($_GET['status'])) {
            $filters['status'] = sanitize_input($_GET['status']);
        }
        
        $segments = $this->segmentModel->getAll($filters);
        
        $data = [
            'title' => 'Segmentos',
            'currentPage' => 'segmentos',
            'segments' => $segments,
            'filters' => $filters
        ];
        
        view('segments/index', $data);
    }
    
    public function create()
    {
        Auth::requireAdmin();
        
        $data = [
            'title' => 'Novo Segmento',
            'currentPage' => 'segmentos'
        ];
        
        view('segments/create', $data);
    }
    
    public function store()
    {
        Auth::requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('segmentos/create'));
        }
        
        $nome = sanitize_input($_POST['nome'] ?? '');
        $descricao = sanitize_input($_POST['descricao'] ?? '');
        $status = sanitize_input($_POST['status'] ?? 'ACTIVE');
        
        if (empty($nome)) {
            $_SESSION['error'] = 'Nome do segmento é obrigatório';
            redirect(url('segmentos/create'));
        }
        
        try {
            $this->segmentModel->create([
                'nome' => $nome,
                'descricao' => $descricao,
                'status' => $status
            ]);
            
            $_SESSION['success'] = 'Segmento cadastrado com sucesso!';
            redirect(url('segmentos'));
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao cadastrar segmento: ' . $e->getMessage();
            redirect(url('segmentos/create'));
        }
    }
    
    public function edit($id)
    {
        Auth::requireAdmin();
        
        $segment = $this->segmentModel->findById($id);
        
        if (!$segment) {
            $_SESSION['error'] = 'Segmento não encontrado';
            redirect(url('segmentos'));
        }
        
        $data = [
            'title' => 'Editar Segmento',
            'currentPage' => 'segmentos',
            'segment' => $segment
        ];
        
        view('segments/edit', $data);
    }
    
    public function update($id)
    {
        Auth::requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('segmentos/' . $id . '/edit'));
        }
        
        $segment = $this->segmentModel->findById($id);
        
        if (!$segment) {
            $_SESSION['error'] = 'Segmento não encontrado';
            redirect(url('segmentos'));
        }
        
        $nome = sanitize_input($_POST['nome'] ?? '');
        $descricao = sanitize_input($_POST['descricao'] ?? '');
        $status = sanitize_input($_POST['status'] ?? 'ACTIVE');
        
        if (empty($nome)) {
            $_SESSION['error'] = 'Nome do segmento é obrigatório';
            redirect(url('segmentos/' . $id . '/edit'));
        }
        
        try {
            $this->segmentModel->update($id, [
                'nome' => $nome,
                'descricao' => $descricao,
                'status' => $status
            ]);
            
            $_SESSION['success'] = 'Segmento atualizado com sucesso!';
            redirect(url('segmentos'));
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar segmento: ' . $e->getMessage();
            redirect(url('segmentos/' . $id . '/edit'));
        }
    }
    
    public function destroy($id)
    {
        Auth::requireAdmin();
        
        $segment = $this->segmentModel->findById($id);
        
        if (!$segment) {
            $_SESSION['error'] = 'Segmento não encontrado';
            redirect(url('segmentos'));
        }
        
        try {
            $this->segmentModel->delete($id);
            $_SESSION['success'] = 'Segmento excluído com sucesso!';
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao excluir segmento: ' . $e->getMessage();
        }
        
        redirect(url('segmentos'));
    }
    
    public function toggleStatus($id)
    {
        Auth::requireAdmin();
        
        try {
            $this->segmentModel->toggleStatus($id);
            $_SESSION['success'] = 'Status alterado com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao alterar status: ' . $e->getMessage();
        }
        
        redirect(url('segmentos'));
    }
}


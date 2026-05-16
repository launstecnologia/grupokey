<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\DocumentType;

class DocumentTypeController
{
    private $model;

    public function __construct()
    {
        $this->model = new DocumentType();
    }

    public function index()
    {
        Auth::requireAdmin();

        $filters = [];
        if (!empty($_GET['search'])) {
            $filters['search'] = sanitize_input($_GET['search']);
        }
        if (isset($_GET['status']) && $_GET['status'] !== '') {
            $filters['is_active'] = (int) ($_GET['status'] === '1' ? 1 : 0);
        }

        $data = [
            'title' => 'Tipos de Documento',
            'currentPage' => 'tipos-documentos',
            'types' => $this->model->getAll($filters),
            'filters' => $filters
        ];

        view('document-types/index', $data);
    }

    public function create()
    {
        Auth::requireAdmin();

        $data = [
            'title' => 'Novo Tipo de Documento',
            'currentPage' => 'tipos-documentos',
            'type' => null
        ];

        view('document-types/form', $data);
    }

    public function store()
    {
        Auth::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('tipos-documentos/create'));
        }

        $payload = $this->buildPayload();
        if (isset($_SESSION['validation_errors'])) {
            $_SESSION['old_input'] = $_POST;
            redirect(url('tipos-documentos/create'));
        }

        try {
            $this->model->create($payload);
            $_SESSION['success'] = 'Tipo de documento criado com sucesso.';
            redirect(url('tipos-documentos'));
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao criar tipo de documento: ' . $e->getMessage();
            $_SESSION['old_input'] = $_POST;
            redirect(url('tipos-documentos/create'));
        }
    }

    public function edit($id)
    {
        Auth::requireAdmin();

        $type = $this->model->findById((int) $id);
        if (!$type) {
            $_SESSION['error'] = 'Tipo de documento não encontrado.';
            redirect(url('tipos-documentos'));
        }

        $data = [
            'title' => 'Editar Tipo de Documento',
            'currentPage' => 'tipos-documentos',
            'type' => $type
        ];

        view('document-types/form', $data);
    }

    public function update($id)
    {
        Auth::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('tipos-documentos/' . $id . '/edit'));
        }

        $type = $this->model->findById((int) $id);
        if (!$type) {
            $_SESSION['error'] = 'Tipo de documento não encontrado.';
            redirect(url('tipos-documentos'));
        }

        $payload = $this->buildPayload();
        if (isset($_SESSION['validation_errors'])) {
            $_SESSION['old_input'] = $_POST;
            redirect(url('tipos-documentos/' . $id . '/edit'));
        }

        try {
            $this->model->update((int) $id, $payload);
            $_SESSION['success'] = 'Tipo de documento atualizado com sucesso.';
            redirect(url('tipos-documentos'));
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar tipo de documento: ' . $e->getMessage();
            $_SESSION['old_input'] = $_POST;
            redirect(url('tipos-documentos/' . $id . '/edit'));
        }
    }

    public function destroy($id)
    {
        Auth::requireAdmin();

        try {
            $this->model->delete((int) $id);
            $_SESSION['success'] = 'Tipo de documento excluído com sucesso.';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Não foi possível excluir: ' . $e->getMessage();
        }

        redirect(url('tipos-documentos'));
    }

    private function buildPayload()
    {
        $errors = [];

        $code = strtoupper(trim((string) sanitize_input($_POST['code'] ?? '')));
        $label = trim((string) sanitize_input($_POST['label'] ?? ''));
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) && (string) $_POST['is_active'] === '1' ? 1 : 0;

        if ($code === '') {
            $errors[] = 'Código é obrigatório.';
        } elseif (!preg_match('/^[A-Z0-9_]+$/', $code)) {
            $errors[] = 'Código inválido. Use apenas letras maiúsculas, números e underscore.';
        }

        if ($label === '') {
            $errors[] = 'Nome/descrição é obrigatório.';
        }

        if (!empty($errors)) {
            $_SESSION['validation_errors'] = $errors;
            return [];
        }

        return [
            'code' => $code,
            'label' => $label,
            'sort_order' => $sortOrder,
            'is_active' => $isActive,
        ];
    }
}


<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\AgendaContact;

class AgendaController
{
    private $contactModel;

    public function __construct()
    {
        $this->contactModel = new AgendaContact();
    }

    public function index()
    {
        Auth::requireAuth();

        $filters = [];
        if (!empty($_GET['search'])) {
            $filters['search'] = trim($_GET['search']);
        }

        $contacts = $this->contactModel->getAll($filters);

        $data = [
            'title' => 'Agenda de Contatos',
            'currentPage' => 'agenda',
            'contacts' => $contacts,
            'filters' => $filters
        ];

        view('agenda/index', $data);
    }

    public function create()
    {
        Auth::requireAuth();

        $data = [
            'title' => 'Novo Contato',
            'currentPage' => 'agenda'
        ];

        view('agenda/create', $data);
    }

    public function store()
    {
        Auth::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('agenda/create'));
        }

        $name = trim($_POST['name'] ?? '');
        $phone = preg_replace('/\D/', '', trim($_POST['phone'] ?? ''));
        $email = trim($_POST['email'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if (empty($name) || empty($phone)) {
            $_SESSION['error'] = 'Nome e telefone são obrigatórios.';
            redirect(url('agenda/create'));
        }

        if (strlen($phone) < 10) {
            $_SESSION['error'] = 'Informe um telefone válido com DDD.';
            redirect(url('agenda/create'));
        }

        if (strlen($phone) === 10 || strlen($phone) === 11) {
            $phone = '55' . $phone;
        }

        try {
            $this->contactModel->create([
                'name' => $name,
                'phone' => $phone,
                'email' => $email ?: null,
                'notes' => $notes ?: null
            ]);
            $_SESSION['success'] = 'Contato cadastrado com sucesso!';
            redirect(url('agenda'));
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao cadastrar: ' . $e->getMessage();
            redirect(url('agenda/create'));
        }
    }

    public function edit($id)
    {
        Auth::requireAuth();

        $id = is_array($id) ? (int) ($id['id'] ?? $id[0] ?? 0) : (int) $id;
        $contact = $this->contactModel->findById($id);

        if (!$contact) {
            $_SESSION['error'] = 'Contato não encontrado.';
            redirect(url('agenda'));
        }

        $data = [
            'title' => 'Editar Contato',
            'currentPage' => 'agenda',
            'contact' => $contact
        ];

        view('agenda/edit', $data);
    }

    public function update($id)
    {
        Auth::requireAuth();

        $id = is_array($id) ? (int) ($id['id'] ?? $id[0] ?? 0) : (int) $id;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('agenda/' . $id . '/edit'));
        }

        $contact = $this->contactModel->findById($id);
        if (!$contact) {
            $_SESSION['error'] = 'Contato não encontrado.';
            redirect(url('agenda'));
        }

        $name = trim($_POST['name'] ?? '');
        $phone = preg_replace('/\D/', '', trim($_POST['phone'] ?? ''));
        $email = trim($_POST['email'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if (empty($name) || empty($phone)) {
            $_SESSION['error'] = 'Nome e telefone são obrigatórios.';
            redirect(url('agenda/' . $id . '/edit'));
        }

        if (strlen($phone) < 10) {
            $_SESSION['error'] = 'Informe um telefone válido com DDD.';
            redirect(url('agenda/' . $id . '/edit'));
        }

        if (strlen($phone) === 10 || strlen($phone) === 11) {
            $phone = '55' . $phone;
        }

        try {
            $this->contactModel->update($id, [
                'name' => $name,
                'phone' => $phone,
                'email' => $email ?: null,
                'notes' => $notes ?: null
            ]);
            $_SESSION['success'] = 'Contato atualizado com sucesso!';
            redirect(url('agenda'));
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar: ' . $e->getMessage();
            redirect(url('agenda/' . $id . '/edit'));
        }
    }

    public function destroy($id)
    {
        Auth::requireAuth();

        $id = is_array($id) ? (int) ($id['id'] ?? $id[0] ?? 0) : (int) $id;
        $contact = $this->contactModel->findById($id);

        if (!$contact) {
            $_SESSION['error'] = 'Contato não encontrado.';
            redirect(url('agenda'));
        }

        try {
            $this->contactModel->delete($id);
            $_SESSION['success'] = 'Contato removido.';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao remover: ' . $e->getMessage();
        }
        redirect(url('agenda'));
    }
}

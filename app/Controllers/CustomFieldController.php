<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\CustomFieldDefinition;

class CustomFieldController
{
    private $model;

    public function __construct()
    {
        $this->model = new CustomFieldDefinition();
    }

    public function index()
    {
        Auth::requireAdmin();

        try {
            $all = $this->model->getAll(null, false);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Campos dinâmicos indisponíveis. Execute as migrações do banco antes de continuar.';
            $all = [];
        }
        $fieldsByEntity = [
            'establishment' => [],
            'representative' => [],
        ];

        foreach ($all as $field) {
            $entity = (string) ($field['entity_type'] ?? '');
            if (!isset($fieldsByEntity[$entity])) {
                continue;
            }
            $fieldsByEntity[$entity][] = $field;
        }

        view('custom-fields/index', [
            'title' => 'Campos Dinâmicos',
            'currentPage' => 'campos-dinamicos',
            'fieldsByEntity' => $fieldsByEntity
        ]);
    }

    public function create()
    {
        Auth::requireAdmin();

        view('custom-fields/form', [
            'title' => 'Novo Campo Dinâmico',
            'currentPage' => 'campos-dinamicos',
            'field' => null
        ]);
    }

    public function store()
    {
        Auth::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('campos-dinamicos/create'));
        }

        $payload = $this->buildPayload();
        if (isset($_SESSION['validation_errors'])) {
            $_SESSION['old_input'] = $_POST;
            redirect(url('campos-dinamicos/create'));
        }

        try {
            $this->model->create($payload);
            $_SESSION['success'] = 'Campo dinâmico criado com sucesso.';
            redirect(url('campos-dinamicos'));
        } catch (\Exception $e) {
            $_SESSION['old_input'] = $_POST;
            $_SESSION['error'] = 'Erro ao criar campo dinâmico: ' . $e->getMessage();
            redirect(url('campos-dinamicos/create'));
        }
    }

    public function edit($id)
    {
        Auth::requireAdmin();

        try {
            $field = $this->model->findById((int) $id);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Campos dinâmicos indisponíveis. Execute as migrações do banco antes de continuar.';
            redirect(url('campos-dinamicos'));
        }
        if (!$field || (int) ($field['is_active'] ?? 0) !== 1) {
            $_SESSION['error'] = 'Campo dinâmico não encontrado.';
            redirect(url('campos-dinamicos'));
        }

        view('custom-fields/form', [
            'title' => 'Editar Campo Dinâmico',
            'currentPage' => 'campos-dinamicos',
            'field' => $field
        ]);
    }

    public function update($id)
    {
        Auth::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('campos-dinamicos/' . (int) $id . '/edit'));
        }

        try {
            $field = $this->model->findById((int) $id);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Campos dinâmicos indisponíveis. Execute as migrações do banco antes de continuar.';
            redirect(url('campos-dinamicos'));
        }
        if (!$field || (int) ($field['is_active'] ?? 0) !== 1) {
            $_SESSION['error'] = 'Campo dinâmico não encontrado.';
            redirect(url('campos-dinamicos'));
        }

        $payload = $this->buildPayload((int) $id);
        if (isset($_SESSION['validation_errors'])) {
            $_SESSION['old_input'] = $_POST;
            redirect(url('campos-dinamicos/' . (int) $id . '/edit'));
        }

        try {
            $this->model->update((int) $id, $payload);
            $_SESSION['success'] = 'Campo dinâmico atualizado com sucesso.';
            redirect(url('campos-dinamicos'));
        } catch (\Exception $e) {
            $_SESSION['old_input'] = $_POST;
            $_SESSION['error'] = 'Erro ao atualizar campo dinâmico: ' . $e->getMessage();
            redirect(url('campos-dinamicos/' . (int) $id . '/edit'));
        }
    }

    public function destroy($id)
    {
        Auth::requireAdmin();

        try {
            $this->model->delete((int) $id);
            $_SESSION['success'] = 'Campo dinâmico removido com sucesso.';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao remover campo dinâmico: ' . $e->getMessage();
        }

        redirect(url('campos-dinamicos'));
    }

    private function buildPayload($id = null)
    {
        $errors = [];

        $entityType = sanitize_input($_POST['entity_type'] ?? '');
        $fieldKeyInput = sanitize_input($_POST['field_key'] ?? '');
        $label = sanitize_input($_POST['label'] ?? '');
        $fieldType = sanitize_input($_POST['field_type'] ?? 'text');
        $isRequired = isset($_POST['is_required']) && $_POST['is_required'] === '1';
        $placeholder = sanitize_input($_POST['placeholder'] ?? '');
        $helpText = sanitize_input($_POST['help_text'] ?? '');
        $optionsText = trim($_POST['options_text'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 1);

        $validEntities = ['establishment', 'representative'];
        $validTypes = ['text', 'number', 'email', 'date', 'datetime-local', 'textarea', 'select', 'currency', 'phone', 'cpf', 'cnpj'];

        if (!in_array($entityType, $validEntities, true)) {
            $errors[] = 'Selecione uma entidade válida.';
        }

        $fieldKey = $this->slugify($fieldKeyInput);
        if ($fieldKey === '') {
            $errors[] = 'Chave do campo inválida.';
        }

        if ($label === '') {
            $errors[] = 'Rótulo é obrigatório.';
        }

        if (!in_array($fieldType, $validTypes, true)) {
            $errors[] = 'Tipo de campo inválido.';
        }

        $options = [];
        if ($fieldType === 'select') {
            if ($optionsText === '') {
                $errors[] = 'Campo do tipo seleção exige opções.';
            } else {
                $parts = preg_split('/\r\n|\r|\n|,/', $optionsText);
                foreach ($parts as $part) {
                    $value = trim($part);
                    if ($value !== '') {
                        $options[] = $value;
                    }
                }
                if (empty($options)) {
                    $errors[] = 'Informe pelo menos uma opção para o campo seleção.';
                }
            }
        }

        if ($sortOrder <= 0) {
            $sortOrder = 1;
        }

        if ($fieldKey !== '' && in_array($entityType, $validEntities, true)) {
            $existing = $this->model->findByEntityAndKey($entityType, $fieldKey);
            if ($existing && ((int) $existing['id'] !== (int) $id)) {
                $errors[] = 'Já existe um campo com esta chave para a entidade selecionada.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['validation_errors'] = $errors;
            return [];
        }

        return [
            'entity_type' => $entityType,
            'field_key' => $fieldKey,
            'label' => $label,
            'field_type' => $fieldType,
            'is_required' => $isRequired,
            'placeholder' => $placeholder,
            'help_text' => $helpText,
            'options' => $options,
            'sort_order' => $sortOrder
        ];
    }

    private function slugify($value)
    {
        $value = strtolower(trim((string) $value));
        $value = preg_replace('/[^a-z0-9_]+/', '_', $value);
        $value = preg_replace('/_+/', '_', $value);
        $value = trim((string) $value, '_');
        return $value;
    }
}

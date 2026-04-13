<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\DynamicProduct;

class DynamicProductController
{
    private $model;

    public function __construct()
    {
        $this->model = new DynamicProduct();
    }

    public function index()
    {
        Auth::requireAdmin();

        $filters = [];
        if (!empty($_GET['search'])) {
            $filters['search'] = sanitize_input($_GET['search']);
        }

        $data = [
            'title' => 'Produtos Dinâmicos',
            'currentPage' => 'produtos-dinamicos',
            'products' => $this->model->getAll($filters),
            'filters' => $filters
        ];

        view('dynamic-products/index', $data);
    }

    public function create()
    {
        Auth::requireAdmin();

        $data = [
            'title' => 'Novo Produto Dinâmico',
            'currentPage' => 'produtos-dinamicos',
            'product' => null
        ];

        view('dynamic-products/form', $data);
    }

    public function store()
    {
        Auth::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('produtos-dinamicos/create'));
        }

        $payload = $this->buildPayload();
        if (isset($_SESSION['validation_errors'])) {
            $_SESSION['old_input'] = $_POST;
            redirect(url('produtos-dinamicos/create'));
        }

        try {
            $this->model->create($payload['product'], $payload['fields']);
            $_SESSION['success'] = 'Produto dinâmico criado com sucesso.';
            redirect(url('produtos-dinamicos'));
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao criar produto dinâmico: ' . $e->getMessage();
            $_SESSION['old_input'] = $_POST;
            redirect(url('produtos-dinamicos/create'));
        }
    }

    public function edit($id)
    {
        Auth::requireAdmin();

        $product = $this->model->findById($id);
        if (!$product || (int) ($product['is_active'] ?? 0) !== 1) {
            $_SESSION['error'] = 'Produto dinâmico não encontrado.';
            redirect(url('produtos-dinamicos'));
        }

        $data = [
            'title' => 'Editar Produto Dinâmico',
            'currentPage' => 'produtos-dinamicos',
            'product' => $product
        ];

        view('dynamic-products/form', $data);
    }

    public function update($id)
    {
        Auth::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('produtos-dinamicos/' . $id . '/edit'));
        }

        $product = $this->model->findById($id);
        if (!$product || (int) ($product['is_active'] ?? 0) !== 1) {
            $_SESSION['error'] = 'Produto dinâmico não encontrado.';
            redirect(url('produtos-dinamicos'));
        }

        $payload = $this->buildPayload($id);
        if (isset($_SESSION['validation_errors'])) {
            $_SESSION['old_input'] = $_POST;
            redirect(url('produtos-dinamicos/' . $id . '/edit'));
        }

        try {
            $this->model->update($id, $payload['product'], $payload['fields']);
            $_SESSION['success'] = 'Produto dinâmico atualizado com sucesso.';
            redirect(url('produtos-dinamicos'));
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar produto dinâmico: ' . $e->getMessage();
            $_SESSION['old_input'] = $_POST;
            redirect(url('produtos-dinamicos/' . $id . '/edit'));
        }
    }

    public function destroy($id)
    {
        Auth::requireAdmin();

        try {
            $this->model->delete($id);
            $_SESSION['success'] = 'Produto dinâmico removido com sucesso.';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao remover produto dinâmico: ' . $e->getMessage();
        }

        redirect(url('produtos-dinamicos'));
    }

    private function buildPayload($id = null)
    {
        $errors = [];

        $name = sanitize_input($_POST['name'] ?? '');
        $slugInput = sanitize_input($_POST['slug'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $hasApi = isset($_POST['has_api']) && $_POST['has_api'] === '1';
        $apiProvider = sanitize_input($_POST['api_provider'] ?? '');
        $apiConfig = trim($_POST['api_config_json'] ?? '');

        if ($name === '') {
            $errors[] = 'Nome do produto é obrigatório.';
        }

        $slug = $this->slugify($slugInput !== '' ? $slugInput : $name);
        if ($slug === '') {
            $errors[] = 'Slug inválido.';
        }

        if ($apiConfig !== '') {
            json_decode($apiConfig, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $errors[] = 'JSON de configuração de API inválido.';
            }
        }

        $fields = $this->extractFields($errors);

        if (empty($errors) && empty($fields)) {
            $errors[] = 'Adicione pelo menos um campo para o produto.';
        }

        if (!empty($errors)) {
            $_SESSION['validation_errors'] = $errors;
            return [];
        }

        return [
            'product' => [
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'has_api' => $hasApi,
                'api_provider' => $apiProvider,
                'api_config_json' => $apiConfig,
            ],
            'fields' => $fields
        ];
    }

    private function extractFields(&$errors)
    {
        $types = ['text', 'number', 'email', 'date', 'datetime-local', 'textarea', 'select', 'currency', 'phone', 'cpf', 'cnpj'];

        $fieldKeys = $_POST['field_key'] ?? [];
        $labels = $_POST['field_label'] ?? [];
        $fieldTypes = $_POST['field_type'] ?? [];
        $requiredFlags = $_POST['field_required'] ?? [];
        $placeholders = $_POST['field_placeholder'] ?? [];
        $helpTexts = $_POST['field_help_text'] ?? [];
        $optionsTexts = $_POST['field_options'] ?? [];

        $fields = [];
        $seenKeys = [];
        $total = max(
            count($fieldKeys),
            count($labels),
            count($fieldTypes)
        );

        for ($i = 0; $i < $total; $i++) {
            $rawKey = sanitize_input($fieldKeys[$i] ?? '');
            $label = sanitize_input($labels[$i] ?? '');
            $type = sanitize_input($fieldTypes[$i] ?? 'text');
            $placeholder = sanitize_input($placeholders[$i] ?? '');
            $helpText = sanitize_input($helpTexts[$i] ?? '');
            $optionsRaw = trim($optionsTexts[$i] ?? '');
            $isRequired = isset($requiredFlags[$i]) && $requiredFlags[$i] === '1';

            if ($rawKey === '' && $label === '') {
                continue;
            }

            $fieldKey = $this->slugify($rawKey);
            if ($fieldKey === '') {
                $errors[] = 'Chave de campo inválida na linha ' . ($i + 1) . '.';
                continue;
            }

            if ($label === '') {
                $errors[] = 'Label do campo é obrigatória na linha ' . ($i + 1) . '.';
                continue;
            }

            if (!in_array($type, $types, true)) {
                $errors[] = 'Tipo de campo inválido na linha ' . ($i + 1) . '.';
                continue;
            }

            if (isset($seenKeys[$fieldKey])) {
                $errors[] = 'Campo duplicado: ' . $fieldKey . '.';
                continue;
            }
            $seenKeys[$fieldKey] = true;

            $options = [];
            if ($type === 'select') {
                if ($optionsRaw === '') {
                    $errors[] = 'Campo select exige opções na linha ' . ($i + 1) . '.';
                    continue;
                }

                $lines = preg_split('/\r\n|\r|\n|,/', $optionsRaw);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '') {
                        continue;
                    }
                    $value = $this->slugify($line);
                    if ($value === '') {
                        $value = strtolower($line);
                    }
                    $options[] = ['value' => $value, 'label' => $line];
                }

                if (empty($options)) {
                    $errors[] = 'Campo select sem opções válidas na linha ' . ($i + 1) . '.';
                    continue;
                }
            }

            $fields[] = [
                'field_key' => $fieldKey,
                'label' => $label,
                'field_type' => $type,
                'is_required' => $isRequired,
                'placeholder' => $placeholder,
                'help_text' => $helpText,
                'options' => $options
            ];
        }

        return $fields;
    }

    private function slugify($value)
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '_', $value);
        return trim($value, '_');
    }
}

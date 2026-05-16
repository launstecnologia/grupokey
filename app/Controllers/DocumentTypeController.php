<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\DocumentType;
use App\Models\DynamicProduct;

class DocumentTypeController
{
    private $model;
    private $dynamicProductModel;

    public function __construct()
    {
        $this->model = new DocumentType();
        $this->dynamicProductModel = new DynamicProduct();
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
            'filters' => $filters,
            'productLabelMap' => $this->getProductLabelMap(),
            'typeProductLinksMap' => $this->getTypeProductLinksMap()
        ];

        view('document-types/index', $data);
    }

    public function create()
    {
        Auth::requireAdmin();

        $data = [
            'title' => 'Novo Tipo de Documento',
            'currentPage' => 'tipos-documentos',
            'type' => null,
            'selectedProductKeys' => [],
            'availableProducts' => $this->getAvailableProductKeys()
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
            'type' => $type,
            'selectedProductKeys' => array_column($this->model->getProductLinksByTypeId((int) $id), 'product_key'),
            'availableProducts' => $this->getAvailableProductKeys()
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
            'product_keys' => isset($_POST['product_keys']) && is_array($_POST['product_keys']) ? $_POST['product_keys'] : [],
        ];
    }

    private function getAvailableProductKeys(): array
    {
        $options = [
            ['key' => 'PAGSEGURO', 'label' => 'PAGSEGURO'],
        ];

        $dynamicProducts = $this->dynamicProductModel->getAll();
        usort($dynamicProducts, function ($a, $b) {
            return strcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
        });

        foreach ($dynamicProducts as $product) {
            $id = (int) ($product['id'] ?? 0);
            $name = trim((string) ($product['name'] ?? ''));
            if ($id <= 0 || $name === '') {
                continue;
            }

            $options[] = [
                'key' => 'DYNAMIC_' . $id,
                'label' => $name,
            ];
        }

        return $options;
    }

    private function getProductLabelMap(): array
    {
        $map = ['PAGSEGURO' => 'PAGSEGURO'];
        foreach ($this->dynamicProductModel->getAll() as $product) {
            $id = (int) ($product['id'] ?? 0);
            $name = trim((string) ($product['name'] ?? ''));
            if ($id > 0 && $name !== '') {
                $map['DYNAMIC_' . $id] = $name;
            }
        }

        return $map;
    }

    private function getTypeProductLinksMap(): array
    {
        $map = [];
        $types = $this->model->getAll();
        foreach ($types as $type) {
            $typeId = (int) ($type['id'] ?? 0);
            if ($typeId <= 0) {
                continue;
            }
            $map[$typeId] = array_column($this->model->getProductLinksByTypeId($typeId), 'product_key');
        }

        return $map;
    }
}

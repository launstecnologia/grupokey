<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\FileUpload;
use App\Models\Banner;
use App\Models\Material;

class BannerController
{
    private $bannerModel;
    private $materialModel;
    private $fileUpload;

    public function __construct()
    {
        $this->bannerModel = new Banner();
        $this->materialModel = new Material();
        $this->fileUpload = new FileUpload();
    }

    public function index()
    {
        Auth::requireAdmin();

        $filters = [
            'search' => sanitize_input($_GET['search'] ?? ''),
            'is_active' => isset($_GET['status']) && $_GET['status'] !== '' ? (int) $_GET['status'] : '',
        ];

        $data = [
            'title' => 'Banners',
            'currentPage' => 'banners',
            'banners' => $this->bannerModel->getAll($filters),
            'filters' => $filters,
        ];

        view('banners/index', $data);
    }

    public function create()
    {
        Auth::requireAdmin();

        $data = [
            'title' => 'Novo Banner',
            'currentPage' => 'banners',
            'banner' => null,
            'materialFiles' => $this->materialModel->getAllFiles(['is_active' => 1]),
        ];

        view('banners/form', $data);
    }

    public function store()
    {
        Auth::requireAdmin();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect(url('banners/create'));
        }

        $payload = $this->buildPayload();
        if (isset($_SESSION['validation_errors'])) {
            $_SESSION['old_input'] = $_POST;
            redirect(url('banners/create'));
        }

        try {
            $this->bannerModel->create($payload);
            $_SESSION['success'] = 'Banner cadastrado com sucesso.';
            redirect(url('banners'));
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Erro ao cadastrar banner: ' . $e->getMessage();
            $_SESSION['old_input'] = $_POST;
            redirect(url('banners/create'));
        }
    }

    public function edit($id)
    {
        Auth::requireAdmin();
        $banner = $this->bannerModel->findById((int) $id);
        if (!$banner) {
            $_SESSION['error'] = 'Banner não encontrado.';
            redirect(url('banners'));
        }

        $data = [
            'title' => 'Editar Banner',
            'currentPage' => 'banners',
            'banner' => $banner,
            'materialFiles' => $this->materialModel->getAllFiles(['is_active' => 1]),
        ];

        view('banners/form', $data);
    }

    public function update($id)
    {
        Auth::requireAdmin();
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        if (!in_array($method, ['POST', 'PUT'], true)) {
            redirect(url('banners/' . (int) $id . '/edit'));
        }

        $banner = $this->bannerModel->findById((int) $id);
        if (!$banner) {
            $_SESSION['error'] = 'Banner não encontrado.';
            redirect(url('banners'));
        }

        $payload = $this->buildPayload($banner);
        if (isset($_SESSION['validation_errors'])) {
            $_SESSION['old_input'] = $_POST;
            redirect(url('banners/' . (int) $id . '/edit'));
        }

        try {
            $this->bannerModel->update((int) $id, $payload);
            $_SESSION['success'] = 'Banner atualizado com sucesso.';
            redirect(url('banners'));
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Erro ao atualizar banner: ' . $e->getMessage();
            $_SESSION['old_input'] = $_POST;
            redirect(url('banners/' . (int) $id . '/edit'));
        }
    }

    public function destroy($id)
    {
        Auth::requireAdmin();
        try {
            $this->bannerModel->delete((int) $id);
            $_SESSION['success'] = 'Banner excluído com sucesso.';
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Erro ao excluir banner: ' . $e->getMessage();
        }
        redirect(url('banners'));
    }

    public function image($id)
    {
        Auth::requireAuth();
        $banner = $this->bannerModel->findById((int) $id);
        if (!$banner || empty($banner['image_path']) || !file_exists($banner['image_path'])) {
            http_response_code(404);
            exit('Imagem não encontrada');
        }

        $mime = function_exists('mime_content_type') ? (mime_content_type($banner['image_path']) ?: 'image/jpeg') : 'image/jpeg';
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($banner['image_path']));
        readfile($banner['image_path']);
        exit;
    }

    private function buildPayload(array $existing = null): array
    {
        unset($_SESSION['validation_errors']);
        $errors = [];

        $title = trim((string) sanitize_input($_POST['title'] ?? ''));
        $subtitle = trim((string) sanitize_input($_POST['subtitle'] ?? ''));
        $imageSourceType = trim((string) ($_POST['image_source_type'] ?? 'upload'));
        $imageUrl = trim((string) sanitize_input($_POST['image_url'] ?? ''));
        $linkType = trim((string) ($_POST['link_type'] ?? 'none'));
        $externalLink = trim((string) sanitize_input($_POST['external_link'] ?? ''));
        $internalTargetType = trim((string) ($_POST['internal_target_type'] ?? ''));
        $internalTargetId = trim((string) ($_POST['internal_target_id'] ?? ''));
        $slideDuration = (int) ($_POST['slide_duration_seconds'] ?? 5);
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) && (string) $_POST['is_active'] === '1' ? 1 : 0;

        $imagePath = $existing['image_path'] ?? null;
        if ($imageSourceType === 'upload' && isset($_FILES['image']) && !empty($_FILES['image']['tmp_name'])) {
            $upload = $this->fileUpload->upload($_FILES['image'], 'banners');
            $imagePath = $upload['file_path'] ?? null;
            $imageUrl = '';
        }

        if ($imageSourceType === 'url') {
            $imagePath = null;
            if ($imageUrl === '' || !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                $errors[] = 'Informe uma URL de imagem válida.';
            }
        }

        if ($imageSourceType === 'upload' && empty($imagePath)) {
            $errors[] = 'Envie uma imagem para o banner.';
        }

        if (!in_array($linkType, ['none', 'external', 'internal'], true)) {
            $linkType = 'none';
        }

        if ($linkType === 'external') {
            if ($externalLink === '' || !filter_var($externalLink, FILTER_VALIDATE_URL)) {
                $errors[] = 'Informe um link externo válido.';
            }
            $internalTargetType = '';
            $internalTargetId = '';
        }

        if ($linkType === 'internal') {
            if ($internalTargetType === '') {
                $errors[] = 'Selecione o destino interno.';
            }
            if ($internalTargetType === 'material_file' && $internalTargetId === '') {
                $errors[] = 'Selecione um material para o destino interno.';
            }
            $externalLink = '';
        }

        if ($slideDuration < 1 || $slideDuration > 60) {
            $errors[] = 'Tempo do slide deve ser entre 1 e 60 segundos.';
        }

        if (!empty($errors)) {
            $_SESSION['validation_errors'] = $errors;
            return [];
        }

        return [
            'title' => $title !== '' ? $title : null,
            'subtitle' => $subtitle !== '' ? $subtitle : null,
            'image_path' => $imagePath,
            'image_url' => $imageUrl !== '' ? $imageUrl : null,
            'link_type' => $linkType,
            'external_link' => $externalLink !== '' ? $externalLink : null,
            'internal_target_type' => $internalTargetType !== '' ? $internalTargetType : null,
            'internal_target_id' => $internalTargetId !== '' ? $internalTargetId : null,
            'slide_duration_seconds' => $slideDuration,
            'sort_order' => $sortOrder,
            'is_active' => $isActive,
        ];
    }
}


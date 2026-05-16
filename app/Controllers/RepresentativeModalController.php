<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\FileUpload;
use App\Models\Material;
use App\Models\Representative;
use App\Models\RepresentativeModal;

class RepresentativeModalController
{
    private $model;
    private $representativeModel;
    private $materialModel;
    private $fileUpload;

    public function __construct()
    {
        $this->model = new RepresentativeModal();
        $this->representativeModel = new Representative();
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

        view('representative-modals/index', [
            'title' => 'Modais Representante',
            'currentPage' => 'modais-representante',
            'modals' => $this->model->getAll($filters),
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        Auth::requireAdmin();
        view('representative-modals/form', [
            'title' => 'Novo Modal',
            'currentPage' => 'modais-representante',
            'modal' => null,
            'representatives' => $this->representativeModel->getAll(['status' => 'ACTIVE']),
            'materialFiles' => $this->materialModel->getAllFiles(['is_active' => 1]),
        ]);
    }

    public function store()
    {
        Auth::requireAdmin();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect(url('modais-representante/create'));
        }
        $payload = $this->buildPayload();
        if (isset($_SESSION['validation_errors'])) {
            $_SESSION['old_input'] = $_POST;
            redirect(url('modais-representante/create'));
        }
        try {
            $this->model->create($payload);
            $_SESSION['success'] = 'Modal cadastrado com sucesso.';
            redirect(url('modais-representante'));
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Erro ao cadastrar modal: ' . $e->getMessage();
            $_SESSION['old_input'] = $_POST;
            redirect(url('modais-representante/create'));
        }
    }

    public function edit($id)
    {
        Auth::requireAdmin();
        $modal = $this->model->findById((int) $id);
        if (!$modal) {
            $_SESSION['error'] = 'Modal não encontrado.';
            redirect(url('modais-representante'));
        }
        view('representative-modals/form', [
            'title' => 'Editar Modal',
            'currentPage' => 'modais-representante',
            'modal' => $modal,
            'representatives' => $this->representativeModel->getAll(['status' => 'ACTIVE']),
            'materialFiles' => $this->materialModel->getAllFiles(['is_active' => 1]),
        ]);
    }

    public function update($id)
    {
        Auth::requireAdmin();
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        if (!in_array($method, ['POST', 'PUT'], true)) {
            redirect(url('modais-representante/' . (int) $id . '/edit'));
        }
        $current = $this->model->findById((int) $id);
        if (!$current) {
            $_SESSION['error'] = 'Modal não encontrado.';
            redirect(url('modais-representante'));
        }
        $payload = $this->buildPayload($current);
        if (isset($_SESSION['validation_errors'])) {
            $_SESSION['old_input'] = $_POST;
            redirect(url('modais-representante/' . (int) $id . '/edit'));
        }
        try {
            $this->model->update((int) $id, $payload);
            $_SESSION['success'] = 'Modal atualizado com sucesso.';
            redirect(url('modais-representante'));
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Erro ao atualizar modal: ' . $e->getMessage();
            $_SESSION['old_input'] = $_POST;
            redirect(url('modais-representante/' . (int) $id . '/edit'));
        }
    }

    public function destroy($id)
    {
        Auth::requireAdmin();
        try {
            $this->model->delete((int) $id);
            $_SESSION['success'] = 'Modal excluído com sucesso.';
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Erro ao excluir modal: ' . $e->getMessage();
        }
        redirect(url('modais-representante'));
    }

    public function image($id)
    {
        Auth::requireAuth();
        $modal = $this->model->findById((int) $id);
        if (!$modal || empty($modal['image_path']) || !file_exists($modal['image_path'])) {
            http_response_code(404);
            exit('Imagem não encontrada');
        }
        $mime = function_exists('mime_content_type') ? (mime_content_type($modal['image_path']) ?: 'image/jpeg') : 'image/jpeg';
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($modal['image_path']));
        readfile($modal['image_path']);
        exit;
    }

    public function ack($deliveryId)
    {
        Auth::requireRepresentative();
        $repId = (int) (Auth::representative()['id'] ?? 0);
        $this->model->acknowledgeDelivery((int) $deliveryId, $repId);
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
        exit;
    }

    private function buildPayload(array $existing = null): array
    {
        unset($_SESSION['validation_errors']);
        $errors = [];

        $title = trim((string) sanitize_input($_POST['title'] ?? ''));
        $message = $this->sanitizeRichTextMessage((string) ($_POST['message'] ?? ''));
        $imageSourceType = trim((string) ($_POST['image_source_type'] ?? 'upload'));
        $imageUrl = trim((string) sanitize_input($_POST['image_url'] ?? ''));
        $imagePath = $existing['image_path'] ?? null;

        $linkType = trim((string) ($_POST['link_type'] ?? 'none'));
        $externalLink = trim((string) sanitize_input($_POST['external_link'] ?? ''));
        $internalTargetType = trim((string) ($_POST['internal_target_type'] ?? ''));
        $internalTargetId = trim((string) ($_POST['internal_target_id'] ?? ''));

        $triggerType = trim((string) ($_POST['trigger_type'] ?? 'custom_date'));
        $triggerDate = trim((string) ($_POST['trigger_date'] ?? ''));
        $triggerMonthDay = trim((string) ($_POST['trigger_month_day'] ?? ''));
        $anniversaryYears = (int) ($_POST['anniversary_years'] ?? 0);
        $milestoneEst = (int) ($_POST['milestone_establishments'] ?? 0);

        $audienceType = trim((string) ($_POST['audience_type'] ?? 'all'));
        $selectedRepIds = isset($_POST['selected_representative_ids']) && is_array($_POST['selected_representative_ids'])
            ? array_values(array_unique(array_map('intval', $_POST['selected_representative_ids'])))
            : [];
        $isActive = isset($_POST['is_active']) && (string) $_POST['is_active'] === '1' ? 1 : 0;

        if ($message === '') {
            $errors[] = 'Texto do modal é obrigatório.';
        }

        if ($imageSourceType === 'upload' && isset($_FILES['image']) && !empty($_FILES['image']['tmp_name'])) {
            $upload = $this->fileUpload->upload($_FILES['image'], 'rep-modals');
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
            $errors[] = 'Envie uma imagem para o modal.';
        }

        if (!in_array($linkType, ['none', 'external', 'internal'], true)) {
            $linkType = 'none';
        }
        if ($linkType === 'external' && ($externalLink === '' || !filter_var($externalLink, FILTER_VALIDATE_URL))) {
            $errors[] = 'Informe um link externo válido.';
        }
        if ($linkType === 'internal' && $internalTargetType === '') {
            $errors[] = 'Selecione o destino interno.';
        }
        if ($linkType !== 'external') {
            $externalLink = '';
        }
        if ($linkType !== 'internal') {
            $internalTargetType = '';
            $internalTargetId = '';
        }

        $validTriggers = ['birthday', 'commemorative_date', 'platform_anniversary', 'establishment_milestone', 'custom_date'];
        if (!in_array($triggerType, $validTriggers, true)) {
            $triggerType = 'custom_date';
        }
        if ($triggerType === 'custom_date' && $triggerDate === '') {
            $errors[] = 'Informe a data do disparo.';
        }
        if ($triggerType === 'commemorative_date' && !preg_match('/^\d{2}-\d{2}$/', $triggerMonthDay)) {
            $errors[] = 'Informe o dia/mês no formato MM-DD para data comemorativa.';
        }
        if ($triggerType === 'platform_anniversary' && $anniversaryYears <= 0) {
            $errors[] = 'Informe os anos para aniversário de plataforma.';
        }
        if ($triggerType === 'establishment_milestone' && $milestoneEst <= 0) {
            $errors[] = 'Informe a meta de cadastros.';
        }

        if ($audienceType === 'selected' && empty($selectedRepIds)) {
            $errors[] = 'Selecione ao menos um representante.';
        }
        if (!in_array($audienceType, ['all', 'selected'], true)) {
            $audienceType = 'all';
        }

        if (!empty($errors)) {
            $_SESSION['validation_errors'] = $errors;
            return [];
        }

        return [
            'title' => $title !== '' ? $title : null,
            'message' => $message,
            'image_path' => $imagePath,
            'image_url' => $imageUrl !== '' ? $imageUrl : null,
            'link_type' => $linkType,
            'external_link' => $externalLink !== '' ? $externalLink : null,
            'internal_target_type' => $internalTargetType !== '' ? $internalTargetType : null,
            'internal_target_id' => $internalTargetId !== '' ? $internalTargetId : null,
            'trigger_type' => $triggerType,
            'trigger_date' => $triggerType === 'custom_date' ? $triggerDate : null,
            'trigger_month_day' => $triggerType === 'commemorative_date' ? $triggerMonthDay : null,
            'anniversary_years' => $triggerType === 'platform_anniversary' ? $anniversaryYears : null,
            'milestone_establishments' => $triggerType === 'establishment_milestone' ? $milestoneEst : null,
            'audience_type' => $audienceType,
            'selected_representative_ids_json' => $audienceType === 'selected' ? json_encode($selectedRepIds) : null,
            'is_active' => $isActive,
        ];
    }

    private function sanitizeRichTextMessage(string $html): string
    {
        $clean = trim($html);
        if ($clean === '') {
            return '';
        }

        $clean = preg_replace('#<\s*(script|style)[^>]*>.*?<\s*/\s*\1\s*>#is', '', $clean);
        $clean = preg_replace('/\s+on[a-z]+\s*=\s*"[^"]*"/i', '', $clean);
        $clean = preg_replace("/\s+on[a-z]+\s*=\s*'[^']*'/i", '', $clean);
        $clean = preg_replace('/\s+on[a-z]+\s*=\s*[^ >]+/i', '', $clean);
        $clean = preg_replace('/\s+style\s*=\s*"[^"]*"/i', '', $clean);
        $clean = preg_replace("/\s+style\s*=\s*'[^']*'/i", '', $clean);
        $clean = preg_replace('/\s+style\s*=\s*[^ >]+/i', '', $clean);

        $allowedTags = '<p><br><strong><b><em><i><u><ul><ol><li><a>';
        $clean = strip_tags($clean, $allowedTags);
        $clean = preg_replace('/<a\b([^>]*)>/i', '<a$1 target="_blank" rel="noopener noreferrer">', $clean);

        return trim($clean);
    }
}

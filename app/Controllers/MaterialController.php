<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Material;
use App\Models\Representative;
use App\Models\Notification;

class MaterialController
{
    private const MATERIAL_MAX_FILE_SIZE = 200 * 1024 * 1024;
    private const MATERIAL_ALLOWED_EXTENSIONS = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt',
        'jpg', 'jpeg', 'png', 'gif',
        'mp4', 'm4v', 'mov', 'avi', 'webm', 'mkv',
        'zip', 'rar'
    ];
    private const MATERIAL_ALLOWED_MIME_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain',
        'image/jpeg',
        'image/png',
        'image/gif',
        'video/mp4',
        'video/x-m4v',
        'video/quicktime',
        'video/avi',
        'video/x-msvideo',
        'video/webm',
        'video/x-matroska',
        'application/zip',
        'application/x-zip-compressed',
        'application/x-rar-compressed',
        'application/vnd.rar',
        'application/octet-stream'
    ];

    private $materialModel;
    private $representativeModel;
    private $notificationModel;

    public function __construct()
    {
        $this->materialModel = new Material();
        $this->representativeModel = new Representative();
        $this->notificationModel = new Notification();
    }

    // ===========================================
    // VISUALIZAÇÃO PARA REPRESENTANTES
    // ===========================================

    public function index()
    {
        Auth::requireAuth();
        
        $filters = $this->getFilters();
        $files = $this->materialModel->getAllFiles($filters);
        $productOptions = $this->materialModel->getProductOptions();
        $stats = $this->materialModel->getStats();
        $readMap = [];

        if (Auth::isRepresentative()) {
            $representativeId = (int) (Auth::representative()['id'] ?? 0);
            $fileIds = array_values(array_filter(array_map(static function ($file) {
                return $file['id'] ?? null;
            }, $files)));
            $readMap = $this->materialModel->getReadMapForRepresentative($representativeId, $fileIds);
        }
        
        $data = [
            'title' => 'Material de Apoio',
            'currentPage' => 'material',
            'files' => $files,
            'productOptions' => $productOptions,
            'stats' => $stats,
            'filters' => $filters,
            'readMap' => $readMap
        ];
        
        view('material/index', $data);
    }

    public function download($id)
    {
        Auth::requireAuth();
        
        $file = $this->materialModel->getFileById($id);
        
        if (!$file) {
            $_SESSION['error'] = 'Arquivo não encontrado';
            redirect(url('material'));
        }
        
        $filePath = $file['file_path'];
        
        if (!file_exists($filePath)) {
            $_SESSION['error'] = 'Arquivo não encontrado no servidor';
            redirect(url('material'));
        }
        
        // Incrementar contador de downloads
        $this->materialModel->incrementDownloadCount($id);
        
        // Forçar download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file['original_filename'] . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        readfile($filePath);
        exit;
    }

    public function preview($id)
    {
        Auth::requireAuth();

        $file = $this->materialModel->getFileById($id);
        if (!$file) {
            http_response_code(404);
            exit('Arquivo não encontrado');
        }

        $filePath = (string) ($file['file_path'] ?? '');
        if ($filePath === '' || !file_exists($filePath)) {
            http_response_code(404);
            exit('Arquivo não encontrado no servidor');
        }

        $mimeType = (string) ($file['mime_type'] ?? '');
        if (stripos($mimeType, 'image/') !== 0) {
            http_response_code(415);
            exit('Preview disponível apenas para imagens');
        }

        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: public, max-age=86400');
        readfile($filePath);
        exit;
    }

    // ===========================================
    // ADMINISTRAÇÃO - CATEGORIAS
    // ===========================================

    public function categories()
    {
        Auth::requireAdmin();
        
        $categories = $this->materialModel->getAllCategories();
        
        $data = [
            'title' => 'Categorias - Material de Apoio',
            'currentPage' => 'material',
            'categories' => $categories
        ];
        
        view('material/categories', $data);
    }

    public function createCategory()
    {
        Auth::requireAdmin();
        
        $data = [
            'title' => 'Nova Categoria',
            'currentPage' => 'material'
        ];
        
        view('material/create-category', $data);
    }

    public function storeCategory()
    {
        Auth::requireAdmin();
        
        $data = $this->validateAndSanitizeCategoryInput();
        
        if (empty($data)) {
            redirect(url('material/categories/create'));
        }
        
        try {
            $this->materialModel->createCategory($data);
            $_SESSION['success'] = 'Categoria criada com sucesso!';
            redirect(url('material/categories'));
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erro ao criar categoria: ' . $e->getMessage();
            redirect(url('material/categories/create'));
        }
    }

    public function editCategory($id)
    {
        Auth::requireAdmin();
        
        $category = $this->materialModel->getCategoryById($id);
        
        if (!$category) {
            $_SESSION['error'] = 'Categoria não encontrada';
            redirect(url('material/categories'));
        }
        
        $data = [
            'title' => 'Editar Categoria',
            'currentPage' => 'material',
            'category' => $category
        ];
        
        view('material/edit-category', $data);
    }

    public function updateCategory($id)
    {
        Auth::requireAdmin();
        
        $category = $this->materialModel->getCategoryById($id);
        
        if (!$category) {
            $_SESSION['error'] = 'Categoria não encontrada';
            redirect(url('material/categories'));
        }
        
        $data = $this->validateAndSanitizeCategoryInput();
        
        if (empty($data)) {
            redirect(url('material/categories/' . $id . '/edit'));
        }
        
        try {
            $this->materialModel->updateCategory($id, $data);
            $_SESSION['success'] = 'Categoria atualizada com sucesso!';
            redirect(url('material/categories'));
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar categoria: ' . $e->getMessage();
            redirect(url('material/categories/' . $id . '/edit'));
        }
    }

    public function destroyCategory($id)
    {
        Auth::requireAdmin();
        
        $category = $this->materialModel->getCategoryById($id);
        
        if (!$category) {
            $_SESSION['error'] = 'Categoria não encontrada';
            redirect(url('material/categories'));
        }
        
        try {
            $this->materialModel->deleteCategory($id);
            $_SESSION['success'] = 'Categoria excluída com sucesso!';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erro ao excluir categoria: ' . $e->getMessage();
        }
        
        redirect(url('material/categories'));
    }

    // ===========================================
    // ADMINISTRAÇÃO - SUBCATEGORIAS
    // ===========================================

    public function subcategories()
    {
        Auth::requireAdmin();
        
        $subcategories = $this->materialModel->getAllSubcategories();
        $categories = $this->materialModel->getAllCategories();
        
        $data = [
            'title' => 'Subcategorias - Material de Apoio',
            'currentPage' => 'material',
            'subcategories' => $subcategories,
            'categories' => $categories
        ];
        
        view('material/subcategories', $data);
    }

    public function createSubcategory()
    {
        Auth::requireAdmin();
        
        $categories = $this->materialModel->getAllCategories();
        
        $data = [
            'title' => 'Nova Subcategoria',
            'currentPage' => 'material',
            'categories' => $categories
        ];
        
        view('material/create-subcategory', $data);
    }

    public function storeSubcategory()
    {
        Auth::requireAdmin();
        
        $data = $this->validateAndSanitizeSubcategoryInput();
        
        if (empty($data)) {
            redirect(url('material/subcategories/create'));
        }
        
        try {
            $this->materialModel->createSubcategory($data);
            $_SESSION['success'] = 'Subcategoria criada com sucesso!';
            redirect(url('material/subcategories'));
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erro ao criar subcategoria: ' . $e->getMessage();
            redirect(url('material/subcategories/create'));
        }
    }

    public function editSubcategory($id)
    {
        Auth::requireAdmin();
        
        $subcategory = $this->materialModel->getSubcategoryById($id);
        
        if (!$subcategory) {
            $_SESSION['error'] = 'Subcategoria não encontrada';
            redirect(url('material/subcategories'));
        }
        
        $categories = $this->materialModel->getAllCategories();
        
        $data = [
            'title' => 'Editar Subcategoria',
            'currentPage' => 'material',
            'subcategory' => $subcategory,
            'categories' => $categories
        ];
        
        view('material/edit-subcategory', $data);
    }

    public function updateSubcategory($id)
    {
        Auth::requireAdmin();
        
        $subcategory = $this->materialModel->getSubcategoryById($id);
        
        if (!$subcategory) {
            $_SESSION['error'] = 'Subcategoria não encontrada';
            redirect(url('material/subcategories'));
        }
        
        $data = $this->validateAndSanitizeSubcategoryInput();
        
        if (empty($data)) {
            redirect(url('material/subcategories/' . $id . '/edit'));
        }
        
        try {
            $this->materialModel->updateSubcategory($id, $data);
            $_SESSION['success'] = 'Subcategoria atualizada com sucesso!';
            redirect(url('material/subcategories'));
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar subcategoria: ' . $e->getMessage();
            redirect(url('material/subcategories/' . $id . '/edit'));
        }
    }

    public function destroySubcategory($id)
    {
        Auth::requireAdmin();
        
        $subcategory = $this->materialModel->getSubcategoryById($id);
        
        if (!$subcategory) {
            $_SESSION['error'] = 'Subcategoria não encontrada';
            redirect(url('material/subcategories'));
        }
        
        try {
            $this->materialModel->deleteSubcategory($id);
            $_SESSION['success'] = 'Subcategoria excluída com sucesso!';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erro ao excluir subcategoria: ' . $e->getMessage();
        }
        
        redirect(url('material/subcategories'));
    }

    // ===========================================
    // ADMINISTRAÇÃO - ARQUIVOS
    // ===========================================

    public function files()
    {
        Auth::requireAdmin();

        // Tela legada removida: centralizar sempre na listagem principal.
        redirect(url('material'));
    }

    public function createFile()
    {
        Auth::requireAdmin();
        
        $productOptions = $this->materialModel->getProductOptions();
        
        $data = [
            'title' => 'Novo Arquivo',
            'currentPage' => 'material',
            'productOptions' => $productOptions
        ];
        
        view('material/create-file', $data);
    }

    public function storeFile()
    {
        Auth::requireAdmin();
        
        $data = $this->validateAndSanitizeFileInput();
        
        if (empty($data)) {
            redirect(url('material/files/create'));
        }
        
        try {
            $fileId = $this->materialModel->createFile($data);
            $this->notifyRepresentativesAboutNewMaterial($fileId, $data);
            $_SESSION['success'] = 'Arquivo enviado com sucesso!';
            redirect(url('material'));
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erro ao enviar arquivo: ' . $e->getMessage();
            redirect(url('material/files/create'));
        }
    }

    public function markAsRead($id)
    {
        Auth::requireRepresentative();

        $file = $this->materialModel->getFileById($id);
        if (!$file) {
            $_SESSION['error'] = 'Arquivo não encontrado';
            redirect(url('material'));
        }

        try {
            $this->materialModel->markAsReadByRepresentative($id, (int) (Auth::representative()['id'] ?? 0));
            $_SESSION['success'] = 'Leitura confirmada com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao confirmar leitura: ' . $e->getMessage();
        }

        redirect(url('material'));
    }

    public function editFile($id)
    {
        Auth::requireAdmin();
        
        $file = $this->materialModel->getFileById($id);
        
        if (!$file) {
            $_SESSION['error'] = 'Arquivo não encontrado';
            redirect(url('material'));
        }
        
        $productOptions = $this->materialModel->getProductOptions();
        $selectedProduct = $this->materialModel->getProductKeyByCategoryId($file['category_id'] ?? '');
        
        $data = [
            'title' => 'Editar Arquivo',
            'currentPage' => 'material',
            'file' => $file,
            'productOptions' => $productOptions,
            'selectedProduct' => $selectedProduct
        ];
        
        view('material/edit-file', $data);
    }

    public function updateFile($id)
    {
        Auth::requireAdmin();
        
        $file = $this->materialModel->getFileById($id);
        
        if (!$file) {
            $_SESSION['error'] = 'Arquivo não encontrado';
            redirect(url('material'));
        }
        
        $data = $this->validateAndSanitizeFileInput();
        
        if (empty($data)) {
            redirect(url('material/files/' . $id . '/edit'));
        }
        
        try {
            $this->materialModel->updateFile($id, $data);
            if (!empty($data['file_path']) && !empty($file['file_path']) && file_exists((string) $file['file_path'])) {
                @unlink((string) $file['file_path']);
            }
            $_SESSION['success'] = 'Arquivo atualizado com sucesso!';
            redirect(url('material'));
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar arquivo: ' . $e->getMessage();
            redirect(url('material/files/' . $id . '/edit'));
        }
    }

    public function destroyFile($id)
    {
        Auth::requireAdmin();
        
        $file = $this->materialModel->getFileById($id);
        
        if (!$file) {
            $_SESSION['error'] = 'Arquivo não encontrado';
            redirect(url('material'));
        }
        
        try {
            $this->materialModel->deleteFile($id);
            $_SESSION['success'] = 'Arquivo excluído com sucesso!';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erro ao excluir arquivo: ' . $e->getMessage();
        }
        
        redirect(url('material'));
    }

    // ===========================================
    // MÉTODOS AUXILIARES
    // ===========================================

    private function getFilters()
    {
        return [
            'search' => $_GET['search'] ?? '',
            'product' => strtoupper(trim($_GET['product'] ?? ''))
        ];
    }

    private function validateAndSanitizeCategoryInput()
    {
        $errors = [];
        
        $name = trim($_POST['name'] ?? '');
        if (empty($name)) {
            $errors[] = 'Nome é obrigatório';
        }
        
        $description = trim($_POST['description'] ?? '');
        $icon = trim($_POST['icon'] ?? 'fas fa-folder');
        $color = trim($_POST['color'] ?? '#007bff');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        
        if (!empty($errors)) {
            $_SESSION['validation_errors'] = $errors;
            return [];
        }
        
        return [
            'name' => $name,
            'description' => $description,
            'icon' => $icon,
            'color' => $color,
            'sort_order' => $sortOrder
        ];
    }

    private function validateAndSanitizeSubcategoryInput()
    {
        $errors = [];
        
        $productKey = strtoupper(trim($_POST['product'] ?? ''));
        if (empty($productKey)) {
            $errors[] = 'Produto é obrigatório';
        } else {
            $validProducts = array_keys($this->materialModel->getProductOptions());
            if (!in_array($productKey, $validProducts, true)) {
                $errors[] = 'Produto inválido';
            }
        }
        
        $name = trim($_POST['name'] ?? '');
        if (empty($name)) {
            $errors[] = 'Nome é obrigatório';
        }
        
        $description = trim($_POST['description'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        
        if (!empty($errors)) {
            $_SESSION['validation_errors'] = $errors;
            return [];
        }
        
        return [
            'category_id' => $categoryId,
            'name' => $name,
            'description' => $description,
            'sort_order' => $sortOrder
        ];
    }

    private function validateAndSanitizeFileInput()
    {
        $errors = [];
        $isUpdate = strtoupper($_POST['_method'] ?? '') === 'PUT';
        
        $productKey = strtoupper(trim($_POST['product'] ?? ''));
        if ($productKey === '') {
            $errors[] = 'Categoria é obrigatória';
        }
        
        $title = trim($_POST['title'] ?? '');
        if (empty($title)) {
            $errors[] = 'Título é obrigatório';
        }
        
        $description = trim($_POST['description'] ?? '');
        $fileData = [];

        $uploadError = $_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE;
        $hasUploadedFile = isset($_FILES['file']) && $uploadError === UPLOAD_ERR_OK;

        if (!$isUpdate || $uploadError !== UPLOAD_ERR_NO_FILE) {
            if (!$hasUploadedFile) {
                $errors[] = $uploadError === UPLOAD_ERR_NO_FILE
                    ? 'Arquivo é obrigatório'
                    : $this->getUploadErrorMessage((int) $uploadError);
            } else {
                $file = $_FILES['file'];

                $fileExtension = strtolower((string) pathinfo($file['name'], PATHINFO_EXTENSION));
                $mimeType = $this->detectUploadedMimeType($file);

                if (!$this->isAllowedMaterialFile($fileExtension, $mimeType)) {
                    $errors[] = 'Tipo de arquivo não permitido. Tipos permitidos: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, JPG, PNG, GIF, MP4, M4V, MOV, AVI, WEBM, MKV, ZIP e RAR.';
                }
                
                if ($file['size'] > self::MATERIAL_MAX_FILE_SIZE) {
                    $errors[] = 'Arquivo muito grande (máximo 200MB)';
                }
            }
        }

        if (!empty($errors)) {
            $_SESSION['validation_errors'] = $errors;
            return [];
        }

        if (!$isUpdate || $hasUploadedFile) {
            $file = $_FILES['file'];

            // Processar upload
            $uploadDir = 'storage/uploads/material/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileExtension = strtolower((string) pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = uniqid() . '.' . $fileExtension;
            $filePath = $uploadDir . $filename;
            $mimeType = $this->detectUploadedMimeType($file);
            
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                $_SESSION['error'] = 'Erro ao fazer upload do arquivo';
                return [];
            }

            $fileData = [
                'filename' => $filename,
                'original_filename' => $file['name'],
                'file_path' => $filePath,
                'file_size' => $file['size'],
                'file_type' => $fileExtension,
                'mime_type' => $mimeType,
                'uploaded_by' => Auth::user()['id'],
            ];
        }
        
        $isActive = isset($_POST['is_active']) && (string) $_POST['is_active'] === '1' ? 1 : 0;

        return array_merge([
            'product_key' => $productKey,
            'title' => $title,
            'description' => $description,
            'is_active' => $isActive
        ], $fileData);
    }

    private function isAllowedMaterialFile(string $extension, string $mimeType): bool
    {
        if (!in_array($extension, self::MATERIAL_ALLOWED_EXTENSIONS, true)) {
            return false;
        }

        if ($mimeType === '') {
            return true;
        }

        return in_array($mimeType, self::MATERIAL_ALLOWED_MIME_TYPES, true);
    }

    private function detectUploadedMimeType(array $file): string
    {
        $tmpName = (string) ($file['tmp_name'] ?? '');
        if ($tmpName !== '' && is_file($tmpName)) {
            if (function_exists('mime_content_type')) {
                $mimeType = \mime_content_type($tmpName);
                if (is_string($mimeType) && $mimeType !== '') {
                    return $mimeType;
                }
            }

            if (function_exists('finfo_open')) {
                $finfo = \finfo_open(FILEINFO_MIME_TYPE);
                if ($finfo) {
                    $mimeType = \finfo_file($finfo, $tmpName);
                    \finfo_close($finfo);
                    if (is_string($mimeType) && $mimeType !== '') {
                        return $mimeType;
                    }
                }
            }
        }

        return (string) ($file['type'] ?? '');
    }

    private function getUploadErrorMessage(int $errorCode): string
    {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'Arquivo excede o limite permitido pelo servidor. Para vídeos, o limite configurado deve ser de até 200MB.',
            UPLOAD_ERR_FORM_SIZE => 'Arquivo excede o tamanho máximo permitido pelo formulário.',
            UPLOAD_ERR_PARTIAL => 'Upload incompleto. Tente enviar o arquivo novamente.',
            UPLOAD_ERR_NO_TMP_DIR => 'Diretório temporário de upload não encontrado no servidor.',
            UPLOAD_ERR_CANT_WRITE => 'Não foi possível gravar o arquivo no servidor.',
            UPLOAD_ERR_EXTENSION => 'Upload bloqueado por uma extensão do PHP.'
        ];

        return $messages[$errorCode] ?? 'Erro ao enviar arquivo (código: ' . $errorCode . ').';
    }

    private function notifyRepresentativesAboutNewMaterial(string $fileId, array $data): void
    {
        try {
            $representatives = $this->representativeModel->getAll(['status' => 'ACTIVE']);
            if (empty($representatives)) {
                return;
            }

            $title = trim((string) ($data['title'] ?? 'Novo material'));
            $message = sprintf('Novo material de apoio disponível: "%s".', $title);

            foreach ($representatives as $representative) {
                $repId = (int) ($representative['id'] ?? 0);
                if ($repId <= 0) {
                    continue;
                }

                $this->notificationModel->create([
                    'recipient_type' => 'representative',
                    'representative_id' => $repId,
                    'type' => 'MATERIAL_NEW',
                    'title' => 'Novo Material de Apoio',
                    'message' => $message,
                    'related_type' => 'material_file',
                    'related_id' => null
                ]);
            }
        } catch (\Throwable $e) {
            write_log('Falha ao notificar representantes sobre novo material: ' . $e->getMessage(), 'app.log');
        }
    }
}

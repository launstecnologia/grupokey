<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Material;

class MaterialController
{
    private $materialModel;

    public function __construct()
    {
        $this->materialModel = new Material();
    }

    // ===========================================
    // VISUALIZAÇÃO PARA REPRESENTANTES
    // ===========================================

    public function index()
    {
        Auth::requireAuth();
        
        $filters = $this->getFilters();
        $files = $this->materialModel->getAllFiles($filters);
        $categories = $this->materialModel->getAllCategories();
        $stats = $this->materialModel->getStats();
        
        $data = [
            'title' => 'Material de Apoio',
            'currentPage' => 'material',
            'files' => $files,
            'categories' => $categories,
            'stats' => $stats,
            'filters' => $filters
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
        
        $filters = $this->getFilters();
        $files = $this->materialModel->getAllFiles($filters);
        $categories = $this->materialModel->getAllCategories();
        $subcategories = $this->materialModel->getAllSubcategories();
        
        $data = [
            'title' => 'Arquivos - Material de Apoio',
            'currentPage' => 'material',
            'files' => $files,
            'categories' => $categories,
            'subcategories' => $subcategories,
            'filters' => $filters
        ];
        
        view('material/files', $data);
    }

    public function createFile()
    {
        Auth::requireAdmin();
        
        $categories = $this->materialModel->getAllCategories();
        $subcategories = $this->materialModel->getAllSubcategories();
        
        $data = [
            'title' => 'Novo Arquivo',
            'currentPage' => 'material',
            'categories' => $categories,
            'subcategories' => $subcategories
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
            $this->materialModel->createFile($data);
            $_SESSION['success'] = 'Arquivo enviado com sucesso!';
            redirect(url('material/files'));
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erro ao enviar arquivo: ' . $e->getMessage();
            redirect(url('material/files/create'));
        }
    }

    public function editFile($id)
    {
        Auth::requireAdmin();
        
        $file = $this->materialModel->getFileById($id);
        
        if (!$file) {
            $_SESSION['error'] = 'Arquivo não encontrado';
            redirect(url('material/files'));
        }
        
        $categories = $this->materialModel->getAllCategories();
        $subcategories = $this->materialModel->getAllSubcategories();
        
        $data = [
            'title' => 'Editar Arquivo',
            'currentPage' => 'material',
            'file' => $file,
            'categories' => $categories,
            'subcategories' => $subcategories
        ];
        
        view('material/edit-file', $data);
    }

    public function updateFile($id)
    {
        Auth::requireAdmin();
        
        $file = $this->materialModel->getFileById($id);
        
        if (!$file) {
            $_SESSION['error'] = 'Arquivo não encontrado';
            redirect(url('material/files'));
        }
        
        $data = $this->validateAndSanitizeFileInput();
        
        if (empty($data)) {
            redirect(url('material/files/' . $id . '/edit'));
        }
        
        try {
            $this->materialModel->updateFile($id, $data);
            $_SESSION['success'] = 'Arquivo atualizado com sucesso!';
            redirect(url('material/files'));
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
            redirect(url('material/files'));
        }
        
        try {
            $this->materialModel->deleteFile($id);
            $_SESSION['success'] = 'Arquivo excluído com sucesso!';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erro ao excluir arquivo: ' . $e->getMessage();
        }
        
        redirect(url('material/files'));
    }

    // ===========================================
    // MÉTODOS AUXILIARES
    // ===========================================

    private function getFilters()
    {
        return [
            'search' => $_GET['search'] ?? '',
            'category_id' => $_GET['category_id'] ?? '',
            'subcategory_id' => $_GET['subcategory_id'] ?? ''
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
        
        $categoryId = trim($_POST['category_id'] ?? '');
        if (empty($categoryId)) {
            $errors[] = 'Categoria é obrigatória';
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
        
        $categoryId = trim($_POST['category_id'] ?? '');
        if (empty($categoryId)) {
            $errors[] = 'Categoria é obrigatória';
        }
        
        $title = trim($_POST['title'] ?? '');
        if (empty($title)) {
            $errors[] = 'Título é obrigatório';
        }
        
        $description = trim($_POST['description'] ?? '');
        $subcategoryId = trim($_POST['subcategory_id'] ?? '');
        
        // Verificar upload de arquivo
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Arquivo é obrigatório';
        } else {
            $file = $_FILES['file'];
            
            // Validar tipo de arquivo
            $allowedTypes = [
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
                'video/avi',
                'application/zip',
                'application/x-rar-compressed'
            ];
            
            if (!in_array($file['type'], $allowedTypes)) {
                $errors[] = 'Tipo de arquivo não permitido';
            }
            
            // Validar tamanho (máximo 50MB)
            if ($file['size'] > 50 * 1024 * 1024) {
                $errors[] = 'Arquivo muito grande (máximo 50MB)';
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['validation_errors'] = $errors;
            return [];
        }
        
        // Processar upload
        $uploadDir = 'storage/uploads/material/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $fileExtension;
        $filePath = $uploadDir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            $_SESSION['error'] = 'Erro ao fazer upload do arquivo';
            return [];
        }
        
        return [
            'category_id' => $categoryId,
            'subcategory_id' => $subcategoryId ?: null,
            'title' => $title,
            'description' => $description,
            'filename' => $filename,
            'original_filename' => $file['name'],
            'file_path' => $filePath,
            'file_size' => $file['size'],
            'file_type' => $fileExtension,
            'mime_type' => $file['type'],
            'uploaded_by' => Auth::user()['id']
        ];
    }
}

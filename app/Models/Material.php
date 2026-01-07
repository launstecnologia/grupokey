<?php

namespace App\Models;

use App\Core\Database;

class Material
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ===========================================
    // CATEGORIAS
    // ===========================================

    public function getAllCategories()
    {
        $sql = "SELECT * FROM material_categories 
                WHERE is_active = 1 
                ORDER BY sort_order ASC, name ASC";
        return $this->db->fetchAll($sql);
    }

    public function getCategoryById($id)
    {
        $sql = "SELECT * FROM material_categories WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function createCategory($data)
    {
        $id = uniqid();
        $sql = "INSERT INTO material_categories (id, name, description, icon, color, sort_order) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $params = [
            $id,
            $data['name'],
            $data['description'] ?? null,
            $data['icon'] ?? 'fas fa-folder',
            $data['color'] ?? '#007bff',
            $data['sort_order'] ?? 0
        ];
        
        $this->db->query($sql, $params);
        return $id;
    }

    public function updateCategory($id, $data)
    {
        $sql = "UPDATE material_categories 
                SET name = ?, description = ?, icon = ?, color = ?, sort_order = ?, updated_at = NOW() 
                WHERE id = ?";
        
        $params = [
            $data['name'],
            $data['description'] ?? null,
            $data['icon'] ?? 'fas fa-folder',
            $data['color'] ?? '#007bff',
            $data['sort_order'] ?? 0,
            $id
        ];
        
        return $this->db->query($sql, $params);
    }

    public function deleteCategory($id)
    {
        // Primeiro deletar arquivos da categoria
        $this->db->query("DELETE FROM material_files WHERE category_id = ?", [$id]);
        
        // Depois deletar subcategorias
        $this->db->query("DELETE FROM material_subcategories WHERE category_id = ?", [$id]);
        
        // Por fim deletar a categoria
        return $this->db->query("DELETE FROM material_categories WHERE id = ?", [$id]);
    }

    // ===========================================
    // SUBCATEGORIAS
    // ===========================================

    public function getAllSubcategories($categoryId = null)
    {
        $sql = "SELECT s.*, c.name as category_name 
                FROM material_subcategories s
                LEFT JOIN material_categories c ON s.category_id = c.id
                WHERE s.is_active = 1";
        
        $params = [];
        if ($categoryId) {
            $sql .= " AND s.category_id = ?";
            $params[] = $categoryId;
        }
        
        $sql .= " ORDER BY s.sort_order ASC, s.name ASC";
        
        return $this->db->fetchAll($sql, $params);
    }

    public function getSubcategoryById($id)
    {
        $sql = "SELECT s.*, c.name as category_name 
                FROM material_subcategories s
                LEFT JOIN material_categories c ON s.category_id = c.id
                WHERE s.id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function createSubcategory($data)
    {
        $id = uniqid();
        $sql = "INSERT INTO material_subcategories (id, category_id, name, description, sort_order) 
                VALUES (?, ?, ?, ?, ?)";
        
        $params = [
            $id,
            $data['category_id'],
            $data['name'],
            $data['description'] ?? null,
            $data['sort_order'] ?? 0
        ];
        
        $this->db->query($sql, $params);
        return $id;
    }

    public function updateSubcategory($id, $data)
    {
        $sql = "UPDATE material_subcategories 
                SET category_id = ?, name = ?, description = ?, sort_order = ?, updated_at = NOW() 
                WHERE id = ?";
        
        $params = [
            $data['category_id'],
            $data['name'],
            $data['description'] ?? null,
            $data['sort_order'] ?? 0,
            $id
        ];
        
        return $this->db->query($sql, $params);
    }

    public function deleteSubcategory($id)
    {
        // Primeiro deletar arquivos da subcategoria
        $this->db->query("DELETE FROM material_files WHERE subcategory_id = ?", [$id]);
        
        // Depois deletar a subcategoria
        return $this->db->query("DELETE FROM material_subcategories WHERE id = ?", [$id]);
    }

    // ===========================================
    // ARQUIVOS
    // ===========================================

    public function getAllFiles($filters = [])
    {
        $sql = "SELECT f.*, c.name as category_name, s.name as subcategory_name, u.name as uploaded_by_name
                FROM material_files f
                LEFT JOIN material_categories c ON f.category_id = c.id
                LEFT JOIN material_subcategories s ON f.subcategory_id = s.id
                LEFT JOIN users u ON f.uploaded_by = u.id
                WHERE f.is_active = 1";
        
        $params = [];
        
        if (!empty($filters['category_id'])) {
            $sql .= " AND f.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['subcategory_id'])) {
            $sql .= " AND f.subcategory_id = ?";
            $params[] = $filters['subcategory_id'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (f.title LIKE ? OR f.description LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY f.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }

    public function getFileById($id)
    {
        $sql = "SELECT f.*, c.name as category_name, s.name as subcategory_name, u.name as uploaded_by_name
                FROM material_files f
                LEFT JOIN material_categories c ON f.category_id = c.id
                LEFT JOIN material_subcategories s ON f.subcategory_id = s.id
                LEFT JOIN users u ON f.uploaded_by = u.id
                WHERE f.id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function createFile($data)
    {
        $id = uniqid();
        $sql = "INSERT INTO material_files (id, category_id, subcategory_id, title, description, filename, original_filename, file_path, file_size, file_type, mime_type, uploaded_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $id,
            $data['category_id'],
            $data['subcategory_id'] ?? null,
            $data['title'],
            $data['description'] ?? null,
            $data['filename'],
            $data['original_filename'],
            $data['file_path'],
            $data['file_size'],
            $data['file_type'],
            $data['mime_type'],
            $data['uploaded_by']
        ];
        
        $this->db->query($sql, $params);
        return $id;
    }

    public function updateFile($id, $data)
    {
        $sql = "UPDATE material_files 
                SET category_id = ?, subcategory_id = ?, title = ?, description = ?, updated_at = NOW() 
                WHERE id = ?";
        
        $params = [
            $data['category_id'],
            $data['subcategory_id'] ?? null,
            $data['title'],
            $data['description'] ?? null,
            $id
        ];
        
        return $this->db->query($sql, $params);
    }

    public function deleteFile($id)
    {
        $file = $this->getFileById($id);
        if ($file) {
            // Deletar arquivo físico
            $filePath = $file['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Deletar registro do banco
            return $this->db->query("DELETE FROM material_files WHERE id = ?", [$id]);
        }
        return false;
    }

    public function incrementDownloadCount($id)
    {
        $sql = "UPDATE material_files SET download_count = download_count + 1 WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    // ===========================================
    // ESTATÍSTICAS
    // ===========================================

    public function getStats()
    {
        $stats = [];
        
        // Total de arquivos
        $result = $this->db->fetch("SELECT COUNT(*) as total FROM material_files WHERE is_active = 1");
        $stats['total_files'] = $result['total'];
        
        // Total de categorias
        $result = $this->db->fetch("SELECT COUNT(*) as total FROM material_categories WHERE is_active = 1");
        $stats['total_categories'] = $result['total'];
        
        // Total de subcategorias
        $result = $this->db->fetch("SELECT COUNT(*) as total FROM material_subcategories WHERE is_active = 1");
        $stats['total_subcategories'] = $result['total'];
        
        // Total de downloads
        $result = $this->db->fetch("SELECT SUM(download_count) as total FROM material_files WHERE is_active = 1");
        $stats['total_downloads'] = $result['total'] ?? 0;
        
        // Arquivos por categoria
        $stats['files_by_category'] = $this->db->fetchAll("
            SELECT c.name, COUNT(f.id) as count 
            FROM material_categories c
            LEFT JOIN material_files f ON c.id = f.category_id AND f.is_active = 1
            WHERE c.is_active = 1
            GROUP BY c.id, c.name
            ORDER BY count DESC
        ");
        
        return $stats;
    }
}

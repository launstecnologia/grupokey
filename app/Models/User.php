<?php

namespace App\Models;

use App\Core\Database;

class User
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function create($data)
    {
        $sql = "INSERT INTO users (email, name, password, status, created_at, updated_at) 
                VALUES (?, ?, ?, ?, NOW(), NOW())";
        
        $params = [
            $data['email'],
            $data['name'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['status'] ?? 'ACTIVE'
        ];
        
        $this->db->query($sql, $params);
        $userId = $this->db->lastInsertId();
        
        // Criar perfil se especificado e se a tabela existir
        if (isset($data['profile'])) {
            try {
                $this->createProfile($userId, $data['profile']);
            } catch (Exception $e) {
                // Ignorar erro se tabela não existir
            }
        }
        
        // Criar permissões se especificadas e se a tabela existir
        if (isset($data['permissions'])) {
            try {
                $this->createPermissions($userId, $data['permissions']);
            } catch (Exception $e) {
                // Ignorar erro se tabela não existir
            }
        }
        
        return $userId;
    }
    
    public function findByEmail($email)
    {
        $sql = "SELECT u.*, p.profile, perm.can_create, perm.can_edit, perm.can_approve, perm.can_export
                FROM users u
                LEFT JOIN user_profiles p ON u.id = p.user_id
                LEFT JOIN permissions perm ON u.id = perm.user_id
                WHERE u.email = ? AND u.status = 'ACTIVE'";
        
        return $this->db->fetch($sql, [$email]);
    }
    
    public function findById($id)
    {
        $sql = "SELECT u.*, 
                (SELECT GROUP_CONCAT(p.profile) FROM user_profiles p WHERE p.user_id = u.id) as profiles,
                perm.can_create, perm.can_edit, perm.can_approve, perm.can_export
                FROM users u
                LEFT JOIN permissions perm ON u.id = perm.user_id
                WHERE u.id = ?";
        
        return $this->db->fetch($sql, [$id]);
    }
    
    public function getAll($filters = [])
    {
        $sql = "SELECT u.*, p.profile, perm.can_create, perm.can_edit, perm.can_approve, perm.can_export
                FROM users u
                LEFT JOIN user_profiles p ON u.id = p.user_id
                LEFT JOIN permissions perm ON u.id = perm.user_id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND u.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['profile'])) {
            $sql .= " AND p.profile = ?";
            $params[] = $filters['profile'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (u.name LIKE ? OR u.email LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(u.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(u.created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        $sql .= " ORDER BY u.name ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function update($id, $data)
    {
        $fields = [];
        $params = [];
        
        if (isset($data['name'])) {
            $fields[] = "name = ?";
            $params[] = $data['name'];
        }
        
        if (isset($data['email'])) {
            $fields[] = "email = ?";
            $params[] = $data['email'];
        }
        
        if (isset($data['photo'])) {
            $fields[] = "photo = ?";
            $params[] = $data['photo'];
        }
        
        if (isset($data['password'])) {
            $fields[] = "password = ?";
            $params[] = $data['password']; // Já vem hashada do controller
        }
        
        if (isset($data['status'])) {
            $fields[] = "status = ?";
            $params[] = $data['status'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $fields[] = "updated_at = NOW()";
        $params[] = $id;
        
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $this->db->query($sql, $params);
        
        // Atualizar perfil se especificado
        if (isset($data['profile'])) {
            $this->updateProfile($id, $data['profile']);
        }
        
        // Atualizar permissões se especificadas
        if (isset($data['permissions'])) {
            $this->updatePermissions($id, $data['permissions']);
        }
        
        return true;
    }
    
    public function updatePassword($id, $password)
    {
        $sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
        $params = [password_hash($password, PASSWORD_DEFAULT), $id];
        
        return $this->db->query($sql, $params);
    }
    
    public function updateLastLogin($id)
    {
        $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function incrementFailedAttempts($id)
    {
        $sql = "UPDATE users SET failed_attempts = failed_attempts + 1 WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function resetFailedAttempts($id)
    {
        $sql = "UPDATE users SET failed_attempts = 0 WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function block($id)
    {
        $sql = "UPDATE users SET status = 'BLOCKED', updated_at = NOW() WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function unblock($id)
    {
        $sql = "UPDATE users SET status = 'ACTIVE', failed_attempts = 0, updated_at = NOW() WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function delete($id)
    {
        $sql = "DELETE FROM users WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    private function createProfile($userId, $profile)
    {
        // Verificar se a tabela user_profiles existe
        $sql = "INSERT INTO user_profiles (user_id, profile) VALUES (?, ?)";
        return $this->db->query($sql, [$userId, $profile]);
    }
    
    private function updateProfile($userId, $profile)
    {
        $sql = "UPDATE user_profiles SET profile = ? WHERE user_id = ?";
        return $this->db->query($sql, [$profile, $userId]);
    }
    
    private function createPermissions($userId, $permissions)
    {
        $sql = "INSERT INTO permissions (user_id, can_create, can_edit, can_approve, can_export) 
                VALUES (?, ?, ?, ?, ?)";
        
        $params = [
            $userId,
            $permissions['can_create'] ?? false,
            $permissions['can_edit'] ?? false,
            $permissions['can_approve'] ?? false,
            $permissions['can_export'] ?? false
        ];
        
        return $this->db->query($sql, $params);
    }
    
    private function updatePermissions($userId, $permissions)
    {
        $sql = "UPDATE permissions SET can_create = ?, can_edit = ?, can_approve = ?, can_export = ? 
                WHERE user_id = ?";
        
        $params = [
            $permissions['can_create'] ?? false,
            $permissions['can_edit'] ?? false,
            $permissions['can_approve'] ?? false,
            $permissions['can_export'] ?? false,
            $userId
        ];
        
        return $this->db->query($sql, $params);
    }
    
    public function getStats($filters = [])
    {
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $whereClause .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['search'])) {
            $whereClause .= " AND (name LIKE ? OR email LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        
        if (!empty($filters['date_from'])) {
            $whereClause .= " AND DATE(created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereClause .= " AND DATE(created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        $sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN status = 'ACTIVE' THEN 1 END) as active,
                    COUNT(CASE WHEN status = 'INACTIVE' THEN 1 END) as inactive,
                    COUNT(CASE WHEN status = 'BLOCKED' THEN 1 END) as blocked,
                    COUNT(CASE WHEN last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as active_last_30_days
                FROM users $whereClause";
        
        return $this->db->fetch($sql, $params);
    }
    
    public function updateStatus($id, $status)
    {
        $sql = "UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?";
        return $this->db->query($sql, [$status, $id]);
    }
    
    public function getUserActivities($userId)
    {
        $sql = "SELECT * FROM audit_logs 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT 20";
        
        return $this->db->fetchAll($sql, [$userId]);
    }
    
    public function getUserStats($userId)
    {
        $sql = "SELECT 
                    COUNT(*) as total_activities,
                    COUNT(CASE WHEN action = 'LOGIN' THEN 1 END) as logins,
                    COUNT(CASE WHEN action = 'LOGOUT' THEN 1 END) as logouts,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as activities_last_month
                FROM audit_logs 
                WHERE user_id = ?";
        
        return $this->db->fetch($sql, [$userId]);
    }
}

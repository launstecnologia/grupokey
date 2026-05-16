<?php

namespace App\Models;

use App\Core\Database;

class Banner
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureTable();
    }

    public function getAll(array $filters = []): array
    {
        $sql = "SELECT * FROM banners WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (title LIKE ? OR subtitle LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $sql .= " AND is_active = ?";
            $params[] = (int) $filters['is_active'];
        }

        $sql .= " ORDER BY sort_order ASC, id DESC";
        return $this->db->fetchAll($sql, $params);
    }

    public function getActiveForRepresentative(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM banners
             WHERE is_active = 1
             ORDER BY sort_order ASC, id DESC"
        );
    }

    public function findById(int $id)
    {
        return $this->db->fetch("SELECT * FROM banners WHERE id = ? LIMIT 1", [$id]);
    }

    public function create(array $data): int
    {
        $this->db->query(
            "INSERT INTO banners
            (title, subtitle, image_path, image_url, link_type, external_link, internal_target_type, internal_target_id, slide_duration_seconds, sort_order, is_active, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
            [
                $data['title'] ?? null,
                $data['subtitle'] ?? null,
                $data['image_path'] ?? null,
                $data['image_url'] ?? null,
                $data['link_type'] ?? 'none',
                $data['external_link'] ?? null,
                $data['internal_target_type'] ?? null,
                $data['internal_target_id'] ?? null,
                (int) ($data['slide_duration_seconds'] ?? 5),
                (int) ($data['sort_order'] ?? 0),
                (int) ($data['is_active'] ?? 1) === 1 ? 1 : 0,
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $this->db->query(
            "UPDATE banners SET
                title = ?,
                subtitle = ?,
                image_path = ?,
                image_url = ?,
                link_type = ?,
                external_link = ?,
                internal_target_type = ?,
                internal_target_id = ?,
                slide_duration_seconds = ?,
                sort_order = ?,
                is_active = ?,
                updated_at = NOW()
             WHERE id = ?",
            [
                $data['title'] ?? null,
                $data['subtitle'] ?? null,
                $data['image_path'] ?? null,
                $data['image_url'] ?? null,
                $data['link_type'] ?? 'none',
                $data['external_link'] ?? null,
                $data['internal_target_type'] ?? null,
                $data['internal_target_id'] ?? null,
                (int) ($data['slide_duration_seconds'] ?? 5),
                (int) ($data['sort_order'] ?? 0),
                (int) ($data['is_active'] ?? 1) === 1 ? 1 : 0,
                $id,
            ]
        );

        return true;
    }

    public function delete(int $id): bool
    {
        $row = $this->findById($id);
        if ($row && !empty($row['image_path']) && file_exists($row['image_path'])) {
            @unlink($row['image_path']);
        }

        $this->db->query("DELETE FROM banners WHERE id = ?", [$id]);
        return true;
    }

    private function ensureTable(): void
    {
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS banners (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NULL,
                subtitle VARCHAR(255) NULL,
                image_path VARCHAR(500) NULL,
                image_url VARCHAR(1000) NULL,
                link_type ENUM('none','external','internal') NOT NULL DEFAULT 'none',
                external_link VARCHAR(1000) NULL,
                internal_target_type VARCHAR(50) NULL,
                internal_target_id VARCHAR(50) NULL,
                slide_duration_seconds INT NOT NULL DEFAULT 5,
                sort_order INT NOT NULL DEFAULT 0,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_banners_active (is_active),
                INDEX idx_banners_sort (sort_order)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }
}


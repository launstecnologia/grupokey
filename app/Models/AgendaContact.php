<?php

namespace App\Models;

use App\Core\Database;

class AgendaContact
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create($data)
    {
        $sql = "INSERT INTO agenda_contacts (name, phone, email, notes, created_by_user_id) VALUES (?, ?, ?, ?, ?)";
        $params = [
            $data['name'],
            $data['phone'],
            $data['email'] ?? null,
            $data['notes'] ?? null,
            $data['created_by_user_id'] ?? $_SESSION['user_id'] ?? null
        ];
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE agenda_contacts SET name = ?, phone = ?, email = ?, notes = ?, updated_at = NOW() WHERE id = ?";
        $params = [
            $data['name'],
            $data['phone'],
            $data['email'] ?? null,
            $data['notes'] ?? null,
            $id
        ];
        return $this->db->query($sql, $params);
    }

    public function findById($id)
    {
        $sql = "SELECT * FROM agenda_contacts WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function getAll($filters = [])
    {
        $sql = "SELECT * FROM agenda_contacts WHERE 1=1";
        $params = [];
        if (!empty($filters['search'])) {
            $sql .= " AND (name LIKE ? OR phone LIKE ? OR email LIKE ?)";
            $term = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$term, $term, $term]);
        }
        $sql .= " ORDER BY name ASC";
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int) $filters['limit'];
        }
        return $this->db->fetchAll($sql, $params);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM agenda_contacts WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
}

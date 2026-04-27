<?php

namespace App\Models;

use App\Core\Database;

class CustomFieldDefinition
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll($entityType = null, $includeInactive = false)
    {
        $sql = "SELECT * FROM custom_field_definitions WHERE 1=1";
        $params = [];

        if ($entityType !== null) {
            $sql .= " AND entity_type = ?";
            $params[] = $entityType;
        }

        if (!$includeInactive) {
            $sql .= " AND is_active = 1";
        }

        $sql .= " ORDER BY entity_type ASC, sort_order ASC, id ASC";

        $rows = $this->db->fetchAll($sql, $params);
        foreach ($rows as &$row) {
            $row['options'] = $this->decodeOptions($row['options_json'] ?? null);
        }
        return $rows;
    }

    public function getByEntity($entityType)
    {
        return $this->getAll($entityType, false);
    }

    public function findById($id)
    {
        $row = $this->db->fetch(
            "SELECT * FROM custom_field_definitions WHERE id = ? LIMIT 1",
            [(int) $id]
        );

        if (!$row) {
            return null;
        }

        $row['options'] = $this->decodeOptions($row['options_json'] ?? null);
        return $row;
    }

    public function findByEntityAndKey($entityType, $fieldKey)
    {
        $row = $this->db->fetch(
            "SELECT * FROM custom_field_definitions WHERE entity_type = ? AND field_key = ? LIMIT 1",
            [(string) $entityType, (string) $fieldKey]
        );

        if (!$row) {
            return null;
        }

        $row['options'] = $this->decodeOptions($row['options_json'] ?? null);
        return $row;
    }

    public function create(array $data)
    {
        $this->db->query(
            "INSERT INTO custom_field_definitions
             (entity_type, field_key, label, field_type, is_required, placeholder, help_text, options_json, sort_order, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())",
            [
                $data['entity_type'],
                $data['field_key'],
                $data['label'],
                $data['field_type'],
                !empty($data['is_required']) ? 1 : 0,
                $data['placeholder'] ?? null,
                $data['help_text'] ?? null,
                $this->encodeOptions($data['options'] ?? []),
                (int) ($data['sort_order'] ?? 1),
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    public function update($id, array $data)
    {
        $this->db->query(
            "UPDATE custom_field_definitions
             SET entity_type = ?,
                 field_key = ?,
                 label = ?,
                 field_type = ?,
                 is_required = ?,
                 placeholder = ?,
                 help_text = ?,
                 options_json = ?,
                 sort_order = ?,
                 updated_at = NOW()
             WHERE id = ?",
            [
                $data['entity_type'],
                $data['field_key'],
                $data['label'],
                $data['field_type'],
                !empty($data['is_required']) ? 1 : 0,
                $data['placeholder'] ?? null,
                $data['help_text'] ?? null,
                $this->encodeOptions($data['options'] ?? []),
                (int) ($data['sort_order'] ?? 1),
                (int) $id
            ]
        );

        return true;
    }

    public function delete($id)
    {
        $this->db->query(
            "UPDATE custom_field_definitions
             SET is_active = 0, updated_at = NOW()
             WHERE id = ?",
            [(int) $id]
        );
        return true;
    }

    private function encodeOptions(array $options)
    {
        $clean = [];
        foreach ($options as $option) {
            $value = trim((string) $option);
            if ($value !== '') {
                $clean[] = $value;
            }
        }
        if (empty($clean)) {
            return null;
        }

        return json_encode(array_values(array_unique($clean)), JSON_UNESCAPED_UNICODE);
    }

    private function decodeOptions($optionsJson)
    {
        if ($optionsJson === null || $optionsJson === '') {
            return [];
        }
        $decoded = json_decode((string) $optionsJson, true);
        return is_array($decoded) ? $decoded : [];
    }
}

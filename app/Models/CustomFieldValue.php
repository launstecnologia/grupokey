<?php

namespace App\Models;

use App\Core\Database;

class CustomFieldValue
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getByEntity($entityType, $entityId)
    {
        $rows = $this->db->fetchAll(
            "SELECT v.field_id, d.field_key, v.value_text
             FROM custom_field_values v
             INNER JOIN custom_field_definitions d ON d.id = v.field_id
             WHERE v.entity_type = ? AND v.entity_id = ?",
            [$entityType, (int) $entityId]
        );

        $result = [];
        foreach ($rows as $row) {
            $key = (string) ($row['field_key'] ?? '');
            if ($key === '') {
                continue;
            }
            $result[$key] = $row['value_text'] ?? '';
        }
        return $result;
    }

    public function replaceByEntity($entityType, $entityId, array $fieldDefinitions, array $valuesByKey)
    {
        $entityId = (int) $entityId;
        if ($entityId <= 0) {
            return;
        }

        $this->db->beginTransaction();
        try {
            $this->db->query(
                "DELETE FROM custom_field_values WHERE entity_type = ? AND entity_id = ?",
                [$entityType, $entityId]
            );

            foreach ($fieldDefinitions as $definition) {
                $fieldId = (int) ($definition['id'] ?? 0);
                $fieldKey = (string) ($definition['field_key'] ?? '');
                if ($fieldId <= 0 || $fieldKey === '') {
                    continue;
                }

                $value = $valuesByKey[$fieldKey] ?? null;
                if ($value === null || $value === '') {
                    continue;
                }

                $this->db->query(
                    "INSERT INTO custom_field_values
                     (entity_type, entity_id, field_id, value_text, created_at, updated_at)
                     VALUES (?, ?, ?, ?, NOW(), NOW())",
                    [$entityType, $entityId, $fieldId, (string) $value]
                );
            }

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}

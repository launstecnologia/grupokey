<?php

namespace App\Models;

use App\Core\Database;

class DynamicProduct
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll($filters = [])
    {
        $sql = "SELECT p.*,
                       (SELECT COUNT(*) 
                        FROM dynamic_product_fields f 
                        WHERE f.product_id = p.id AND f.is_active = 1) AS total_fields
                FROM dynamic_products p
                WHERE p.is_active = 1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE ? OR p.slug LIKE ? OR p.description LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $sql .= " ORDER BY p.created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }

    public function findById($id)
    {
        $product = $this->db->fetch(
            "SELECT * FROM dynamic_products WHERE id = ? LIMIT 1",
            [$id]
        );

        if (!$product) {
            return null;
        }

        $fields = $this->db->fetchAll(
            "SELECT * 
             FROM dynamic_product_fields
             WHERE product_id = ? AND is_active = 1
             ORDER BY sort_order ASC, id ASC",
            [$id]
        );

        foreach ($fields as &$field) {
            $field['options'] = $this->db->fetchAll(
                "SELECT *
                 FROM dynamic_product_field_options
                 WHERE field_id = ?
                 ORDER BY sort_order ASC, id ASC",
                [$field['id']]
            );
        }

        $product['fields'] = $fields;
        return $product;
    }

    public function create($data, $fields)
    {
        $this->db->beginTransaction();

        try {
            $this->db->query(
                "INSERT INTO dynamic_products
                 (slug, name, description, has_api, api_provider, api_config_json, is_active, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())",
                [
                    $data['slug'],
                    $data['name'],
                    $data['description'] ?: null,
                    0,
                    null,
                    null,
                ]
            );

            $productId = (int) $this->db->lastInsertId();
            $this->replaceFields($productId, $fields);

            $this->db->commit();
            return $productId;
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function update($id, $data, $fields)
    {
        $this->db->beginTransaction();

        try {
            $this->db->query(
                "UPDATE dynamic_products
                 SET slug = ?,
                     name = ?,
                     description = ?,
                     has_api = ?,
                     api_provider = ?,
                     api_config_json = ?,
                     updated_at = NOW()
                 WHERE id = ?",
                [
                    $data['slug'],
                    $data['name'],
                    $data['description'] ?: null,
                    0,
                    null,
                    null,
                    $id,
                ]
            );

            $this->replaceFields((int) $id, $fields);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function delete($id)
    {
        return $this->db->query(
            "UPDATE dynamic_products SET is_active = 0, updated_at = NOW() WHERE id = ?",
            [$id]
        );
    }

    private function replaceFields($productId, $fields)
    {
        $existingFields = $this->db->fetchAll(
            "SELECT id FROM dynamic_product_fields WHERE product_id = ?",
            [$productId]
        );

        foreach ($existingFields as $field) {
            $this->db->query(
                "DELETE FROM dynamic_product_field_options WHERE field_id = ?",
                [$field['id']]
            );
        }

        $this->db->query(
            "DELETE FROM dynamic_product_fields WHERE product_id = ?",
            [$productId]
        );

        $sort = 1;
        foreach ($fields as $field) {
            $this->db->query(
                "INSERT INTO dynamic_product_fields
                 (product_id, field_key, label, field_type, is_required, placeholder, help_text, sort_order, is_active, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())",
                [
                    $productId,
                    $field['field_key'],
                    $field['label'],
                    $field['field_type'],
                    $field['is_required'] ? 1 : 0,
                    $field['placeholder'] ?: null,
                    $field['help_text'] ?: null,
                    $sort++,
                ]
            );

            $fieldId = (int) $this->db->lastInsertId();

            if (!empty($field['options'])) {
                $optionSort = 1;
                foreach ($field['options'] as $option) {
                    $this->db->query(
                        "INSERT INTO dynamic_product_field_options
                         (field_id, option_value, option_label, sort_order, created_at, updated_at)
                         VALUES (?, ?, ?, ?, NOW(), NOW())",
                        [
                            $fieldId,
                            $option['value'],
                            $option['label'],
                            $optionSort++,
                        ]
                    );
                }
            }
        }
    }
}

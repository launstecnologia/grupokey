<?php

namespace App\Models;

use App\Core\Database;

class EstablishmentDynamicProduct
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function replaceAllForEstablishment($establishmentId, $dynamicProductIds, $valuesByProduct)
    {
        $this->db->query(
            "DELETE FROM establishment_dynamic_products WHERE establishment_id = ?",
            [$establishmentId]
        );

        foreach ($dynamicProductIds as $productId) {
            $this->db->query(
                "INSERT INTO establishment_dynamic_products
                 (establishment_id, dynamic_product_id, created_at, updated_at)
                 VALUES (?, ?, NOW(), NOW())",
                [$establishmentId, $productId]
            );

            $pivotId = (int) $this->db->lastInsertId();
            $productValues = $valuesByProduct[$productId] ?? [];

            foreach ($productValues as $fieldKey => $fieldValue) {
                $this->db->query(
                    "INSERT INTO establishment_dynamic_product_values
                     (establishment_dynamic_product_id, field_key, field_value, created_at, updated_at)
                     VALUES (?, ?, ?, NOW(), NOW())",
                    [$pivotId, $fieldKey, $fieldValue]
                );
            }
        }
    }

    public function getByEstablishment($establishmentId)
    {
        $rows = $this->db->fetchAll(
            "SELECT
                edp.id AS pivot_id,
                edp.dynamic_product_id,
                dp.name AS product_name,
                dp.slug AS product_slug,
                f.field_key,
                f.label,
                f.field_type,
                f.is_required,
                f.placeholder,
                f.help_text,
                v.field_value
             FROM establishment_dynamic_products edp
             INNER JOIN dynamic_products dp ON dp.id = edp.dynamic_product_id
             INNER JOIN dynamic_product_fields f ON f.product_id = dp.id AND f.is_active = 1
             LEFT JOIN establishment_dynamic_product_values v
                ON v.establishment_dynamic_product_id = edp.id
               AND v.field_key = f.field_key
             WHERE edp.establishment_id = ?
             ORDER BY dp.name ASC, f.sort_order ASC, f.id ASC",
            [$establishmentId]
        );

        $grouped = [];
        foreach ($rows as $row) {
            $pid = (int) $row['dynamic_product_id'];
            if (!isset($grouped[$pid])) {
                $grouped[$pid] = [
                    'dynamic_product_id' => $pid,
                    'product_name' => $row['product_name'],
                    'product_slug' => $row['product_slug'],
                    'values' => [],
                    'fields' => []
                ];
            }

            $grouped[$pid]['values'][$row['field_key']] = $row['field_value'];
            $grouped[$pid]['fields'][] = [
                'field_key' => $row['field_key'],
                'label' => $row['label'],
                'field_type' => $row['field_type'],
                'is_required' => (int) $row['is_required'] === 1,
                'placeholder' => $row['placeholder'],
                'help_text' => $row['help_text']
            ];
        }

        return $grouped;
    }
}

